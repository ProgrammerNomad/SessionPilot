<div class="wrap sp-admin-wrap" x-data="spRules()">
    <h1 class="sp-page-title">Session Rules</h1>

    {{-- Add/Edit Rule Form --}}
    <div class="sp-card sp-card--form">
        <h2>Add / Update Rule</h2>
        <form @submit.prevent="saveRule()">
            <div class="sp-form-row">
                <label>Apply to</label>
                <select x-model="form.target" class="sp-select">
                    <option value="role">Role</option>
                    <option value="user">Specific User</option>
                </select>
            </div>
            <div class="sp-form-row" x-show="form.target === 'role'">
                <label>Role</label>
                <select x-model="form.user_role" class="sp-select">
                    <option value="">— select role —</option>
                    @foreach (wp_roles()->get_names() as $slug => $name)
                        <option value="{{ esc_attr($slug) }}">{{ esc_html($name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sp-form-row" x-show="form.target === 'user'">
                <label>User ID</label>
                <input type="number" x-model.number="form.user_id" min="1" class="sp-input sp-input--sm" placeholder="WordPress user ID" />
            </div>
            <div class="sp-form-row">
                <label>Max sessions <small>(0 = unlimited)</small></label>
                <input type="number" x-model.number="form.max_sessions" min="0" class="sp-input sp-input--sm" />
            </div>
            <div class="sp-form-row">
                <label>When limit exceeded</label>
                <select x-model="form.enforcement_mode" class="sp-select">
                    <option value="logout_oldest">Logout oldest session</option>
                    <option value="block_new">Block new login</option>
                    <option value="logout_all">Logout all sessions</option>
                </select>
            </div>
            <div class="sp-form-row">
                <label>Idle timeout (seconds) <small>(0 = disabled)</small></label>
                <input type="number" x-model.number="form.idle_timeout_seconds" min="0" class="sp-input sp-input--sm" />
            </div>
            <div class="sp-form-actions">
                <button type="submit" class="sp-btn sp-btn--primary" :disabled="saving">Save Rule</button>
            </div>
        </form>
    </div>

    {{-- Rules Table --}}
    <div class="sp-card">
        <h2>Active Rules</h2>
        <table class="sp-table">
            <thead>
                <tr>
                    <th>Target</th>
                    <th>Max Sessions</th>
                    <th>Enforcement</th>
                    <th>Idle Timeout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="rule in rules" :key="rule.id">
                    <tr>
                        <td x-text="rule.user_role ? 'Role: ' + rule.user_role : 'User: ' + rule.user_id"></td>
                        <td x-text="rule.max_sessions === 0 ? 'Unlimited' : rule.max_sessions"></td>
                        <td x-text="rule.enforcement_mode"></td>
                        <td x-text="rule.idle_timeout_seconds === 0 ? 'Disabled' : rule.idle_timeout_seconds + 's'"></td>
                        <td>
                            <button class="sp-btn sp-btn--sm sp-btn--danger" @click="deleteRule(rule.id)">Delete</button>
                        </td>
                    </tr>
                </template>
                <template x-if="rules.length === 0">
                    <tr><td colspan="5" class="sp-empty">No rules configured. Default: unlimited sessions for all roles.</td></tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
function spRules() {
    return {
        rules: [],
        saving: false,
        form: { target: 'role', user_role: '', user_id: null, max_sessions: 2, enforcement_mode: 'logout_oldest', idle_timeout_seconds: 0 },

        async init() { await this.fetchRules(); },

        async fetchRules() {
            const res = await fetch(spData.restUrl + 'rules', { headers: { 'X-WP-Nonce': spData.restNonce } });
            this.rules = await res.json();
        },

        async saveRule() {
            this.saving = true;
            const body = {
                max_sessions: this.form.max_sessions,
                enforcement_mode: this.form.enforcement_mode,
                idle_timeout_seconds: this.form.idle_timeout_seconds,
            };
            if (this.form.target === 'role') body.user_role = this.form.user_role;
            else body.user_id = this.form.user_id;

            await fetch(spData.restUrl + 'rules', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': spData.restNonce },
                body: JSON.stringify(body),
            });
            this.saving = false;
            await this.fetchRules();
        },

        async deleteRule(id) {
            if (!confirm('Delete this rule?')) return;
            await fetch(spData.restUrl + 'rules/' + id, { method: 'DELETE', headers: { 'X-WP-Nonce': spData.restNonce } });
            await this.fetchRules();
        },
    };
}
</script>
