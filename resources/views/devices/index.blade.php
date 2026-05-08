<div class="wrap sp-admin-wrap" x-data="spDevices()">
    <h1 class="sp-page-title">Devices</h1>

    <div class="sp-card">
        <table class="sp-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Device</th>
                    <th>Browser</th>
                    <th>OS</th>
                    <th>Type</th>
                    <th>Last IP</th>
                    <th>Last Seen</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr><td colspan="7" class="sp-empty">Loading…</td></tr>
                </template>
                <template x-for="d in devices" :key="d.id">
                    <tr>
                        <td>
                            <strong x-text="d.display_name || d.user_login"></strong><br>
                            <small x-text="d.user_login"></small>
                        </td>
                        <td x-text="d.device_name"></td>
                        <td x-text="(d.browser || '') + (d.browser_version ? ' ' + d.browser_version : '')"></td>
                        <td x-text="d.os"></td>
                        <td x-text="d.device_type"></td>
                        <td x-text="d.last_ip || '—'"></td>
                        <td x-text="d.last_seen || '—'"></td>
                    </tr>
                </template>
                <template x-if="!loading && devices.length === 0">
                    <tr><td colspan="7" class="sp-empty">No devices tracked yet.</td></tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
function spDevices() {
    return {
        devices: [],
        loading: true,

        async init() {
            const res = await fetch(spData.restUrl + 'devices', { headers: { 'X-WP-Nonce': spData.restNonce } });
            this.devices = await res.json();
            this.loading = false;
        }
    };
}
</script>
