<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>E-Portfolio — <?= htmlspecialchars($student['full_name'] ?? '') ?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@400;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600&display=swap');

* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Be Vietnam Pro',sans-serif; background:#fff; color:#1e293b; font-size:12px; }

.page { max-width:800px; margin:0 auto; padding:40px; }

/* Header */
.header { display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:24px; border-bottom:3px solid #6366f1; margin-bottom:28px; }
.header-left {}
.badge-school { font-size:10px; text-transform:uppercase; letter-spacing:1px; color:#6366f1; font-weight:700; margin-bottom:6px; }
.student-name { font-family:'Lexend Deca',sans-serif; font-size:26px; font-weight:800; color:#0f172a; }
.student-sub { font-size:12px; color:#64748b; margin-top:4px; }

.header-right { text-align:right; }
.portfolio-label { font-family:'Lexend Deca',sans-serif; font-size:20px; font-weight:700; color:#6366f1; }
.generated-at { font-size:10px; color:#94a3b8; margin-top:4px; }

/* Summary banner */
.summary-banner {
    background:linear-gradient(135deg, #6366f1 0%, #0ea5e9 100%);
    border-radius:12px; padding:20px 24px; color:white; margin-bottom:24px;
    display:flex; gap:32px;
}
.summary-item { text-align:center; }
.summary-val { font-family:'Lexend Deca',sans-serif; font-size:28px; font-weight:800; }
.summary-lbl { font-size:10px; opacity:.8; text-transform:uppercase; letter-spacing:.5px; margin-top:2px; }

/* Section */
.section { margin-bottom:24px; }
.section-title { font-family:'Lexend Deca',sans-serif; font-size:14px; font-weight:700; color:#334155; text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px; padding-bottom:6px; border-bottom:1px solid #e2e8f0; }

/* PLO table */
.plo-table { width:100%; border-collapse:collapse; }
.plo-table th { background:#f8fafc; padding:8px 10px; font-size:10px; text-transform:uppercase; letter-spacing:.4px; color:#64748b; font-weight:700; text-align:left; border-bottom:2px solid #e2e8f0; }
.plo-table td { padding:10px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.plo-table tr:last-child td { border-bottom:none; }

.plo-code { font-family:'Lexend Deca',sans-serif; font-weight:700; font-size:12px; color:#6366f1; }
.plo-pct { font-family:'Lexend Deca',sans-serif; font-weight:700; font-size:14px; }
.pct-good { color:#10b981; }
.pct-mid  { color:#f59e0b; }
.pct-low  { color:#f43f5e; }

.bar-track { height:6px; background:#f1f5f9; border-radius:3px; min-width:100px; }
.bar-fill { height:6px; border-radius:3px; }
.bar-good { background:#10b981; }
.bar-mid  { background:#f59e0b; }
.bar-low  { background:#f43f5e; }

.status-badge { font-size:10px; padding:2px 8px; border-radius:20px; font-weight:700; white-space:nowrap; }
.status-pass { background:#d1fae5; color:#065f46; }
.status-fail { background:#fee2e2; color:#991b1b; }

/* Footer */
.footer { margin-top:32px; padding-top:16px; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; font-size:10px; color:#94a3b8; }

/* Print */
@media print {
    body { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .no-print { display:none !important; }
    .page { padding:20px; }
}
</style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="badge-school">iSchool — Hệ thống OBE E-Portfolio</div>
            <div class="student-name"><?= htmlspecialchars($student['full_name'] ?? 'Sinh viên') ?></div>
            <div class="student-sub"><?= htmlspecialchars($student['username'] ?? '') ?> · <?= htmlspecialchars($student['email'] ?? '') ?></div>
        </div>
        <div class="header-right">
            <div class="portfolio-label">E-Portfolio</div>
            <div class="generated-at">Xuất ngày: <?= date('d/m/Y H:i') ?></div>
        </div>
    </div>

    <!-- Summary Banner -->
    <?php
        $overallPct = count($ploData) > 0
            ? round(array_sum(array_column($ploData, 'achieved_percentage')) / count($ploData), 1)
            : 0;
        $passCount = count(array_filter($ploData, fn($p) => $p['achieved_percentage'] >= 70));
    ?>
    <div class="summary-banner">
        <div class="summary-item">
            <div class="summary-val"><?= $overallPct ?>%</div>
            <div class="summary-lbl">Năng lực tổng thể</div>
        </div>
        <div class="summary-item">
            <div class="summary-val"><?= $passCount ?>/<?= count($ploData) ?></div>
            <div class="summary-lbl">PLO đạt chuẩn</div>
        </div>
        <div class="summary-item">
            <div class="summary-val"><?= date('Y') ?></div>
            <div class="summary-lbl">Năm học</div>
        </div>
    </div>

    <!-- PLO Attainment Table -->
    <div class="section">
        <div class="section-title">Bảng đạt chuẩn đầu ra chương trình (PLO)</div>
        <table class="plo-table">
            <thead>
                <tr>
                    <th>Mã PLO</th>
                    <th>Mô tả chuẩn đầu ra</th>
                    <th>Phân loại</th>
                    <th>Mức đạt</th>
                    <th>Tiến trình</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ploData as $p): ?>
                <?php $pct = (float)$p['achieved_percentage']; ?>
                <tr>
                    <td class="plo-code"><?= htmlspecialchars($p['code']) ?></td>
                    <td><?= htmlspecialchars(mb_substr($p['description'], 0, 70)) ?>...</td>
                    <td style="color:#64748b;font-size:11px;"><?= htmlspecialchars($p['category'] ?? '—') ?></td>
                    <td class="plo-pct <?= $pct >= 70 ? 'pct-good' : ($pct >= 50 ? 'pct-mid' : 'pct-low') ?>">
                        <?= number_format($pct, 1) ?>%
                    </td>
                    <td>
                        <div class="bar-track">
                            <div class="bar-fill <?= $pct >= 70 ? 'bar-good' : ($pct >= 50 ? 'bar-mid' : 'bar-low') ?>"
                                 style="width:<?= min($pct, 100) ?>%"></div>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge <?= $pct >= 70 ? 'status-pass' : 'status-fail' ?>">
                            <?= $pct >= 70 ? '✓ Đạt' : '✗ Chưa đạt' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Note -->
    <div class="section">
        <div class="section-title">Ghi chú</div>
        <p style="font-size:11px;color:#64748b;line-height:1.7">
            Báo cáo này được tạo tự động bởi Hệ thống OBE &amp; E-Portfolio của iSchool.
            Mức đạt chuẩn được tính dựa trên điểm thực tế của sinh viên trong các bài kiểm tra đã được công bố,
            thông qua thuật toán ánh xạ CLO-PLO (Weighted Aggregation).
            <strong>Ngưỡng đạt chuẩn: ≥ 70%.</strong>
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <span>iSchool OBE &amp; E-Portfolio System v2.0</span>
        <span>Tài liệu này có giá trị xác nhận năng lực học tập</span>
        <span><?= date('d/m/Y') ?></span>
    </div>
</div>

<!-- Print button (ẩn khi in) -->
<div class="no-print" style="text-align:center;padding:20px">
    <button onclick="window.print()" style="padding:10px 28px;background:#6366f1;color:white;border:none;border-radius:8px;font-family:'Lexend Deca',sans-serif;font-weight:600;font-size:14px;cursor:pointer">
        🖨️ In PDF / Lưu PDF
    </button>
</div>
</body>
</html>
