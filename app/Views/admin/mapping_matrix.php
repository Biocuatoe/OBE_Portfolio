<?php /* app/Views/admin/mapping_matrix.php */ ?>

<div class="card">
    <div class="card-header">
        <div class="section-title-group">
            <span class="course-badge"><?= htmlspecialchars($course['code']) ?></span>
            <h3 class="section-title">Ma trận ánh xạ CLO → PLO</h3>
        </div>
        <div class="header-right">
            <div class="matrix-legend">
                <span class="legend-dot" style="background:var(--accent)"></span><span class="legend-text">Có ánh xạ (nhập 0-100%)</span>
                <span class="legend-dot" style="background:var(--surface-2)"></span><span class="legend-text">Không ánh xạ (để trống)</span>
            </div>
            <button id="exportCsv" class="btn btn-secondary btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Xuất CSV
            </button>
        </div>
    </div>

    <p class="matrix-help">Nhập trọng số % đóng góp của từng CLO vào mỗi PLO. Để trống hoặc nhập 0 để xóa ánh xạ. Thay đổi được lưu tự động.</p>

    <!-- Weight warning banners -->
    <div class="matrix-warnings" id="matrixWarnings">
        <div class="warning-banner" id="warningRowSum" style="display:none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>Tổng trọng số theo CLO vượt quá 100% — kiểm tra các dòng được đánh dấu đỏ.</span>
        </div>
        <div class="warning-banner" id="warningColSum" style="display:none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>Tổng theo PLO vượt 100% — kiểm tra các cột được đánh dấu đỏ.</span>
        </div>
    </div>

    <div class="table-wrap">
        <table class="mapping-table">
            <thead>
                <tr>
                    <th class="mth-clo">CLO / PLO</th>
                    <?php foreach ($plos as $plo): ?>
                    <th class="mth-plo">
                        <div class="plo-th-content">
                            <span class="plo-th-code"><?= htmlspecialchars($plo['code']) ?></span>
                            <span class="plo-th-cat"><?= htmlspecialchars($plo['category'] ?? '') ?></span>
                        </div>
                    </th>
                    <?php endforeach; ?>
                    <th class="mth-total">Tổng %</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clos as $clo): ?>
                <tr>
                    <td class="mtd-clo">
                        <div class="clo-td">
                            <span class="clo-code"><?= htmlspecialchars($clo['code']) ?></span>
                            <span class="clo-td-desc"><?= htmlspecialchars(mb_substr($clo['description'], 0, 45)) ?>...</span>
                            <?php if ($clo['bloom_level']): ?>
                            <span class="clo-bloom">B<?= $clo['bloom_level'] ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php $rowTotal = 0; ?>
                    <?php foreach ($plos as $plo): ?>
                    <?php $current = $matrix[$clo['id']][$plo['id']] ?? ''; $rowTotal += (float)$current; ?>
                    <td class="mtd-weight">
                        <input
                            type="number"
                            class="weight-input"
                            data-clo="<?= $clo['id'] ?>"
                            data-plo="<?= $plo['id'] ?>"
                            value="<?= $current !== '' ? number_format((float)$current, 0) : '' ?>"
                            min="0" max="100" step="5"
                            placeholder="—"
                            aria-label="<?= htmlspecialchars($clo['code']) ?> → <?= htmlspecialchars($plo['code']) ?>"
                        >
                    </td>
                    <?php endforeach; ?>
                    <td class="mtd-row-total">
                        <span class="row-total <?= $rowTotal > 0 ? 'has-value' : '' ?>" id="rowtotal-<?= $clo['id'] ?>">
                            <?= $rowTotal > 0 ? number_format($rowTotal, 0) . '%' : '—' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="mtd-clo"><strong>Tổng theo PLO</strong></td>
                    <?php foreach ($plos as $plo): ?>
                    <?php
                        $colTotal = 0;
                        foreach ($clos as $clo) { $colTotal += (float)($matrix[$clo['id']][$plo['id']] ?? 0); }
                    ?>
                    <td class="mtd-col-total" id="coltotal-<?= $plo['id'] ?>">
                        <span class="col-total <?= $colTotal > 0 ? 'has-value' : '' ?>">
                            <?= $colTotal > 0 ? number_format($colTotal, 0) . '%' : '—' ?>
                        </span>
                    </td>
                    <?php endforeach; ?>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="matrix-status" id="matrixStatus">
        <span class="status-dot saved"></span>
        <span id="matrixStatusText">Tất cả thay đổi đã được lưu</span>
    </div>
</div>

<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">

<style>
.header-right { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
.matrix-help { font-size:12px; color:#94a3b8; margin-bottom:12px; }

/* Warning banners */
.matrix-warnings { display:flex; flex-direction:column; gap:6px; margin-bottom:12px; }
.warning-banner {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(239,68,68,0.06);
    border: 1px solid rgba(239,68,68,0.2);
    border-radius: 6px;
    font-size: 12px;
    color: #ef4444;
}
.warning-banner svg { flex-shrink:0; color: #ef4444; }

.mapping-table { width:100%; border-collapse:collapse; white-space:nowrap; }
.mapping-table th,
.mapping-table td { border:1px solid #e2e8f0; padding:8px; }

.mth-clo { min-width:200px; text-align:left; background:#f8fafc; font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#94a3b8; }
.mth-plo { min-width:100px; text-align:center; background:#f8fafc; }
.mth-total { min-width:80px; text-align:center; background:#f8fafc; font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#94a3b8; }

.plo-th-content { display:flex; flex-direction:column; align-items:center; gap:2px; }
.plo-th-code { font-family:'Lexend Deca',sans-serif; font-weight:700; font-size:13px; color:#4f46e5; }
.plo-th-cat { font-size:10px; color:#94a3b8; }

.mtd-clo { background:#f8fafc; padding:10px 12px; }
.clo-td { display:flex; flex-direction:column; gap:2px; }
.clo-td-desc { font-size:11px; color:#94a3b8; white-space:normal; max-width:180px; }

.mtd-weight { text-align:center; padding:4px; }
.weight-input {
    width:72px; background:#ffffff; border:1px solid #e2e8f0;
    border-radius:6px; color:#0f172a;
    padding:6px 4px; text-align:center;
    font-family:'Lexend Deca',sans-serif; font-weight:600; font-size:13px;
    outline:none; transition:all 0.2s ease;
}
.weight-input:focus { border-color:#4f46e5; box-shadow:0 0 0 2px rgba(79,70,229,0.08); }
.weight-input.has-value { background:rgba(99,102,241,0.08); border-color:rgba(99,102,241,0.3); color:#4f46e5; }
.weight-input.saving { border-color:#f59e0b; }
.weight-input.saved  { border-color:#10b981; animation:flashGreen .5s; }
.weight-input.row-over  { border-color:#ef4444; background:rgba(239,68,68,0.05); color:#ef4444; }

@keyframes flashGreen { 0%{background:rgba(16,185,129,0.15)} 100%{background:rgba(79,70,229,0.08)} }

.mtd-row-total, .mtd-col-total { text-align:center; font-size:12px; }
.row-total, .col-total { color:#94a3b8; font-weight:500; }
.row-total.has-value, .col-total.has-value { color:#0f172a; font-weight:700; font-family:'Lexend Deca',sans-serif; }
.row-total.over-limit { color:#ef4444; font-weight:800; }
.row-total.has-value.over-limit { background:rgba(239,68,68,0.1); color:#ef4444; border-radius:4px; padding:2px 6px; }
.col-total.over-limit { color:#ef4444; font-weight:800; }

tfoot td { background:#f8fafc; font-size:12px; font-weight:600; padding:10px 8px; }

.matrix-status { display:flex; align-items:center; gap:8px; margin-top:12px; font-size:12px; color:#94a3b8; }
.status-dot { width:6px; height:6px; border-radius:50%; }
.status-dot.saved   { background:#10b981; }
.status-dot.saving  { background:#f59e0b; animation:pulse 1s infinite; }
.status-dot.saved-flash { background:#10b981; animation:pulseDot .8s ease-out; }
@keyframes pulseDot { 0%{opacity:1; transform:scale(1)} 50%{opacity:.5; transform:scale(1.5)} 100%{opacity:1; transform:scale(1)} }
@keyframes pulse { to { opacity:.5 } }

.saved-ack {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #10b981;
    font-weight: 600;
    transition: opacity .4s;
}
</style>

<script>
(function() {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const courseCode = <?= json_encode($course['code'] ?? 'matrix') ?>;

    // ── Debounce utility ─────────────────────────────────────────────
    function debounce(fn, ms) {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    // ── Row total validation ─────────────────────────────────────────
    function updateRowTotal(cloId) {
        let total = 0;
        document.querySelectorAll(`.weight-input[data-clo="${cloId}"]`).forEach(inp => {
            total += parseFloat(inp.value) || 0;
        });
        const el = document.getElementById(`rowtotal-${cloId}`);
        if (el) {
            el.textContent = total > 0 ? total + '%' : '—';
            el.className = `row-total ${total > 0 ? 'has-value' : ''} ${total > 100 ? 'over-limit' : ''}`;
        }
        document.querySelectorAll(`.weight-input[data-clo="${cloId}"]`).forEach(inp => {
            if (total > 100) {
                inp.classList.add('row-over');
            } else {
                inp.classList.remove('row-over');
            }
        });
        checkWarnings();
    }

    // ── Column total validation ─────────────────────────────────────
    function updateColTotal(ploId) {
        let total = 0;
        document.querySelectorAll(`.weight-input[data-plo="${ploId}"]`).forEach(inp => {
            total += parseFloat(inp.value) || 0;
        });
        const el = document.getElementById(`coltotal-${ploId}`);
        if (el) {
            const span = el.querySelector('span');
            if (span) {
                span.textContent = total > 0 ? total + '%' : '—';
                span.className = `col-total ${total > 0 ? 'has-value' : ''} ${total > 100 ? 'over-limit' : ''}`;
            }
        }
        checkWarnings();
    }

    // ── Global warning check ─────────────────────────────────────────
    function checkWarnings() {
        let rowOver = false, colOver = false;
        document.querySelectorAll('.row-total').forEach(el => {
            if (el.classList.contains('over-limit')) { rowOver = true; }
        });
        document.querySelectorAll('.col-total').forEach(el => {
            if (el.classList.contains('over-limit')) { colOver = true; }
        });
        const warnRow = document.getElementById('warningRowSum');
        const warnCol = document.getElementById('warningColSum');
        if (warnRow) warnRow.style.display = rowOver ? 'flex' : 'none';
        if (warnCol) warnCol.style.display = colOver ? 'flex' : 'none';
    }

    // ── Status bar helpers ───────────────────────────────────────────
    function setStatusSaving() {
        const dot = document.querySelector('.status-dot');
        const txt = document.getElementById('matrixStatusText');
        if (dot) dot.className = 'status-dot saving';
        if (txt) txt.textContent = 'Đang lưu...';
    }

    function setStatusSaved() {
        const dot = document.querySelector('.status-dot');
        const txt = document.getElementById('matrixStatusText');
        if (dot) dot.className = 'status-dot saved-flash';
        if (txt) {
            txt.innerHTML = '<span class="saved-ack"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="12" height="12"><polyline points="20 6 9 17 4 12"/></svg> Đã lưu</span>';
            setTimeout(() => {
                if (txt) {
                    txt.innerHTML = 'Tất cả thay đổi đã được lưu';
                    txt.style.color = '';
                }
                if (dot) dot.className = 'status-dot saved';
            }, 1800);
        }
    }

    function setStatusError(msg) {
        const dot = document.querySelector('.status-dot');
        const txt = document.getElementById('matrixStatusText');
        if (dot) dot.className = 'status-dot saved';
        if (txt) txt.textContent = msg;
        window.Toast?.error(msg);
    }

    // ── Save mapping ─────────────────────────────────────────────────
    async function saveMapping(input) {
        const cloId  = input.dataset.clo;
        const ploId  = input.dataset.plo;
        const weight = parseFloat(input.value) || 0;

        setStatusSaving();
        input.classList.add('saving');

        try {
            const res = await fetch('/api/mapping/save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ clo_id: +cloId, plo_id: +ploId, weight, _token: CSRF }),
            });
            const data = await res.json();
            if (data.status === 'success') {
                input.classList.remove('saving');
                input.classList.add('saved');
                input.classList.toggle('has-value', weight > 0);
                setStatusSaved();
                setTimeout(() => input.classList.remove('saved'), 1000);
            } else {
                throw new Error(data.error || 'Lỗi không xác định');
            }
        } catch(e) {
            input.classList.remove('saving');
            setStatusError('Lỗi lưu: ' + e.message);
        }
    }

    const debouncedSave = debounce(saveMapping, 700);

    // ── Attach input listeners ───────────────────────────────────────
    document.querySelectorAll('.weight-input').forEach(inp => {
        if (inp.value) inp.classList.add('has-value');

        inp.addEventListener('input', () => {
            updateRowTotal(inp.dataset.clo);
            updateColTotal(inp.dataset.plo);
            debouncedSave(inp);
        });
        inp.addEventListener('focus', () => inp.select());
    });

    // Initial warning check on page load
    checkWarnings();

    // ── CSV Export ───────────────────────────────────────────────────
    document.getElementById('exportCsv')?.addEventListener('click', function() {
        const rows = [['CLO/PLO']];
        const ploCodes = Array.from(document.querySelectorAll('.mth-plo .plo-th-code')).map(el => el.textContent.trim());
        rows[0].push(...ploCodes, 'Total');

        document.querySelectorAll('.mapping-table tbody tr').forEach((tr, idx) => {
            const cloCode = tr.querySelector('.clo-code')?.textContent?.trim() || `CLO${idx + 1}`;
            const cells = [cloCode];
            tr.querySelectorAll('.weight-input').forEach(inp => {
                cells.push(inp.value || '0');
            });
            const totalCell = tr.querySelector('.row-total');
            cells.push(totalCell?.textContent?.replace('%', '') || '0');
            rows.push(cells);
        });

        const ploTotalRow = ['PLO Total'];
        document.querySelectorAll('.col-total').forEach(el => {
            ploTotalRow.push(el.textContent?.replace('%', '') || '0');
        });
        ploTotalRow.push('');
        rows.push(ploTotalRow);

        const date = new Date().toISOString().slice(0, 10);
        const filename = `matrix_${courseCode}_${date}.csv`;
        const csvContent = rows.map(r => r.map(cell => {
            const s = String(cell);
            return s.includes(',') || s.includes('"') || s.includes('\n')
                ? `"${s.replace(/"/g, '""')}"`
                : s;
        }).join(',')).join('\r\n');

        const BOM = '\uFEFF';
        const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);

        window.Toast?.success(`Đã xuất ${filename}`);
    });
})();
</script>
