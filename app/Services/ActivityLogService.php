<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Services;

use WP_User;
use ProgrammerNomad\SessionPilot\Support\IpHelper;

class ActivityLogService
{
    private function insert(
        ?int $userId,
        string $actionType,
        string $description,
        string $severity = 'info'
    ): void {
        global $wpdb;

        // Guard: skip silently if table doesn't exist yet (e.g. during install/uninstall)
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}sp_activity_logs'" ) === null ) {
            return;
        }

        $wpdb->insert(
            $wpdb->prefix . 'sp_activity_logs',
            [
                'user_id'     => $userId,
                'action_type' => $actionType,
                'description' => $description,
                'ip'          => IpHelper::maybeAnonymize(IpHelper::getClientIp()),
                'user_agent'  => isset($_SERVER['HTTP_USER_AGENT'])
                    ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']))
                    : '',
                'severity'    => $severity,
                'timestamp'   => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    public function logLogin(string $userLogin, WP_User $user): void
    {
        $this->insert(
            $user->ID,
            'login',
            sprintf('User "%s" logged in.', esc_html($userLogin))
        );
    }

    public function logLoginFailed(string $userLogin): void
    {
        $this->insert(
            null,
            'login_failed',
            sprintf('Failed login attempt for username "%s".', esc_html($userLogin)),
            'warning'
        );

        $this->maybeAlertOnFailures($userLogin);
    }

    public function logLogout(int $userId): void
    {
        $user = get_userdata($userId);
        $this->insert(
            $userId,
            'logout',
            sprintf('User "%s" logged out.', $user ? esc_html($user->user_login) : $userId)
        );
    }

    public function logRoleChange(int $userId, string $newRole, array $oldRoles): void
    {
        $user = get_userdata($userId);
        $from = implode(', ', $oldRoles);
        $this->insert(
            get_current_user_id() ?: $userId,
            'role_change',
            sprintf('User "%s" role changed from "%s" to "%s".', $user ? $user->user_login : $userId, esc_html($from), esc_html($newRole)),
            'warning'
        );
    }

    public function logPluginActivated(string $plugin): void
    {
        $this->insert(
            get_current_user_id(),
            'plugin_activated',
            sprintf('Plugin activated: %s', esc_html($plugin))
        );
    }

    public function logPluginDeactivated(string $plugin): void
    {
        $this->insert(
            get_current_user_id(),
            'plugin_deactivated',
            sprintf('Plugin deactivated: %s', esc_html($plugin))
        );
    }

    public function logThemeSwitch(string $newThemeName): void
    {
        $this->insert(
            get_current_user_id(),
            'theme_switch',
            sprintf('Theme switched to: %s', esc_html($newThemeName))
        );
    }

    public function logSettingChange(string $option, mixed $oldValue, mixed $newValue): void
    {
        // Only log actual WP settings, not transients or internal options
        $ignored = ['_transient_', '_site_transient_', 'cron', 'session_tokens'];
        foreach ($ignored as $prefix) {
            if (str_contains($option, $prefix)) {
                return;
            }
        }

        $this->insert(
            get_current_user_id(),
            'setting_change',
            sprintf('WordPress option "%s" was changed.', esc_html($option))
        );
    }

    public function logPasswordReset(WP_User $user): void
    {
        $this->insert(
            $user->ID,
            'password_reset',
            sprintf('Password was reset for user "%s".', esc_html($user->user_login)),
            'warning'
        );
    }

    public function getRecentLogs(int $limit = 20): array
    {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sp_activity_logs ORDER BY timestamp DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
    }

    public function getFailedLoginCount(int $hours = 24): int
    {
        global $wpdb;
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}sp_activity_logs
                 WHERE action_type = 'login_failed' AND timestamp > %s",
                date('Y-m-d H:i:s', strtotime("-{$hours} hours"))
            )
        );
    }

    private function maybeAlertOnFailures(string $userLogin): void
    {
        $threshold = (int) $this->getSetting('login_failure_threshold', 5);
        $enabled   = (bool) $this->getSetting('alert_on_login_failures', 1);

        if ( ! $enabled ) {
            return;
        }

        $recent = $this->getFailedLoginCount(1); // last hour
        if ($recent >= $threshold) {
            $email = $this->getSetting('alert_email') ?: get_option('admin_email');
            wp_mail(
                $email,
                '[SessionPilot] Multiple failed login attempts detected',
                sprintf(
                    "%d failed login attempts in the last hour on %s.\n\nLast attempt was for username: %s",
                    $recent,
                    get_bloginfo('url'),
                    $userLogin
                )
            );
        }
    }

    private function getSetting(string $key, mixed $default = ''): mixed
    {
        global $wpdb;
        $val = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT setting_value FROM {$wpdb->prefix}sp_settings WHERE setting_key = %s",
                $key
            )
        );

        return $val ?? $default;
    }
}
