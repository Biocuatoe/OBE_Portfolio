<?php /* app/Views/admin/plos.php */ ?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading"><?= htmlspecialchars($pageTitle ?? 'Báo cáo đạt chuẩn PLO') ?></h2>
        <p class="page-sub">Thống kê tỷ lệ đạt chuẩn đầu ra chương trình đào tạo theo PLO</p>
    </div>
    <div class="header-actions">
        <select id="programFilter" class="form-control" style="min-width:240px" aria-label="Chọn chương trình đào tạo">
            <?php foreach ($programs as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($p['id'] == ($program_id ?? 0)) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['code'] . ' – ' . $p['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <a href="/admin/programs" class="btn btn-secondary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Quay lại CTĐT
        </a>
    </div>
</div>

<?php if (empty($programs)): ?>
    <!-- Empty State: No Programs -->
    <div class="card">
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
    <div class="card">
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            <p>Chưa có dữ liệu PLO cho chương trình này.</p>
            <p class="text-sm text-muted">Vui lòng thêm PLO và đo lường đạt chuẩn cho sinh viên.</p>
        </div>
    </div>
<?php else: ?>

    <!-- PLO Attainment Table -->
    <div class="card">
        <div class="card-header">
            <div class="section-title-group">
                <h3 class="card-title">Bảng đạt chuẩn theo PLO</h3>
                <span class="badge badge-gray"><?= count($plo_report) ?> PLO</span>
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

        <div class="table-wrap">
            <table class="data-table striped">
                <thead>
                    <tr>
                        <th style="min-width:90px">Mã PLO</th>
                        <th style="min-width:280px">Mô tả</th>
                        <th style="min-width:120px">Danh mục</th>
                        <th class="text-center" style="min-width:70px">SV đo</th>
                        <th class="text-center" style="min-width:100px">Trung bình %</th>
                        <th class="text-center" style="min-width:130px">Đạt</th>
                        <th class="text-center" style="min-width:110px">Mức đạt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalStudents = 0;
                    $totalPassed  = 0;
                    $totalMeasured = 0;
                    foreach ($plo_report as $plo):
                        $avg      = (float)(($plo['avg_pct'] ?? 0));
                        $passed   = (int)  (($plo['passed_count'] ?? 0));
                        $measured = (int)  (($plo['measured_students'] ?? 0));
                        $total    = (int)  (($plo['total_count'] ?? 0));
                        $passRate = $measured > 0 ? round($passed / $measured * 100, 1) : 0;

                        $totalStudents += $measured;
                        $totalPassed   += $passed;
                        $totalMeasured += $measured;

                        $levelClass = $avg >= 70 ? 'badge badge-emerald' : ($avg >= 50 ? 'badge badge-amber' : 'badge badge-rose');
                        $levelLabel = $avg >= 70 ? 'Đạt' : ($avg >= 50 ? 'Cần cải thiện' : 'Chưa đạt');
                        $avgClass   = $avg >= 70 ? 'text-emerald' : ($avg >= 50 ? 'text-amber' : 'text-rose');
                        $barClass   = $avg >= 70 ? 'fill--good' : ($avg >= 50 ? 'fill--mid' : 'fill--low');

                        $categoryColors = [
                            'Knowledge'      => 'badge badge-accent',
                            'Skill'          => 'badge badge-sky',
                            'Attitude'       => 'badge badge-emerald',
                            'Responsibility' => 'badge badge-amber',
                            'Communication'  => 'badge badge-rose',
                        ];
                        $catClass = $categoryColors[$plo['category']] ?? 'badge badge-gray';
                    ?>
                        <tr class="plo-row">
                            <td>
                                <span class="badge badge-accent"><?= htmlspecialchars($plo['code']) ?></span>
                            </td>
                            <td>
                                <div class="cell-primary"><?= htmlspecialchars(mb_substr($plo['description'], 0, 80)) ?><?= mb_strlen($plo['description']) > 80 ? '…' : '' ?></div>
                            </td>
                            <td>
                                <span class="<?= $catClass ?>"><?= htmlspecialchars($plo['category'] ?? '—') ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-accent"><?= $measured ?></span>
                            </td>
                            <td class="text-center">
                                <span class="<?= $avgClass ?>" style="font-weight:700;font-size:14px"><?= $avg > 0 ? number_format($avg, 1) . '%' : '—' ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($measured > 0): ?>
                                    <div style="display:flex;align-items:center;gap:8px;justify-content:center">
                                        <div class="mini-progress-track">
                                            <div class="mini-progress-fill <?= $barClass ?>" style="width:<?= min($passRate, 100) ?>%"></div>
                                        </div>
                                        <span class="text-muted" style="font-size:11px;font-weight:500"><?= $passed ?>/<?= $measured ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted text-sm">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="<?= $levelClass ?>"><?= $levelLabel ?></span>
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
        <div class="summary-bar" style="margin-top:var(--space-5)">
            <div class="summary-stat">
                <span class="label">Tổng hợp toàn chương trình</span>
                <span class="label text-muted" style="font-size:11px"><?= $totalPassed ?> / <?= $totalMeasured ?> SV đạt chuẩn</span>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:200px">
                <div class="progress-track" style="flex:1">
                    <div class="progress-fill <?= $overallClass ?>" style="width:<?= min($overallPct, 100) ?>%"></div>
                    <div class="progress-threshold" style="left:70%"></div>
                </div>
                <span class="<?= $overallPct >= 70 ? 'text-emerald' : ($overallPct >= 50 ? 'text-amber' : 'text-rose') ?>" style="font-weight:800;font-size:18px;flex-shrink:0">
                    <?= $overallPct ?>%
                </span>
            </div>
        </div>
    </div>

    <!-- Top Students Section -->
    <?php if (!empty($top_students)): ?>
    <div class="card">
        <div class="card-header">
            <div class="section-title-group">
                <h3 class="card-title">Top sinh viên xuất sắc</h3>
                <span class="badge badge-amber">Top 10</span>
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
                $pct    = (float)(($student['overall_pct'] ?? 0));
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
                        <span class="<?= $pct >= 70 ? 'text-emerald' : ($pct >= 50 ? 'text-amber' : 'text-rose') ?>" style="font-weight:700;font-size:13px;min-width:44px;text-align:right">
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
.mt-8 { margin-top: 8px; }

/* ── Legend ──────────────────────────────────────────────────────── */
.legend-group { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.legend-item { display: flex; align-items: center; gap: 5px; }
.legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.legend-text { font-size: 11px; color: var(--text-muted); }

/* ── Cell primary ───────────────────────────────────────────────── */
.cell-primary { font-size: 13px; color: var(--text-primary); }
.text-center { text-align: center; }
.text-muted  { color: var(--text-muted); }
.text-sm     { font-size: 12px; }

/* ── Mini Progress Bar ─────────────────────────────────────────── */
.mini-progress-track {
    height: 5px;
    background: var(--surface-2);
    border-radius: 3px;
    overflow: hidden;
    width: 70px;
    flex-shrink: 0;
}
.mini-progress-fill { height: 100%; border-radius: 3px; transition: width .6s cubic-bezier(.16,1,.3,1); }

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

@media (max-width: 768px) {
    .top-students-grid { grid-template-columns: 1fr; }
    .header-actions { flex-direction: column; align-items: stretch; }
    .data-table { font-size: 12px; }
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
