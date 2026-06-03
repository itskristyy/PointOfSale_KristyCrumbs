<?php
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

$r_kat = mysqli_query($koneksi, "SELECT DISTINCT kategori FROM tb_bahan ORDER BY kategori ASC");
$kategori_list = [];
while ($row = mysqli_fetch_assoc($r_kat)) $kategori_list[] = $row['kategori'];

$stmt = mysqli_prepare($koneksi, "SELECT id_bahan, nama_bahan, kategori, stok, stok_minimum, satuan, harga_modal,
    (stok * harga_modal) AS nilai_stok
    FROM tb_bahan {$where_sql} ORDER BY nama_bahan ASC");
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$bahans = [];
while ($row = mysqli_fetch_assoc($result)) $bahans[] = $row;
mysqli_stmt_close($stmt);

function getStatus(float $stok, float $min): string
{
    if ($stok <= 0)    return 'habis';
    if ($stok <= $min) return 'menipis';
    return 'aman';
}

function getBadgeKat(string $kat): string
{
    return match (strtolower($kat)) {
        'topping'    => 'kc-badge-yellow',
        'minuman'    => 'kc-badge-blue',
        'bahan_baku', 'bahan baku' => 'kc-badge-brown',
        'kemasan'    => 'kc-badge-green',
        default      => 'kc-badge-brown',
    };
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="laporan_stok_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ['No', 'Nama Bahan', 'Kategori', 'Stok', 'Satuan', 'Harga Modal', 'Nilai Stok', 'Status']);
    $no = 1;
    foreach ($bahans as $b) {
        $st = getStatus((float)$b['stok'], (float)$b['stok_minimum']);
        fputcsv($out, [
            $no++,
            $b['nama_bahan'],
            $b['kategori'],
            number_format((float)$b['stok'], 2, ',', '.'),
            $b['satuan'],
            $b['harga_modal'],
            $b['nilai_stok'],
            ucfirst($st),
        ]);
    }
    fclose($out);
    exit;
}

$page_title = 'Laporan Stok Bahan';
$active     = 'laporan_werehouse';
include '../../../_layout.php';
?>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">
    <div class="kc-card" style="padding:16px 20px;">
        <div style="font-size:11px;font-weight:600;color:#a07850;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">Total Jenis Bahan</div>
        <div style="font-size:28px;font-weight:700;color:#3b1f0a;"><?= number_format((int)$summary['total_jenis']) ?></div>
    </div>
    <div class="kc-card" style="padding:16px 20px;">
        <div style="font-size:11px;font-weight:600;color:#a07850;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">Total Nilai Stok</div>
        <div style="font-size:24px;font-weight:700;color:#92400e;">Rp <?= number_format((float)$summary['total_nilai'], 0, ',', '.') ?></div>
    </div>
    <div class="kc-card" style="padding:16px 20px;">
        <div style="font-size:11px;font-weight:600;color:#a07850;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">⚠ Menipis</div>
        <div style="font-size:28px;font-weight:700;color:#d97706;"><?= (int)$summary['total_menipis'] ?></div>
    </div>
    <div class="kc-card" style="padding:16px 20px;">
        <div style="font-size:11px;font-weight:600;color:#a07850;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;">🔴 Habis</div>
        <div style="font-size:28px;font-weight:700;color:#dc2626;"><?= (int)$summary['total_habis'] ?></div>
    </div>
</div>

<div class="kc-card mb-3">
    <div class="kc-card-body" style="padding:10px 12px;">
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <input type="text" name="search" class="form-control form-control-sm"
                placeholder="Cari nama bahan..."
                value="<?= htmlspecialchars($filter_search) ?>"
                style="max-width:200px;">
            <select name="kategori" class="form-select form-select-sm" style="max-width:180px;">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategori_list as $kat): ?>
                    <option value="<?= htmlspecialchars($kat) ?>" <?= $filter_kategori === $kat ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $kat))) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-select form-select-sm" style="max-width:160px;">
                <option value="">Semua Status</option>
                <option value="aman" <?= $filter_status === 'aman'    ? 'selected' : '' ?>>Aman</option>
                <option value="menipis" <?= $filter_status === 'menipis' ? 'selected' : '' ?>>Menipis</option>
                <option value="habis" <?= $filter_status === 'habis'   ? 'selected' : '' ?>>Habis</option>
            </select>
            <button type="submit" class="btn-kc btn-kc-sm"><i class='bx bx-filter-alt'></i> Filter</button>
            <a href="index.php" class="btn-kc-outline"><i class='bx bx-reset'></i> Reset</a>
            <a href="index.php?export=csv&kategori=<?= urlencode($filter_kategori) ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($filter_search) ?>"
                class="btn-kc-outline" style="margin-left:auto;">
                <i class='bx bx-export'></i> Export CSV
            </a>
        </form>
    </div>
</div>

<div class="kc-card">
    <div class="kc-card-header">
        <span><i class='bx bx-bar-chart-alt-2'></i> Ringkasan Stok Semua Bahan</span>
        <span style="font-size:11px;color:#a07850;font-weight:400;"><?= count($bahans) ?> bahan</span>
    </div>
    <table class="kc-table w-100">
        <thead>
            <tr>
                <th style="width:40px;">No</th>
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
                <tr>
                    <td colspan="7" style="text-align:center;color:#a07850;padding:24px;">
                        Tidak ada bahan dengan filter yang dipilih.
                    </td>
                </tr>
            <?php endif; ?>
            <?php $no = 1;
            foreach ($bahans as $b):
                $st  = getStatus((float)$b['stok'], (float)$b['stok_minimum']);
                $kat = getBadgeKat($b['kategori']);
            ?>
                <tr>
                    <td style="color:#a07850;"><?= $no++ ?></td>
                    <td style="font-weight:600;color:#3b1f0a;"><?= htmlspecialchars($b['nama_bahan']) ?></td>
                    <td>
                        <span class="kc-badge <?= $kat ?>">
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $b['kategori']))) ?>
                        </span>
                    </td>
                    <td style="font-weight:600;">
                        <?= number_format((float)$b['stok'], 2, ',', '.') ?>
                        <small style="color:#a07850;"><?= htmlspecialchars($b['satuan']) ?></small>
                    </td>
                    <td>Rp <?= number_format((float)$b['harga_modal'], 0, ',', '.') ?></td>
                    <td style="font-weight:600;color:#92400e;">Rp <?= number_format((float)$b['nilai_stok'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($st === 'aman'): ?>
                            <span class="kc-badge kc-badge-green"> Aman</span>
                        <?php elseif ($st === 'menipis'): ?>
                            <span class="kc-badge kc-badge-yellow"> Menipis</span>
                        <?php else: ?>
                            <span class="kc-badge kc-badge-red"> Habis</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../../_layout_end.php'; ?>