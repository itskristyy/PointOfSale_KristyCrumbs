<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../auth.php';
checkRole(['admin', 'owner']);
include '../../connect.php';

$page_title = 'Manajemen Bahan Baku';
$active     = 'warehouse';
include '../../../_layout.php';

// filter & search
$filter_kategori = $_GET['kategori'] ?? '';
$search          = $_GET['search'] ?? '';

// hitung alert stok untuk badge sidebar
$q_alert = mysqli_query($koneksi, "SELECT COUNT(*) AS total_menipis FROM tb_bahan WHERE stok <= stok_minimum");
$_SESSION['alert_stok'] = (int) mysqli_fetch_assoc($q_alert)['total_menipis'];

// query dengan filter & search
$where_parts = [];
$params      = [];
$types       = '';

if ($filter_kategori !== '') {
    $where_parts[] = 'kategori = ?';
    $params[]      = $filter_kategori;
    $types        .= 's';
}
if ($search !== '') {
    $like           = '%' . $search . '%';
    $where_parts[]  = 'nama_bahan LIKE ?';
    $params[]       = $like;
    $types         .= 's';
}

$sql = "SELECT * FROM tb_bahan";
if ($where_parts) {
    $sql .= ' WHERE ' . implode(' AND ', $where_parts);
}
$sql .= ' ORDER BY nama_bahan ASC';

$stmt = mysqli_prepare($koneksi, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$bahans = [];
while ($row = mysqli_fetch_assoc($result)) {
    $bahans[] = $row;
}
mysqli_stmt_close($stmt);

// kategori bahan buat cafe bakery
$kategori_list = ['bahan_baku', 'minuman', 'topping', 'kemasan'];
?>

<!-- Toolbar: Filter + Search + Tambah -->
<div class="kc-card mb-3">
    <div class="kc-card-body" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding:10px 12px;">
        <form method="GET" style="display:flex;gap:8px;flex:1;flex-wrap:wrap;align-items:center;">
            <!-- Search bar -->
            <input
                type="text"
                name="search"
                id="search-bahan"
                class="form-control form-control-sm"
                placeholder="🔍 Cari nama bahan..."
                value="<?= htmlspecialchars($search) ?>"
                style="max-width:220px;">
            <!-- Filter kategori -->
            <select name="kategori" id="filter-kategori" class="form-select form-select-sm" style="max-width:180px;">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategori_list as $kat): ?>
                    <option value="<?= $kat ?>" <?= $filter_kategori === $kat ? 'selected' : '' ?>>
                        <?= ucwords(str_replace('_', ' ', $kat)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-kc btn-kc-sm"><i class='bx bx-filter-alt'></i> Filter</button>
            <?php if ($search || $filter_kategori): ?>
                <a href="index.php" class="btn-kc-outline">Reset</a>
            <?php endif; ?>
        </form>
        <!-- Tombol tambah -->
        <a href="add_bahan.php" class="btn-kc btn-kc-sm" id="btn-tambah-bahan">
            <i class='bx bx-plus'></i> Tambah Bahan
        </a>
    </div>
</div>

<!-- Tabel Bahan Baku -->
<div class="kc-card">
    <div class="kc-card-header">
        <span><i class='bx bx-package'></i> Data Bahan Baku</span>
        <span style="font-size:11px;color:#a07850;font-weight:400;">
            <?= count($bahans) ?> bahan ditemukan
        </span>
    </div>
    <table class="kc-table w-100">
        <thead>
            <tr>
                <th style="width:40px;">No</th>
                <th>Nama Bahan</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Stok Min.</th>
                <th>Harga Modal</th>
                <th>Status</th>
                <th style="width:160px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bahans)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;color:#a07850;padding:20px;">
                        Tidak ada data bahan baku.
                    </td>
                </tr>
            <?php endif; ?>
            <?php $no = 1;
            foreach ($bahans as $b): ?>
                <?php
                $menipis = ((float)$b['stok'] <= (float)$b['stok_minimum']);
                $habis   = ((float)$b['stok'] <= 0);
                $badge_class = match ($b['kategori']) {
                    'bahan_baku' => 'kc-badge-brown',
                    'minuman'    => 'kc-badge-blue',
                    'topping'    => 'kc-badge-yellow',
                    'kemasan'    => 'kc-badge-gray',
                    default      => 'kc-badge-gray',
                };
                ?>
                <tr>
                    <td style="color:#a07850;"><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($b['nama_bahan']) ?></strong></td>
                    <td>
                        <span class="kc-badge <?= $badge_class ?>">
                            <?= ucwords(str_replace('_', ' ', $b['kategori'])) ?>
                        </span>
                    </td>
                    <td>
                        <span style="<?= $habis ? 'color:#b91c1c;font-weight:700;' : ($menipis ? 'color:#7c3a0e;font-weight:600;' : '') ?>">
                            <?= number_format((float)$b['stok'], 2, ',', '.') ?>
                            <small style="color:#a07850;"><?= htmlspecialchars($b['satuan']) ?></small>
                        </span>
                    </td>
                    <td style="color:#a07850;">
                        <?= number_format((float)$b['stok_minimum'], 2, ',', '.') ?>
                        <small><?= htmlspecialchars($b['satuan']) ?></small>
                    </td>
                    <td>Rp <?= number_format((float)$b['harga_modal'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($habis): ?>
                            <span class="kc-badge kc-badge-red">Habis</span>
                        <?php elseif ($menipis): ?>
                            <span class="kc-badge kc-badge-yellow">Menipis</span>
                        <?php else: ?>
                            <span class="kc-badge kc-badge-green">Aman</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;flex-direction:row;gap:4px;align-items:center;flex-wrap:nowrap;">
                            <a href="update_bahan.php?id=<?= $b['id_bahan'] ?>" class="btn-kc-outline" title="Edit Bahan" id="btn-edit-<?= $b['id_bahan'] ?>"><i class='bx bx-edit'></i></a>
                            <a href="restock.php?id=<?= $b['id_bahan'] ?>" class="btn-kc btn-kc-sm" title="Tambah Stok" id="btn-restock-<?= $b['id_bahan'] ?>" style="font-size:11px;"><i class='bx bx-plus-circle'></i></a>
                            <a href="../laporan/log.php?id_bahan=<?= $b['id_bahan'] ?>" class="btn-kc-outline" title="Lihat Log Stok" id="btn-log-<?= $b['id_bahan'] ?>"><i class='bx bx-history'></i></a>
                            <a href="delete_bahan.php?id=<?= $b['id_bahan'] ?>" class="btn-kc-danger" title="Hapus Bahan" id="btn-hapus-<?= $b['id_bahan'] ?>" onclick="return confirm('Hapus bahan \'<?= addslashes(htmlspecialchars($b['nama_bahan'])) ?>\'?\n\nSemua resep yang menggunakan bahan ini juga akan terpengaruh.')"><i class='bx bx-trash'></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../../_layout_end.php'; ?>