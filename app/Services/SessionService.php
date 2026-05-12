<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Services;

use WP_User;
use WP_Session_Tokens;
use ProgrammerNomad\SessionPilot\Models\Session;
use ProgrammerNomad\SessionPilot\Models\Rule;
use ProgrammerNomad\SessionPilot\Support\IpHelper;

class SessionService
{
    public function __construct(
        private readonly DeviceService $deviceService
    ) {}

    /**
     * Called on wp_login hook. Records the new session.
     */
    public function onLogin(string $userLogin, WP_User $user): void
    {
        $token   = $this->getCurrentToken($user->ID);
        $ip      = IpHelper::getClientIp();
        $ua      = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
        $device  = $this->deviceService->resolveDevice($user->ID, $ua, $ip);
        $parsed  = $this->deviceService->parseUserAgent($ua);

        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'sp_sessions',
            [
                'user_id'         => $user->ID,
                'token'           => $token,
                'device_id'       => $device?->id,
                'ip_address'      => IpHelper::maybeAnonymize($ip),
                'user_agent'      => $ua,
                'browser'         => $parsed['browser'],
                'browser_version' => $parsed['browser_version'],
                'os'              => $parsed['os'],
                'device_type'     => $parsed['device_type'],
                'created_at'      => current_time('mysql'),
                'last_activity'   => current_time('mysql'),
            ],
            ['%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        $this->enforceRules($user);
    }

    /**
     * Called on wp_logout hook.
     */
    public function onLogout(int $userId): void
    {
        $token = $this->getCurrentToken($userId);
        if ( ! $token ) {
            return;
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'sp_sessions',
            ['logged_out_at' => current_time('mysql')],
            ['user_id' => $userId, 'token' => $token, 'logged_out_at' => null],
            ['%s'],
            ['%d', '%s', null]
        );
    }

    /**
     * Marks session logged out when auth cookie is cleared (covers all logout paths).
     */
    public function onAuthCookieCleared(): void
    {
        $userId = get_current_user_id();
        if ($userId) {
            $this->onLogout($userId);
        }
    }

    /**
     * WordPress heartbeat -update last_activity for the current session.
     */
    public function onHeartbeat(array $response, array $data): array
    {
        // Only process our own tick flag
        if ( empty($data['sp_session_tick']) ) {
            return $response;
        }

        if ( ! is_user_logged_in() ) {
            $response['sp_force_logout'] = true;
            $response['sp_logout_url']   = wp_login_url();
            return $response;
        }

        $userId = get_current_user_id();
        $token  = $this->getCurrentToken($userId);

        if ( ! $token ) {
            return $response;
        }

        global $wpdb;

        // Check if this session was force-killed
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT logged_out_at FROM {$wpdb->prefix}sp_sessions WHERE user_id = %d AND token = %s",
            $userId, $token
        ));

        if ( $row && $row->logged_out_at !== null ) {
            $response['sp_force_logout'] = true;
            $response['sp_logout_url']   = wp_login_url();
            return $response;
        }

        // Session still active - refresh last_activity
        $wpdb->update(
            $wpdb->prefix . 'sp_sessions',
            ['last_activity' => current_time('mysql')],
            ['user_id' => $userId, 'token' => $token],
            ['%s'],
            ['%d', '%s']
        );

        return $response;
    }

    /**
     * Force-logout a single session by DB row ID.
     */
    public function forceLogoutById(int $sessionId): bool
    {
        $session = Session::find($sessionId);
        if ( ! $session || ! $session->isActive() ) {
            return false;
        }

        return $this->terminateSession($session);
    }

    /**
     * Force-logout all active sessions for a user.
     */
    public function forceLogoutUser(int $userId, ?string $exceptToken = null): int
    {
        $sessions = Session::active()->where('user_id', $userId)->get();
        $count    = 0;

        foreach ($sessions as $session) {
            if ($exceptToken && $session->token === $exceptToken) {
                continue;
            }
            if ($this->terminateSession($session)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Force-logout all sessions site-wide.
     */
    public function forceLogoutAll(?string $exceptToken = null): int
    {
        $sessions = Session::active()->get();
        $count    = 0;

        foreach ($sessions as $session) {
            if ($exceptToken && $session->token === $exceptToken) {
                continue;
            }
            if ($this->terminateSession($session)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Force-logout all active sessions for a given role.
     */
    public function forceLogoutByRole(string $role): int
    {
        $users = get_users(['role' => $role, 'fields' => 'ID']);
        $count = 0;

        foreach ($users as $userId) {
            $count += $this->forceLogoutUser((int) $userId);
        }

        return $count;
    }

    /**
     * Get count of online users (active in last 5 minutes).
     */
    public function getOnlineUserCount(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}sp_sessions
                 WHERE logged_out_at IS NULL
                   AND (expires_at IS NULL OR expires_at > %s)
                   AND last_activity > %s",
                current_time('mysql'),
                date('Y-m-d H:i:s', strtotime('-5 minutes'))
            )
        );
    }

    /**
     * Get count of currently active sessions.
     */
    public function getActiveSessionCount(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}sp_sessions
                 WHERE logged_out_at IS NULL
                   AND (expires_at IS NULL OR expires_at > %s)",
                current_time('mysql')
            )
        );
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function terminateSession(Session $session): bool
    {
        // Destroy the WP session token
        $manager = WP_Session_Tokens::get_instance($session->user_id);
        $manager->destroy($session->token);

        // Mark logged out in our table
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'sp_sessions',
            ['logged_out_at' => current_time('mysql')],
            ['id' => $session->id],
            ['%s'],
            ['%d']
        );

        return true;
    }

    private function enforceRules(WP_User $user): void
    {
        // Per-user rule takes priority over role rule
        $rule = Rule::active()->forUser($user->ID)->first()
             ?? Rule::active()->forRole($user->roles[0] ?? '')->first();

        if ( ! $rule || $rule->max_sessions === 0 ) {
            return;
        }

        $activeSessions = Session::active()
            ->where('user_id', $user->ID)
            ->orderBy('created_at', 'asc')
            ->get();

        $overflow = $activeSessions->count() - $rule->max_sessions;

        if ($overflow <= 0) {
            return;
        }

        match ($rule->enforcement_mode) {
            'logout_oldest' => $activeSessions->take($overflow)->each(fn($s) => $this->terminateSession($s)),
            'logout_all'    => $activeSessions->each(fn($s) => $this->terminateSession($s)),
            'block_new'     => $this->terminateSession($activeSessions->last()), // newest = just logged in
            default         => null,
        };
    }

    private function getCurrentToken(int $userId): string
    {
        $cookie = $_COOKIE['wordpress_logged_in_' . COOKIEHASH] ?? '';
        if ( ! $cookie ) {
            return '';
        }

        $parts = explode('|', $cookie);
        return isset($parts[2]) ? $parts[2] : '';
    }
}
