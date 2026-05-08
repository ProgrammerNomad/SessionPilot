<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Console\Commands;

use WP_CLI;
use WP_CLI_Command;
use ProgrammerNomad\SessionPilot\Models\Session;
use ProgrammerNomad\SessionPilot\Services\SessionService;

/**
 * Manage SessionPilot sessions.
 */
class SessionsCommand extends WP_CLI_Command
{
    /**
     * List sessions.
     *
     * ## OPTIONS
     *
     * [--user=<id>]
     * : Filter by WordPress user ID.
     *
     * [--active]
     * : Show only active (not logged out) sessions.
     *
     * [--format=<format>]
     * : Output format: table, json, csv. Default: table.
     *
     * ## EXAMPLES
     *
     *     wp sessionpilot sessions list
     *     wp sessionpilot sessions list --user=3 --active
     *     wp sessionpilot sessions list --format=csv
     *
     * @subcommand list
     */
    public function list(array $args, array $assoc): void
    {
        $query = Session::orderBy('created_at', 'desc');

        if ( ! empty($assoc['user']) ) {
            $query->where('user_id', (int) $assoc['user']);
        }

        if ( isset($assoc['active']) ) {
            $query->active();
        }

        $sessions = $query->get()->map(function (Session $s) {
            $user = get_userdata($s->user_id);
            return [
                'id'            => $s->id,
                'user_id'       => $s->user_id,
                'user_login'    => $user ? $user->user_login : 'N/A',
                'browser'       => trim(($s->browser ?? '') . ' ' . ($s->browser_version ?? '')),
                'os'            => $s->os ?? '',
                'ip_address'    => $s->ip_address ?? '',
                'last_activity' => $s->last_activity ?? '',
                'active'        => $s->isActive() ? 'yes' : 'no',
            ];
        })->toArray();

        WP_CLI\Utils\format_items($assoc['format'] ?? 'table', $sessions, ['id', 'user_id', 'user_login', 'browser', 'os', 'ip_address', 'last_activity', 'active']);
    }

    /**
     * Kill session(s).
     *
     * ## OPTIONS
     *
     * <target>
     * : Session ID, 'all', or 'oldest'.
     *
     * [--user=<id>]
     * : Scope to a specific user.
     *
     * ## EXAMPLES
     *
     *     wp sessionpilot sessions kill 12
     *     wp sessionpilot sessions kill all --user=3
     *     wp sessionpilot sessions kill oldest --user=7
     */
    public function kill(array $args, array $assoc): void
    {
        $target  = $args[0] ?? '';
        $userId  = isset($assoc['user']) ? (int) $assoc['user'] : null;
        $service = app(SessionService::class);

        if (is_numeric($target)) {
            $result = $service->forceLogoutById((int) $target);
            WP_CLI::success($result ? "Session {$target} terminated." : "Session not found or already inactive.");
            return;
        }

        if ($target === 'all') {
            $count = $userId ? $service->forceLogoutUser($userId) : $service->forceLogoutAll();
            WP_CLI::success("Terminated {$count} session(s).");
            return;
        }

        if ($target === 'oldest' && $userId) {
            $session = Session::active()->where('user_id', $userId)->orderBy('created_at', 'asc')->first();
            if ($session) {
                $service->forceLogoutById($session->id);
                WP_CLI::success("Oldest session for user {$userId} terminated.");
            } else {
                WP_CLI::warning("No active sessions found for user {$userId}.");
            }
            return;
        }

        WP_CLI::error("Invalid target. Use a session ID, 'all', or 'oldest --user=<id>'.");
    }
}
