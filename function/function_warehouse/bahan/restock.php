<?php
// function/function_warehouse/bahan/restock.php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../auth.php';
checkRole(['admin', 'owner']);
include '../../connect.php';

$page_title = 'Restock Bahan';
$active     = 'warehouse';

$errors  = [];
$success = '';

// Ambil ID bahan dari GET
if (!isset($_GET['id']) || intval($_GET['id']) <= 0) {
    header("Location: index.php");
    exit;
}
$id_bahan = intval($_GET['id']);

// Ambil data bahan
$stmt_get = mysqli_prepare($koneksi, "SELECT * FROM tb_bahan WHERE id_bahan = ?");
mysqli_stmt_bind_param($stmt_get, 'i', $id_bahan);
mysqli_stmt_execute($stmt_get);
$res   = mysqli_stmt_get_result($stmt_get);
$bahan = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt_get);

if (!$bahan) {
    header("Location: index.php");
    exit;
}

// Ambil 10 log terakhir bahan ini
$stmt_log = mysqli_prepare($koneksi,
    "SELECT * FROM tb_stok_log WHERE id_bahan = ? ORDER BY id_log DESC LIMIT 10");
mysqli_stmt_bind_param($stmt_log, 'i', $id_bahan);
mysqli_stmt_execute($stmt_log);
$res_log = mysqli_stmt_get_result($stmt_log);
$logs = [];
while ($row = mysqli_fetch_assoc($res_log)) $logs[] = $row;
mysqli_stmt_close($stmt_log);

// Proses form restock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restock'])) {
    $jumlah_tambah  = trim($_POST['jumlah_tambah']  ?? '');
    $keterangan     = trim($_POST['keterangan']      ?? 'Restock manual');
    $stok_minimum   = trim($_POST['stok_minimum']    ?? '');

    // Validasi
    if ($jumlah_tambah === '' || !is_numeric($jumlah_tambah)) {
        $errors['jumlah'] = 'Jumlah tambah stok wajib diisi (angka).';
    } elseif ((float)$jumlah_tambah <= 0) {
        $errors['jumlah'] = 'Jumlah tambah stok harus lebih dari 0.';
    }
    if ($stok_minimum !== '' && is_numeric($stok_minimum) && (float)$stok_minimum < 0) {
        $errors['stok_minimum'] = 'Stok minimum tidak boleh negatif.';
    }

    if (empty($errors)) {
        $tambah = (float) $jumlah_tambah;
        $ket    = $keterangan ?: 'Restock manual';

        mysqli_begin_transaction($koneksi);
        try {
            // UPDATE stok bahan
            $stmt_upd = mysqli_prepare($koneksi,
                "UPDATE tb_bahan SET stok = stok + ? WHERE id_bahan = ?");
            mysqli_stmt_bind_param($stmt_upd, 'di', $tambah, $id_bahan);
            mysqli_stmt_execute($stmt_upd);
            mysqli_stmt_close($stmt_upd);

            // Update stok_minimum jika diisi
            if ($stok_minimum !== '' && is_numeric($stok_minimum)) {
                $min = (float)$stok_minimum;
                $stmt_min = mysqli_prepare($koneksi,
                    "UPDATE tb_bahan SET stok_minimum = ? WHERE id_bahan = ?");
                mysqli_stmt_bind_param($stmt_min, 'di', $min, $id_bahan);
                mysqli_stmt_execute($stmt_min);
                mysqli_stmt_close($stmt_min);
            }

            // INSERT log stok
            $stmt_log2 = mysqli_prepare($koneksi,
                "INSERT INTO tb_stok_log (id_bahan, jenis, jumlah, keterangan) VALUES (?, 'masuk', ?, ?)");
            mysqli_stmt_bind_param($stmt_log2, 'ids', $id_bahan, $tambah, $ket);
            mysqli_stmt_execute($stmt_log2);
            mysqli_stmt_close($stmt_log2);

            // Refresh session alert
            $q_alert = mysqli_query($koneksi,
                "SELECT COUNT(*) AS total FROM tb_bahan WHERE stok <= stok_minimum");
            $_SESSION['alert_stok'] = (int) mysqli_fetch_assoc($q_alert)['total'];

            mysqli_commit($koneksi);

            // Refresh data bahan setelah update
            $stmt_refresh = mysqli_prepare($koneksi, "SELECT * FROM tb_bahan WHERE id_bahan = ?");
            mysqli_stmt_bind_param($stmt_refresh, 'i', $id_bahan);
            mysqli_stmt_execute($stmt_refresh);
            $bahan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_refresh));
            mysqli_stmt_close($stmt_refresh);

            // Refresh log
            $stmt_log3 = mysqli_prepare($koneksi,
                "SELECT * FROM tb_stok_log WHERE id_bahan = ? ORDER BY id_log DESC LIMIT 10");
            mysqli_stmt_bind_param($stmt_log3, 'i', $id_bahan);
            mysqli_stmt_execute($stmt_log3);
            $res_log3 = mysqli_stmt_get_result($stmt_log3);
            $logs = [];
            while ($row = mysqli_fetch_assoc($res_log3)) $logs[] = $row;
            mysqli_stmt_close($stmt_log3);

            $success = number_format($tambah, 2, ',', '.') . ' ' . htmlspecialchars($bahan['satuan']) . ' berhasil ditambahkan ke stok ' . htmlspecialchars($bahan['nama_bahan']) . '!';
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $errors['global'] = 'Gagal restock: ' . $e->getMessage();
        }
    }
}

include '../../../_layout.php';
?>

<?php if ($success): ?>
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:10px;font-size:13px;color:#15803d;">
        <i class='bx bx-check-circle' style="font-size:20px;"></i>
        <span><?= $success ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($errors['global'])): ?>
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:10px;font-size:13px;color:#dc2626;">
        <i class='bx bx-error-circle' style="font-size:20px;"></i>
        <span><?= htmlspecialchars($errors['global']) ?></span>
    </div>
<?php endif; ?>

<div class="row g-3">

    <!-- Form Restock -->
    <div class="col-md-5 col-lg-4">
        <div class="kc-card">
            <div class="kc-card-header">
                <span><i class='bx bx-plus-circle'></i> Form Restock Bahan</span>
            </div>
            <div class="kc-card-body">

                <!-- Info bahan saat ini -->
                <div style="background:#fffbf5;border:1px solid #e8d5b8;border-radius:8px;padding:12px 14px;margin-bottom:16px;">
                    <div style="font-size:11px;color:#a07850;text-transform:uppercase;font-weight:600;margin-bottom:6px;letter-spacing:.05em;">
                        Info Bahan
                    </div>
                    <div style="font-weight:700;font-size:14px;color:#3b1f0a;margin-bottom:4px;">
                        <?= htmlspecialchars($bahan['nama_bahan']) ?>
                    </div>
                    <?php
                    $badge_class = match($bahan['kategori']) {
                        'bahan_baku' => 'kc-badge-brown',
                        'minuman'    => 'kc-badge-blue',
                        'topping'    => 'kc-badge-yellow',
                        'kemasan'    => 'kc-badge-gray',
                        default      => 'kc-badge-gray',
                    };
                    $habis   = (float)$bahan['stok'] <= 0;
                    $menipis = (float)$bahan['stok'] <= (float)$bahan['stok_minimum'] && !$habis;
                    ?>
                    <span class="kc-badge <?= $badge_class ?>" style="margin-bottom:8px;">
                        <?= ucwords(str_replace('_', ' ', $bahan['kategori'])) ?>
                    </span>
                    <div style="margin-top:10px;display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div style="text-align:center;background:#fff;border:1px solid #e8d5b8;border-radius:6px;padding:8px;">
                            <div style="font-size:10px;color:#a07850;font-weight:600;text-transform:uppercase;">Stok Sekarang</div>
                            <div style="font-size:20px;font-weight:700;color:<?= $habis ? '#dc2626' : ($menipis ? '#d97706' : '#15803d') ?>;">
                                <?= number_format((float)$bahan['stok'], 2, ',', '.') ?>
                            </div>
                            <div style="font-size:11px;color:#a07850;"><?= htmlspecialchars($bahan['satuan']) ?></div>
                        </div>
                        <div style="text-align:center;background:#fff;border:1px solid #e8d5b8;border-radius:6px;padding:8px;">
                            <div style="font-size:10px;color:#a07850;font-weight:600;text-transform:uppercase;">Stok Min.</div>
                            <div style="font-size:20px;font-weight:700;color:#92400e;">
                                <?= number_format((float)$bahan['stok_minimum'], 2, ',', '.') ?>
                            </div>
                            <div style="font-size:11px;color:#a07850;"><?= htmlspecialchars($bahan['satuan']) ?></div>
                        </div>
                    </div>
                    <?php if ($habis): ?>
                        <div style="margin-top:8px;text-align:center;"><span class="kc-badge kc-badge-red">🔴 Habis</span></div>
                    <?php elseif ($menipis): ?>
                        <div style="margin-top:8px;text-align:center;"><span class="kc-badge kc-badge-yellow">🟡 Menipis</span></div>
                    <?php else: ?>
                        <div style="margin-top:8px;text-align:center;"><span class="kc-badge kc-badge-green">✅ Aman</span></div>
                    <?php endif; ?>
                </div>

                <form method="POST" id="form-restock" novalidate>
                    <!-- Jumlah Tambah Stok -->
                    <div class="mb-3">
                        <label class="form-label" for="jumlah_tambah">
                            Jumlah Tambah Stok <span style="color:#964261">*</span>
                        </label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input
                                type="number"
                                id="jumlah_tambah"
                                name="jumlah_tambah"
                                step="0.01"
                                min="0.01"
                                class="form-control form-control-sm <?= isset($errors['jumlah']) ? 'is-invalid' : '' ?>"
                                placeholder="0"
                                value="<?= $_POST['jumlah_tambah'] ?? '' ?>"
                                required
                                style="flex:1;">
                            <span style="font-size:12px;color:#a07850;white-space:nowrap;min-width:36px;">
                                <?= htmlspecialchars($bahan['satuan']) ?>
                            </span>
                        </div>
                        <?php if (isset($errors['jumlah'])): ?>
                            <div style="color:#dc2626;font-size:11px;margin-top:3px;"><?= $errors['jumlah'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Update Stok Minimum (opsional) -->
                    <div class="mb-3">
                        <label class="form-label" for="stok_minimum">
                            Stok Minimum (opsional)
                        </label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input
                                type="number"
                                id="stok_minimum"
                                name="stok_minimum"
                                step="0.01"
                                min="0"
                                class="form-control form-control-sm <?= isset($errors['stok_minimum']) ? 'is-invalid' : '' ?>"
                                placeholder="<?= htmlspecialchars($bahan['stok_minimum']) ?>"
                                value="<?= $_POST['stok_minimum'] ?? '' ?>"
                                style="flex:1;">
                            <span style="font-size:12px;color:#a07850;white-space:nowrap;min-width:36px;">
                                <?= htmlspecialchars($bahan['satuan']) ?>
                            </span>
                        </div>
                        <div style="font-size:10px;color:#a07850;margin-top:3px;">
                            Kosongkan jika tidak ingin mengubah stok minimum
                        </div>
                        <?php if (isset($errors['stok_minimum'])): ?>
                            <div style="color:#dc2626;font-size:11px;margin-top:3px;"><?= $errors['stok_minimum'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label class="form-label" for="keterangan">Keterangan</label>
                        <input
                            type="text"
                            id="keterangan"
                            name="keterangan"
                            class="form-control form-control-sm"
                            placeholder="Contoh: Restok dari supplier"
                            value="<?= htmlspecialchars($_POST['keterangan'] ?? 'Restock manual') ?>"
                            maxlength="300">
                    </div>

                    <!-- Tombol -->
                    <div style="display:flex;gap:8px;margin-top:16px;">
                        <button type="submit" name="restock" class="btn-kc btn-kc-sm" id="btn-restock-simpan">
                            <i class='bx bx-plus-circle'></i> Tambah Stok
                        </button>
                        <a href="index.php" class="btn-kc-outline">
                            <i class='bx bx-arrow-back'></i> Kembali
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Riwayat Log -->
    <div class="col-md-7 col-lg-8">
        <div class="kc-card">
            <div class="kc-card-header">
                <span><i class='bx bx-history'></i> Riwayat Log Stok (10 Terakhir)</span>
                <a href="../laporan/log.php?id_bahan=<?= $id_bahan ?>" class="btn-kc-outline" style="font-size:11px;">
                    <i class='bx bx-list-ul'></i> Lihat Semua
                </a>
            </div>
            <table class="kc-table w-100">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                        <th>Pesanan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:#a07850;padding:20px;">
                                Belum ada riwayat log untuk bahan ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td style="font-size:11px;color:#a07850;white-space:nowrap;">
                                <?= date('d/m/Y H:i', strtotime($log['tanggal'])) ?>
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
                                <small style="color:#a07850;"><?= htmlspecialchars($bahan['satuan']) ?></small>
                            </td>
                            <td style="font-size:11px;color:#5a3a1a;max-width:200px;word-break:break-word;">
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
        </div>
    </div>

</div>

<?php include '../../../_layout_end.php'; ?>
