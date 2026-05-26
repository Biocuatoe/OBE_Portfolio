<?php /* app/Views/admin/mapping_matrix.php */ ?>

<div class="section-card">
    <div class="section-header">
        <div class="section-title-group">
            <span class="course-badge"><?= htmlspecialchars($course['code']) ?></span>
            <h3 class="section-title">Ma trận ánh xạ CLO → PLO</h3>
        </div>
        <div class="matrix-legend">
            <span class="legend-dot" style="background:var(--accent)"></span><span class="legend-text">Có ánh xạ (nhập 0-100%)</span>
            <span class="legend-dot" style="background:var(--surface-2)"></span><span class="legend-text">Không ánh xạ (để trống)</span>
        </div>
    </div>

    <p class="matrix-help">Nhập trọng số % đóng góp của từng CLO vào mỗi PLO. Để trống hoặc nhập 0 để xóa ánh xạ. Thay đổi được lưu tự động.</p>

    <div class="matrix-scroll">
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
.matrix-help { font-size:12px; color:var(--text-muted); margin-bottom:16px; }
.matrix-scroll { overflow-x:auto; }
.matrix-legend { display:flex; align-items:center; gap:10px; }

.mapping-table { width:100%; border-collapse:collapse; white-space:nowrap; }
.mapping-table th,
.mapping-table td { border:1px solid var(--surface-2); padding:8px; }

.mth-clo { min-width:200px; text-align:left; background:var(--surface-0); font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); }
.mth-plo { min-width:100px; text-align:center; background:var(--surface-0); }
.mth-total { min-width:80px; text-align:center; background:var(--surface-0); font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); }

.plo-th-content { display:flex; flex-direction:column; align-items:center; gap:2px; }
.plo-th-code { font-family:'Lexend Deca',sans-serif; font-weight:700; font-size:13px; color:var(--accent); }
.plo-th-cat { font-size:10px; color:var(--text-muted); }

.mtd-clo { background:var(--surface-0); padding:10px 12px; }
.clo-td { display:flex; flex-direction:column; gap:2px; }
.clo-td-desc { font-size:11px; color:var(--text-muted); white-space:normal; max-width:180px; }

.mtd-weight { text-align:center; padding:4px; }
.weight-input {
    width:72px; background:var(--surface-1); border:1px solid var(--surface-2);
    border-radius:var(--radius-sm); color:var(--text-primary);
    padding:6px 4px; text-align:center;
    font-family:'Lexend Deca',sans-serif; font-weight:600; font-size:13px;
    outline:none; transition:all var(--transition);
}
.weight-input:focus { border-color:var(--accent); box-shadow:0 0 0 2px var(--accent-soft); }
.weight-input.has-value { background:rgba(99,102,241,.1); border-color:rgba(99,102,241,.4); color:#a5b4fc; }
.weight-input.saving { border-color:var(--amber); }
.weight-input.saved  { border-color:var(--emerald); animation:flashGreen .5s; }

@keyframes flashGreen { 0%{background:rgba(16,185,129,.2)} 100%{background:rgba(99,102,241,.1)} }

.mtd-row-total, .mtd-col-total { text-align:center; font-size:12px; }
.row-total, .col-total { color:var(--text-muted); font-weight:500; }
.row-total.has-value, .col-total.has-value { color:var(--text-primary); font-weight:700; font-family:'Lexend Deca',sans-serif; }

tfoot td { background:var(--surface-0); font-size:12px; font-weight:600; padding:10px 8px; }

.matrix-status { display:flex; align-items:center; gap:8px; margin-top:12px; font-size:12px; color:var(--text-muted); }
.status-dot { width:6px; height:6px; border-radius:50%; }
.status-dot.saved   { background:var(--emerald); }
.status-dot.saving  { background:var(--amber); animation:pulse 1s infinite; }
</style>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function debounce(fn, ms) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

function updateRowTotal(cloId) {
    let total = 0;
    document.querySelectorAll(`.weight-input[data-clo="${cloId}"]`).forEach(inp => {
        total += parseFloat(inp.value) || 0;
    });
    const el = document.getElementById(`rowtotal-${cloId}`);
    if (el) {
        el.textContent = total > 0 ? total + '%' : '—';
        el.className = `row-total ${total > 0 ? 'has-value' : ''}`;
    }
}

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
            span.className = `col-total ${total > 0 ? 'has-value' : ''}`;
        }
    }
}

async function saveMapping(input) {
    const cloId  = input.dataset.clo;
    const ploId  = input.dataset.plo;
    const weight = parseFloat(input.value) || 0;

    const statusText = document.getElementById('matrixStatusText');
    const statusDot  = document.querySelector('.status-dot');
    if (statusDot) { statusDot.className = 'status-dot saving'; }
    if (statusText) statusText.textContent = 'Đang lưu...';
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
            if (statusDot) statusDot.className = 'status-dot saved';
            if (statusText) statusText.textContent = 'Đã lưu thành công';
            setTimeout(() => input.classList.remove('saved'), 1000);
        } else throw new Error(data.error);
    } catch(e) {
        input.classList.remove('saving');
        window.Toast?.error('Lỗi lưu: ' + e.message);
        if (statusDot) statusDot.className = 'status-dot saved';
    }
}

const debouncedSave = debounce(saveMapping, 700);

document.querySelectorAll('.weight-input').forEach(inp => {
    if (inp.value) inp.classList.add('has-value');

    inp.addEventListener('input', () => {
        updateRowTotal(inp.dataset.clo);
        updateColTotal(inp.dataset.plo);
        debouncedSave(inp);
    });
    inp.addEventListener('focus', () => inp.select());
});
</script>
