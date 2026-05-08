<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Console\Commands;

use WP_CLI;
use WP_CLI_Command;
use ProgrammerNomad\SessionPilot\Models\Device;

/**
 * Manage SessionPilot tracked devices.
 */
class DevicesCommand extends WP_CLI_Command
{
    /**
     * List known devices.
     *
     * ## OPTIONS
     *
     * [--user=<id>]
     * : Filter by user ID.
     *
     * [--format=<format>]
     * : Output format: table, json, csv. Default: table.
     *
     * ## EXAMPLES
     *
     *     wp sessionpilot devices list
     *     wp sessionpilot devices list --user=3
     */
    public function list(array $args, array $assoc): void
    {
        $query = Device::orderBy('last_seen', 'desc');

        if ( ! empty($assoc['user']) ) {
            $query->where('user_id', (int) $assoc['user']);
        }

        $devices = $query->get()->map(function (Device $d) {
            $user = get_userdata($d->user_id);
            return [
                'id'          => $d->id,
                'user_id'     => $d->user_id,
                'user_login'  => $user ? $user->user_login : 'N/A',
                'device_name' => $d->device_name,
                'browser'     => trim(($d->browser ?? '') . ' ' . ($d->browser_version ?? '')),
                'os'          => $d->os,
                'device_type' => $d->device_type,
                'last_ip'     => $d->last_ip,
                'last_seen'   => $d->last_seen,
            ];
        })->toArray();

        WP_CLI\Utils\format_items($assoc['format'] ?? 'table', $devices, ['id', 'user_id', 'user_login', 'device_name', 'browser', 'os', 'device_type', 'last_ip', 'last_seen']);
    }
}
