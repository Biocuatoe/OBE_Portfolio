<?php /* app/Views/admin/report_attainment.php */ ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token ?? '') ?>">

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading">Báo cáo đạt chuẩn PLO</h2>
        <p class="page-sub">Thống kê tỷ lệ đạt chuẩn đầu ra chương trình đào tạo</p>
    </div>
    <div class="header-actions">
        <select id="programSelect" class="form-control" style="min-width:220px" title="Chọn chương trình đào tạo">
            <?php if (!empty($programs)): ?>
                <?php foreach ($programs as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($p['id'] == $selected_program_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['code']) ?> — <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="">— Chưa có chương trình —</option>
            <?php endif; ?>
        </select>
        <button class="btn btn-secondary btn-sm" id="printBtn" title="In báo cáo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            In báo cáo
        </button>
    </div>
</div>

<?php if (empty($programs)): ?>
    <!-- Empty: No programs -->
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
<?php elseif ($selected_program_id == 0): ?>
    <div class="card">
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            <p>Vui lòng chọn một chương trình đào tạo để xem báo cáo.</p>
        </div>
    </div>
<?php else: ?>

    <!-- ── PLO Attainment Bar Chart ─────────────────────────────────── -->
    <div class="card" id="ploChartSection">
        <div class="card-header">
            <div class="section-title-group">
                <h3 class="card-title">Biểu đồ đạt chuẩn PLO</h3>
                <span class="badge badge-gray"><?= count($plo_report) ?> PLO</span>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-dot" style="background:var(--emerald)"></span>
                    <span class="legend-text">≥ 70% Đạt</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background:var(--amber)"></span>
                    <span class="legend-text">50–69% Cải thiện</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background:var(--rose)"></span>
                    <span class="legend-text">&lt; 50% Yếu</span>
                </div>
                <div class="legend-item">
                    <span class="threshold-line"></span>
                    <span class="legend-text">─ Ngưỡng đạt (70%)</span>
                </div>
            </div>
        </div>

        <?php if (empty($plo_report)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                </svg>
                <p>Chưa có PLO nào hoặc chưa có dữ liệu đạt chuẩn.</p>
                <a href="/admin/plos/<?= $selected_program_id ?>" class="btn btn-secondary btn-sm mt-8">Quản lý PLO</a>
            </div>
        <?php else: ?>
            <div class="bar-chart-container">
                <?php foreach ($plo_report as $idx => $plo):
                    $pct      = (float)(($plo['avg_attainment'] ?? 0));
                    $passR    = (float)(($plo['pass_rate'] ?? 0));
                    $barScale = $pct / 100;
                    $barColor = $pct >= 70 ? 'good' : ($pct >= 50 ? 'mid' : 'low');
                    $thresholdPos = 70;
                ?>
                    <div class="bar-row" data-delay="<?= $idx * 80 ?>">
                        <div class="bar-label-col">
                            <span class="bar-plo-code"><?= htmlspecialchars($plo['code']) ?></span>
                            <span class="bar-plo-desc" title="<?= htmlspecialchars($plo['description']) ?>">
                                <?= htmlspecialchars($plo['description']) ?>
                            </span>
                        </div>
                        <div class="bar-track-col">
                            <div class="bar-track">
                                <div class="bar-fill bar-fill--<?= $barColor ?>"
                                     style="width:<?= $barScale * 100 ?>%"></div>
                                <div class="bar-threshold" style="left:<?= $thresholdPos ?>%"></div>
                                <span class="bar-pct-label bar-pct-label--<?= $barColor ?>">
                                    <?= number_format($pct, 1) ?>%
                                </span>
                            </div>
                            <div class="bar-pass-rate">
                                <span class="pass-rate-label">
                                    Tỷ lệ đạt:
                                    <?php if ($plo['measured_students'] > 0): ?>
                                        <strong class="<?= $passR >= 70 ? 'text-emerald' : ($passR >= 50 ? 'text-amber' : 'text-rose') ?>">
                                            <?= number_format($passR, 1) ?>%
                                        </strong>
                                        <span class="text-muted">(<?= $plo['students_passed'] ?>/<?= $plo['measured_students'] ?> SV)</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── PLO Detail Table ─────────────────────────────────────────── -->
    <div class="card" id="ploTableSection">
        <div class="card-header">
            <div class="section-title-group">
                <h3 class="card-title">Chi tiết từng PLO</h3>
                <span class="badge badge-gray"><?= count($plo_report) ?> PLO</span>
            </div>
        </div>

        <?php if (empty($plo_report)): ?>
            <div class="empty-state">
                <p>Không có dữ liệu PLO.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table striped" id="ploTable">
                    <thead>
                        <tr>
                            <th>Mã PLO</th>
                            <th>Mô tả</th>
                            <th>Danh mục</th>
                            <th class="text-center">SV đo</th>
                            <th class="text-center">Điểm TB</th>
                            <th class="text-center">SV đạt</th>
                            <th class="text-center">Tỷ lệ đạt</th>
                            <th>CLO liên quan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plo_report as $plo):
                            $pct    = (float)(($plo['avg_attainment'] ?? 0));
                            $pColor = $pct >= 70 ? 'good' : ($pct >= 50 ? 'mid' : 'low');
                            $catColor = match(strtolower($plo['category'] ?? '')) {
                                'knowledge' => 'badge badge-accent',
                                'skill'    => 'badge badge-sky',
                                'attitude'  => 'badge badge-amber',
                                default    => 'badge badge-gray',
                            };
                        ?>
                            <tr>
                                <td>
                                    <span class="badge badge-accent"><?= htmlspecialchars($plo['code']) ?></span>
                                </td>
                                <td>
                                    <span class="cell-desc" title="<?= htmlspecialchars($plo['description']) ?>">
                                        <?= htmlspecialchars($plo['description']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?= $catColor ?>">
                                        <?= htmlspecialchars($plo['category'] ?: '—') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="text-muted"><?= $plo['measured_students'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($plo['measured_students'] > 0): ?>
                                        <span class="score-display score--<?= $pColor ?>">
                                            <?= number_format($pct, 1) ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="text-muted"><?= $plo['students_passed'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($plo['measured_students'] > 0): ?>
                                        <div class="mini-progress-wrap">
                                            <div class="mini-progress-track">
                                                <div class="mini-progress-fill mini-fill--<?= ($plo['pass_rate'] >= 70) ? 'good' : (($plo['pass_rate'] >= 50) ? 'mid' : 'low') ?>"
                                                     style="width:<?= min(100, $plo['pass_rate']) ?>%"></div>
                                            </div>
                                            <span class="mini-pct-label"><?= number_format($plo['pass_rate'], 1) ?>%</span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $cloArr = $plo['clo_codes'] ? explode(', ', $plo['clo_codes']) : [];
                                    ?>
                                    <?php if (!empty($cloArr)): ?>
                                        <div class="clo-pills">
                                            <?php foreach ($cloArr as $cc): ?>
                                                <span class="clo-pill"><?= htmlspecialchars($cc) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted text-sm">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Top 10 Students ─────────────────────────────────────────── -->
    <div class="card" id="topStudentsSection">
        <div class="card-header">
            <div class="section-title-group">
                <h3 class="card-title">Top 10 sinh viên xuất sắc</h3>
                <span class="badge badge-emerald">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Theo tỷ lệ đạt PLO
                </span>
            </div>
        </div>

        <?php if (empty($top_students)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <p>Chưa có dữ liệu sinh viên đạt chuẩn PLO.</p>
            </div>
        <?php else: ?>
            <div class="top-students-layout">
                <!-- Left: Table -->
                <div class="top-students-table-wrap">
                    <table class="data-table" id="topStudentsTable">
                        <thead>
                            <tr>
                                <th class="text-center col-rank">#</th>
                                <th>Sinh viên</th>
                                <th class="text-center">Tỷ lệ đạt PLO</th>
                                <th class="text-center">PLO đo được</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_students as $i => $s):
                                $rank      = $i + 1;
                                $rankClass = $rank === 1 ? 'rank--gold' : ($rank === 2 ? 'rank--silver' : ($rank === 3 ? 'rank--bronze' : 'rank--gray'));
                                $rankIcon  = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : ''));
                                $pct       = (float)(($s['overall_pct'] ?? 0));
                                $barWidth  = min(100, $pct);
                                $pColor    = $pct >= 70 ? 'good' : ($pct >= 50 ? 'mid' : 'low');
                            ?>
                                <tr class="student-rank-row">
                                    <td class="text-center col-rank">
                                        <?php if ($rank <= 3): ?>
                                            <span class="rank-icon <?= $rankClass ?>"><?= $rankIcon ?></span>
                                        <?php else: ?>
                                            <span class="rank-num <?= $rankClass ?>"><?= $rank ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="student-avatar-sm">
                                                <?= strtoupper(mb_substr($s['full_name'] ?? $s['username'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="student-full-name"><?= htmlspecialchars($s['full_name'] ?? $s['username']) ?></div>
                                                <div class="student-username text-muted">@<?= htmlspecialchars($s['username']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="student-pct-cell">
                                            <div class="student-bar-track">
                                                <div class="student-bar-fill student-fill--<?= $pColor ?>"
                                                     style="width:<?= $barWidth ?>%"></div>
                                            </div>
                                            <span class="score-display score--<?= $pColor ?>">
                                                <?= number_format($pct, 1) ?>%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-gray">
                                            <?= (int)($s['plos_measured'] ?? 0) ?> PLO
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Right: Mini bar chart of top 5 -->
                <?php
                $top5   = array_slice($top_students, 0, 5);
                $maxPct = max(array_column($top5, 'overall_pct'), 100);
                ?>
                <?php if (!empty($top5)): ?>
                    <div class="top5-chart-wrap">
                        <h4 class="top5-chart-title">Top 5 — Biểu đồ so sánh</h4>
                        <?php foreach ($top5 as $i => $s):
                            $pct       = (float)(($s['overall_pct'] ?? 0));
                            $barWidth   = $maxPct > 0 ? ($pct / $maxPct) * 100 : 0;
                            $pColor     = $pct >= 70 ? 'good' : ($pct >= 50 ? 'mid' : 'low');
                            $name       = $s['full_name'] ?? $s['username'];
                            $shortName  = mb_strlen($name) > 18 ? mb_substr($name, 0, 16) . '…' : $name;
                        ?>
                            <div class="top5-bar-item">
                                <div class="top5-bar-label">
                                    <span class="top5-rank-badge"><?= $i + 1 ?></span>
                                    <span class="top5-name" title="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($shortName) ?></span>
                                </div>
                                <div class="top5-bar-track">
                                    <div class="top5-bar-fill top5-fill--<?= $pColor ?>"
                                         style="width:<?= $barWidth ?>%"></div>
                                </div>
                                <span class="score-display score--<?= $pColor ?>" style="font-size:11px;min-width:44px;text-align:right">
                                    <?= number_format($pct, 1) ?>%
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php endif; /* end programs exist */ ?>

<script>
(function() {
    // ── Program selector navigation ──────────────────────────────────
    const select = document.getElementById('programSelect');
    if (select) {
        select.addEventListener('change', function() {
            const id = this.value;
            if (id) {
                window.location.href = '/admin/report/attainment?program_id=' + encodeURIComponent(id);
            }
        });
    }

    // ── Print button ───────────────────────────────────────────────
    document.getElementById('printBtn')?.addEventListener('click', function() {
        window.print();
    });

    // ── Animate bar chart on load ───────────────────────────────────
    function animateBars() {
        document.querySelectorAll('.bar-row').forEach(function(row) {
            const delay = parseInt(row.dataset.delay || 0, 10);
            const track = row.querySelector('.bar-fill');
            if (!track) return;
            const targetWidth = track.style.width;
            track.style.width = '0%';
            setTimeout(function() {
                track.style.transition = 'width 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
                track.style.width = targetWidth;
            }, delay);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', animateBars);
    } else {
        setTimeout(animateBars, 100);
    }
})();
</script>

<style>
/* ── Page Header ─────────────────────────────────────────────── */
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

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.mt-8 { margin-top: 8px; }

/* Chart legend */
.chart-legend {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}
.legend-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
}
.legend-text { font-size: 11px; color: var(--text-muted); }
.threshold-line {
    display: inline-block;
    width: 18px;
    height: 2px;
    background: rgba(245,158,11,.7);
    border-radius: 1px;
}

/* ── Bar Chart ─────────────────────────────────────────────────── */
.bar-chart-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.bar-row {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 16px;
    align-items: start;
}
@media (max-width: 700px) {
    .bar-row { grid-template-columns: 1fr; gap: 6px; }
}

.bar-label-col {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding-top: 2px;
}
.bar-plo-code {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: var(--accent);
    white-space: nowrap;
}
.bar-plo-desc {
    font-size: 11px;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}

.bar-track-col { flex: 1; min-width: 0; }

.bar-track {
    position: relative;
    height: 10px;
    background: var(--surface-2);
    border-radius: 5px;
    overflow: visible;
    margin-bottom: 5px;
}
.bar-fill {
    height: 100%;
    border-radius: 5px;
    width: 0%;
    transition: none;
}
.bar-fill--good { background: linear-gradient(90deg, #10b981, #34d399); }
.bar-fill--mid  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.bar-fill--low  { background: linear-gradient(90deg, #f43f5e, #fb7185); }

.bar-threshold {
    position: absolute;
    top: -3px;
    bottom: -3px;
    width: 2px;
    background: rgba(245,158,11,.8);
    border-radius: 1px;
    transform: translateX(-50%);
}

.bar-pct-label {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%) translateX(calc(100% + 8px));
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    white-space: nowrap;
}
.bar-pct-label--good { color: var(--emerald); }
.bar-pct-label--mid  { color: var(--amber); }
.bar-pct-label--low  { color: var(--rose); }

.bar-pass-rate {
    display: flex;
    align-items: center;
    gap: 6px;
}
.pass-rate-label {
    font-size: 11px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
}

/* ── PLO Detail Table ──────────────────────────────────────────── */
.cell-desc {
    display: block;
    max-width: 240px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--text-secondary);
    font-size: 12px;
}

.score-display { font-weight: 700; font-size: 13px; }
.score--good { color: var(--emerald); }
.score--mid  { color: var(--amber); }
.score--low  { color: var(--rose); }

.mini-progress-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
}
.mini-progress-track {
    flex: 1;
    height: 5px;
    background: var(--surface-2);
    border-radius: 3px;
    overflow: hidden;
    min-width: 60px;
}
.mini-progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 1s cubic-bezier(.16,1,.3,1);
}
.mini-fill--good { background: linear-gradient(90deg, #10b981, #34d399); }
.mini-fill--mid  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.mini-fill--low  { background: linear-gradient(90deg, #f43f5e, #fb7185); }
.mini-pct-label {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 11px;
    color: var(--text-secondary);
    white-space: nowrap;
    min-width: 38px;
    text-align: right;
}

.clo-pills { display: flex; flex-wrap: wrap; gap: 4px; }
.clo-pill {
    font-size: 10px;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    padding: 2px 7px;
    background: var(--surface-2);
    color: var(--text-secondary);
    border-radius: 4px;
}

.text-center { text-align: center; }
.text-muted { color: var(--text-muted); }
.text-sm { font-size: 12px; }

/* ── Top Students ──────────────────────────────────────────────── */
.top-students-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 900px) {
    .top-students-layout { grid-template-columns: 1fr; }
    .top5-chart-wrap { display: none; }
}

.top-students-table-wrap { overflow-x: auto; }

.col-rank { width: 48px; }
.rank-icon {
    font-size: 18px;
    line-height: 1;
}
.rank--gold   { filter: drop-shadow(0 0 4px rgba(245,158,11,.6)); }
.rank--silver { filter: drop-shadow(0 0 4px rgba(148,163,184,.5)); }
.rank--bronze { filter: drop-shadow(0 0 4px rgba(180,83,9,.5)); }
.rank-num {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 13px;
    color: var(--text-muted);
}

.student-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.student-avatar-sm {
    width: 32px;
    height: 32px;
    background: var(--accent-soft);
    border: 2px solid var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: var(--accent);
    flex-shrink: 0;
}
.student-full-name { font-weight: 600; font-size: 13px; color: var(--text-primary); }
.student-username  { font-size: 11px; }

.student-pct-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
}
.student-bar-track {
    flex: 1;
    height: 6px;
    background: var(--surface-2);
    border-radius: 3px;
    overflow: hidden;
    min-width: 60px;
}
.student-bar-fill {
    height: 100%;
    border-radius: 3px;
}
.student-fill--good { background: linear-gradient(90deg, #10b981, #34d399); }
.student-fill--mid  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.student-fill--low  { background: linear-gradient(90deg, #f43f5e, #fb7185); }

/* Top 5 chart */
.top5-chart-wrap {
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-md);
    padding: 16px;
}
.top5-chart-title {
    font-family: 'Lexend Deca', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 14px;
}
.top5-bar-item {
    display: grid;
    grid-template-columns: 100px 1fr 44px;
    gap: 8px;
    align-items: center;
    margin-bottom: 10px;
}
.top5-bar-item:last-child { margin-bottom: 0; }
.top5-bar-label {
    display: flex;
    align-items: center;
    gap: 6px;
}
.top5-rank-badge {
    width: 18px;
    height: 18px;
    background: var(--accent-soft);
    color: var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Lexend Deca', sans-serif;
    font-size: 10px;
    font-weight: 700;
    flex-shrink: 0;
}
.top5-name {
    font-size: 11px;
    color: var(--text-secondary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.top5-bar-track {
    height: 8px;
    background: var(--surface-2);
    border-radius: 4px;
    overflow: hidden;
}
.top5-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.8s cubic-bezier(.16,1,.3,1);
}
.top5-fill--good { background: linear-gradient(90deg, #10b981, #34d399); }
.top5-fill--mid  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.top5-fill--low  { background: linear-gradient(90deg, #f43f5e, #fb7185); }

/* ── Responsive ─────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .header-actions { flex-direction: column; width: 100%; }
    .form-control, .btn { width: 100%; }
}
</style>
