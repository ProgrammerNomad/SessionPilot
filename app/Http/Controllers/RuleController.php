<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use ProgrammerNomad\SessionPilot\Models\Rule;

class RuleController
{
    public static function index(WP_REST_Request $request): WP_REST_Response
    {
        $rules = Rule::orderBy('created_at', 'desc')->get();
        return new WP_REST_Response($rules->toArray(), 200);
    }

    public static function store(WP_REST_Request $request): WP_REST_Response
    {
        $role    = sanitize_text_field($request->get_param('user_role') ?? '');
        $userId  = (int) $request->get_param('user_id');
        $max     = (int) ($request->get_param('max_sessions') ?? 0);
        $mode    = sanitize_text_field($request->get_param('enforcement_mode') ?? 'logout_oldest');
        $idle    = (int) ($request->get_param('idle_timeout_seconds') ?? 0);

        if ( ! $role && ! $userId ) {
            return new WP_REST_Response(['message' => 'Either user_role or user_id is required.'], 422);
        }

        $allowedModes = ['block_new', 'logout_oldest', 'logout_all'];
        if ( ! in_array($mode, $allowedModes, true) ) {
            return new WP_REST_Response(['message' => 'Invalid enforcement_mode.'], 422);
        }

        $rule = Rule::updateOrCreate(
            [
                'user_role' => $role ?: null,
                'user_id'   => $userId ?: null,
            ],
            [
                'max_sessions'         => $max,
                'enforcement_mode'     => $mode,
                'idle_timeout_seconds' => $idle,
                'is_active'            => true,
            ]
        );

        return new WP_REST_Response($rule->toArray(), 201);
    }

    public static function destroy(WP_REST_Request $request): WP_REST_Response
    {
        $rule = Rule::find((int) $request->get_param('id'));

        if ( ! $rule ) {
            return new WP_REST_Response(['message' => 'Rule not found.'], 404);
        }

        $rule->delete();
        return new WP_REST_Response(['message' => 'Rule deleted.'], 200);
    }
}
