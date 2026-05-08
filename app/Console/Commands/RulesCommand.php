<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Console\Commands;

use WP_CLI;
use WP_CLI_Command;
use ProgrammerNomad\SessionPilot\Models\Rule;

/**
 * Manage SessionPilot session rules.
 */
class RulesCommand extends WP_CLI_Command
{
    /**
     * Set a session rule for a role or user.
     *
     * ## OPTIONS
     *
     * [--role=<role>]
     * : Apply rule to a WordPress role (e.g. editor).
     *
     * [--user=<id>]
     * : Apply rule to a specific user (overrides role rule).
     *
     * [--max=<n>]
     * : Maximum concurrent sessions allowed. 0 = unlimited.
     *
     * [--mode=<mode>]
     * : Enforcement mode: block_new, logout_oldest, logout_all. Default: logout_oldest.
     *
     * [--idle=<seconds>]
     * : Idle timeout in seconds. 0 = disabled.
     *
     * ## EXAMPLES
     *
     *     wp sessionpilot rules set --role=editor --max=2 --mode=logout_oldest
     *     wp sessionpilot rules set --user=5 --max=1
     */
    public function set(array $args, array $assoc): void
    {
        $role   = sanitize_text_field($assoc['role'] ?? '');
        $userId = isset($assoc['user']) ? (int) $assoc['user'] : null;
        $max    = isset($assoc['max']) ? (int) $assoc['max'] : 0;
        $mode   = $assoc['mode'] ?? 'logout_oldest';
        $idle   = isset($assoc['idle']) ? (int) $assoc['idle'] : 0;

        if ( ! $role && ! $userId ) {
            WP_CLI::error('Provide --role=<role> or --user=<id>.');
        }

        $allowedModes = ['block_new', 'logout_oldest', 'logout_all'];
        if ( ! in_array($mode, $allowedModes, true) ) {
            WP_CLI::error('Invalid --mode. Use: block_new, logout_oldest, or logout_all.');
        }

        Rule::updateOrCreate(
            ['user_role' => $role ?: null, 'user_id' => $userId],
            ['max_sessions' => $max, 'enforcement_mode' => $mode, 'idle_timeout_seconds' => $idle, 'is_active' => true]
        );

        $target = $role ? "role '{$role}'" : "user {$userId}";
        WP_CLI::success("Rule set for {$target}: max={$max}, mode={$mode}, idle={$idle}s.");
    }

    /**
     * List all active rules.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format: table, json, csv. Default: table.
     *
     * ## EXAMPLES
     *
     *     wp sessionpilot rules list
     */
    public function list(array $args, array $assoc): void
    {
        $rules = Rule::all()->toArray();
        WP_CLI\Utils\format_items($assoc['format'] ?? 'table', $rules, ['id', 'user_role', 'user_id', 'max_sessions', 'enforcement_mode', 'idle_timeout_seconds', 'is_active']);
    }

    /**
     * Delete a rule by ID.
     *
     * ## OPTIONS
     *
     * <id>
     * : The rule ID to delete.
     *
     * ## EXAMPLES
     *
     *     wp sessionpilot rules delete 3
     */
    public function delete(array $args, array $assoc): void
    {
        $rule = Rule::find((int) ($args[0] ?? 0));
        if ( ! $rule ) {
            WP_CLI::error('Rule not found.');
        }
        $rule->delete();
        WP_CLI::success("Rule {$args[0]} deleted.");
    }
}
