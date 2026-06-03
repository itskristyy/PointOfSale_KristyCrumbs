<?php
// function/function_warehouse/laporan/cetak_stok.php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../auth.php';
checkRole(['admin', 'owner']);
include '../../connect.php';

$filter_kategori = trim($_GET['kategori'] ?? '');
$filter_status   = trim($_GET['status']   ?? '');
$filter_search   = trim($_GET['search']   ?? '');

$where_parts = [];
$params      = [];
$types       = '';

if ($filter_kategori !== '') {
    $where_parts[] = 'kategori = ?';
    $params[]      = $filter_kategori;
    $types        .= 's';
}
if ($filter_search !== '') {
    $where_parts[] = 'nama_bahan LIKE ?';
    $params[]      = '%' . $filter_search . '%';
    $types        .= 's';
}
if ($filter_status === 'aman') {
    $where_parts[] = 'stok > stok_minimum';
} elseif ($filter_status === 'menipis') {
    $where_parts[] = 'stok > 0 AND stok <= stok_minimum';
} elseif ($filter_status === 'habis') {
    $where_parts[] = 'stok <= 0';
}

$where_sql = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

$r_summary = mysqli_query($koneksi, "SELECT
    COUNT(*)                               AS total_jenis,
    SUM(stok * harga_modal)                AS total_nilai,
    SUM(stok > 0 AND stok <= stok_minimum) AS total_menipis,
    SUM(stok <= 0)                         AS total_habis
FROM tb_bahan");
$summary = mysqli_fetch_assoc($r_summary);

$stmt = mysqli_prepare($koneksi, "SELECT id_bahan, nama_bahan, kategori, stok, stok_minimum, satuan, harga_modal,
    (stok * harga_modal) AS nilai_stok
    FROM tb_bahan {$where_sql} ORDER BY kategori ASC, nama_bahan ASC");
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$bahans = [];
while ($row = mysqli_fetch_assoc($result)) $bahans[] = $row;
mysqli_stmt_close($stmt);

function getStatus(float $stok, float $min): string {
    if ($stok <= 0)    return 'Habis';
    if ($stok <= $min) return 'Menipis';
    return 'Aman';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Stok Bahan - Kristy Crumbs</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1a0a00;
            background: #fff;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #92400e;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 700;
            color: #3b1f0a;
            letter-spacing: 0.5px;
        }
        .header p {
            font-size: 11px;
            color: #a07850;
            margin-top: 2px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        .summary-box {
            border: 1px solid #d9c4a8;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
            background: #fffbf5;
        }
        .summary-box .label {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #a07850;
            margin-bottom: 4px;
        }
        .summary-box .value {
            font-size: 18px;
            font-weight: 700;
            color: #3b1f0a;
        }
        .summary-box .value.amber { color: #d97706; }
        .summary-box .value.red   { color: #dc2626; }
        .summary-box .value.brown { color: #92400e; font-size: 14px; }
        .filter-info {
            font-size: 10px;
            color: #a07850;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        thead tr {
            background: #92400e;
            color: #fff;
        }
        thead th {
            padding: 7px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        thead th:last-child, thead th:nth-child(4),
        thead th:nth-child(5), thead th:nth-child(6) { text-align: right; }
        tbody tr { border-bottom: 1px solid #f0e8d8; }
        tbody tr:nth-child(even) { background: #fffbf5; }
        tbody td { padding: 6px 8px; }
        tbody td:nth-child(4),
        tbody td:nth-child(5),
        tbody td:nth-child(6) { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 600;
        }
        .badge-aman    { background: #d1fae5; color: #065f46; }
        .badge-menipis { background: #fef3c7; color: #92400e; }
        .badge-habis   { background: #fee2e2; color: #991b1b; }
        .badge-bahan_baku, .badge-default { background: #fef3c7; color: #78350f; }
        .badge-minuman  { background: #dbeafe; color: #1e40af; }
        .badge-topping  { background: #fef9c3; color: #713f12; }
        .badge-kemasan  { background: #dcfce7; color: #166534; }
        .footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #a07850;
            border-top: 1px solid #d9c4a8;
            padding-top: 10px;
        }
        .sign-area {
            display: flex;
            gap: 80px;
            justify-content: flex-end;
            margin-top: 40px;
        }
        .sign-box { text-align: center; }
        .sign-box .line { border-top: 1px solid #3b1f0a; width: 120px; margin: 40px auto 4px; }
        .sign-box p { font-size: 10px; color: #3b1f0a; }
        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px;display:flex;gap:8px;">
        <button onclick="window.print()"
            style="padding:7px 18px;background:#92400e;color:#fff;border:none;border-radius:6px;font-weight:600;cursor:pointer;font-size:12px;">
            🖨️ Cetak / Print
        </button>
        <a href="index.php"
            style="padding:7px 18px;background:#fff;color:#92400e;border:1px solid #92400e;border-radius:6px;font-weight:600;text-decoration:none;font-size:12px;">
            ← Kembali
        </a>
    </div>

    <div class="header">
        <h1>🍞 KRISTY CRUMBS</h1>
        <p>Laporan Stok Bahan Baku &amp; Inventori</p>
        <p>Dicetak: <?= date('d F Y, H:i') ?> WIB</p>
        <?php if ($filter_kategori || $filter_status || $filter_search): ?>
            <p class="filter-info">Filter aktif: 
                <?= $filter_kategori ? 'Kategori: ' . htmlspecialchars(ucwords(str_replace('_',' ',$filter_kategori))) : '' ?>
                <?= $filter_status   ? ' | Status: '   . ucfirst($filter_status) : '' ?>
                <?= $filter_search   ? ' | Cari: '     . htmlspecialchars($filter_search) : '' ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="summary-grid">
        <div class="summary-box">
            <div class="label">Total Jenis</div>
            <div class="value"><?= number_format((int)$summary['total_jenis']) ?></div>
        </div>
        <div class="summary-box">
            <div class="label">Nilai Stok</div>
            <div class="value brown">Rp <?= number_format((float)$summary['total_nilai'], 0, ',', '.') ?></div>
        </div>
        <div class="summary-box">
            <div class="label">⚠ Menipis</div>
            <div class="value amber"><?= (int)$summary['total_menipis'] ?></div>
        </div>
        <div class="summary-box">
            <div class="label">🔴 Habis</div>
            <div class="value red"><?= (int)$summary['total_habis'] ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Nama Bahan</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Harga Modal</th>
                <th>Nilai Stok</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bahans)): ?>
                <tr><td colspan="7" style="text-align:center;padding:16px;color:#a07850;">Tidak ada data bahan.</td></tr>
            <?php endif; ?>
            <?php $no = 1; foreach ($bahans as $b):
                $st  = getStatus((float)$b['stok'], (float)$b['stok_minimum']);
                $kat = strtolower($b['kategori']);
            ?>
                <tr>
                    <td style="color:#a07850;"><?= $no++ ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($b['nama_bahan']) ?></td>
                    <td>
                        <span class="badge badge-<?= htmlspecialchars($kat) ?>">
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $b['kategori']))) ?>
                        </span>
                    </td>
                    <td>
                        <?= number_format((float)$b['stok'], 2, ',', '.') ?>
                        <small style="color:#a07850;"><?= htmlspecialchars($b['satuan']) ?></small>
                    </td>
                    <td>Rp <?= number_format((float)$b['harga_modal'], 0, ',', '.') ?></td>
                    <td style="font-weight:600;">Rp <?= number_format((float)$b['nilai_stok'], 0, ',', '.') ?></td>
                    <td>
                        <span class="badge badge-<?= strtolower($st) ?>">
                            <?= $st ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <span>Kristy Crumbs &copy; <?= date('Y') ?> — Sistem POS Internal</span>
        <span>Total bahan ditampilkan: <?= count($bahans) ?> item</span>
    </div>

    <div class="sign-area no-print">
        <!-- signature area only on physical print -->
    </div>
</body>
</html>
