@php
    /** @var \ProgrammerNomad\SessionPilot\Services\SessionService $sessions */
    /** @var \ProgrammerNomad\SessionPilot\Services\ActivityLogService $logs */
    $sessions    = app(\ProgrammerNomad\SessionPilot\Services\SessionService::class);
    $logs        = app(\ProgrammerNomad\SessionPilot\Services\ActivityLogService::class);
    $onlineCount = $sessions->getOnlineUserCount();
    $activeCount = $sessions->getActiveSessionCount();
    $recentLogs  = $logs->getRecentLogs(5);
    $failedLogins = $logs->getFailedLoginCount(24);
@endphp

<div class="wrap sp-admin-wrap">
    <h1 class="sp-page-title">
        <span class="sp-logo">SessionPilot</span>
        <span class="sp-version">v{{ SESSIONPILOT_VERSION }}</span>
    </h1>

    {{-- Stat widgets --}}
    <div class="sp-widgets">
        <div class="sp-widget">
            <div class="sp-widget-value">{{ $onlineCount }}</div>
            <div class="sp-widget-label">Online Users</div>
            <div class="sp-widget-sub">Active in last 5 minutes</div>
        </div>

        <div class="sp-widget">
            <div class="sp-widget-value">{{ $activeCount }}</div>
            <div class="sp-widget-label">Active Sessions</div>
            <div class="sp-widget-sub">All non-expired sessions</div>
        </div>

        <div class="sp-widget sp-widget--{{ $failedLogins >= 5 ? 'warning' : 'neutral' }}">
            <div class="sp-widget-value">{{ $failedLogins }}</div>
            <div class="sp-widget-label">Failed Logins</div>
            <div class="sp-widget-sub">Last 24 hours</div>
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <h2>Recent Activity</h2>
            <a href="{{ admin_url('admin.php?page=sessionpilot-logs') }}" class="sp-link">View all</a>
        </div>
        <table class="sp-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Severity</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentLogs as $log)
                    <tr>
                        <td>{{ esc_html($log['timestamp']) }}</td>
                        <td>{{ $log['user_id'] ? esc_html(get_userdata($log['user_id'])?->user_login ?? $log['user_id']) : '-' }}</td>
                        <td><span class="sp-badge sp-badge--action">{{ esc_html($log['action_type']) }}</span></td>
                        <td><span class="sp-badge sp-badge--{{ esc_attr($log['severity']) }}">{{ esc_html($log['severity']) }}</span></td>
                        <td>{{ esc_html($log['ip']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="sp-empty">No activity logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
