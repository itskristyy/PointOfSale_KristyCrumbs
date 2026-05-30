<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../auth.php';
checkRole(['admin', 'owner']);
include '../../connect.php';

$id_menu = isset($_GET['id_menu']) ? (int) $_GET['id_menu'] : 0;
if ($id_menu <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data menu
$stmt_menu = mysqli_prepare($koneksi, "SELECT * FROM tb_menu WHERE id_menu = ?");
mysqli_stmt_bind_param($stmt_menu, 'i', $id_menu);
mysqli_stmt_execute($stmt_menu);
$menu = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_menu));
mysqli_stmt_close($stmt_menu);

if (!$menu) {
    header("Location: index.php?error=Menu+tidak+ditemukan");
    exit;
}

// ─── Post Request Handler ───────────────────────────────────────────────────
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bahans_input = $_POST['bahan'] ?? [];
    $jumlahs_input = $_POST['jumlah'] ?? [];

    // Filter input valid
    $resep_items = [];
    for ($i = 0; $i < count($bahans_input); $i++) {
        $id_b = (int) $bahans_input[$i];
        $qty  = (float) $jumlahs_input[$i];

        if ($id_b > 0 && $qty > 0) {
            // Cek duplikasi bahan dalam satu resep
            if (isset($resep_items[$id_b])) {
                $resep_items[$id_b] += $qty;
            } else {
                $resep_items[$id_b] = $qty;
            }
        }
    }

    // Eksekusi Transaction
    mysqli_begin_transaction($koneksi);
    try {
        // 1. Hapus resep yang lama untuk menu ini
        $stmt_del = mysqli_prepare($koneksi, "DELETE FROM tb_resep WHERE id_menu = ?");
        mysqli_stmt_bind_param($stmt_del, 'i', $id_menu);
        mysqli_stmt_execute($stmt_del);
        mysqli_stmt_close($stmt_del);

        // 2. Insert resep baru jika ada
        if (!empty($resep_items)) {
            $stmt_ins = mysqli_prepare($koneksi, "INSERT INTO tb_resep (id_menu, id_bahan, jumlah) VALUES (?, ?, ?)");
            foreach ($resep_items as $id_b => $qty) {
                mysqli_stmt_bind_param($stmt_ins, 'iid', $id_menu, $id_b, $qty);
                mysqli_stmt_execute($stmt_ins);
            }
            mysqli_stmt_close($stmt_ins);
        }

        mysqli_commit($koneksi);
        header("Location: index.php?success=Resep+menu+" . urlencode($menu['nama_menu']) . "+berhasil+diupdate");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $error = 'Gagal menyimpan resep: ' . $e->getMessage();
    }
}

// Ambil resep saat ini
$stmt_cur = mysqli_prepare($koneksi, "
    SELECT r.id_bahan, r.jumlah, b.satuan 
    FROM tb_resep r 
    JOIN tb_bahan b ON b.id_bahan = r.id_bahan 
    WHERE r.id_menu = ?
");
mysqli_stmt_bind_param($stmt_cur, 'i', $id_menu);
mysqli_stmt_execute($stmt_cur);
$res_cur = mysqli_stmt_get_result($stmt_cur);
$current_resep = [];
while ($row = mysqli_fetch_assoc($res_cur)) {
    $current_resep[] = $row;
}
mysqli_stmt_close($stmt_cur);

// Ambil semua bahan baku untuk pilihan dropdown
$res_bahans = mysqli_query($koneksi, "SELECT id_bahan, nama_bahan, satuan FROM tb_bahan ORDER BY nama_bahan ASC");
$all_bahans = [];
while ($row = mysqli_fetch_assoc($res_bahans)) {
    $all_bahans[] = $row;
}

$page_title = 'Setup Resep: ' . htmlspecialchars($menu['nama_menu']);
$active     = 'warehouse-resep';
include '../../../_layout.php';
?>

<div class="row justify-content-center">
  <div class="col-md-10">
    <div class="kc-card">
      <div class="kc-card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <span><i class='bx bx-cog'></i> Setup Komposisi Resep</span>
        <span class="kc-badge kc-badge-gray"><?= ucfirst($menu['kategori']) ?></span>
      </div>
      <div class="kc-card-body" style="padding: 20px;">
        <?php if ($error): ?>
          <div class="alert alert-danger mb-3">
            <i class='bx bx-error-circle'></i> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div style="margin-bottom: 20px; border-bottom: 1px solid #fed7aa; padding-bottom: 12px;">
          <h4 style="color:#1c1007; margin-bottom: 4px; font-weight:700;"><?= htmlspecialchars($menu['nama_menu']) ?></h4>
          <p style="color:#a07850; font-size: 13px; margin:0;">Tentukan jumlah kebutuhan bahan baku per porsi/sajian menu ini.</p>
        </div>

        <form method="POST" id="resep-form">
          <table class="kc-table w-100 mb-3" id="resep-table">
            <thead>
              <tr>
                <th>Bahan Baku</th>
                <th style="width: 200px;">Jumlah / Porsi</th>
                <th style="width: 100px;">Satuan</th>
                <th style="width: 80px; text-align: center;">Aksi</th>
              </tr>
            </thead>
            <tbody id="resep-tbody">
              <!-- Baris dynamic resep -->
              <?php if (empty($current_resep)): ?>
                <!-- Render 1 row kosong jika resep kosong -->
                <tr class="resep-row">
                  <td>
                    <select name="bahan[]" class="form-select form-select-sm select-bahan" required onchange="updateSatuan(this)">
                      <option value="">-- Pilih Bahan Baku --</option>
                      <?php foreach ($all_bahans as $b): ?>
                        <option value="<?= $b['id_bahan'] ?>" data-satuan="<?= htmlspecialchars($b['satuan']) ?>">
                          <?= htmlspecialchars($b['nama_bahan']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td>
                    <input type="number" name="jumlah[]" class="form-control form-control-sm" placeholder="0.00" step="0.01" min="0.01" required>
                  </td>
                  <td class="satuan-lbl" style="color:#a07850; font-size:13px; vertical-align:middle;">-</td>
                  <td style="text-align: center; vertical-align:middle;">
                    <button type="button" class="btn btn-sm btn-outline-danger" style="padding: 2px 6px;" onclick="removeRow(this)">
                      <i class='bx bx-trash'></i>
                    </button>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($current_resep as $cr): ?>
                  <tr class="resep-row">
                    <td>
                      <select name="bahan[]" class="form-select form-select-sm select-bahan" required onchange="updateSatuan(this)">
                        <option value="">-- Pilih Bahan Baku --</option>
                        <?php foreach ($all_bahans as $b): ?>
                          <option value="<?= $b['id_bahan'] ?>" data-satuan="<?= htmlspecialchars($b['satuan']) ?>" <?= $cr['id_bahan'] == $b['id_bahan'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nama_bahan']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td>
                      <input type="number" name="jumlah[]" class="form-control form-control-sm" placeholder="0.00" step="0.01" min="0.01" value="<?= (float)$cr['jumlah'] ?>" required>
                    </td>
                    <td class="satuan-lbl" style="color:#a07850; font-size:13px; vertical-align:middle;">
                      <?= htmlspecialchars($cr['satuan']) ?>
                    </td>
                    <td style="text-align: center; vertical-align:middle;">
                      <button type="button" class="btn btn-sm btn-outline-danger" style="padding: 2px 6px;" onclick="removeRow(this)">
                        <i class='bx bx-trash'></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>

          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
            <button type="button" class="btn-kc-outline" id="btn-tambah-baris" onclick="addRow()">
              <i class='bx bx-plus'></i> Tambah Bahan
            </button>
            <span style="font-size:12px; color:#a07850;">
              * Bahan yang sama otomatis akan diakumulasikan.
            </span>
          </div>

          <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-kc" id="btn-save-resep">
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
</div>

<script>
// Data bahan untuk dynamic row insertion
const allBahans = <?= json_encode($all_bahans) ?>;

function addRow() {
    const tbody = document.getElementById('resep-tbody');
    const tr = document.createElement('tr');
    tr.className = 'resep-row';

    let options = '<option value="">-- Pilih Bahan Baku --</option>';
    allBahans.forEach(b => {
        options += `<option value="${b.id_bahan}" data-satuan="${escapeHtml(b.satuan)}">${escapeHtml(b.nama_bahan)}</option>`;
    });

    tr.innerHTML = `
        <td>
            <select name="bahan[]" class="form-select form-select-sm select-bahan" required onchange="updateSatuan(this)">
                ${options}
            </select>
        </td>
        <td>
            <input type="number" name="jumlah[]" class="form-control form-control-sm" placeholder="0.00" step="0.01" min="0.01" required>
        </td>
        <td class="satuan-lbl" style="color:#a07850; font-size:13px; vertical-align:middle;">-</td>
        <td style="text-align: center; vertical-align:middle;">
            <button type="button" class="btn btn-sm btn-outline-danger" style="padding: 2px 6px;" onclick="removeRow(this)">
                <i class='bx bx-trash'></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function removeRow(btn) {
    const row = btn.closest('tr');
    const tbody = document.getElementById('resep-tbody');
    if (tbody.querySelectorAll('.resep-row').length > 1) {
        row.remove();
    } else {
        // Reset row terakhir jika tinggal 1 agar tidak kosong melompong
        row.querySelector('.select-bahan').value = '';
        row.querySelector('input[type="number"]').value = '';
        row.querySelector('.satuan-lbl').textContent = '-';
    }
}

function updateSatuan(select) {
    const selectedOption = select.options[select.selectedIndex];
    const row = select.closest('tr');
    const label = row.querySelector('.satuan-lbl');
    const satuan = selectedOption.getAttribute('data-satuan');
    label.textContent = satuan ? satuan : '-';
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>

<?php include '../../../_layout_end.php'; ?>
