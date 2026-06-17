<?php /* app/Views/lecturer/grading.php */
$assessment = $assessment ?? []; // for IDE static analysis
$students   = $students ?? [];
$rubrics    = $rubrics ?? [];
$stats      = $stats ?? [];
$csrf_token = $csrf_token ?? '';

$pageTitle = 'Chấm điểm — ' . htmlspecialchars($assessment['title'] ?? '');
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

<!-- Stats UI (B6) -->
<div id="gradingStatsTab" style="margin-top:16px;display:none;">
    <div class="grading-stats-panel">
        <div style="display:flex;gap:16px;align-items:flex-start;">
            <div style="flex:1;min-width:360px;">
                <h3 style="font-family:Lexend Deca, sans-serif;margin-bottom:10px;">Phân bố điểm</h3>
                <div id="gradeDistributionBars" style="display:flex;gap:10px;align-items:flex-end;height:220px;">
                </div>
            </div>
            <div style="flex:1;min-width:320px;">
                <h3 style="font-family:Lexend Deca, sans-serif;margin-bottom:10px;">Hoàn thành chấm điểm</h3>
                <div class="gop-track" style="height:10px;background:var(--surface-2);">
                    <div class="gop-fill" id="statsGopFill" style="width:0%;height:10px;"></div>
                </div>
                <div id="statsGopCount" style="margin-top:8px;color:var(--text-secondary);font-size:12px;">—</div>
                <div id="statsDistributionText" style="margin-top:14px;display:flex;flex-direction:column;gap:6px;font-size:12px;color:var(--text-secondary);"></div>
            </div>
        </div>

        <div style="margin-top:18px;">
            <h3 style="font-family:Lexend Deca, sans-serif;margin-bottom:10px;">Bảng stats theo criteria</h3>
            <div class="table-scroll" style="border:1px solid var(--surface-2);border-radius:10px;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                    <tr>
                        <th style="padding:10px 12px;background:var(--surface-0);text-align:left;font-size:11px;color:var(--text-muted);">Criteria</th>
                        <th style="padding:10px 12px;background:var(--surface-0);text-align:center;font-size:11px;color:var(--text-muted);">Avg</th>
                        <th style="padding:10px 12px;background:var(--surface-0);text-align:center;font-size:11px;color:var(--text-muted);">Min</th>
                        <th style="padding:10px 12px;background:var(--surface-0);text-align:center;font-size:11px;color:var(--text-muted);">Max</th>
                        <th style="padding:10px 12px;background:var(--surface-0);text-align:center;font-size:11px;color:var(--text-muted);">Đã chấm</th>
                    </tr>
                    </thead>
                    <tbody id="statsCriteriaRows">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="grading-toolbar" style="margin-top:16px;">

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

<!-- Expandable feedback -->
                            <?php $currentFeedback = $student['scores'][$r['id']]['feedback'] ?? ''; ?>
                            <div class="feedback-wrap">
                                <button type="button" class="btn-feedback-toggle"
                                        data-student="<?= $student['id'] ?>" data-rubric="<?= $r['id'] ?>">
                                    Feedback <?= $currentFeedback ? '✓' : '' ?>
                                </button>
                                <textarea class="score-feedback"
                                          data-student="<?= $student['id'] ?>"
                                          data-rubric="<?= $r['id'] ?>"
                                          rows="1"
                                          placeholder="Ghi chú (tuỳ chọn)..."><?= htmlspecialchars((string)$currentFeedback) ?></textarea>
                            </div>
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
<script>
// expose csrf token for grade_sync.js
window.getCsrfToken = function () {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
};
</script>

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
/* ── Feedback (expandable) + status idle */
.feedback-wrap{width:100%;margin-top:6px;display:flex;flex-direction:column;gap:6px;align-items:stretch;}
.btn-feedback-toggle{font-size:11px;padding:4px 8px;border-radius:6px;border:1px solid var(--surface-2);background:rgba(51,65,85,.15);color:var(--text-muted);cursor:pointer;transition:all var(--transition);}
.btn-feedback-toggle:hover{border-color:var(--accent);color:var(--accent);}
.score-feedback{width:130px;max-width:130px;resize:none;overflow:hidden;border:1px solid var(--surface-2);background:var(--surface-0);color:var(--text-primary);border-radius:8px;padding:8px 10px;outline:none;opacity:0;max-height:0;transition:max-height .22s ease,opacity .18s ease;box-shadow:none;font-family:inherit;font-size:12px;line-height:1.4;}
.score-feedback.open{opacity:1;}
</style>

<script>
// feedback expand/collapse + textarea autosize
(function initFeedbackUI(){
    const table = document.getElementById('gradingTable');
    if (!table) return;

    function autosize(el){
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight) + 'px';
    }

    table.querySelectorAll('.btn-feedback-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const sid = btn.dataset.student;
            const rid = btn.dataset.rubric;
            const ta = table.querySelector(`textarea.score-feedback[data-student="${sid}"][data-rubric="${rid}"]`);
            if (!ta) return;
            const isOpen = ta.classList.toggle('open');
            btn.classList.toggle('open', isOpen);
            ta.style.maxHeight = isOpen ? '220px' : '0px';
            ta.style.opacity = isOpen ? '1' : '0';
            if (isOpen){
                autosize(ta);
                ta.focus();
            }
        });
    });

    table.querySelectorAll('.score-feedback').forEach(ta => {
        autosize(ta);
        ta.addEventListener('input', () => autosize(ta));

        // mark dirty & auto-save on debounce via triggering input on related score-input? 
        // grade_sync.js listens only score-input; feedback is saved when score-input saves.
        // here we just mark dirty state by toggling corresponding score-input.
        ta.addEventListener('input', () => {
            const sid = ta.dataset.student;
            const rid = ta.dataset.rubric;
            const inp = table.querySelector(`input.score-input[data-student="${sid}"][data-rubric="${rid}"]`);
            if (inp) {
                inp.classList.add('dirty');
            }
        });
    });
})();

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