<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../auth.php';
checkRole(['admin', 'owner']);
include '../../connect.php';

// ambil nilai filter dari URL
$filter_dari    = $_GET['dari']     ?? '';
$filter_sampai  = $_GET['sampai']   ?? '';
$filter_jenis   = $_GET['jenis']    ?? '';
$filter_id_bahan = isset($_GET['id_bahan']) ? (int) $_GET['id_bahan'] : 0;
$filter_search  = trim($_GET['search'] ?? '');

// pagination, 20 data per halaman
$per_page = 20;
$page     = max(1, (int) ($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

// cek format tanggal biar ga error
$valid_dari   = $filter_dari   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_dari);
$valid_sampai = $filter_sampai && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_sampai);

// bangun kondisi WHERE sesuai filter yang aktif
$where_parts = [];
$params      = [];
$types       = '';

if ($valid_dari) {
    $where_parts[] = 'DATE(sl.tanggal) >= ?';
    $params[]      = $filter_dari;
    $types        .= 's';
}
if ($valid_sampai) {
    $where_parts[] = 'DATE(sl.tanggal) <= ?';
    $params[]      = $filter_sampai;
    $types        .= 's';
}
if (in_array($filter_jenis, ['masuk', 'keluar', 'terpakai'])) {
    $where_parts[] = 'sl.jenis = ?';
    $params[]      = $filter_jenis;
    $types        .= 's';
}
if ($filter_id_bahan > 0) {
    $where_parts[] = 'sl.id_bahan = ?';
    $params[]      = $filter_id_bahan;
    $types        .= 'i';
}
if ($filter_search !== '') {
    $like           = '%' . $filter_search . '%';
    $where_parts[]  = 'b.nama_bahan LIKE ?';
    $params[]       = $like;
    $types         .= 's';
}

$where_sql = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

// hitung total data dulu buat pagination
$sql_count = "SELECT COUNT(*) AS total
              FROM tb_stok_log sl
              JOIN tb_bahan b ON b.id_bahan = sl.id_bahan
              {$where_sql}";
$stmt_count = mysqli_prepare($koneksi, $sql_count);
if ($params) {
    mysqli_stmt_bind_param($stmt_count, $types, ...$params);
}
mysqli_stmt_execute($stmt_count);
$total_records = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count))['total'];
mysqli_stmt_close($stmt_count);
$total_pages = max(1, (int) ceil($total_records / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// ambil data log sesuai halaman & filter
$sql_log = "SELECT sl.*, b.nama_bahan, b.satuan
            FROM tb_stok_log sl
            JOIN tb_bahan b ON b.id_bahan = sl.id_bahan
            {$where_sql}
            ORDER BY sl.id_log DESC
            LIMIT {$per_page} OFFSET {$offset}";
$stmt_log = mysqli_prepare($koneksi, $sql_log);
if ($params) {
    mysqli_stmt_bind_param($stmt_log, $types, ...$params);
}
mysqli_stmt_execute($stmt_log);
$result_log = mysqli_stmt_get_result($stmt_log);
$logs = [];
while ($row = mysqli_fetch_assoc($result_log)) {
    $logs[] = $row;
}
mysqli_stmt_close($stmt_log);

// kalau ada filter id_bahan, ambil namanya buat ditampilin di badge
$nama_bahan_filter = '';
if ($filter_id_bahan > 0) {
    $qn = mysqli_prepare($koneksi, "SELECT nama_bahan FROM tb_bahan WHERE id_bahan = ?");
    mysqli_stmt_bind_param($qn, 'i', $filter_id_bahan);
    mysqli_stmt_execute($qn);
    $rn = mysqli_fetch_assoc(mysqli_stmt_get_result($qn));
    $nama_bahan_filter = $rn['nama_bahan'] ?? '';
    mysqli_stmt_close($qn);
}

// helper buat bikin URL pagination yang tetap bawa filter aktif
function buildUrl(array $overrides = []): string
{
    $base = $_GET;
    unset($base['page']); // page di-reset tiap filter berubah
    $merged = array_merge($base, $overrides);
    return 'log.php?' . http_build_query(array_filter($merged, fn($v) => $v !== ''));
}

$page_title = 'Riwayat Log Stok';
$active = 'log_warehouse'; 
include '../../../_layout.php';
?>

<!-- filter toolbar -->
<div class="kc-card mb-3">
    <div class="kc-card-body" style="padding:10px 12px;">
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <input
                type="text" name="search"
                class="form-control form-control-sm"
                placeholder="Cari nama bahan..."
                value="<?= htmlspecialchars($filter_search) ?>"
                style="max-width:200px;">
            <input
                type="date" name="dari"
                class="form-control form-control-sm"
                value="<?= htmlspecialchars($filter_dari) ?>"
                style="max-width:150px;"
                title="Dari tanggal">
            <span style="font-size:11px;color:#a07850;">s/d</span>
            <input
                type="date" name="sampai"
                class="form-control form-control-sm"
                value="<?= htmlspecialchars($filter_sampai) ?>"
                style="max-width:150px;"
                title="Sampai tanggal">
            <select name="jenis" class="form-select form-select-sm" style="max-width:150px;">
                <option value="">Semua Jenis</option>
                <option value="masuk" <?= $filter_jenis === 'masuk'    ? 'selected' : '' ?>>Masuk</option>
                <option value="keluar" <?= $filter_jenis === 'keluar'   ? 'selected' : '' ?>>Keluar</option>
                <option value="terpakai" <?= $filter_jenis === 'terpakai' ? 'selected' : '' ?>>Terpakai</option>
            </select>
            <?php if ($filter_id_bahan > 0): ?>
                <input type="hidden" name="id_bahan" value="<?= $filter_id_bahan ?>">
                <span class="kc-badge kc-badge-brown" style="white-space:nowrap;">
                    <i class='bx bx-package'></i> <?= htmlspecialchars($nama_bahan_filter) ?>
                    <a href="log.php" style="color:#7c3a0e;margin-left:4px;" title="Hapus filter bahan"><i class='bx bx-x'></i></a>
                </span>
            <?php endif; ?>
            <button type="submit" class="btn-kc btn-kc-sm"><i class='bx bx-filter-alt'></i> Terapkan</button>
            <a href="log.php" class="btn-kc-outline"><i class='bx bx-reset'></i> Reset</a>
        </form>
    </div>
</div>

<!-- tabel log -->
<div class="kc-card">
    <div class="kc-card-header">
        <span><i class='bx bx-history'></i> Riwayat Log Stok</span>
        <span style="font-size:11px;color:#a07850;font-weight:400;">
            <?= number_format($total_records) ?> record — halaman <?= $page ?> dari <?= $total_pages ?>
        </span>
    </div>
    <table class="kc-table w-100">
        <thead>
            <tr>
                <th style="width:40px;">No</th>
                <th>Tanggal</th>
                <th>Nama Bahan</th>
                <th>Jenis</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
                <th>No. Pesanan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="7" style="text-align:center;color:#a07850;padding:24px;">
                        Tidak ada log dengan filter yang dipilih.
                    </td>
                </tr>
            <?php endif; ?>
            <?php $no = $offset + 1;
            foreach ($logs as $log): ?>
                <tr>
                    <td style="color:#a07850;"><?= $no++ ?></td>
                    <td style="font-size:11px;color:#a07850;white-space:nowrap;">
                        <?= date('d/m/Y H:i', strtotime($log['tanggal'])) ?>
                    </td>
                    <td>
                        <a href="log.php?id_bahan=<?= $log['id_bahan'] ?>" style="color:#7c3a0e;font-weight:600;text-decoration:none;">
                            <?= htmlspecialchars($log['nama_bahan']) ?>
                        </a>
                    </td>
                    <td>
                        <?php if ($log['jenis'] === 'masuk'): ?>
                            <span class="kc-badge kc-badge-green"><i class='bx bx-download'></i> Masuk</span>
                        <?php elseif ($log['jenis'] === 'keluar'): ?>
                            <span class="kc-badge kc-badge-red"><i class='bx bx-upload'></i> Keluar</span>
                        <?php else: ?>
                            <span class="kc-badge kc-badge-yellow"><i class='bx bx-bowl-hot'></i> Terpakai</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:600;">
                        <?= number_format((float)$log['jumlah'], 2, ',', '.') ?>
                        <small style="color:#a07850;"><?= htmlspecialchars($log['satuan']) ?></small>
                    </td>
                    <td style="font-size:11px;color:#5a3a1a;max-width:220px;word-break:break-word;">
                        <?= htmlspecialchars($log['keterangan'] ?? '–') ?>
                    </td>
                    <td>
                        <?php if ($log['id_pesanan']): ?>
                            <span class="kc-badge kc-badge-blue">#<?= $log['id_pesanan'] ?></span>
                        <?php else: ?>
                            <span style="color:#a07850;">–</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- pagination, muncul kalau lebih dari 1 halaman -->
    <?php if ($total_pages > 1): ?>
        <style>
            .pagination .page-link {
                color: #92400e;
                background-color: #fff;
                border: 1px solid #e8d5b8;
            }

            .pagination .page-item.active .page-link {
                background-color: #92400e;
                border-color: #92400e;
                color: #fff;
            }

            .pagination .page-link:hover {
                background-color: #fde8cc;
                border-color: #e8d5b8;
                color: #7c3a0e;
            }

            .pagination .page-item.disabled .page-link {
                color: #a07850;
                opacity: 0.6;
            }
        </style>
        <div class="d-flex justify-content-center p-3 border-top" style="background:#fffcf7;">
            <nav>
                <ul class="pagination pagination-sm m-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars(buildUrl(['page' => $page - 1])) ?>">&laquo;</a>
                    </li>
                    <?php
                    // tampilin max 7 nomor halaman di sekitar halaman aktif
                    $start_p = max(1, $page - 3);
                    $end_p   = min($total_pages, $page + 3);
                    for ($i = $start_p; $i <= $end_p; $i++):
                    ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars(buildUrl(['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars(buildUrl(['page' => $page + 1])) ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php include '../../../_layout_end.php'; ?>