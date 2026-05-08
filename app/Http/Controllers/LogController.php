<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;

class LogController
{
    public static function index(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;

        $userId     = (int) $request->get_param('user_id');
        $actionType = sanitize_text_field($request->get_param('action') ?? '');
        $severity   = sanitize_text_field($request->get_param('severity') ?? '');
        $since      = sanitize_text_field($request->get_param('since') ?? '');
        $page       = max(1, (int) ($request->get_param('page') ?? 1));
        $perPage    = 50;

        $where  = ['1=1'];
        $params = [];

        if ($userId) {
            $where[]  = 'user_id = %d';
            $params[] = $userId;
        }

        if ($actionType) {
            $where[]  = 'action_type = %s';
            $params[] = $actionType;
        }

        if ($severity) {
            $where[]  = 'severity = %s';
            $params[] = $severity;
        }

        if ($since) {
            $where[]  = 'timestamp > %s';
            $params[] = $since;
        }

        $whereClause = implode(' AND ', $where);
        $offset      = ($page - 1) * $perPage;

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}sp_activity_logs WHERE {$whereClause}",
            ...$params
        ));

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sp_activity_logs WHERE {$whereClause} ORDER BY timestamp DESC LIMIT %d OFFSET %d",
            ...array_merge($params, [$perPage, $offset])
        ), ARRAY_A);

        return new WP_REST_Response([
            'data'  => $logs,
            'total' => $total,
            'page'  => $page,
        ], 200);
    }
}
