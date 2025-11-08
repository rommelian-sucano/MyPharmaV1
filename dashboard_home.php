<script>
const METRICS_URL = 'api/metrics.php';
const ACTIVITY_URL = 'api/activity.php?limit=10';

function escapeHtml(str){return (str||'').replace(/[&<>"']/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));}

async function loadMetrics() {
    try {
        const res = await fetch(METRICS_URL, { cache: 'no-store' });
        if (!res.ok) throw new Error('Failed to fetch metrics');
        const data = await res.json();
        document.getElementById('totalMedicines').textContent = data.totalMedicines ?? 0;
        document.getElementById('pendingApprovals').textContent = data.pendingApprovals ?? 0;
        document.getElementById('totalUsers').textContent = data.totalUsers ?? 0;
        document.getElementById('totalPharmacies').textContent = data.totalPharmacies ?? 0;
        document.getElementById('lowStock').textContent = data.lowStock ?? 0;
        document.getElementById('expiringSoon').textContent = data.expiringSoon ?? 0;

        // Update chart if present
        const ctx = document.getElementById('searchesChart');
        if (ctx) {
            const labels = (data.searches7Days || []).map(d => d.day);
            const counts = (data.searches7Days || []).map(d => d.count);
            new Chart(ctx, {
                type: 'line',
                data: { labels, datasets: [{ label: 'Searches', data: counts, borderColor: '#0d6efd', tension: 0.3 }] },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
        }
    } catch (e) {
        console.error('Metrics error:', e);
    }
}

async function loadActivity() {
    try {
        const res = await fetch(ACTIVITY_URL, { cache: 'no-store' });
        if (!res.ok) throw new Error('Failed to fetch activity');
        const logs = await res.json();
        const container = document.getElementById('recentActivity');
        if (!Array.isArray(logs) || logs.length === 0) {
            container.innerHTML = '<p class="text-muted mb-0">No recent activity.</p>';
            return;
        }
        container.innerHTML = logs.map(l => `
            <div class="d-flex justify-content-between border-bottom py-2">
                <div>
                    <strong>${escapeHtml(l.action || '')}</strong>
                    <span class="text-muted">
                        on ${escapeHtml(l.entity_type || '')} #${Number(l.entity_id || 0)} by ${escapeHtml(l.user_name || 'System')}
                    </span>
                    ${l.details ? `<div class="text-muted small mt-1">${escapeHtml(l.details)}</div>` : ''}
                </div>
                <small class="text-muted">${escapeHtml((l.created_at || '').replace('T',' ').slice(0,16))}</small>
            </div>
        `).join('');
    } catch (e) {
        console.error('Activity error:', e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadMetrics();
    loadActivity();
    // Real-time polling every 15s (matches notification polling convention)
    setInterval(loadMetrics, 15000);
    setInterval(loadActivity, 15000);
});
</script>
```
```
    <div class="row g-3 mb-4" id="metricCards">
        <div class="col-md-3">
            <div class="card p-3">
                <div class="h2 mb-0" id="totalMedicines">0</div>
                <div class="text-muted">Total Medicines</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="h2 mb-0" id="pendingApprovals">0</div>
                <div class="text-muted">Pending Approvals</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="h2 mb-0" id="totalUsers">0</div>
                <div class="text-muted">Total Users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="h2 mb-0" id="totalPharmacies">0</div>
                <div class="text-muted">Verified Pharmacies</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card p-3">
                <div class="h2 mb-0 text-warning" id="lowStock">0</div>
                <div class="text-muted">Low Stock Items</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <div class="h2 mb-0 text-danger" id="expiringSoon">0</div>
                <div class="text-muted">Expiring Soon</div>
            </div>
        </div>
    </div>
    <div class="card p-3">
        <h2 class="h6 mb-3">Recent Activity</h2>
        <div id="recentActivity"></div>
    </div>
