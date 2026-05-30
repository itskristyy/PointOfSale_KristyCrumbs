<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../auth.php';
checkRole(['admin', 'owner']);
include '../../connect.php';

$page_title = 'Kelola Resep Menu';
$active     = 'warehouse-resep';
include '../../../_layout.php';

// ─── Search & Category Filter ────────────────────────────────────────────────
$search    = $_GET['search'] ?? '';
$kategori  = $_GET['kategori'] ?? '';

// Build query
$where_parts = [];
$params      = [];
$types       = '';

if ($search !== '') {
    $where_parts[] = 'nama_menu LIKE ?';
    $params[]      = '%' . $search . '%';
    $types        .= 's';
}
if ($kategori !== '') {
    $where_parts[] = 'kategori = ?';
    $params[]      = $kategori;
    $types        .= 's';
}

$sql = "SELECT * FROM tb_menu";
if ($where_parts) {
    $sql .= " WHERE " . implode(" AND ", $where_parts);
}
$sql .= " ORDER BY nama_menu ASC";

$stmt = mysqli_prepare($koneksi, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$menus = [];
while ($row = mysqli_fetch_assoc($res)) {
    // Ambil detail resep untuk menu ini
    $id_menu = (int) $row['id_menu'];
    $stmt_resep = mysqli_prepare($koneksi, "
        SELECT r.id_resep, r.jumlah, b.nama_bahan, b.satuan 
        FROM tb_resep r 
        JOIN tb_bahan b ON b.id_bahan = r.id_bahan 
        WHERE r.id_menu = ?
    ");
    mysqli_stmt_bind_param($stmt_resep, 'i', $id_menu);
    mysqli_stmt_execute($stmt_resep);
    $res_resep = mysqli_stmt_get_result($stmt_resep);
    $resep = [];
    while ($r_row = mysqli_fetch_assoc($res_resep)) {
        $resep[] = $r_row;
    }
    mysqli_stmt_close($stmt_resep);

    $row['resep'] = $resep;
    $menus[] = $row;
}
mysqli_stmt_close($stmt);

?>

<!-- ─── Toolbar: Search & Filter ───────────────────────────────────────────── -->
<div class="kc-card mb-3">
  <div class="kc-card-body" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding:10px 12px;">
    <form method="GET" style="display:flex;gap:8px;flex:1;flex-wrap:wrap;align-items:center;">
      <input
        type="text"
        name="search"
        id="search-menu"
        class="form-control form-control-sm"
        placeholder="🔍 Cari nama menu..."
        value="<?= htmlspecialchars($search) ?>"
        style="max-width:220px;"
      >
      <select name="kategori" id="filter-kategori" class="form-select form-select-sm" style="max-width:180px;">
        <option value="">Semua Kategori</option>
        <option value="makanan" <?= $kategori === 'makanan' ? 'selected' : '' ?>>Makanan</option>
        <option value="minuman" <?= $kategori === 'minuman' ? 'selected' : '' ?>>Minuman</option>
      </select>
      <button type="submit" class="btn-kc btn-kc-sm"><i class='bx bx-filter-alt'></i> Filter</button>
      <?php if ($search || $kategori): ?>
        <a href="index.php" class="btn-kc-outline">Reset</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<?php if (isset($_GET['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class='bx bx-check-circle'></i> <?= htmlspecialchars($_GET['success']) ?>
  </div>
<?php endif; ?>

<!-- ─── Tabel Menu & Resep ───────────────────────────────────────────────────── -->
<div class="kc-card">
  <div class="kc-card-header">
    <span><i class='bx bx-book-open'></i> Setup Resep Menu POS</span>
    <span style="font-size:11px;color:#a07850;font-weight:400;">
      <?= count($menus) ?> menu terdaftar
    </span>
  </div>
  <table class="kc-table w-100">
    <thead>
      <tr>
        <th style="width:40px;">No</th>
        <th style="width:200px;">Nama Menu</th>
        <th style="width:120px;">Kategori</th>
        <th>Bahan Baku / Resep (Per Porsi)</th>
        <th style="width:120px; text-align:center;">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($menus)): ?>
        <tr>
          <td colspan="5" style="text-align:center;color:#a07850;padding:20px;">
            Tidak ada data menu ditemukan.
          </td>
        </tr>
      <?php endif; ?>
      <?php $no = 1; foreach ($menus as $m): ?>
        <tr>
          <td style="color:#a07850;"><?= $no++ ?></td>
          <td>
            <strong><?= htmlspecialchars($m['nama_menu']) ?></strong>
            <div style="font-size:11px;color:#a07850;">Rp <?= number_format($m['harga'], 0, ',', '.') ?></div>
          </td>
          <td>
            <span class="kc-badge kc-badge-gray">
              <?= ucfirst($m['kategori']) ?>
            </span>
          </td>
          <td>
            <?php if (empty($m['resep'])): ?>
              <span style="color:#dc2626; font-size:12px; font-style:italic;">
                ⚠️ Resep belum diset. Bahan baku tidak akan berkurang otomatis saat pesanan selesai.
              </span>
            <?php else: ?>
              <div style="display:flex; flex-wrap:wrap; gap:6px;">
                <?php foreach ($m['resep'] as $r): ?>
                  <span class="kc-badge kc-badge-green" style="font-weight:500; font-size:11px; border: 1px solid #bbf7d0;">
                    <?= htmlspecialchars($r['nama_bahan']) ?>: <strong><?= number_format((float)$r['jumlah'], 2, ',', '.') ?></strong> <small><?= htmlspecialchars($r['satuan']) ?></small>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </td>
          <td style="text-align:center;">
            <a href="edit.php?id_menu=<?= $m['id_menu'] ?>" 
               class="btn-kc" 
               style="font-size:12px; padding:4px 10px; display:inline-flex; align-items:center; gap:4px;"
               id="btn-resep-<?= $m['id_menu'] ?>">
              <i class='bx bx-cog'></i> Set Resep
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include '../../../_layout_end.php'; ?>
