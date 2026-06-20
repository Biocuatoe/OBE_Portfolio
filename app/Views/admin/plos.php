<?php /* app/Views/admin/plos.php */ ?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading"><?= htmlspecialchars($pageTitle ?? 'Báo cáo đạt chuẩn PLO') ?></h2>
        <p class="page-sub">Thống kê tỷ lệ đạt chuẩn đầu ra chương trình đào tạo theo PLO</p>
    </div>
    <div class="header-actions">
        <select id="programFilter" class="filter-select" aria-label="Chọn chương trình đào tạo">
            <?php foreach ($programs as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($p['id'] == ($program_id ?? 0)) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['code'] . ' – ' . $p['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <a href="/admin/programs" class="btn btn-ghost btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Quay lại CTĐT
        </a>
    </div>
</div>

<?php if (empty($programs)): ?>
    <!-- Empty State: No Programs -->
    <div class="section-card">
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            <p>Chưa có chương trình đào tạo nào.</p>
            <a href="/admin/programs" class="btn btn-primary btn-sm mt-8">Tạo chương trình đầu tiên</a>
        </div>
    </div>
<?php elseif (empty($plo_report)): ?>
    <!-- Empty State: No PLO Data -->
    <div class="section-card">
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            <p>Chưa có dữ liệu PLO cho chương trình này.</p>
            <p class="empty-sub">Vui lòng thêm PLO và đo lường đạt chuẩn cho sinh viên.</p>
        </div>
    </div>
<?php else: ?>

    <!-- PLO Attainment Table -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-title-group">
                <h3 class="section-title">Bảng đạt chuẩn theo PLO</h3>
                <span class="section-badge"><?= count($plo_report) ?> PLO</span>
            </div>
            <div class="legend-group">
                <div class="legend-item">
                    <span class="legend-dot" style="background: var(--emerald)"></span>
                    <span class="legend-text">≥ 70% Đạt</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background: var(--amber)"></span>
                    <span class="legend-text">50–69% Cần cải thiện</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background: var(--rose)"></span>
                    <span class="legend-text">< 50% Chưa đạt</span>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table plo-table">
                <thead>
                    <tr>
                        <th class="th-plo-code">Mã PLO</th>
                        <th class="th-plo-desc">Mô tả</th>
                        <th class="th-plo-cat">Danh mục</th>
                        <th class="th-plo-students text-center">SV đo</th>
                        <th class="th-plo-avg text-center">Trung bình %</th>
                        <th class="th-plo-pass text-center">Đạt</th>
                        <th class="th-plo-level text-center">Mức đạt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalStudents = 0;
                    $totalPassed  = 0;
                    $totalMeasured = 0;
                    foreach ($plo_report as $plo):
                        $avg    = (float)($plo['avg_pct'] ?? 0);
                        $passed = (int)($plo['passed_count'] ?? 0);
                        $measured = (int)($plo['measured_students'] ?? 0);
                        $total   = (int)($plo['total_count'] ?? 0);
                        $passRate = $measured > 0 ? round($passed / $measured * 100, 1) : 0;

                        $totalStudents += $measured;
                        $totalPassed   += $passed;
                        $totalMeasured += $measured;

                        $levelClass = $avg >= 70 ? 'level--good' : ($avg >= 50 ? 'level--mid' : 'level--low');
                        $levelLabel = $avg >= 70 ? 'Đạt' : ($avg >= 50 ? 'Cần cải thiện' : 'Chưa đạt');
                        $avgClass   = $avg >= 70 ? 'pct--good' : ($avg >= 50 ? 'pct--mid' : 'pct--low');
                        $barClass   = $avg >= 70 ? 'fill--good' : ($avg >= 50 ? 'fill--mid' : 'fill--low');

                        $categoryColors = [
                            'Knowledge'      => 'cat--accent',
                            'Skill'          => 'cat--sky',
                            'Attitude'       => 'cat--emerald',
                            'Responsibility' => 'cat--amber',
                            'Communication'  => 'cat--rose',
                        ];
                        $catClass = $categoryColors[$plo['category']] ?? 'cat--muted';
                    ?>
                        <tr class="plo-row">
                            <td>
                                <span class="plo-code-badge"><?= htmlspecialchars($plo['code']) ?></span>
                            </td>
                            <td>
                                <div class="cell-primary"><?= htmlspecialchars(mb_substr($plo['description'], 0, 80)) ?><?= mb_strlen($plo['description']) > 80 ? '…' : '' ?></div>
                            </td>
                            <td>
                                <span class="category-badge <?= $catClass ?>"><?= htmlspecialchars($plo['category'] ?? '—') ?></span>
                            </td>
                            <td class="text-center">
                                <span class="stat-pill stat-pill--primary"><?= $measured ?></span>
                            </td>
                            <td class="text-center">
                                <span class="avg-pct <?= $avgClass ?>"><?= $avg > 0 ? number_format($avg, 1) . '%' : '—' ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($measured > 0): ?>
                                    <div class="pass-rate-cell">
                                        <div class="mini-progress-track">
                                            <div class="mini-progress-fill <?= $barClass ?>" style="width:<?= min($passRate, 100) ?>%"></div>
                                        </div>
                                        <span class="pass-rate-text"><?= $passed ?>/<?= $measured ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted text-sm">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="level-badge <?= $levelClass ?>"><?= $levelLabel ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Overall summary bar -->
        <?php
        $overallPct = $totalMeasured > 0 ? round($totalPassed / $totalMeasured * 100, 1) : 0;
        $overallClass = $overallPct >= 70 ? 'fill--good' : ($overallPct >= 50 ? 'fill--mid' : 'fill--low');
        ?>
        <div class="overall-summary">
            <div class="summary-label">
                <span class="summary-text">Tổng hợp toàn chương trình</span>
                <span class="summary-meta"><?= $totalPassed ?> / <?= $totalMeasured ?> SV đạt chuẩn</span>
            </div>
            <div class="summary-bar-wrap">
                <div class="progress-track">
                    <div class="progress-fill <?= $overallClass ?>" style="width:<?= min($overallPct, 100) ?>%"></div>
                    <div class="progress-threshold" style="left:70%"></div>
                </div>
                <span class="summary-pct <?= $overallPct >= 70 ? 'pct--good' : ($overallPct >= 50 ? 'pct--mid' : 'pct--low') ?>">
                    <?= $overallPct ?>%
                </span>
            </div>
        </div>
    </div>

    <!-- Top Students Section -->
    <?php if (!empty($top_students)): ?>
    <div class="section-card">
        <div class="section-header">
            <div class="section-title-group">
                <h3 class="section-title">Top sinh viên xuất sắc</h3>
                <span class="section-badge">Top 10</span>
            </div>
        </div>

        <div class="top-students-grid">
            <?php
            $rankColors = ['#f59e0b', '#94a3b8', '#cd7c4b', '#64748b', '#64748b'];
            $rankBgColors = [
                'rgba(245,158,11,.15)',
                'rgba(148,163,184,.12)',
                'rgba(205,124,75,.12)',
                'rgba(100,116,139,.08)',
                'rgba(100,116,139,.08)',
            ];
            foreach (array_slice($top_students, 0, 10) as $i => $student):
                $pct    = (float)($student['overall_pct'] ?? 0);
                $barCls = $pct >= 70 ? 'fill--good' : ($pct >= 50 ? 'fill--mid' : 'fill--low');
                $rankBg = $rankBgColors[$i] ?? 'rgba(100,116,139,.08)';
                $rankFg = $rankColors[$i] ?? '#64748b';
            ?>
                <div class="student-rank-card">
                    <div class="rank-badge" style="background:<?= $rankBg ?>; color:<?= $rankFg ?>">
                        <?= $i + 1 ?>
                    </div>
                    <div class="student-info">
                        <div class="student-name"><?= htmlspecialchars($student['full_name'] ?? $student['username']) ?></div>
                        <div class="student-user text-muted text-sm">@<?= htmlspecialchars($student['username']) ?></div>
                    </div>
                    <div class="student-bar-wrap">
                        <div class="mini-progress-track">
                            <div class="mini-progress-fill <?= $barCls ?>" style="width:<?= min($pct, 100) ?>%"></div>
                        </div>
                        <span class="student-pct <?= $pct >= 70 ? 'pct--good' : ($pct >= 50 ? 'pct--mid' : 'pct--low') ?>">
                            <?= number_format($pct, 1) ?>%
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

<style>
/* ── Page Header ─────────────────────────────────────────────────── */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.page-heading {
    font-family: 'Lexend Deca', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 2px;
}
.page-sub { font-size: 13px; color: var(--text-muted); }
.header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.filter-select {
    background: var(--surface-1);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    font-family: 'Lexend Deca', sans-serif;
    font-size: 13px;
    font-weight: 600;
    padding: 8px 12px;
    cursor: pointer;
    outline: none;
    transition: border-color var(--transition);
    min-width: 240px;
}
.filter-select:focus { border-color: var(--accent); box-shadow: 0 0 0 2px var(--accent-soft); }

/* ── Section Card & Header ───────────────────────────────────────── */
.section-card { background: var(--surface-1); border: 1px solid var(--surface-2); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 20px; }
.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 12px; flex-wrap: wrap; }
.section-title-group { display: flex; align-items: center; gap: 10px; }
.section-title { font-family: 'Lexend Deca', sans-serif; font-weight: 600; font-size: 15px; color: var(--text-primary); }
.section-badge {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 600;
    font-size: 11px;
    color: var(--accent);
    background: var(--accent-soft);
    padding: 2px 8px;
    border-radius: 20px;
}

/* ── Legend ──────────────────────────────────────────────────────── */
.legend-group { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.legend-item { display: flex; align-items: center; gap: 5px; }
.legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.legend-text { font-size: 11px; color: var(--text-muted); }

/* ── Table ──────────────────────────────────────────────────────── */
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    text-align: left;
    padding: 10px 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text-muted);
    border-bottom: 1px solid var(--surface-2);
    white-space: nowrap;
}
.data-table td { padding: 12px 12px; border-bottom: 1px solid rgba(51,65,85,.4); vertical-align: middle; }
.data-table tbody tr:hover td { background: rgba(51,65,85,.2); }
.data-table tbody tr:last-child td { border-bottom: none; }

.th-plo-code { min-width: 90px; }
.th-plo-desc { min-width: 280px; }
.th-plo-cat  { min-width: 120px; }
.th-plo-students { min-width: 70px; }
.th-plo-avg  { min-width: 100px; }
.th-plo-pass { min-width: 130px; }
.th-plo-level { min-width: 110px; }

.text-center { text-align: center; }
.text-muted  { color: var(--text-muted); }
.text-sm     { font-size: 12px; }

/* ── PLO Code Badge ─────────────────────────────────────────────── */
.plo-code-badge {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: var(--accent);
    background: var(--accent-soft);
    padding: 3px 10px;
    border-radius: 5px;
    white-space: nowrap;
}

/* ── Category Badge ─────────────────────────────────────────────── */
.category-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 20px;
    white-space: nowrap;
}
.cat--accent   { background: var(--accent-soft);   color: var(--accent); }
.cat--sky      { background: rgba(14,165,233,.12); color: var(--sky); }
.cat--emerald  { background: rgba(16,185,129,.12); color: var(--emerald); }
.cat--amber    { background: rgba(245,158,11,.12); color: var(--amber); }
.cat--rose     { background: rgba(244,63,94,.12); color: var(--rose); }
.cat--muted    { background: rgba(100,116,139,.15); color: var(--text-muted); }

/* ── Avg & Level ────────────────────────────────────────────────── */
.avg-pct {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 14px;
}
.pct--good { color: var(--emerald); }
.pct--mid  { color: var(--amber); }
.pct--low  { color: var(--rose); }

/* ── Pass Rate Mini Bar ─────────────────────────────────────────── */
.pass-rate-cell { display: flex; align-items: center; gap: 8px; justify-content: center; }
.mini-progress-track {
    height: 5px;
    background: var(--surface-2);
    border-radius: 3px;
    overflow: hidden;
    width: 70px;
    flex-shrink: 0;
}
.mini-progress-fill { height: 100%; border-radius: 3px; transition: width .6s cubic-bezier(.16,1,.3,1); }
.mini-fill--good { background: linear-gradient(90deg, #10b981, #34d399); }
.pass-rate-text { font-size: 11px; color: var(--text-muted); font-weight: 500; }

/* ── Level Badge ────────────────────────────────────────────────── */
.level-badge {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 20px;
    white-space: nowrap;
}
.level--good { background: rgba(16,185,129,.15); color: var(--emerald); }
.level--mid  { background: rgba(245,158,11,.15); color: var(--amber); }
.level--low  { background: rgba(244,63,94,.15); color: var(--rose); }

/* ── Stat Pill ──────────────────────────────────────────────────── */
.stat-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    min-width: 28px;
    padding: 2px 8px;
    border-radius: 20px;
}
.stat-pill--primary { background: var(--accent-soft); color: #a5b4fc; }

/* ── Overall Summary ────────────────────────────────────────────── */
.overall-summary {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-top: 16px;
    padding: 14px 16px;
    background: var(--surface-0);
    border-radius: var(--radius-md);
    flex-wrap: wrap;
}
.summary-label { display: flex; flex-direction: column; gap: 2px; flex-shrink: 0; }
.summary-text { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.summary-meta { font-size: 11px; color: var(--text-muted); }
.summary-bar-wrap { flex: 1; min-width: 200px; display: flex; align-items: center; gap: 12px; }
.summary-pct {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 800;
    font-size: 18px;
    flex-shrink: 0;
}

/* ── Progress Track (shared) ────────────────────────────────────── */
.progress-track {
    height: 6px;
    background: var(--surface-2);
    border-radius: 3px;
    position: relative;
    overflow: visible;
    flex: 1;
}
.progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 1s cubic-bezier(.16,1,.3,1);
}
.fill--good { background: linear-gradient(90deg, #10b981, #34d399); }
.fill--mid  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.fill--low  { background: linear-gradient(90deg, #f43f5e, #fb7185); }
.progress-threshold {
    position: absolute;
    top: -2px; bottom: -2px;
    width: 2px;
    background: rgba(245,158,11,.6);
    border-radius: 1px;
}

/* ── Top Students ────────────────────────────────────────────────── */
.top-students-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.student-rank-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-md);
    transition: border-color var(--transition);
}
.student-rank-card:hover { border-color: var(--surface-3); }

.rank-badge {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 800;
    font-size: 12px;
    flex-shrink: 0;
}

.student-info { flex: 1; min-width: 0; }
.student-name { font-size: 13px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.student-user { font-size: 11px; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.student-bar-wrap { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.student-pct {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 13px;
    min-width: 44px;
    text-align: right;
}

/* ── Buttons ─────────────────────────────────────────────────────── */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 18px; border-radius: var(--radius-sm); font-family: 'Lexend Deca', sans-serif; font-weight: 600; font-size: 13px; cursor: pointer; border: none; text-decoration: none; transition: all var(--transition); }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { background: var(--accent-hover); }
.btn-ghost { background: none; color: var(--text-secondary); border: 1px solid var(--surface-2); }
.btn-ghost:hover { border-color: var(--surface-3); color: var(--text-primary); }
.btn-sm { font-size: 12px; padding: 7px 14px; }
.mt-8 { margin-top: 8px; }

/* ── Empty State ────────────────────────────────────────────────── */
.empty-state { text-align: center; padding: 48px 24px; color: var(--text-muted); }
.empty-state svg { margin: 0 auto 12px; display: block; opacity: .4; }
.empty-state p { font-size: 14px; margin-bottom: 4px; }
.empty-sub { font-size: 12px; }

/* ── Cell primary ───────────────────────────────────────────────── */
.cell-primary { font-size: 13px; color: var(--text-primary); }

@media (max-width: 768px) {
    .top-students-grid { grid-template-columns: 1fr; }
    .header-actions { flex-direction: column; align-items: stretch; }
    .filter-select { min-width: 100%; }
    .overall-summary { flex-direction: column; align-items: stretch; }
    .summary-bar-wrap { width: 100%; }
    .data-table { font-size: 12px; }
    .th-plo-desc, .th-plo-cat { display: none; }
}
</style>

<script>
(function() {
    // ── Program Filter Redirect ─────────────────────────────────────
    const programFilter = document.getElementById('programFilter');
    if (programFilter) {
        programFilter.addEventListener('change', function() {
            const programId = this.value;
            if (programId) {
                window.location.href = '/admin/report/attainment/' + programId;
            }
        });
    }
})();
</script>
