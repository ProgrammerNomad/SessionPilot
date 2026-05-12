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
        $format     = sanitize_text_field($request->get_param('format') ?? '');
        $page       = max(1, (int) ($request->get_param('page') ?? 1));
        $perPage    = ($format === 'csv') ? 10000 : 50;

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

        if ($format === 'csv') {
            return self::streamCsv($logs);
        }

        return new WP_REST_Response([
            'data'  => $logs,
            'total' => $total,
            'page'  => $page,
        ], 200);
    }

    private static function streamCsv(array $rows): never
    {
        $filename = 'sessionpilot-logs-' . gmdate('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // Header row
        fputcsv($out, ['ID', 'Timestamp', 'User ID', 'Action', 'Description', 'IP', 'User Agent', 'Severity']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['id'],
                $row['timestamp'],
                $row['user_id'] ?? '',
                $row['action_type'],
                self::csvSafe($row['description'] ?? ''),
                $row['ip'] ?? '',
                self::csvSafe($row['user_agent'] ?? ''),
                $row['severity'],
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Prevent CSV injection by prefixing formula-like values with a tab.
     * Affects fields that could contain user-controlled data.
     */
    private static function csvSafe(string $value): string
    {
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "\t" . $value;
        }
        return $value;
    }
}
