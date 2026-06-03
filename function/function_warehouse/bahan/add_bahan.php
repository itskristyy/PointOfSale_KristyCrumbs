<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../auth.php';
checkRole(['admin', 'owner']);
include '../../connect.php';

$page_title = 'Tambah Bahan Baku';
$active     = 'warehouse';

$errors = [];
$form   = [
    'nama_bahan'   => '',
    'kategori'     => '',
    'stok'         => '',
    'stok_minimum' => '',
    'satuan'       => '',
    'harga_modal'  => '',
];

// kategori bahan buat cafe 
$kategori_list = ['bahan_baku', 'minuman', 'topping', 'kemasan'];

// proses submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_bahan'])) {
    // ambil input
    $form['nama_bahan']   = trim($_POST['nama_bahan'] ?? '');
    $form['kategori']     = trim($_POST['kategori'] ?? '');
    $form['stok']         = trim($_POST['stok'] ?? '');
    $form['stok_minimum'] = trim($_POST['stok_minimum'] ?? '');
    $form['satuan']       = trim($_POST['satuan'] ?? '');
    $form['harga_modal']  = trim($_POST['harga_modal'] ?? '');

    // validasi
    if ($form['nama_bahan'] === '') {
        $errors['nama_bahan'] = 'Nama bahan wajib diisi.';
    }
    if (!in_array($form['kategori'], $kategori_list)) {
        $errors['kategori'] = 'Pilih kategori yang valid.';
    }
    if ($form['stok'] === '' || !is_numeric($form['stok'])) {
        $errors['stok'] = 'Stok awal wajib diisi (angka).';
    } elseif ((float)$form['stok'] < 0) {
        $errors['stok'] = 'Stok tidak boleh negatif.';
    }
    if ($form['stok_minimum'] === '' || !is_numeric($form['stok_minimum'])) {
        $errors['stok_minimum'] = 'Stok minimum wajib diisi (angka).';
    } elseif ((float)$form['stok_minimum'] < 0) {
        $errors['stok_minimum'] = 'Stok minimum tidak boleh negatif.';
    }
    if ($form['satuan'] === '') {
        $errors['satuan'] = 'Satuan wajib diisi.';
    }
    if ($form['harga_modal'] === '' || !is_numeric($form['harga_modal'])) {
        $errors['harga_modal'] = 'Harga modal wajib diisi (angka).';
    } elseif ((float)$form['harga_modal'] < 0) {
        $errors['harga_modal'] = 'Harga modal tidak boleh negatif.';
    }

    // simpan kalau ga ada error
    if (empty($errors)) {
        $nama_bahan   = $form['nama_bahan'];
        $kategori     = $form['kategori'];
        $stok         = (float) $form['stok'];
        $stok_minimum = (float) $form['stok_minimum'];
        $satuan       = $form['satuan'];
        $harga_modal  = (float) $form['harga_modal'];

        // insert bahan + log harus atomik
        mysqli_begin_transaction($koneksi);
        try {
            // insert bahan baru
            $stmt_insert = mysqli_prepare(
                $koneksi,
                "INSERT INTO tb_bahan (nama_bahan, kategori, stok, stok_minimum, satuan, harga_modal)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt_insert, 'ssddsd', $nama_bahan, $kategori, $stok, $stok_minimum, $satuan, $harga_modal);
            mysqli_stmt_execute($stmt_insert);
            $id_bahan_baru = mysqli_insert_id($koneksi);
            mysqli_stmt_close($stmt_insert);

            // log stok awal
            $keterangan_awal = "Stok awal saat tambah bahan";
            $stmt_log = mysqli_prepare(
                $koneksi,
                "INSERT INTO tb_stok_log (id_bahan, jenis, jumlah, keterangan)
                 VALUES (?, 'masuk', ?, ?)"
            );
            mysqli_stmt_bind_param($stmt_log, 'ids', $id_bahan_baru, $stok, $keterangan_awal);
            mysqli_stmt_execute($stmt_log);
            mysqli_stmt_close($stmt_log);

            // refresh session alert stok
            $q_alert = mysqli_query($koneksi, "SELECT COUNT(*) AS total_menipis FROM tb_bahan WHERE stok <= stok_minimum");
            $_SESSION['alert_stok'] = (int) mysqli_fetch_assoc($q_alert)['total_menipis'];

            mysqli_commit($koneksi);
            header("Location: index.php?success=Bahan+" . urlencode($nama_bahan) . "+berhasil+ditambahkan");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $errors['global'] = 'Gagal menyimpan bahan: ' . $e->getMessage();
        }
    }
}

include '../../../_layout.php';
?>

<!-- Flash error global -->
<?php if (!empty($errors['global'])): ?>
    <div class="alert alert-danger py-2 mb-3" style="font-size:12px;">
        <i class='bx bx-error-circle'></i> <?= htmlspecialchars($errors['global']) ?>
    </div>
<?php endif; ?>

<!-- Flash success dari redirect -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success py-2 mb-3" style="font-size:12px;">
        <i class='bx bx-check-circle'></i> <?= htmlspecialchars($_GET['success']) ?>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-md-7 col-lg-6">
        <div class="kc-card">
            <div class="kc-card-header">
                <span><i class='bx bx-plus-circle'></i> Form Tambah Bahan Baku</span>
            </div>
            <div class="kc-card-body">
                <form method="POST" id="form-tambah-bahan" novalidate>
                    <!-- Nama Bahan -->
                    <div class="mb-3">
                        <label class="form-label" for="nama_bahan">Nama Bahan <span style="color:#964261">*</span></label>
                        <input
                            type="text"
                            id="nama_bahan"
                            name="nama_bahan"
                            class="form-control form-control-sm <?= isset($errors['nama_bahan']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($form['nama_bahan']) ?>"
                            placeholder="Contoh: Tepung Terigu"
                            required>
                        <?php if (isset($errors['nama_bahan'])): ?>
                            <div class="invalid-feedback"><?= $errors['nama_bahan'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Kategori -->
                    <div class="mb-3">
                        <label class="form-label" for="kategori">Kategori <span style="color:#964261">*</span></label>
                        <select
                            id="kategori"
                            name="kategori"
                            class="form-select form-select-sm <?= isset($errors['kategori']) ? 'is-invalid' : '' ?>"
                            required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori_list as $kat): ?>
                                <option value="<?= $kat ?>" <?= $form['kategori'] === $kat ? 'selected' : '' ?>>
                                    <?= ucwords(str_replace('_', ' ', $kat)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['kategori'])): ?>
                            <div class="invalid-feedback"><?= $errors['kategori'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Stok Awal & Satuan (satu baris) -->
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label" for="stok">Stok Awal <span style="color:#964261">*</span></label>
                            <input
                                type="number"
                                id="stok"
                                name="stok"
                                step="0.01"
                                min="0"
                                class="form-control form-control-sm <?= isset($errors['stok']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($form['stok']) ?>"
                                placeholder="0"
                                required>
                            <?php if (isset($errors['stok'])): ?>
                                <div class="invalid-feedback"><?= $errors['stok'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-4">
                            <label class="form-label" for="satuan">Satuan <span style="color:#964261">*</span></label>
                            <input
                                type="text"
                                id="satuan"
                                name="satuan"
                                class="form-control form-control-sm <?= isset($errors['satuan']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($form['satuan']) ?>"
                                placeholder="kg / pcs"
                                required>
                            <?php if (isset($errors['satuan'])): ?>
                                <div class="invalid-feedback"><?= $errors['satuan'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Stok Minimum -->
                    <div class="mb-3">
                        <label class="form-label" for="stok_minimum">Stok Minimum <span style="color:#964261">*</span></label>
                        <input
                            type="number"
                            id="stok_minimum"
                            name="stok_minimum"
                            step="0.01"
                            min="0"
                            class="form-control form-control-sm <?= isset($errors['stok_minimum']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($form['stok_minimum']) ?>"
                            placeholder="0"
                            required>
                        <div style="font-size:10px;color:#a07850;margin-top:3px;">
                            Alert akan muncul jika stok ≤ nilai ini
                        </div>
                        <?php if (isset($errors['stok_minimum'])): ?>
                            <div class="invalid-feedback"><?= $errors['stok_minimum'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Harga Modal -->
                    <div class="mb-3">
                        <label class="form-label" for="harga_modal">Harga Modal (Rp) <span style="color:#964261">*</span></label>
                        <input
                            type="number"
                            id="harga_modal"
                            name="harga_modal"
                            step="1"
                            min="0"
                            class="form-control form-control-sm <?= isset($errors['harga_modal']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($form['harga_modal']) ?>"
                            placeholder="0"
                            required>
                        <?php if (isset($errors['harga_modal'])): ?>
                            <div class="invalid-feedback"><?= $errors['harga_modal'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Tombol Aksi -->
                    <div style="display:flex;gap:8px;margin-top:16px;">
                        <button type="submit" name="simpan_bahan" class="btn-kc btn-kc-sm" id="btn-simpan-bahan">
                            <i class='bx bx-save'></i> Simpan Bahan
                        </button>
                        <a href="index.php" class="btn-kc-outline">
                            <i class='bx bx-arrow-back'></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info tambahan di sebelah kanan -->
    <div class="col-md-5 col-lg-6">
        <div class="kc-card">
            <div class="kc-card-header"><i class='bx bx-info-circle'></i> Info</div>
            <div class="kc-card-body" style="font-size:12px;color:#534247;line-height:1.7;">
                <p>• <strong>Stok Awal</strong> akan otomatis dicatat sebagai log <em>masuk</em> pertama.</p>
                <p>• <strong>Stok Minimum</strong> digunakan untuk trigger alert menipis di sidebar.</p>
                <p>• Gunakan satuan yang konsisten (misal: kg, pcs, liter, butir).</p>
                <p>• Harga modal digunakan untuk kalkulasi nilai stok di laporan.</p>
            </div>
        </div>
    </div>
</div>

<?php include '../../../_layout_end.php'; ?>