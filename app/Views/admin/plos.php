<?php /* app/Views/admin/plos.php */ ?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading"><?= htmlspecialchars($pageTitle ?? 'Báo cáo đạt chuẩn PLO') ?></h2>
        <p class="page-sub">Thống kê tỷ lệ đạt chuẩn đầu ra chương trình đào tạo theo PLO</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-primary btn-sm" id="btnAddPlo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Thêm PLO
        </button>
        <select id="programFilter" class="form-control filter-select" aria-label="Chọn chương trình đào tạo">
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
    <div class="card section-card">
        <div class="card-header">
            <div class="section-title-group">
                <h3 class="card-title">Bảng đạt chuẩn theo PLO</h3>
                <span class="badge badge-gray"><?= count($plo_report) ?> PLO</span>
            </div>
            <div class="legend-group">
                <div class="legend-item">
                    <span class="legend-dot legend-dot--emerald"></span>
                    <span class="legend-text">≥ 70% Đạt</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot legend-dot--amber"></span>
                    <span class="legend-text">50–69% Cần cải thiện</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot legend-dot--rose"></span>
                    <span class="legend-text">< 50% Chưa đạt</span>
                </div>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table striped">
                <thead>
                    <tr>
                        <th class="col-plo-code">Mã PLO</th>
                        <th class="col-plo-desc">Mô tả</th>
                        <th class="col-plo-cat">Danh mục</th>
                        <th class="text-center col-plo-students">SV đo</th>
                        <th class="text-center col-plo-avg">Trung bình %</th>
                        <th class="text-center col-plo-pass">Đạt</th>
                        <th class="text-center col-plo-level">Mức đạt</th>
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
                                <span class="<?= $avgClass ?> plo-avg-value"><?= $avg > 0 ? number_format($avg, 1) . '%' : '—' ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($measured > 0): ?>
                                    <div class="cell-progress">
                                        <div class="mini-progress-track">
                                            <div class="mini-progress-fill <?= $barClass ?>" style="width:<?= min($passRate, 100) ?>%"></div>
                                        </div>
                                        <span class="mini-pass-count"><?= $passed ?>/<?= $measured ?></span>
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
        <div class="summary-bar">
            <div class="summary-stat">
                <span class="label">Tổng hợp toàn chương trình</span>
                <span class="text-muted summary-label-small"><?= $totalPassed ?> / <?= $totalMeasured ?> SV đạt chuẩn</span>
            </div>
            <div class="summary-progress-row">
                <div class="progress-track progress-track--main">
                    <div class="progress-fill <?= $overallClass ?>" style="width:<?= min($overallPct, 100) ?>%"></div>
                    <div class="progress-threshold" style="left:70%"></div>
                </div>
                <span class="summary-pct <?= $overallPct >= 70 ? 'text-emerald' : ($overallPct >= 50 ? 'text-amber' : 'text-rose') ?>">
                    <?= $overallPct ?>%
                </span>
            </div>
        </div>
    </div>

    <!-- Top Students Section -->
    <?php if (!empty($top_students)): ?>
    <div class="card section-card">
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
                        <span class="student-pct <?= $pct >= 70 ? 'text-emerald' : ($pct >= 50 ? 'text-amber' : 'text-rose') ?>">
                            <?= number_format($pct, 1) ?>%
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

<!-- ── Add PLO Modal ─────────────────────────────────────────────── -->
<div class="modal-overlay" id="ploModal" role="dialog" aria-modal="true">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="ploModalTitle">Thêm chuẩn đầu ra (PLO)</h3>
            <button class="modal-close" id="ploModalClose">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="ploForm" novalidate>
            <input type="hidden" name="program_id" id="plo-program-id" value="<?= htmlspecialchars($program['id'] ?? '') ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="plo-code">Mã PLO <span class="required">*</span></label>
                    <input type="text" id="plo-code" name="code" class="form-control" placeholder="VD: PLO1" maxlength="20">
                    <span class="field-error" id="err-code"></span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="plo-desc">Mô tả <span class="required">*</span></label>
                    <textarea id="plo-desc" name="description" class="form-control" rows="3" placeholder="Mô tả chuẩn đầu ra..."></textarea>
                    <span class="field-error" id="err-description"></span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="plo-category">Danh mục</label>
                    <select id="plo-category" name="category" class="form-control">
                        <option value="">— Chọn danh mục —</option>
                        <option value="Knowledge">Knowledge (Kiến thức)</option>
                        <option value="Skill">Skill (Kỹ năng)</option>
                        <option value="Attitude">Attitude (Thái độ)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="ploModalCancel">Hủy</button>
                <button type="submit" class="btn btn-primary" id="ploModalSubmit">
                    <span class="btn-label">Thêm PLO</span>
                    <span class="btn-spinner" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="spin"><circle cx="12" cy="12" r="10" stroke-dasharray="30 70"/></svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

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
    color: #0f172a;
    margin-bottom: 2px;
}
.page-sub { font-size: 13px; color: #94a3b8; }
.header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.mt-8 { margin-top: 8px; }

/* ── Filter Select ─────────────────────────────────────────────── */
.filter-select { min-width: 240px; }

/* ── Legend ──────────────────────────────────────────────────────── */
.legend-group { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.legend-item { display: flex; align-items: center; gap: 5px; }
.legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.legend-dot--emerald { background: #10b981; }
.legend-dot--amber   { background: #f59e0b; }
.legend-dot--rose    { background: #ef4444; }
.legend-text { font-size: 11px; color: #94a3b8; }

/* ── Table Column Widths ────────────────────────────────────────── */
.col-plo-code   { min-width: 90px; }
.col-plo-desc   { min-width: 280px; }
.col-plo-cat    { min-width: 120px; }
.col-plo-students { min-width: 70px; }
.col-plo-avg    { min-width: 100px; }
.col-plo-pass   { min-width: 130px; }
.col-plo-level  { min-width: 110px; }

/* ── Cell Styles ────────────────────────────────────────────────── */
.cell-primary { font-size: 13px; color: #0f172a; }
.cell-progress {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
}
.plo-avg-value { font-weight: 700; font-size: 14px; }
.mini-pass-count { font-size: 11px; font-weight: 500; color: #94a3b8; }

/* ── Mini Progress Bar ─────────────────────────────────────────── */
.mini-progress-track {
    height: 5px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
    width: 70px;
    flex-shrink: 0;
}
.mini-progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1);
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
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    transition: border-color 0.2s ease;
}
.student-rank-card:hover { border-color: #cbd5e1; }

.rank-badge {
    width: 28px;
    height: 28px;
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
.student-name {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.student-user {
    font-size: 11px;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.student-bar-wrap { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.student-pct { font-weight: 700; font-size: 13px; min-width: 44px; text-align: right; }

/* ── Progress Track (PLO summary) ──────────────────────────────── */
.progress-track--main {
    flex: 1;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    position: relative;
    overflow: visible;
}
.progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);
}
.fill--good { background: linear-gradient(90deg, #10b981, #34d399); }
.fill--mid  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.fill--low  { background: linear-gradient(90deg, #ef4444, #fb7185); }
.progress-threshold {
    position: absolute;
    top: -2px;
    bottom: -2px;
    width: 2px;
    background: rgba(245, 158, 11, 0.6);
    border-radius: 1px;
}

/* ── Summary Bar ────────────────────────────────────────────────── */
.summary-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}
.summary-stat {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.summary-stat .label {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
}
.summary-label-small { font-size: 11px; font-weight: 500; }
.summary-progress-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 200px;
}
.summary-pct {
    font-weight: 800;
    font-size: 18px;
    flex-shrink: 0;
}

/* ── Responsive ─────────────────────────────────────────────────── */
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

    // ── Add PLO Modal ─────────────────────────────────────────────
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const ploModal = document.getElementById('ploModal');
    const ploForm  = document.getElementById('ploForm');
    const ploSubmitBtn = document.getElementById('ploModalSubmit');

    document.getElementById('btnAddPlo')?.addEventListener('click', () => {
        ploForm.reset();
        ploModal.classList.add('open');
    });
    document.getElementById('ploModalClose')?.addEventListener('click', () => ploModal.classList.remove('open'));
    document.getElementById('ploModalCancel')?.addEventListener('click', () => ploModal.classList.remove('open'));
    ploModal?.addEventListener('click', e => { if (e.target === ploModal) ploModal.classList.remove('open'); });

    ploForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        ploSubmitBtn.disabled = true;
        const payload = {
            program_id: document.getElementById('plo-program-id').value,
            code: document.getElementById('plo-code').value.trim(),
            description: document.getElementById('plo-desc').value.trim(),
            category: document.getElementById('plo-category').value,
            _token: CSRF,
        };
        try {
            const res = await fetch('/admin/plos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok) throw data;
            window.Toast?.success('Đã thêm PLO mới.');
            ploModal.classList.remove('open');
            window.location.reload();
        } catch(err) {
            window.Toast?.error(err.error || 'Đã xảy ra lỗi.');
            ploSubmitBtn.disabled = false;
        }
    });
})();
</script>
