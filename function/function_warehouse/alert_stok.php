<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include '../auth.php';
checkRole(['admin', 'owner']);
include '../connect.php';

$page_title = 'Alert Stok Menipis';
$active = 'warehouse-alert';

// query bahan yang stoknya menipis atau habis
$q = mysqli_query(
    $koneksi,
    "SELECT *, (stok_minimum - stok) AS selisih
     FROM tb_bahan
     WHERE stok <= stok_minimum
     ORDER BY stok ASC, nama_bahan ASC"
);

$menipis_list = [];
while ($row = mysqli_fetch_assoc($q)) {
    $menipis_list[] = $row;
}

// refresh session badge
$_SESSION['alert_stok'] = count($menipis_list);

include '../../_layout.php';
?>

<!-- Summary Cards -->

<?php
$habis_count   = count(array_filter($menipis_list, fn($b) => (float)$b['stok'] <= 0));
$menipis_count = count($menipis_list) - $habis_count;
?>

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:14px;">

    <div class="stat-box" style="border-color:#fca5a5;">
        <div class="stat-label" style="color:#dc2626;">🔴 Stok Habis</div>
        <div class="stat-value" style="color:#dc2626;"><?= $habis_count ?></div>
    </div>

    <div class="stat-box" style="border-color:#fed7aa;">
        <div class="stat-label" style="color:#d97706;">🟡 Menipis</div>
        <div class="stat-value" style="color:#d97706;"><?= $menipis_count ?></div>
    </div>

    <div class="stat-box">
        <div class="stat-label">Total Bermasalah</div>
        <div class="stat-value"><?= count($menipis_list) ?></div>
    </div>

</div>

<!-- Tabel Alert -->

<div class="kc-card">

    <div class="kc-card-header">
        <span>
            <i class='bx bx-bell' style="color:#dc2626;"></i>
            Daftar Bahan Stok Menipis / Habis
        </span>

        <a href="bahan/add_bahan.php" class="btn-kc btn-kc-sm" id="btn-tambah-stok-alert">
            <i class='bx bx-plus'></i>
            Catat Restock / Tambah Bahan
        </a>
    </div>

    <?php if (empty($menipis_list)) : ?>

        <div style="text-align:center;padding:40px 16px;color:#15803d;">
            <div style="font-size:32px;">✅</div>
            <div style="font-size:14px;font-weight:700;margin-top:8px;">
                Semua Stok Aman!
            </div>
            <div style="font-size:12px;color:#a07850;margin-top:4px;">
                Tidak ada bahan yang stoknya menipis.
            </div>
        </div>

    <?php else : ?>

        <table class="kc-table w-100">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>Nama Bahan</th>
                    <th>Kategori</th>
                    <th>Stok Saat Ini</th>
                    <th>Stok Minimum</th>
                    <th>Kekurangan</th>
                    <th>Status</th>
                    <th style="width:90px;">Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php $no = 1;
                foreach ($menipis_list as $b) : ?>

                    <?php
                    $habis   = (float)$b['stok'] <= 0;
                    $selisih = max(0, (float)$b['selisih']);
                    ?>

                    <tr style="<?= $habis ? 'background:#fff5f5;' : 'background:#fffbeb;' ?>">

                        <td style="color:#a07850;"><?= $no++ ?></td>

                        <td>
                            <strong><?= htmlspecialchars($b['nama_bahan']) ?></strong>
                        </td>

                        <td>
                            <span class="kc-badge kc-badge-gray">
                                <?= ucwords(str_replace('_', ' ', $b['kategori'])) ?>
                            </span>
                        </td>

                        <td>
                            <span style="font-weight:700;color:<?= $habis ? '#dc2626' : '#d97706' ?>;">
                                <?= number_format((float)$b['stok'], 2, ',', '.') ?>
                                <small style="font-weight:400;color:#a07850;">
                                    <?= htmlspecialchars($b['satuan']) ?>
                                </small>
                            </span>
                        </td>

                        <td style="color:#5a3a1a;">
                            <?= number_format((float)$b['stok_minimum'], 2, ',', '.') ?>
                            <small style="color:#a07850;">
                                <?= htmlspecialchars($b['satuan']) ?>
                            </small>
                        </td>

                        <td>

                            <?php if ($selisih > 0) : ?>

                                <span style="color:#dc2626;font-weight:600;">
                                    <?= number_format($selisih, 2, ',', '.') ?>
                                    <?= htmlspecialchars($b['satuan']) ?>
                                </span>

                            <?php else : ?>

                                <span style="color:#a07850;">—</span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php if ($habis) : ?>

                                <span class="kc-badge kc-badge-red">
                                    🔴 Habis
                                </span>

                            <?php else : ?>

                                <span class="kc-badge kc-badge-yellow">
                                    🟡 Menipis
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <a href="bahan/update_bahan.php?id=<?= $b['id_bahan'] ?>"
                                class="btn-kc btn-kc-sm"
                                title="Catat Restock / Edit Stok"
                                id="btn-restock-<?= $b['id_bahan'] ?>"
                                style="font-size:10px;">

                                <i class='bx bx-plus-circle'></i>
                                Restock

                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>

<?php include '../../_layout_end.php'; ?>