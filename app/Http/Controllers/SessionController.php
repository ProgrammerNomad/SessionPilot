<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use ProgrammerNomad\SessionPilot\Models\Session;
use ProgrammerNomad\SessionPilot\Services\SessionService;

class SessionController
{
    public static function index(WP_REST_Request $request): WP_REST_Response
    {
        $userId = (int) $request->get_param('user_id');
        $active = $request->get_param('active') === 'true';
        $page   = max(1, (int) ($request->get_param('page') ?? 1));
        $perPage = 25;

        $query = Session::query()->orderBy('created_at', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($active) {
            $query->active();
        }

        $total    = $query->count();
        $sessions = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $data = $sessions->map(function (Session $session) {
            $user = get_userdata($session->user_id);
            return [
                'id'              => $session->id,
                'user_id'         => $session->user_id,
                'user_login'      => $user ? $user->user_login : null,
                'display_name'    => $user ? $user->display_name : null,
                'browser'         => $session->browser,
                'browser_version' => $session->browser_version,
                'os'              => $session->os,
                'device_type'     => $session->device_type,
                'ip_address'      => $session->ip_address,
                'last_activity'   => $session->last_activity?->toISOString(),
                'created_at'      => $session->created_at?->toISOString(),
                'is_active'       => $session->isActive(),
            ];
        });

        return new WP_REST_Response([
            'data'  => $data,
            'total' => $total,
            'page'  => $page,
        ], 200);
    }

    public static function destroy(WP_REST_Request $request): WP_REST_Response
    {
        $sessionId = (int) $request->get_param('id');
        $service   = app(SessionService::class);

        if ($service->forceLogoutById($sessionId)) {
            return new WP_REST_Response(['message' => 'Session terminated.'], 200);
        }

        return new WP_REST_Response(['message' => 'Session not found or already inactive.'], 404);
    }

    public static function kill(WP_REST_Request $request): WP_REST_Response
    {
        $service = app(SessionService::class);
        $userId  = (int) $request->get_param('user_id');
        $role    = sanitize_text_field($request->get_param('role') ?? '');
        $scope   = sanitize_text_field($request->get_param('scope') ?? 'all');

        if ($userId) {
            $count = $service->forceLogoutUser($userId);
            return new WP_REST_Response(['terminated' => $count], 200);
        }

        if ($role) {
            $count = $service->forceLogoutByRole($role);
            return new WP_REST_Response(['terminated' => $count], 200);
        }

        $count = $service->forceLogoutAll();
        return new WP_REST_Response(['terminated' => $count], 200);
    }
}
