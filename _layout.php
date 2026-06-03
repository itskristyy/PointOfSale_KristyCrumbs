<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$role  = $_SESSION['role'] ?? '';
$uname = $_SESSION['username'] ?? '';

// Hitung path relatif dari file saat ini kembali ke root project
$root_dir = str_replace('\\', '/', realpath(__DIR__));
$current_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
$relative_subpath = trim(str_replace($root_dir, '', $current_dir), '/');

$base_url = '';
if ($relative_subpath !== '') {
  $depth = count(explode('/', $relative_subpath));
  $base_url = str_repeat('../', $depth);
}

if (($role === 'admin' || $role === 'owner') && !isset($_SESSION['alert_stok'])) {
  if (!isset($koneksi)) {
    include_once __DIR__ . '/function/connect.php';
  }
  if (isset($koneksi)) {
    $q_alert = mysqli_query($koneksi, "SELECT COUNT(*) AS total_menipis FROM tb_bahan WHERE stok <= stok_minimum");
    if ($q_alert) {
      $_SESSION['alert_stok'] = (int) mysqli_fetch_assoc($q_alert)['total_menipis'];
    } else {
      $_SESSION['alert_stok'] = 0;
    }
  } else {
    $_SESSION['alert_stok'] = 0;
  }
}
$alert_count = $_SESSION['alert_stok'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'KristyCrumbs' ?> - KristyCrumbs POS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }

    body {
      background: #fdf9f0;
      margin: 0;
    }

    a {
      text-decoration: none;
    }

    .layout {
      display: flex;
      min-height: 100vh;
    }

    .sidebar {
      width: 200px;
      background: #fff;
      border-right: 1px solid #ece8df;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
    }

    .sb-brand {
      padding: 16px 16px 14px;
      border-bottom: 1px solid #ece8df;
    }

    .sb-brand-name {
      font-size: 15px;
      font-weight: 800;
      color: #1c1c17;
      letter-spacing: -0.01em;
    }

    .sb-brand-name span {
      color: #964261;
    }

    .sb-brand-sub {
      font-size: 10px;
      color: #867277;
      margin-top: 1px;
    }

    .sb-section {
      padding: 12px 16px 4px;
      font-size: 10px;
      color: #867277;
      text-transform: uppercase;
      letter-spacing: .06em;
      font-weight: 700;
    }

    .sb-nav {
      padding: 2px 8px;
    }

    .sb-link {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 7px 10px;
      color: #534247;
      font-size: 12px;
      font-weight: 500;
      border-radius: 999px;
      margin-bottom: 1px;
      transition: background 0.15s, color 0.15s;
    }

    .sb-link i {
      font-size: 15px;
    }

    .sb-link:hover {
      background: #f7f3ea;
      color: #964261;
    }

    .sb-link.active {
      background: #fde8cc;
      color: #964261;
      font-weight: 700;
    }

    .sb-foot {
      margin-top: auto;
      padding: 12px 16px;
      border-top: 1px solid #ece8df;
    }

    .sb-user-name {
      font-size: 12px;
      font-weight: 700;
      color: #1c1c17;
    }

    .sb-user-role {
      font-size: 10px;
      color: #867277;
      margin-bottom: 8px;
    }

    .btn-logout {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 5px 12px;
      border: 1px solid #fca5a5;
      border-radius: 999px;
      color: #dc2626;
      font-size: 11px;
      font-weight: 600;
      transition: background 0.15s;
    }

    .btn-logout:hover {
      background: #fee2e2;
    }

    .main-wrap {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }

    .topbar {
      background: #fff;
      border-bottom: 1px solid #ece8df;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .topbar-title {
      font-size: 14px;
      font-weight: 700;
      color: #1c1c17;
    }

    .topbar-sub {
      font-size: 11px;
      color: #867277;
    }

    .page-content {
      padding: 20px;
    }

    .kc-card {
      background: #fff;
      border: 1px solid #ece8df;
      border-radius: 16px;
      margin-bottom: 16px;
    }

    .kc-card-header {
      padding: 10px 16px;
      border-bottom: 1px solid #ece8df;
      font-weight: 700;
      font-size: 12px;
      color: #1c1c17;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-radius: 16px 16px 0 0;
    }

    .kc-card-header i {
      color: #964261;
      font-size: 15px;
      margin-right: 4px;
    }

    .kc-card-body {
      padding: 14px 16px;
    }

    .kc-table thead th {
      background: #fdf5ec;
      color: #964261;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .04em;
      padding: 8px 12px;
      border-bottom: 1px solid #ece8df;
    }

    .kc-table tbody td {
      padding: 9px 12px;
      border-bottom: 1px solid #f7f3ea;
      font-size: 12px;
      vertical-align: middle;
      color: #1c1c17;
    }

    .kc-table tbody tr:hover {
      background: #fdf9f0;
    }

    .kc-table tbody tr:last-child td {
      border-bottom: none;
    }

    .kc-badge {
      border-radius: 999px;
      padding: 3px 10px;
      font-size: 10px;
      font-weight: 700;
      display: inline-block;
    }

    .kc-badge-brown {
      background: #fde8cc;
      color: #7c3a0e;
    }

    .kc-badge-yellow {
      background: #fef9c3;
      color: #854d0e;
    }

    .kc-badge-green {
      background: #dcfce7;
      color: #15803d;
    }

    .kc-badge-blue {
      background: #dbeafe;
      color: #1e40af;
    }

    .kc-badge-red {
      background: #fee2e2;
      color: #b91c1c;
    }

    .kc-badge-gray {
      background: #f1f5f9;
      color: #475569;
    }

    .btn-kc {
      background: #964261;
      border: 1px solid #964261;
      color: #fff;
      border-radius: 999px;
      padding: 6px 14px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: background 0.15s, color 0.15s, border-color 0.15s;
    }

    .btn-kc:hover {
      background: #fdf0f4;
      color: #964261;
      border-color: #f48fb1;
    }

    .btn-kc-sm {
      padding: 4px 10px;
      font-size: 11px;
    }

    .btn-kc-outline {
      background: #fff;
      border: 1px solid #ece8df;
      color: #534247;
      border-radius: 999px;
      padding: 4px 10px;
      font-size: 11px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: border-color 0.15s, background 0.15s;
    }

    .btn-kc-outline:hover {
      border-color: #f48fb1;
      background: #fdf0f4;
      color: #964261;
    }

    .btn-kc-danger {
      background: #fff;
      border: 1px solid #fca5a5;
      color: #dc2626;
      border-radius: 999px;
      padding: 4px 10px;
      font-size: 11px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: background 0.15s;
    }

    .btn-kc-danger:hover {
      background: #fee2e2;
    }

    .stat-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-bottom: 16px;
    }

    .stat-box {
      background: #fff;
      border: 1px solid #ece8df;
      border-radius: 16px;
      padding: 14px 16px;
      transition: border-color 0.15s;
    }

    .stat-box:hover {
      border-color: #f48fb1;
    }

    .stat-label {
      font-size: 10px;
      color: #867277;
      text-transform: uppercase;
      letter-spacing: .05em;
      font-weight: 700;
      margin-bottom: 6px;
    }

    .stat-value {
      font-size: 22px;
      font-weight: 800;
      color: #964261;
    }

    .stat-value.dark {
      color: #1c1c17;
    }

    .stat-value.blue {
      color: #1d4ed8;
    }

    .form-label {
      font-size: 12px;
      font-weight: 600;
      color: #534247;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #964261;
      box-shadow: 0 0 0 .15rem rgba(150, 66, 97, .12);
    }
  </style>
</head>

<body>
  <div class="layout">

    <div class="sidebar">
      <div class="sb-brand">
        <div class="sb-brand-name">Kristy<span>Crumbs</span></div>
        <div class="sb-brand-sub">POS System</div>
      </div>

      <?php if ($role === 'admin' || $role === 'owner'): ?>
        <div class="sb-section">Admin</div>
        <div class="sb-nav">
          <a href="<?= $base_url ?>admin.php" class="sb-link <?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>"><i class='bx bx-home-alt'></i> Dashboard</a>
          <a href="<?= $base_url ?>admin.php?tab=menu" class="sb-link <?= ($active ?? '') === 'menu' ? 'active' : '' ?>"><i class='bx bx-dish'></i> Kelola Menu</a>
          <a href="<?= $base_url ?>admin.php?tab=user" class="sb-link <?= ($active ?? '') === 'user' ? 'active' : '' ?>"><i class='bx bx-group'></i> Kelola User</a>
          <a href="<?= $base_url ?>admin.php?tab=pesanan" class="sb-link <?= ($active ?? '') === 'pesanan' ? 'active' : '' ?>"><i class='bx bx-receipt'></i> Pesanan</a>
          <a href="<?= $base_url ?>admin.php?tab=meja" class="sb-link <?= ($active ?? '') === 'meja' ? 'active' : '' ?>"><i class='bx bx-table'></i> Kelola Meja</a>
          <a href="<?= $base_url ?>admin.php?tab=laporan" class="sb-link <?= ($active ?? '') === 'laporan' ? 'active' : '' ?>"><i class='bx bx-bar-chart-alt-2'></i> Laporan</a>
        </div>
      <?php endif; ?>

      <?php if ($role === 'kasir' || $role === 'owner'): ?>
        <div class="sb-section">Kasir</div>
        <div class="sb-nav">
          <a href="<?= $base_url ?>kasir.php" class="sb-link <?= ($active ?? '') === 'kasir' ? 'active' : '' ?>"><i class='bx bx-calculator'></i> Transaksi</a>
        </div>
      <?php endif; ?>

      <?php if ($role === 'dapur' || $role === 'owner'): ?>
        <div class="sb-section">Dapur</div>
        <div class="sb-nav">
          <a href="<?= $base_url ?>dapur.php" class="sb-link <?= ($active ?? '') === 'dapur' ? 'active' : '' ?>"><i class='bx bx-bowl-hot'></i> Antrian Masak</a>
        </div>
      <?php endif; ?>

      <?php if ($role === 'admin' || $role === 'owner'): ?>
        <div class="sb-section">Warehouse</div>
        <div class="sb-nav">
          <a href="<?= $base_url ?>function/function_warehouse/bahan/index.php" class="sb-link <?= ($active ?? '') === 'warehouse' ? 'active' : '' ?>">
            <i class='bx bx-package'></i> Bahan Baku
            <?php if ($alert_count > 0): ?>
              <span class="kc-badge kc-badge-red ms-auto" style="padding: 2px 6px; font-size: 9px; line-height: 1;"><?= $alert_count ?></span>
            <?php endif; ?>
          </a>
          <a href="<?= $base_url ?>function/function_warehouse/resep/index.php" class="sb-link <?= ($active ?? '') === 'warehouse-resep' ? 'active' : '' ?>"><i class='bx bx-book-alt'></i> Kelola Resep</a>
          <a href="<?= $base_url ?>function/function_warehouse/alert_stok.php" class="sb-link <?= ($active ?? '') === 'alert_warehouse' ? 'active' : '' ?>">
            <i class='bx bx-bell'></i> Alert Stok
            <?php if ($alert_count > 0): ?>
              <span class="kc-badge kc-badge-red ms-auto" style="padding: 2px 6px; font-size: 9px; line-height: 1;"><?= $alert_count ?></span>
            <?php endif; ?>
          </a>
          <a href="<?= $base_url ?>function/function_warehouse/laporan/index.php" class="sb-link <?= ($active ?? '') === 'laporan_warehouse' ? 'active' : '' ?>"> <i class='bx bx-bar-chart-alt-2'></i> Laporan Stok</a>
          <a href="<?= $base_url ?>function/function_warehouse/laporan/log.php" class="sb-link <?= ($active ?? '') === 'log_warehouse' ? 'active' : '' ?>"><i class='bx bx-history'></i> Riwayat Log</a>
        </div>
      <?php endif; ?>

      <?php if ($role === 'owner'): ?>
        <div class="sb-section">Owner</div>
        <div class="sb-nav">
          <a href="<?= $base_url ?>owner.php" class="sb-link <?= ($active ?? '') === 'owner' ? 'active' : '' ?>"><i class='bx bx-bar-chart-alt-2'></i> Laporan & Omzet</a>
        </div>
      <?php endif; ?>

      <div class="sb-foot">
        <div class="sb-user-name"><?= htmlspecialchars($uname) ?></div>
        <div class="sb-user-role"><?= ucfirst($role) ?></div>
        <a href="<?= $base_url ?>function/logout.php" class="btn-logout"><i class='bx bx-log-out'></i> Keluar</a>
      </div>
    </div>

    <div class="main-wrap">
      <div class="topbar">
        <div class="topbar-title"><?= $page_title ?? '' ?></div>
        <div class="topbar-sub"><?= date('l, d F Y') ?></div>
      </div>
      <div class="page-content">