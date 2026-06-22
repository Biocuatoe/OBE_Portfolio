<?php /* app/Views/lecturer/dashboard.php */ ?>
<?php $pageTitle = 'Tổng quan giảng dạy'; ?>

<!-- Breadcrumb -->
<nav class="breadcrumb-nav">
    <a href="/lecturer/dashboard" class="breadcrumb-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Tổng quan giảng dạy
    </a>
</nav>

<?php if (!empty($pending_grading)): ?>
<div class="alert-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    Có <strong><?= count($pending_grading) ?></strong> bài kiểm tra chưa chấm đủ cho tất cả sinh viên
</div>
<?php else: ?>
<div class="alert-banner alert-banner--success">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
        <polyline points="20 6 9 17 4 12"/>
    </svg>
    Tất cả bài kiểm tra đã được chấm điểm đầy đủ!
</div>
<?php endif; ?>

<!-- Môn học phụ trách -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Môn học phụ trách</h3>
        <span class="section-badge"><?= count($assignments) ?> môn</span>
    </div>
    <div class="assignment-grid">
        <?php foreach ($assignments as $a): ?>
        <div class="assignment-card">
            <div class="assignment-card-header">
                <span class="course-badge"><?= htmlspecialchars($a['course_code']) ?></span>
                <span class="semester-tag"><?= htmlspecialchars($a['semester']) ?></span>
            </div>
            <h4 class="assignment-title"><?= htmlspecialchars($a['course_name']) ?></h4>
            <div class="assignment-stats">
                <div class="astat">
                    <div class="astat-val"><?= $a['student_count'] ?></div>
                    <div class="astat-lbl">Sinh viên</div>
                </div>
                <div class="astat">
                    <div class="astat-val"><?= $a['assessment_count'] ?></div>
                    <div class="astat-lbl">Bài kiểm tra</div>
                </div>
            </div>
            <div class="assignment-actions">
                <a href="/lecturer/assignment/<?= $a['id'] ?>/clos" class="action-btn">CLO</a>
                <a href="/lecturer/assignment/<?= $a['id'] ?>/assessments" class="action-btn">Bài KT</a>
                <a href="/admin/course/<?= $a['course_id'] ?? 0 ?>/mapping" class="action-btn action-btn--primary">Ma trận</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Danh sách bài kiểm tra cần chấm -->
<?php if (!empty($pending_grading)): ?>
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Cần hoàn thành chấm điểm</h3>
        <span class="section-badge urgent"><?= count($pending_grading) ?></span>
    </div>
    <div class="pending-list">
        <?php foreach ($pending_grading as $p): ?>
        <?php
            $total    = (int)$p['total_students'];
            $graded   = (int)$p['fully_graded_students'];
            $pct      = $total > 0 ? round($graded / $total * 100) : 0;
            $remaining = $total - $graded;
        ?>
        <div class="pending-item">
            <div class="pending-info">
                <span class="assessment-type-badge type-<?= $p['type'] ?>"><?= strtoupper($p['type']) ?></span>
                <div class="pending-title-group">
                    <span class="pending-title"><?= htmlspecialchars($p['title']) ?></span>
                    <span class="pending-remaining">Còn <strong><?= $remaining ?></strong> SV chưa có đủ điểm</span>
                </div>
            </div>
            <div class="pending-progress-group">
                <span class="pending-count"><?= $graded ?>/<?= $total ?> SV hoàn thành</span>
                <div class="progress-track" style="width:140px">
                    <div class="progress-fill <?= $pct >= 70 ? 'fill--good' : ($pct >= 30 ? 'fill--mid' : 'fill--low') ?>"
                         style="width:<?= $pct ?>%"></div>
                </div>
                <span class="pending-pct"><?= $pct ?>%</span>
            </div>
            <a href="/lecturer/assessment/<?= $p['assessment_id'] ?>/grade"
               class="btn btn-primary btn-grading">
                Chấm điểm →
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Average Score Chart -->
<?php if (!empty($avg_scores)): ?>
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Điểm trung bình các môn học</h3>
        <span class="section-badge"><?= count($avg_scores) ?> môn</span>
    </div>
    <div class="chart-container">
        <canvas id="averageScoreChart" width="400" height="100"></canvas>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const avgScores = <?= json_encode($avg_scores) ?>;
        
        if (avgScores.length > 0) {
            const ctx = document.getElementById('averageScoreChart')?.getContext('2d');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: avgScores.map(item => item.course_code),
                        datasets: [{
                            label: 'Điểm trung bình',
                            data: avgScores.map(item => parseFloat(item.avg_score) || 0),
                            backgroundColor: [
                                'rgba(99, 102, 241, 0.8)',
                                'rgba(14, 165, 233, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(244, 63, 94, 0.8)',
                            ],
                            borderColor: 'transparent',
                            borderRadius: 8,
                            borderSkipped: false,
                            hoverBackgroundColor: 'rgba(99, 102, 241, 1)',
                        }]
                    },
                    options: {
                        indexAxis: avgScores.length > 5 ? 'y' : 'x',
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    font: { size: 12, weight: '500' },
                                    color: '#64748b',
                                    usePointStyle: true,
                                    padding: 20,
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.85)',
                                borderColor: 'rgba(51, 65, 85, 0.5)',
                                borderWidth: 1,
                                padding: 10,
                                titleFont: { size: 12, weight: '600' },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        return 'Điểm: ' + context.parsed.y.toFixed(1) + ' / 10';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                stacked: false,
                                grid: {
                                    color: 'rgba(51, 65, 85, 0.3)',
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { size: 11 }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                max: 10,
                                grid: {
                                    color: 'rgba(51, 65, 85, 0.3)',
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { size: 11 }
                                }
                            }
                        }
                    }
                });
            }
        }
    });
    </script>
</div>
<?php endif; ?>

<style>
.alert-banner {
    display:flex; align-items:center; gap:10px;
    padding:12px 16px;
    background:rgba(245,158,11,.08);
    border:1px solid rgba(245,158,11,.2);
    border-radius:10px;
    color:#92400e; font-size:13px; font-weight:500;
}
.alert-banner--success {
    background:rgba(16,185,129,.06);
    border-color:rgba(16,185,129,.2);
    color:#065f46;
}

.assignment-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.assignment-card {
    background:#ffffff; border:1px solid #e2e8f0;
    border-radius:14px; padding:18px;
    display:flex; flex-direction:column; gap:12px;
    box-shadow:0 1px 3px rgba(0,0,0,0.04);
    transition:border-color 0.2s ease, box-shadow 0.2s ease;
}
.assignment-card:hover { border-color:#cbd5e1; box-shadow:0 4px 12px rgba(0,0,0,0.06); }
.assignment-card-header { display:flex; justify-content:space-between; align-items:center; }
.semester-tag { font-size:11px; color:#94a3b8; }
.assignment-title { font-family:'Lexend Deca',sans-serif; font-weight:600; font-size:14px; color:#0f172a; line-height:1.4; }

.assignment-stats { display:flex; gap:24px; }
.astat { text-align:center; }
.astat-val { font-family:'Lexend Deca',sans-serif; font-weight:700; font-size:24px; color:#0f172a; }
.astat-lbl { font-size:11px; color:#94a3b8; }

.assignment-actions { display:flex; gap:8px; flex-wrap:wrap; }
.action-btn {
    padding:6px 14px; border-radius:6px;
    font-size:12px; font-weight:600;
    background:#f8fafc; border:1px solid #e2e8f0;
    color:#64748b; text-decoration:none;
    transition:all 0.2s ease;
}
.action-btn:hover { border-color:#4f46e5; color:#0f172a; }
.action-btn--primary { background:rgba(79,70,229,0.08); border-color:rgba(79,70,229,0.2); color:#4f46e5; }

.pending-list { display:flex; flex-direction:column; gap:12px; }
.pending-item {
    display:flex; align-items:center; gap:16px;
    padding:14px 16px;
    background:#ffffff; border:1px solid #e2e8f0;
    border-radius:10px;
    transition:border-color 0.2s ease;
}
.pending-item:hover { border-color:#cbd5e1; }
.pending-info { display:flex; align-items:center; gap:10px; flex:1; min-width:0; }
.pending-title-group { display:flex; flex-direction:column; gap:2px; min-width:0; }
.pending-title { font-size:13px; font-weight:600; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.pending-remaining { font-size:11px; color:#ef4444; }

.pending-progress-group { display:flex; align-items:center; gap:10px; }
.pending-count { font-size:12px; color:#64748b; white-space:nowrap; }
.pending-pct { font-family:'Lexend Deca',sans-serif; font-weight:700; font-size:13px; color:#64748b; min-width:35px; }

.section-badge.urgent { background:rgba(239,68,68,0.1); color:#ef4444; }

.btn-grading { padding:7px 18px; font-size:13px; white-space:nowrap; }
</style>