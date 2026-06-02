<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'function/auth.php';
checkRole(['admin']);
include 'function/connect.php';

$tab = $_GET['tab'] ?? 'dashboard';
$page_title = match (strtolower($tab)) {
  'menu'    => 'Kelola Menu',
  'user'    => 'Kelola User',
  'pesanan' => 'Pesanan',
  'laporan' => 'Laporan',
  'meja'    => 'Kelola Meja',
  default   => 'Dashboard Admin',
};
$active = strtolower($tab) === 'dashboard' ? 'dashboard' : strtolower($tab);
include '_layout.php';
?>

<?php if ($tab === 'dashboard'): ?>

  <?php
  $omzet    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT IFNULL(SUM(total_harga),0) AS n FROM tb_pesanan WHERE status_bayar='lunas' AND DATE(tgl_pesanan)=CURDATE()"))['n'];
  $trx      = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM tb_pesanan WHERE DATE(tgl_pesanan)=CURDATE()"))['n'];
  $belum    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM tb_pesanan WHERE status_bayar='belum_bayar'"))['n'];
  $tot_menu = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM tb_menu"))['n'];
  ?>

  <div class="stat-grid">
    <div class="stat-box">
      <div class="stat-label">Omzet Hari Ini</div>
      <div class="stat-value">Rp <?= number_format($omzet, 0, ',', '.') ?></div>
    </div>
    <div class="stat-box">
      <div class="stat-label">Transaksi Hari Ini</div>
      <div class="stat-value dark"><?= $trx ?></div>
    </div>
    <div class="stat-box">
      <div class="stat-label">Belum Bayar</div>
      <div class="stat-value blue"><?= $belum ?></div>
    </div>
    <div class="stat-box">
      <div class="stat-label">Total Menu</div>
      <div class="stat-value dark"><?= $tot_menu ?></div>
    </div>
  </div>

  <div class="kc-card">
    <div class="kc-card-header"><span><i class='bx bx-receipt'></i> Pesanan Terbaru</span></div>
    <table class="kc-table w-100">
      <thead>
        <tr>
          <th>#</th>
          <th>Pelanggan</th>
          <th>Meja</th>
          <th>Total</th>
          <th>Status Bayar</th>
          <th>Status Pesanan</th>
        </tr>
      </thead>
      <tbody>
        <?php $q = mysqli_query($koneksi, "SELECT * FROM tb_pesanan ORDER BY id_pesanan DESC LIMIT 10");
        while ($r = mysqli_fetch_assoc($q)): ?>
          <tr>
            <td style="color:#a07850"><?= $r['id_pesanan'] ?></td>
            <td><?= htmlspecialchars($r['nama_pelanggan']) ?></td>
            <td>Meja <?= $r['no_meja'] ?></td>
            <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
            <td><span class="kc-badge <?= $r['status_bayar'] === 'lunas' ? 'kc-badge-green' : 'kc-badge-yellow' ?>"><?= $r['status_bayar'] ?></span></td>
            <td><span class="kc-badge <?= $r['status_pesanan'] === 'selesai' ? 'kc-badge-brown' : 'kc-badge-blue' ?>"><?= $r['status_pesanan'] ?></span></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'menu'): ?>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="kc-card">
        <div class="kc-card-header"><i class='bx bx-plus-circle'></i> Tambah Menu</div>
        <div class="kc-card-body">
          <form action="function/function_menu/add_menu.php" method="POST">
            <div class="mb-2"><label class="form-label">Nama menu</label><input type="text" name="nama_menu" class="form-control form-control-sm" placeholder="Contoh: Nasi Goreng" required></div>
            <div class="mb-2"><label class="form-label">Kategori</label>
              <select name="kategori" class="form-select form-select-sm" required>
                <option value="makanan">Makanan</option>
                <option value="minuman">Minuman</option>
              </select>
            </div>
            <div class="mb-2"><label class="form-label">Harga (Rp)</label><input type="number" name="harga" class="form-control form-control-sm" placeholder="0" required></div>
            <div class="mb-3"><label class="form-label">Stok</label><input type="number" name="stok" class="form-control form-control-sm" placeholder="0" required></div>
            <button type="submit" name="add_menu" class="btn-kc btn-kc-sm"><i class='bx bx-save'></i> Simpan</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="kc-card">
        <div class="kc-card-header"><i class='bx bx-list-ul'></i> Daftar Menu</div>
        <table class="kc-table w-100">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama</th>
              <th>Kategori</th>
              <th>Harga</th>
              <th>Stok</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1;
            $q = mysqli_query($koneksi, "SELECT * FROM tb_menu ORDER BY id_menu DESC");
            while ($d = mysqli_fetch_assoc($q)): ?>
              <tr>
                <td style="color:#a07850"><?= $no++ ?></td>
                <td><?= htmlspecialchars($d['nama_menu']) ?></td>
                <td><span class="kc-badge <?= $d['kategori'] === 'makanan' ? 'kc-badge-brown' : 'kc-badge-blue' ?>"><?= ucfirst($d['kategori']) ?></span></td>
                <td>Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                <td <?= $d['stok'] <= 5 ? 'style="color:#dc2626;font-weight:600"' : '' ?>><?= $d['stok'] ?></td>
                <td>
                  <a href="edit_menu.php?id=<?= $d['id_menu'] ?>" class="btn-kc-outline"><i class='bx bx-edit'></i></a>
                  <a href="function/function_menu/delete_menu.php?id=<?= $d['id_menu'] ?>" class="btn-kc-danger ms-1" onclick="return confirm('Hapus menu ini?')"><i class='bx bx-trash'></i></a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php elseif ($tab === 'user'): ?>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="kc-card">
        <div class="kc-card-header"><i class='bx bx-user-plus'></i> Tambah User</div>
        <div class="kc-card-body">
          <form action="function/function_user/add_user.php" method="POST">
            <div class="mb-2"><label class="form-label">Username</label><input type="text" name="username" class="form-control form-control-sm" placeholder="Username" required></div>
            <div class="mb-2"><label class="form-label">Password</label><input type="password" name="password" class="form-control form-control-sm" placeholder="Password" required></div>
            <div class="mb-3"><label class="form-label">Role</label>
              <select name="role" class="form-select form-select-sm" required>
                <option value="admin">Admin</option>
                <option value="kasir">Kasir</option>
                <option value="dapur">Dapur</option>
              </select>
            </div>
            <button type="submit" name="add_user" class="btn-kc btn-kc-sm"><i class='bx bx-save'></i> Simpan</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="kc-card">
        <div class="kc-card-header"><i class='bx bx-group'></i> Daftar User</div>
        <table class="kc-table w-100">
          <thead>
            <tr>
              <th>#</th>
              <th>Username</th>
              <th>Role</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1;
            $q = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user");
            while ($d = mysqli_fetch_assoc($q)): ?>
              <tr>
                <td style="color:#a07850"><?= $no++ ?></td>
                <td><?= htmlspecialchars($d['username']) ?></td>
                <td><span class="kc-badge kc-badge-gray"><?= ucfirst($d['role']) ?></span></td>
                <td>
                  <a href="edit_user.php?id=<?= $d['id_user'] ?>" class="btn-kc-outline"><i class='bx bx-edit'></i></a>
                  <a href="function/function_user/delete_user.php?id=<?= $d['id_user'] ?>" class="btn-kc-danger ms-1" onclick="return confirm('Hapus user ini?')"><i class='bx bx-trash'></i></a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php elseif ($tab === 'pesanan'): ?>

  <div class="kc-card">
    <div class="kc-card-header"><i class='bx bx-receipt'></i> Semua Pesanan</div>
    <table class="kc-table w-100">
      <thead>
        <tr>
          <th>#</th>
          <th>Pelanggan</th>
          <th>Meja</th>
          <th>Total</th>
          <th>Status Bayar</th>
          <th>Status Pesanan</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $q = mysqli_query($koneksi, "SELECT * FROM tb_pesanan ORDER BY id_pesanan DESC");
        while ($r = mysqli_fetch_assoc($q)): ?>
          <tr>
            <td style="color:#a07850"><?= $r['id_pesanan'] ?></td>
            <td><?= htmlspecialchars($r['nama_pelanggan']) ?></td>
            <td>Meja <?= $r['no_meja'] ?></td>
            <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
            <td><span class="kc-badge <?= $r['status_bayar'] === 'lunas' ? 'kc-badge-green' : 'kc-badge-yellow' ?>"><?= $r['status_bayar'] ?></span></td>
            <td><span class="kc-badge <?= $r['status_pesanan'] === 'selesai' ? 'kc-badge-brown' : 'kc-badge-blue' ?>"><?= $r['status_pesanan'] ?></span></td>
            <td style="font-size:11px;color:#a07850"><?= $r['tgl_pesanan'] ?></td>
            <td><a href="function/function_pesanan/delete_pesanan.php?id=<?= $r['id_pesanan'] ?>" class="btn-kc-danger" onclick="return confirm('Hapus pesanan ini?')"><i class='bx bx-trash'></i></a></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'laporan'): ?>

<?php
// Date filter for laporan — dengan prepared statement
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$has_filter = false;

// Validasi format tanggal (YYYY-MM-DD)
if ($start_date && $end_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    $has_filter = true;
}
?>
<div class="kc-card mb-3">
  <div class="kc-card-body p-3" style="display:flex;gap:8px;align-items:center;">
    <input type="date" id="start-date" class="form-control form-control-sm" placeholder="Tanggal mulai" value="<?= htmlspecialchars($start_date) ?>"/>
    <input type="date" id="end-date" class="form-control form-control-sm" placeholder="Tanggal akhir" value="<?= htmlspecialchars($end_date) ?>"/>
    <button class="btn btn-primary btn-sm" onclick="filterLaporan()">Filter</button>
  </div>
</div>
<script>
function filterLaporan() {
  const start = document.getElementById('start-date').value;
  const end = document.getElementById('end-date').value;
  const url = new URL(window.location.href);
  url.searchParams.set('start_date', start);
  url.searchParams.set('end_date', end);
  window.location = url.toString();
}
</script>
  <?php
  if ($has_filter) {
    $stmt_omzet = mysqli_prepare($koneksi, "SELECT IFNULL(SUM(total_harga),0) AS n FROM tb_pesanan WHERE status_bayar='lunas' AND DATE(tgl_pesanan) BETWEEN ? AND ?");
    mysqli_stmt_bind_param($stmt_omzet, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt_omzet);
    $omzet_total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_omzet))['n'];
    mysqli_stmt_close($stmt_omzet);

    $stmt_lunas = mysqli_prepare($koneksi, "SELECT COUNT(*) AS n FROM tb_pesanan WHERE status_bayar='lunas' AND DATE(tgl_pesanan) BETWEEN ? AND ?");
    mysqli_stmt_bind_param($stmt_lunas, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt_lunas);
    $cnt_lunas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_lunas))['n'];
    mysqli_stmt_close($stmt_lunas);

    $stmt_belum = mysqli_prepare($koneksi, "SELECT COUNT(*) AS n FROM tb_pesanan WHERE status_bayar='belum_bayar' AND DATE(tgl_pesanan) BETWEEN ? AND ?");
    mysqli_stmt_bind_param($stmt_belum, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt_belum);
    $cnt_belum = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_belum))['n'];
    mysqli_stmt_close($stmt_belum);
  } else {
    $omzet_total = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT IFNULL(SUM(total_harga),0) AS n FROM tb_pesanan WHERE status_bayar='lunas'"))['n'];
    $cnt_lunas   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM tb_pesanan WHERE status_bayar='lunas'"))['n'];
    $cnt_belum   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM tb_pesanan WHERE status_bayar='belum_bayar'"))['n'];
  }
  ?>

  <div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-box">
      <div class="stat-label">Total Pendapatan</div>
      <div class="stat-value">Rp <?= number_format($omzet_total, 0, ',', '.') ?></div>
    </div>
    <div class="stat-box">
      <div class="stat-label">Pesanan Lunas</div>
      <div class="stat-value dark"><?= $cnt_lunas ?></div>
    </div>
    <div class="stat-box">
      <div class="stat-label">Belum Bayar</div>
      <div class="stat-value blue"><?= $cnt_belum ?></div>
    </div>
  </div>

  <div class="kc-card">
    <div class="kc-card-header"><i class='bx bx-bar-chart-alt-2'></i> Riwayat Transaksi</div>
    <table class="kc-table w-100">
      <thead>
        <tr>
          <th>#</th>
          <th>Tanggal</th>
          <th>Pelanggan</th>
          <th>Meja</th>
          <th>Total</th>
          <th>Status Bayar</th>
          <th>Status Pesanan</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1;
        if ($has_filter) {
          $q_riwayat = mysqli_prepare($koneksi, "SELECT * FROM tb_pesanan WHERE DATE(tgl_pesanan) BETWEEN ? AND ? ORDER BY id_pesanan DESC");
          mysqli_stmt_bind_param($q_riwayat, "ss", $start_date, $end_date);
          mysqli_stmt_execute($q_riwayat);
          $q = mysqli_stmt_get_result($q_riwayat);
        } else {
          $q = mysqli_query($koneksi, "SELECT * FROM tb_pesanan ORDER BY id_pesanan DESC");
        }
        while ($r = mysqli_fetch_assoc($q)): ?>
          <tr>
            <td style="color:#a07850"><?= $no++ ?></td>
            <td style="font-size:11px;color:#a07850"><?= htmlspecialchars($r['tgl_pesanan']) ?></td>
            <td><?= htmlspecialchars($r['nama_pelanggan']) ?></td>
            <td>Meja <?= intval($r['no_meja']) ?></td>
            <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
            <td><span class="kc-badge <?= $r['status_bayar'] === 'lunas' ? 'kc-badge-green' : 'kc-badge-yellow' ?>"><?= htmlspecialchars($r['status_bayar']) ?></span></td>
            <td><span class="kc-badge <?= $r['status_pesanan'] === 'selesai' ? 'kc-badge-brown' : 'kc-badge-blue' ?>"><?= htmlspecialchars($r['status_pesanan']) ?></span></td>
          </tr>
        <?php endwhile;
        if ($has_filter && isset($q_riwayat)) mysqli_stmt_close($q_riwayat);
        ?>
      </tbody>
    </table>
<?php elseif (strtolower($tab) === 'meja'): ?>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="kc-card">
        <div class="kc-card-header"><i class='bx bx-plus-circle'></i> Tambah Meja</div>
        <div class="kc-card-body">
          <form action="function/function_meja/add_meja.php" method="POST">
            <div class="mb-3">
              <label class="form-label">Nomor Meja</label>
              <input type="number" name="nomor_meja" class="form-control form-control-sm" placeholder="Contoh: 5" min="1" required>
            </div>
            <button type="submit" name="add_meja" class="btn-kc btn-kc-sm"><i class='bx bx-save'></i> Simpan</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="kc-card">
        <div class="kc-card-header"><i class='bx bx-table'></i> Daftar Meja</div>
        <table class="kc-table w-100">
          <thead>
            <tr>
              <th style="width: 80px;">#</th>
              <th>Nomor Meja</th>
              <th>Status</th>
              <th style="width: 120px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $limit = 20;
            $page = intval($_GET['page'] ?? 1);
            if ($page < 1) $page = 1;
            
            // Hitung total meja
            $total_query = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_meja");
            $total_data = mysqli_fetch_assoc($total_query)['total'];
            $total_pages = ceil($total_data / $limit);
            if ($total_pages < 1) $total_pages = 1;
            if ($page > $total_pages) $page = $total_pages;
            $offset = ($page - 1) * $limit;

            $q = mysqli_query($koneksi, "SELECT * FROM tb_meja ORDER BY nomor_meja ASC LIMIT $limit OFFSET $offset");
            $no = $offset + 1;
            while ($d = mysqli_fetch_assoc($q)): 
              $is_kosong = $d['status'] === 'kosong';
              $badge_class = $is_kosong ? 'kc-badge-green' : 'kc-badge-red';
              $status_text = $is_kosong ? 'Kosong' : 'Terisi';
            ?>
              <tr>
                <td style="color:#a07850"><?= $no++ ?></td>
                <td><strong>Meja <?= htmlspecialchars($d['nomor_meja']) ?></strong></td>
                <td><span class="kc-badge <?= $badge_class ?>"><?= $status_text ?></span></td>
                <td>
                  <a href="function/function_meja/delete_meja.php?id=<?= $d['id_meja'] ?>" class="btn-kc-danger" onclick="return confirm('Hapus Meja <?= $d['nomor_meja'] ?>?')"><i class='bx bx-trash'></i> Hapus</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>

        <!-- Custom Styled Pagination -->
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

        <?php if ($total_pages > 1): ?>
          <div class="d-flex justify-content-center p-3 border-top" style="background: #fffcf7;">
            <nav aria-label="Page navigation">
              <ul class="pagination pagination-sm m-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="admin.php?tab=meja&page=<?= $page - 1 ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                  </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                  <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="admin.php?tab=meja&page=<?= $i ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                  <a class="page-link" href="admin.php?tab=meja&page=<?= $page + 1 ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                  </a>
                </li>
              </ul>
            </nav>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php endif; ?>

<?php include '_layout_end.php'; ?>