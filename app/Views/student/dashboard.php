<?php /* app/Views/student/dashboard.php */
$pageTitle = 'E-Portfolio — ' . htmlspecialchars($student_name);
?>

<!-- Student Statistics Overview -->
<div class="stats-grid">
    <div class="stat-card stat-card--primary">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= $overall_pct ?>%</div>
            <div class="stat-label">Mức đạt năng lực tổng thể</div>
        </div>
        <div class="stat-ring">
            <svg viewBox="0 0 36 36" class="circular-chart">
                <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                <path class="circle" stroke-dasharray="<?= $overall_pct ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
            </svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= $achieved_count ?><span class="stat-unit">/<?= $total_plo ?></span></div>
            <div class="stat-label">PLO đạt chuẩn (≥70%)</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= count($clo_data_course) ?></div>
            <div class="stat-label">Môn học đang theo học</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--amber">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= count($recent_scores) ?></div>
            <div class="stat-label">Bài kiểm tra đã chấm</div>
        </div>
    </div>
</div>

<!-- Main Dashboard Charts Section -->
<div class="charts-row">
    <!-- Program Learning Outcomes Radar Chart -->
    <div class="chart-card chart-card--radar">
        <div class="chart-header">
            <div>
                <h3 class="chart-title">Biểu đồ năng lực (PLO)</h3>
                <p class="chart-subtitle">Mức độ đạt chuẩn đầu ra chương trình</p>
            </div>
            <div class="chart-legend">
                <span class="legend-dot" style="background: #6366f1"></span>
                <span class="legend-text">Của bạn</span>
            </div>
        </div>
        <div class="chart-canvas-wrapper">
            <canvas id="ploRadarChart" aria-label="Biểu đồ radar PLO"></canvas>
        </div>
    </div>

    <!-- PLO Performance Bar Chart -->
    <div class="chart-card chart-card--bar">
        <div class="chart-header">
            <div>
                <h3 class="chart-title">Chi tiết từng PLO</h3>
                <p class="chart-subtitle">Thanh tiến trình theo chuẩn đầu ra</p>
            </div>
        </div>
        <div class="plo-bars">
            <?php foreach ($plo_data as $plo): ?>
            <?php $pct = (float)$plo['achieved_percentage']; ?>
            <div class="plo-bar-item">
                <div class="plo-bar-header">
                    <div class="plo-bar-info">
                        <span class="plo-code"><?= htmlspecialchars($plo['code']) ?></span>
                        <span class="plo-desc"><?= htmlspecialchars(mb_substr($plo['description'], 0, 55)) ?>...</span>
                    </div>
                    <span class="plo-pct <?= $pct >= 70 ? 'pct--good' : ($pct >= 50 ? 'pct--mid' : 'pct--low') ?>">
                        <?= number_format($pct, 1) ?>%
                    </span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill <?= $pct >= 70 ? 'fill--good' : ($pct >= 50 ? 'fill--mid' : 'fill--low') ?>"
                         style="width: <?= min($pct, 100) ?>%"
                         role="progressbar"
                         aria-valuenow="<?= $pct ?>"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                    <div class="progress-threshold" style="left: 70%" title="Ngưỡng đạt 70%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Course Learning Outcomes Breakdown -->
<?php foreach ($clo_data_course as $courseId => $item): ?>
<div class="section-card">
    <div class="section-header">
        <div class="section-title-group">
            <span class="course-badge"><?= htmlspecialchars($item['course']['code']) ?></span>
            <h3 class="section-title"><?= htmlspecialchars($item['course']['name']) ?></h3>
        </div>
        <span class="course-credits"><?= $item['course']['credits'] ?> tín chỉ</span>
    </div>
    <div class="clo-grid">
        <?php foreach ($item['clos'] as $clo): ?>
        <?php $pct = (float)$clo['achieved_percentage']; ?>
        <div class="clo-card <?= $pct >= 70 ? 'clo--achieved' : ($pct > 0 ? 'clo--partial' : '') ?>">
            <div class="clo-code"><?= htmlspecialchars($clo['code']) ?></div>
            <div class="clo-pct"><?= number_format($pct, 0) ?>%</div>
            <div class="clo-desc"><?= htmlspecialchars(mb_substr($clo['description'], 0, 60)) ?>...</div>
            <?php if ($clo['bloom_level']): ?>
            <div class="clo-bloom">Bloom L<?= $clo['bloom_level'] ?></div>
            <?php endif; ?>
            <div class="clo-progress">
                <div class="clo-progress-fill" style="width: <?= min($pct, 100) ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- Recent Student Assessment Activity -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Hoạt động chấm điểm gần đây</h3>
        <span class="section-badge"><?= count($recent_scores) ?> bài</span>
    </div>
    <?php if (empty($recent_scores)): ?>
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        <p>Chưa có điểm nào được ghi nhận</p>
    </div>
    <?php else: ?>
    <div class="activity-table-wrapper">
        <table class="activity-table">
            <thead>
                <tr>
                    <th>Bài kiểm tra</th>
                    <th>Tiêu chí</th>
                    <th>CLO</th>
                    <th>Điểm</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recent_scores as $s): ?>
            <?php $pct = ($s['max_score'] > 0) ? round($s['score'] / $s['max_score'] * 100) : 0; ?>
            <tr>
                <td>
                    <div class="activity-title"><?= htmlspecialchars($s['title']) ?></div>
                    <div class="activity-type"><?= htmlspecialchars($s['type']) ?></div>
                </td>
                <td class="text-sm"><?= htmlspecialchars($s['criteria_name']) ?></td>
                <td><span class="tag"><?= htmlspecialchars($s['clo_code']) ?></span></td>
                <td>
                    <span class="score-display <?= $pct >= 70 ? 'score--good' : ($pct >= 50 ? 'score--mid' : 'score--low') ?>">
                        <?= number_format($s['score'], 1) ?>/<?= number_format($s['max_score'], 0) ?>
                    </span>
                </td>
                <td class="text-muted text-sm"><?= date('d/m H:i', strtotime($s['graded_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
// ── Student PLO Radar Chart Visualization ────────────────────────
const ploLabels  = <?= json_encode(array_column($plo_data, 'code'), JSON_UNESCAPED_UNICODE) ?>;
const ploScores  = <?= json_encode(array_map(fn($p) => (float)$p['achieved_percentage'], $plo_data)) ?>;

const radarCtx = document.getElementById('ploRadarChart').getContext('2d');
new Chart(radarCtx, {
    type: 'radar',
    data: {
        labels: ploLabels,
        datasets: [
            {
                label: 'Mức đạt (%)',
                data: ploScores,
                backgroundColor: 'rgba(99, 102, 241, 0.15)',
                borderColor: '#6366f1',
                borderWidth: 2.5,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            },
            {
                // Performance benchmark threshold at 70%
                label: 'Ngưỡng đạt chuẩn (70%)',
                data: ploLabels.map(() => 70),
                backgroundColor: 'rgba(234, 179, 8, 0.05)',
                borderColor: 'rgba(234, 179, 8, 0.5)',
                borderWidth: 1.5,
                borderDash: [5, 4],
                pointRadius: 0,
            }
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    font: { family: 'Lexend Deca', size: 12 },
                    color: '#64748b',
                    boxWidth: 10,
                    padding: 16,
                }
            },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.dataset.label}: ${ctx.raw.toFixed(1)}%`,
                }
            }
        },
        scales: {
            r: {
                min: 0,
                max: 100,
                ticks: {
                    stepSize: 25,
                    color: '#94a3b8',
                    font: { size: 10 },
                    backdropColor: 'transparent',
                },
                grid:     { color: 'rgba(148, 163, 184, 0.2)' },
                angleLines:{ color: 'rgba(148, 163, 184, 0.15)' },
                pointLabels: {
                    font: { family: 'Lexend Deca', size: 13, weight: '600' },
                    color: '#334155',
                },
            },
        },
    },
});

// ── Animate dashboard progress indicators on scroll ─────────────
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.style.transition = 'width 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.progress-fill, .clo-progress-fill').forEach(el => observer.observe(el));
</script>
