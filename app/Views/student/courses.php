<?php /* app/Views/student/courses.php */ ?>
<?php $pageTitle = 'Môn học đang theo học'; ?>

<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Danh sách môn học</h3>
        <span class="section-badge"><?= count($courses) ?> môn</span>
    </div>
    <?php if (empty($courses)): ?>
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        <p>Bạn chưa được đăng ký môn học nào.</p>
    </div>
    <?php else: ?>
    <div class="courses-grid">
        <?php foreach ($courses as $c): ?>
        <?php
            $gradedPct = $c['total_rubrics'] > 0
                ? round($c['graded_rubrics'] / $c['total_rubrics'] * 100)
                : 0;
        ?>
        <div class="course-card">
            <div class="course-card-header">
                <span class="course-badge"><?= htmlspecialchars($c['code']) ?></span>
                <span class="course-semester"><?= htmlspecialchars($c['semester']) ?></span>
            </div>
            <h4 class="course-card-title"><?= htmlspecialchars($c['name']) ?></h4>
            <div class="course-card-meta">
                <span>👨‍🏫 <?= htmlspecialchars($c['lecturer_name']) ?></span>
                <span>📚 <?= $c['credits'] ?> tín chỉ</span>
            </div>
            <div class="course-progress-section">
                <div class="course-progress-label">
                    <span>Tiến độ chấm điểm</span>
                    <span><?= $c['graded_rubrics'] ?>/<?= $c['total_rubrics'] ?> tiêu chí</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill <?= $gradedPct >= 70 ? 'fill--good' : ($gradedPct >= 30 ? 'fill--mid' : 'fill--low') ?>"
                         style="width:<?= $gradedPct ?>%"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.courses-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.course-card {
    background:var(--surface-0);
    border:1px solid var(--surface-2);
    border-radius:var(--radius-lg);
    padding:18px;
    display:flex; flex-direction:column; gap:10px;
    transition:border-color var(--transition);
}
.course-card:hover { border-color:var(--surface-3); }
.course-card-header { display:flex; justify-content:space-between; align-items:center; }
.course-semester { font-size:11px; color:var(--text-muted); }
.course-card-title { font-family:'Lexend Deca',sans-serif; font-weight:600; font-size:14px; color:var(--text-primary); line-height:1.4; }
.course-card-meta { display:flex; flex-direction:column; gap:4px; font-size:12px; color:var(--text-secondary); }
.course-progress-section { margin-top:4px; }
.course-progress-label { display:flex; justify-content:space-between; font-size:11px; color:var(--text-muted); margin-bottom:6px; }
</style>
