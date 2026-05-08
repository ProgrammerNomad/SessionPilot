<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use ProgrammerNomad\SessionPilot\Models\Device;

class DeviceController
{
    public static function index(WP_REST_Request $request): WP_REST_Response
    {
        $userId = (int) $request->get_param('user_id');

        $query = Device::orderBy('last_seen', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $devices = $query->get()->map(function (Device $device) {
            $user = get_userdata($device->user_id);
            return array_merge($device->toArray(), [
                'user_login'   => $user ? $user->user_login : null,
                'display_name' => $user ? $user->display_name : null,
            ]);
        });

        return new WP_REST_Response($devices, 200);
    }
}
