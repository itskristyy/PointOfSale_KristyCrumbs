<?php
// function/function_warehouse/resep/edit.php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../auth.php';
checkRole(['admin', 'owner']);
include '../../connect.php';

// Ambil ID Menu
$id_menu = intval($_GET['id_menu'] ?? 0);
if ($id_menu <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data menu
$query_menu = mysqli_prepare($koneksi, "SELECT * FROM tb_menu WHERE id_menu = ?");
mysqli_stmt_bind_param($query_menu, 'i', $id_menu);
mysqli_stmt_execute($query_menu);
$res_menu = mysqli_stmt_get_result($query_menu);
$menu = mysqli_fetch_assoc($res_menu);
mysqli_stmt_close($query_menu);

if (!$menu) {
    header("Location: index.php");
    exit;
}

// Ambil daftar bahan baku untuk dropdown
$res_bahan = mysqli_query($koneksi, "SELECT id_bahan, nama_bahan, satuan FROM tb_bahan ORDER BY nama_bahan ASC");
$bahan_list = [];
while ($row = mysqli_fetch_assoc($res_bahan)) {
    $bahan_list[] = $row;
}

// Ambil data resep yang sudah ada
$stmt_resep = mysqli_prepare($koneksi, "
    SELECT r.id_bahan, r.jumlah
    FROM tb_resep r
    WHERE r.id_menu = ?
");
mysqli_stmt_bind_param($stmt_resep, 'i', $id_menu);
mysqli_stmt_execute($stmt_resep);
$res_resep = mysqli_stmt_get_result($stmt_resep);
$existing_resep = [];
while ($row = mysqli_fetch_assoc($res_resep)) {
    $existing_resep[] = $row;
}
mysqli_stmt_close($stmt_resep);

$errors = [];

// Proses simpan resep
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_resep'])) {
    $post_bahan = $_POST['bahan'] ?? [];
    $post_jumlah = $_POST['jumlah'] ?? [];

    $resep_items = [];
    // Validasi input
    for ($i = 0; $i < count($post_bahan); $i++) {
        $id_b = intval($post_bahan[$i]);
        $qty = floatval($post_jumlah[$i]);

        if ($id_b > 0 && $qty > 0) {
            // Cek duplikasi bahan dalam resep
            if (isset($resep_items[$id_b])) {
                $errors['global'] = 'Terdapat bahan baku yang duplikat dalam resep!';
                break;
            }
            $resep_items[$id_b] = $qty;
        } elseif ($id_b > 0 && $qty <= 0) {
            $errors['global'] = 'Jumlah bahan baku harus lebih dari 0!';
            break;
        }
    }

    if (empty($errors)) {
        // Mulai transaksi database
        mysqli_begin_transaction($koneksi);
        try {
            // 1. Hapus resep lama
            $stmt_del = mysqli_prepare($koneksi, "DELETE FROM tb_resep WHERE id_menu = ?");
            mysqli_stmt_bind_param($stmt_del, "i", $id_menu);
            mysqli_stmt_execute($stmt_del);
            mysqli_stmt_close($stmt_del);

            // 2. Insert resep baru
            if (!empty($resep_items)) {
                $stmt_ins = mysqli_prepare($koneksi, "INSERT INTO tb_resep (id_menu, id_bahan, jumlah) VALUES (?, ?, ?)");
                foreach ($resep_items as $id_b => $qty) {
                    mysqli_stmt_bind_param($stmt_ins, "iid", $id_menu, $id_b, $qty);
                    mysqli_stmt_execute($stmt_ins);
                }
                mysqli_stmt_close($stmt_ins);
            }

            // Commit transaksi
            mysqli_commit($koneksi);

            header("Location: index.php?success=Resep+untuk+" . urlencode($menu['nama_menu']) . "+berhasil+diperbarui");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $errors['global'] = 'Gagal menyimpan resep: ' . $e->getMessage();
        }
    }
}

$page_title = 'Set Resep Menu';
$active     = 'warehouse-resep';
include '../../../_layout.php';
?>

<!-- Global error notification -->
<?php if (!empty($errors['global'])): ?>
    <div class="alert alert-danger py-2 mb-3" style="font-size:12px;">
        <i class='bx bx-error-circle'></i> <?= htmlspecialchars($errors['global']) ?>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-md-8 col-lg-7">
        <div class="kc-card">
            <div class="kc-card-header">
                <span><i class='bx bx-cog'></i> Pengaturan Resep: <strong><?= htmlspecialchars($menu['nama_menu']) ?></strong></span>
                <span class="kc-badge kc-badge-brown"><?= ucfirst($menu['kategori']) ?></span>
            </div>
            
            <div class="kc-card-body">
                <a href="index.php" class="btn-kc-outline mb-3">
                    <i class='bx bx-arrow-back'></i> Kembali ke Setup Resep
                </a>
                
                <form method="POST" id="form-resep">
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 11px; font-weight:700; color:#534247;">
                            Bahan Baku & Kebutuhan (Per Porsi)
                        </label>
                        
                        <div id="resep-container">
                            <?php if (empty($existing_resep)): ?>
                                <!-- Baris template default jika resep kosong -->
                                <div class="row g-2 mb-2 resep-row">
                                    <div class="col-7">
                                        <select name="bahan[]" class="form-select form-select-sm select-bahan" required>
                                            <option value="">-- Pilih Bahan Baku --</option>
                                            <?php foreach ($bahan_list as $b): ?>
                                                <option value="<?= $b['id_bahan'] ?>" data-satuan="<?= htmlspecialchars($b['satuan']) ?>">
                                                    <?= htmlspecialchars($b['nama_bahan']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-3">
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="jumlah[]" step="0.0001" min="0.0001" class="form-control" placeholder="Jumlah" required>
                                            <span class="input-group-text label-satuan" style="font-size:10px; min-width: 45px; justify-content: center;">-</span>
                                        </div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row w-100" style="border-radius: 999px;">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Tampilkan resep yang sudah ada -->
                                <?php foreach ($existing_resep as $index => $er): ?>
                                    <div class="row g-2 mb-2 resep-row">
                                        <div class="col-7">
                                            <select name="bahan[]" class="form-select form-select-sm select-bahan" required>
                                                <option value="">-- Pilih Bahan Baku --</option>
                                                <?php foreach ($bahan_list as $b): ?>
                                                    <option value="<?= $b['id_bahan'] ?>" 
                                                            data-satuan="<?= htmlspecialchars($b['satuan']) ?>"
                                                            <?= $b['id_bahan'] == $er['id_bahan'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($b['nama_bahan']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-3">
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="jumlah[]" step="0.0001" min="0.0001" class="form-control" placeholder="Jumlah" value="<?= (float)$er['jumlah'] ?>" required>
                                                <?php 
                                                $curr_satuan = '-';
                                                foreach ($bahan_list as $bl) {
                                                    if ($bl['id_bahan'] == $er['id_bahan']) {
                                                        $curr_satuan = $bl['satuan'];
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <span class="input-group-text label-satuan" style="font-size:10px; min-width: 45px; justify-content: center;"><?= htmlspecialchars($curr_satuan) ?></span>
                                            </div>
                                        </div>
                                        <div class="col-2 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row w-100" style="border-radius: 999px;">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="btn-kc-outline btn-kc-sm mt-2" id="btn-add-row">
                            <i class='bx bx-plus'></i> Tambah Baris Bahan
                        </button>
                    </div>
                    
                    <hr style="border-color: #ece8df; margin: 20px 0;">
                    
                    <div style="display:flex; gap:8px;">
                        <button type="submit" name="simpan_resep" class="btn-kc btn-kc-sm" id="btn-save-resep">
                            <i class='bx bx-save'></i> Simpan Resep
                        </button>
                        <a href="index.php" class="btn-kc-outline">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-5">
        <div class="kc-card">
            <div class="kc-card-header"><i class='bx bx-info-circle'></i> Info Resep</div>
            <div class="kc-card-body" style="font-size:12px; color:#534247; line-height:1.7;">
                <p>• <strong>Resep Menu</strong> menentukan berapa banyak bahan baku yang terpakai untuk setiap piring/gelas menu ini yang terjual.</p>
                <p>• Ketika pesanan selesai diproses koki di Dapur, sistem secara otomatis akan mengurangi stok bahan baku sesuai dengan resep yang ditentukan di sini.</p>
                <p>• Jumlah bahan dapat diisi dengan desimal jika diperlukan (misal: 0,25 kg tepung).</p>
                <p>• Pastikan menekan tombol <strong>Simpan Resep</strong> untuk menyimpan perubahan.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('resep-container');
    const btnAdd = document.getElementById('btn-add-row');
    
    // Update label satuan saat bahan dipilih
    container.addEventListener('change', function(e) {
        if (e.target.classList.contains('select-bahan')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const satuan = selectedOption.getAttribute('data-satuan') || '-';
            const row = e.target.closest('.resep-row');
            const labelSatuan = row.querySelector('.label-satuan');
            labelSatuan.textContent = satuan;
        }
    });
    
    // Hapus baris bahan
    container.addEventListener('click', function(e) {
        const btnRemove = e.target.closest('.btn-remove-row');
        if (btnRemove) {
            const rows = container.querySelectorAll('.resep-row');
            if (rows.length > 1) {
                btnRemove.closest('.resep-row').remove();
            } else {
                alert('Resep harus memiliki minimal satu bahan baku! Jika ingin mengosongkan, Anda bisa memilih opsi kosong atau membiarkan form.');
            }
        }
    });
    
    // Tambah baris baru
    btnAdd.addEventListener('click', function() {
        const firstRow = container.querySelector('.resep-row');
        const newRow = firstRow.cloneNode(true);
        
        // Reset input values
        const select = newRow.querySelector('select');
        select.selectedIndex = 0;
        
        const input = newRow.querySelector('input[type="number"]');
        input.value = '';
        
        const labelSatuan = newRow.querySelector('.label-satuan');
        labelSatuan.textContent = '-';
        
        container.appendChild(newRow);
    });
});
</script>

<?php include '../../../_layout_end.php'; ?>
