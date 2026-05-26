<?php /* app/Views/lecturer/grading.php */
$pageTitle = 'Chấm điểm — ' . htmlspecialchars($assessment['title']);
$extraJs   = ['/js/grade_sync.js'];

// Đếm SV có ÍT NHẤT 1 điểm
$gradedCount = 0;
// Đếm SV đã chấm ĐẦY ĐỦ tất cả rubric
$fullyGradedCount = 0;
foreach ($students as $s) {
    $hasAny  = false;
    $allDone = true;
    foreach ($rubrics as $r) {
        $sc = $s['scores'][$r['id']]['score'] ?? null;
        if ($sc !== null) { $hasAny = true; }
        else              { $allDone = false; }
    }
    if ($hasAny)  $gradedCount++;
    if ($allDone && count($rubrics) > 0) $fullyGradedCount++;
}
$totalStudents    = count($students);
$totalRubrics     = count($rubrics);
$isFullyComplete  = ($fullyGradedCount === $totalStudents && $totalStudents > 0 && $totalRubrics > 0);
?>

<!-- Banner trạng thái tổng -->
<?php if ($isFullyComplete): ?>
<div class="grading-complete-banner">
    <div class="gcb-icon">✓</div>
    <div class="gcb-body">
        <div class="gcb-title">Đã chấm điểm xong toàn bộ bài này</div>
        <div class="gcb-desc">
            Tất cả <?= $totalStudents ?> sinh viên đã có đủ điểm ở <?= $totalRubrics ?> tiêu chí.
            Điểm đã được lưu — bạn có thể chỉnh sửa bất kỳ ô nào nếu cần.
        </div>
    </div>
    <a href="/lecturer/dashboard" class="gcb-back-btn">← Về tổng quan</a>
</div>
<?php else: ?>
<div class="grading-progress-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
    </svg>
    Đang chấm: <strong><?= $fullyGradedCount ?>/<?= $totalStudents ?></strong> sinh viên hoàn thành —
    còn <strong><?= $totalStudents - $fullyGradedCount ?></strong> SV chưa đủ điểm
</div>
<?php endif; ?>

<!-- Assessment Header -->
<div class="grading-header">
    <div class="grading-meta">
        <div class="grading-meta-top">
            <span class="assessment-type-badge type-<?= $assessment['type'] ?>">
                <?= strtoupper($assessment['type']) ?>
            </span>
            <?php if ($assessment['is_published']): ?>
            <span class="published-badge">✓ Đã công bố</span>
            <?php else: ?>
            <span class="draft-badge">⏸ Nháp</span>
            <?php endif; ?>
        </div>
        <h2 class="grading-title"><?= htmlspecialchars($assessment['title']) ?></h2>
        <div class="grading-info">
            <span>Trọng số: <strong><?= $assessment['weight'] ?>%</strong></span>
            <span>Hoàn thành: <strong><?= $fullyGradedCount ?>/<?= $totalStudents ?></strong> SV</span>
            <?php if ($assessment['due_date']): ?>
            <span>Hạn nộp: <strong><?= date('d/m/Y H:i', strtotime($assessment['due_date'])) ?></strong></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats theo đúng assessment_id này (không bị cache) -->
    <div class="grading-stats">
        <?php foreach ($stats as $s): ?>
        <div class="grading-stat">
            <div class="gs-header">
                <span class="gs-clo"><?= htmlspecialchars($s['clo_code']) ?></span>
                <span class="gs-name"><?= htmlspecialchars(mb_substr($s['criteria_name'], 0, 28)) ?></span>
            </div>
            <div class="gs-values">
                <span class="gs-avg"><?= $s['avg_score'] ?? '—' ?></span>
                <span class="gs-max">/ <?= $s['max_score'] ?></span>
                <span class="gs-pct"><?= $s['avg_pct'] ? $s['avg_pct'] . '%' : '' ?></span>
            </div>
            <div class="gs-progress">
                <div class="gs-fill" style="width:<?= $s['avg_pct'] ?? 0 ?>%"></div>
            </div>
            <div class="gs-count"><?= (int)$s['graded_count'] ?>/<?= $totalStudents ?> SV đã chấm</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Toolbar -->
<div class="grading-toolbar">
    <div class="save-status-bar" id="saveStatusBar">
        <div class="save-dots"><span></span><span></span><span></span></div>
        <span class="save-text" id="saveText">Tất cả thay đổi đã được lưu</span>
    </div>
    <div class="toolbar-actions">
        <span class="keyboard-hint-inline"><kbd>Tab</kbd> di chuyển &nbsp;·&nbsp; <kbd>↑↓</kbd> lên/xuống</span>
        <button class="btn-save-all" id="btnSaveAll">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
            </svg>
            Lưu tất cả
        </button>
        <button class="btn-confirm-all" id="btnConfirmAll">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <?= $isFullyComplete ? 'Đã hoàn thành ✓' : 'Xác nhận hoàn thành' ?>
        </button>
    </div>
</div>

<!-- Overall Progress Bar -->
<div class="grading-overall-progress">
    <div class="gop-label">
        <span>Tiến độ chấm điểm đầy đủ</span>
        <span id="gopCount"><?= $fullyGradedCount ?>/<?= $totalStudents ?> sinh viên hoàn thành</span>
    </div>
    <div class="gop-track">
        <div class="gop-fill" id="gopFill"
             style="width:<?= $totalStudents > 0 ? round($fullyGradedCount/$totalStudents*100) : 0 ?>%"></div>
    </div>
</div>

<!-- Grading Table -->
<div class="grading-table-container">
    <div class="table-scroll">
        <table class="grading-table" id="gradingTable"
               data-assessment="<?= $assessment['id'] ?>"
               data-total-students="<?= $totalStudents ?>">
            <thead>
                <tr>
                    <th class="th-student sticky-col">Sinh viên</th>
                    <?php foreach ($rubrics as $r): ?>
                    <th class="th-rubric">
                        <div class="rubric-header">
                            <span class="rubric-clo"><?= htmlspecialchars($r['clo_code']) ?></span>
                            <span class="rubric-name"><?= htmlspecialchars($r['criteria_name']) ?></span>
                            <span class="rubric-max">/ <?= number_format($r['max_score'], 0) ?></span>
                        </div>
                    </th>
                    <?php endforeach; ?>
                    <th class="th-total">Tổng %</th>
                    <th class="th-status">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <?php
                    $totalEarned = 0; $totalMax = 0;
                    $allGraded   = true; $anyGraded = false;
                    foreach ($rubrics as $r) {
                        $sc = $student['scores'][$r['id']]['score'] ?? null;
                        $totalMax += (float)$r['max_score'];
                        if ($sc !== null) { $totalEarned += (float)$sc; $anyGraded = true; }
                        else              { $allGraded = false; }
                    }
                    $rowStatus = $allGraded ? 'done' : ($anyGraded ? 'partial' : 'pending');
                ?>
                <tr class="student-row student-row--<?= $rowStatus ?>"
                    data-student-id="<?= $student['id'] ?>">

                    <td class="td-student sticky-col">
                        <div class="student-cell">
                            <div class="student-avatar">
                                <?= strtoupper(mb_substr($student['full_name'], 0, 1)) ?>
                            </div>
                            <div class="student-info">
                                <div class="student-name"><?= htmlspecialchars($student['full_name']) ?></div>
                                <div class="student-id"><?= htmlspecialchars($student['username']) ?></div>
                            </div>
                        </div>
                    </td>

                    <?php foreach ($rubrics as $r): ?>
                    <?php $currentScore = $student['scores'][$r['id']]['score'] ?? null; ?>
                    <td class="td-score">
                        <div class="score-cell">
                            <input type="number" class="score-input"
                                data-student="<?= $student['id'] ?>"
                                data-rubric="<?= $r['id'] ?>"
                                data-max="<?= $r['max_score'] ?>"
                                data-assessment="<?= $assessment['id'] ?>"
                                value="<?= $currentScore !== null ? number_format((float)$currentScore, 1) : '' ?>"
                                min="0" max="<?= $r['max_score'] ?>" step="0.5" placeholder="—">
                            <div class="score-indicator <?= $currentScore !== null ? 'saved' : '' ?>"
                                 id="indicator-<?= $student['id'] ?>-<?= $r['id'] ?>"></div>
                        </div>
                    </td>
                    <?php endforeach; ?>

                    <td class="td-total">
                        <span class="total-score <?= $allGraded ? (($totalMax > 0 && ($totalEarned/$totalMax) >= 0.7) ? 'score--good' : 'score--low') : '' ?>"
                              id="total-<?= $student['id'] ?>">
                            <?= ($totalMax > 0 && $anyGraded) ? round($totalEarned / $totalMax * 100) . '%' : '—' ?>
                        </span>
                    </td>

                    <td class="td-status">
                        <span class="row-status-badge badge--<?= $rowStatus ?>"
                              id="status-<?= $student['id'] ?>">
                            <?php if ($rowStatus==='done'): ?>✓ Xong
                            <?php elseif($rowStatus==='partial'): ?>⋯ Một phần
                            <?php else: ?>○ Chưa chấm<?php endif; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">

<!-- Confirm Modal -->
<div class="confirm-overlay" id="confirmOverlay" style="display:none">
    <div class="confirm-modal">
        <div class="confirm-icon">✓</div>
        <h3 class="confirm-title">Xác nhận hoàn thành chấm điểm</h3>
        <p class="confirm-desc">
            Bài: <strong><?= htmlspecialchars($assessment['title']) ?></strong><br>
            Hệ thống sẽ lưu tất cả điểm và cập nhật báo cáo attainment.
        </p>
        <div class="confirm-stats-preview">
            <div class="csp-item">
                <span class="csp-val"><?= $totalStudents ?></span>
                <span class="csp-lbl">Sinh viên</span>
            </div>
            <div class="csp-item">
                <span class="csp-val"><?= $totalRubrics ?></span>
                <span class="csp-lbl">Tiêu chí</span>
            </div>
            <div class="csp-item">
                <span class="csp-val" id="modalGradedCount"><?= $fullyGradedCount ?></span>
                <span class="csp-lbl">Hoàn thành</span>
            </div>
        </div>
        <div class="confirm-actions">
            <button class="btn-modal-cancel" id="btnModalCancel">Huỷ bỏ</button>
            <button class="btn-modal-confirm" id="btnModalConfirm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Xác nhận & Về tổng quan
            </button>
        </div>
    </div>
</div>

<style>
/* ── Status Banners ──────────────────────────────────────────── */
.grading-complete-banner {
    display:flex; align-items:center; gap:14px;
    padding:14px 20px;
    background:rgba(16,185,129,.1);
    border:1px solid rgba(16,185,129,.35);
    border-radius:var(--radius-lg);
}
.gcb-icon {
    width:40px; height:40px; flex-shrink:0;
    background:rgba(16,185,129,.2);
    border:2px solid var(--emerald);
    border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; color:var(--emerald);
}
.gcb-body { flex:1; }
.gcb-title { font-family:'Lexend Deca',sans-serif; font-weight:700; font-size:14px; color:var(--emerald); }
.gcb-desc  { font-size:12px; color:var(--text-secondary); margin-top:3px; }
.gcb-back-btn {
    padding:7px 16px; border-radius:var(--radius-sm);
    background:var(--emerald); color:white;
    font-family:'Lexend Deca',sans-serif; font-weight:600; font-size:13px;
    text-decoration:none; white-space:nowrap;
    transition:background var(--transition);
}
.gcb-back-btn:hover { background:#059669; }

.grading-progress-banner {
    display:flex; align-items:center; gap:8px;
    padding:10px 16px;
    background:rgba(245,158,11,.08);
    border:1px solid rgba(245,158,11,.25);
    border-radius:var(--radius-md);
    font-size:13px; color:var(--amber);
}

/* ── Toolbar ─────────────────────────────────────────────────── */
.grading-toolbar {
    display:flex; align-items:center; justify-content:space-between;
    background:var(--surface-1); border:1px solid var(--surface-2);
    border-radius:var(--radius-md); padding:10px 16px; gap:12px;
}
.toolbar-actions { display:flex; align-items:center; gap:10px; }
.keyboard-hint-inline { font-size:11px; color:var(--text-muted); }

.btn-save-all {
    display:inline-flex; align-items:center; gap:7px;
    padding:8px 16px; background:var(--surface-2);
    border:1px solid var(--surface-3); border-radius:var(--radius-sm);
    color:var(--text-secondary);
    font-family:'Lexend Deca',sans-serif; font-weight:600; font-size:13px;
    cursor:pointer; transition:all var(--transition);
}
.btn-save-all:hover { background:var(--surface-3); color:var(--text-primary); }

.btn-confirm-all {
    display:inline-flex; align-items:center; gap:7px;
    padding:8px 18px; background:var(--emerald); border:none;
    border-radius:var(--radius-sm); color:white;
    font-family:'Lexend Deca',sans-serif; font-weight:700; font-size:13px;
    cursor:pointer; transition:all var(--transition);
    box-shadow:0 2px 12px rgba(16,185,129,.3);
}
.btn-confirm-all:hover { background:#059669; }

/* ── Overall Progress ────────────────────────────────────────── */
.grading-overall-progress {
    background:var(--surface-1); border:1px solid var(--surface-2);
    border-radius:var(--radius-md); padding:12px 16px;
}
.gop-label {
    display:flex; justify-content:space-between;
    font-size:12px; color:var(--text-secondary); margin-bottom:8px; font-weight:500;
}
.gop-track { height:8px; background:var(--surface-2); border-radius:4px; overflow:hidden; }
.gop-fill {
    height:100%; background:linear-gradient(90deg,var(--emerald),#34d399);
    border-radius:4px; transition:width .6s cubic-bezier(.16,1,.3,1);
}

/* ── Row status ──────────────────────────────────────────────── */
.row-status-badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; white-space:nowrap; }
.badge--done    { background:rgba(16,185,129,.15); color:var(--emerald); }
.badge--partial { background:rgba(245,158,11,.15); color:var(--amber); }
.badge--pending { background:var(--surface-2);     color:var(--text-muted); }
.th-status,.td-status { min-width:110px; text-align:center; padding:8px; }

/* ── Header extras ───────────────────────────────────────────── */
.grading-meta-top { display:flex; align-items:center; gap:10px; margin-bottom:6px; }
.published-badge { font-size:11px; padding:2px 10px; border-radius:20px; background:rgba(16,185,129,.15); color:var(--emerald); font-weight:700; }
.draft-badge     { font-size:11px; padding:2px 10px; border-radius:20px; background:rgba(245,158,11,.15); color:var(--amber);   font-weight:700; }
.gs-count { font-size:10px; color:var(--text-muted); margin-top:4px; text-align:right; }

/* ── Confirm Modal ───────────────────────────────────────────── */
.confirm-overlay { position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.6); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; animation:fadeIn .2s ease; }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.confirm-modal { background:var(--surface-1); border:1px solid var(--surface-2); border-radius:var(--radius-xl); padding:32px; max-width:420px; width:90%; text-align:center; box-shadow:0 24px 80px rgba(0,0,0,.5); animation:slideUp .25s cubic-bezier(.16,1,.3,1); }
@keyframes slideUp { from{transform:translateY(24px);opacity:0} to{transform:translateY(0);opacity:1} }
.confirm-icon { width:56px; height:56px; background:rgba(16,185,129,.15); border:2px solid var(--emerald); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; color:var(--emerald); margin:0 auto 16px; }
.confirm-title { font-family:'Lexend Deca',sans-serif; font-weight:700; font-size:18px; color:var(--text-primary); margin-bottom:10px; }
.confirm-desc { font-size:13px; color:var(--text-secondary); line-height:1.6; margin-bottom:20px; }
.confirm-stats-preview { display:flex; justify-content:center; gap:24px; padding:16px; margin-bottom:24px; background:var(--surface-0); border-radius:var(--radius-md); }
.csp-item { text-align:center; }
.csp-val { font-family:'Lexend Deca',sans-serif; font-weight:800; font-size:24px; color:var(--text-primary); display:block; }
.csp-lbl { font-size:11px; color:var(--text-muted); }
.confirm-actions { display:flex; gap:10px; }
.btn-modal-cancel { flex:1; padding:10px; background:var(--surface-2); border:none; border-radius:var(--radius-sm); color:var(--text-secondary); font-family:'Lexend Deca',sans-serif; font-weight:600; font-size:14px; cursor:pointer; transition:all var(--transition); }
.btn-modal-cancel:hover { background:var(--surface-3); }
.btn-modal-confirm { flex:2; padding:10px; background:var(--emerald); border:none; border-radius:var(--radius-sm); color:white; font-family:'Lexend Deca',sans-serif; font-weight:700; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all var(--transition); }
.btn-modal-confirm:hover { background:#059669; }
</style>

<script>
// ── Cập nhật trạng thái hàng realtime ─────────────────────────
function updateRowStatus(studentId) {
    const row    = document.querySelector(`tr[data-student-id="${studentId}"]`);
    if (!row) return;
    const inputs = [...row.querySelectorAll('.score-input')];
    const filled = inputs.filter(i => i.value !== '').length;
    const total  = inputs.length;
    const badge  = document.getElementById(`status-${studentId}`);
    const totalEl= document.getElementById(`total-${studentId}`);

    // Tính lại tổng %
    let earned = 0, maxTotal = 0;
    inputs.forEach(inp => {
        maxTotal += parseFloat(inp.dataset.max) || 0;
        earned   += parseFloat(inp.value) || 0;
    });
    if (totalEl && maxTotal > 0 && filled > 0) {
        const pct = Math.round(earned / maxTotal * 100);
        totalEl.textContent = pct + '%';
        totalEl.style.color = pct >= 70 ? 'var(--emerald)' : 'var(--rose)';
    }

    // Update badge
    row.className = row.className.replace(/student-row--\w+/g, '').trim();
    if (badge) {
        if (filled === total && total > 0) {
            row.classList.add('student-row--done');
            badge.className = 'row-status-badge badge--done';
            badge.textContent = '✓ Xong';
        } else if (filled > 0) {
            row.classList.add('student-row--partial');
            badge.className = 'row-status-badge badge--partial';
            badge.textContent = '⋯ Một phần';
        } else {
            row.classList.add('student-row--pending');
            badge.className = 'row-status-badge badge--pending';
            badge.textContent = '○ Chưa chấm';
        }
    }

    updateOverallProgress();
}
window.updateRowStatus = updateRowStatus;

// ── Cập nhật progress bar tổng ────────────────────────────────
function updateOverallProgress() {
    const rows  = document.querySelectorAll('.student-row');
    let done    = 0;
    rows.forEach(row => {
        const inputs = [...row.querySelectorAll('.score-input')];
        if (inputs.length > 0 && inputs.every(i => i.value !== '')) done++;
    });
    const total = parseInt(document.getElementById('gradingTable').dataset.totalStudents) || 1;
    const pct   = Math.round(done / total * 100);
    document.getElementById('gopFill').style.width  = pct + '%';
    document.getElementById('gopCount').textContent = done + '/' + total + ' sinh viên hoàn thành';
    document.getElementById('modalGradedCount').textContent = done;
}

// ── Nút Lưu tất cả ───────────────────────────────────────────
document.getElementById('btnSaveAll').addEventListener('click', async function() {
    this.disabled = true;
    this.textContent = '⏳ Đang lưu...';
    document.querySelectorAll('.score-input').forEach(inp => {
        if (inp.value !== '') inp.dispatchEvent(new Event('blur'));
    });
    await new Promise(r => setTimeout(r, 900));
    this.disabled = false;
    this.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Lưu tất cả`;
    window.Toast?.success('Đã lưu tất cả điểm!');
});

// ── Nút Xác nhận hoàn thành → Modal ──────────────────────────
document.getElementById('btnConfirmAll').addEventListener('click', function() {
    updateOverallProgress();
    document.getElementById('confirmOverlay').style.display = 'flex';
});
document.getElementById('btnModalCancel').addEventListener('click', function() {
    document.getElementById('confirmOverlay').style.display = 'none';
});
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
document.getElementById('btnModalConfirm').addEventListener('click', async function() {
    this.disabled = true;
    this.textContent = '⏳ Đang xử lý...';
    // Lưu tất cả điểm còn lại
    document.querySelectorAll('.score-input').forEach(inp => {
        if (inp.value !== '') inp.dispatchEvent(new Event('blur'));
    });
    await new Promise(r => setTimeout(r, 1200));
    window.Toast?.success('✓ Hoàn thành! Đang về tổng quan...');
    // Redirect về dashboard thay vì reload
    setTimeout(() => { window.location.href = '/lecturer/dashboard'; }, 1000);
});
</script>