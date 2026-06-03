<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'function/auth.php';
checkRole(['kasir', 'admin']);
include 'function/connect.php';

$id = intval($_GET['id'] ?? 0);
$pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tb_pesanan WHERE id_pesanan = $id"));
if (!$pesanan) {
  echo "Pesanan tidak ditemukan.";
  exit;
}

// Update nama_kasir jika kosong berdasarkan username yang login
if (empty($pesanan['nama_kasir']) && !empty($_SESSION['username'])) {
  $logged_in_user = $_SESSION['username'];
  mysqli_query($koneksi, "UPDATE tb_pesanan SET nama_kasir = '" . mysqli_real_escape_string($koneksi, $logged_in_user) . "' WHERE id_pesanan = $id");
  $pesanan['nama_kasir'] = $logged_in_user;
}


$details = mysqli_query(
  $koneksi,
  "SELECT d.jumlah, d.subtotal, m.nama_menu, m.harga
     FROM tb_detail_pesanan d
     JOIN tb_menu m ON d.id_menu = m.id_menu
     WHERE d.id_pesanan = $id"
);

$no = 1;
$rows = [];
while ($d = mysqli_fetch_assoc($details)) $rows[] = $d;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Struk #<?= $id ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Courier New', monospace;
      font-size: 12px;
      background: #fff;
    }

    .no-print {
      padding: 10px;
      background: #f5f5f5;
      border-bottom: 1px solid #ddd;
      display: flex;
      gap: 8px;
    }

    .no-print button,
    .no-print a {
      padding: 6px 14px;
      font-size: 12px;
      border-radius: 3px;
      cursor: pointer;
      border: 1px solid #ccc;
      background: #fff;
      text-decoration: none;
      color: #333;
      font-family: sans-serif;
    }

    .no-print button.cetak {
      background: #333;
      color: #fff;
      border-color: #333;
    }

    .struk {
      width: 300px;
      margin: 20px auto;
      padding: 12px;
    }

    .toko-nama {
      text-align: center;
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 2px;
    }

    .toko-sub {
      text-align: center;
      font-size: 11px;
      margin-bottom: 8px;
    }

    .garis {
      border: none;
      border-top: 1px dashed #000;
      margin: 6px 0;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      margin-bottom: 2px;
    }

    .item-nama {
      font-size: 12px;
      font-weight: bold;
    }

    .item-detail {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      margin-bottom: 6px;
      padding-left: 4px;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
      font-weight: bold;
      margin: 4px 0;
    }

    .bayar-row {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      margin: 2px 0;
    }

    .footer {
      text-align: center;
      font-size: 11px;
      margin-top: 10px;
    }

    @media print {
      .no-print {
        display: none;
      }

      body {
        margin: 0;
      }

      .struk {
        margin: 0;
        width: 100%;
      }
    }
  </style>
</head>

<body>

  <div class="no-print">
    <a href="kasir.php">← Kembali</a>
    <button class="cetak" onclick="window.print()">🖨 Print</button>
  </div>

  <div class="struk">

    <div class="toko-nama">KristyCrumbs</div>
    <div class="toko-sub">Point of Sale System</div>

    <hr class="garis">

    <div class="info-row"><span>No. Pesanan</span><span>#<?= $pesanan['id_pesanan'] ?></span></div>
    <div class="info-row"><span>Tanggal</span><span><?= $pesanan['tgl_pesanan'] ?></span></div>
    <div class="info-row"><span>Pelanggan</span><span><?= htmlspecialchars($pesanan['nama_pelanggan']) ?></span></div>
    <div class="info-row"><span>No. Meja</span><span><?= $pesanan['no_meja'] ?></span></div>
    <div class="info-row"><span>Nama Kasir</span><span><?= htmlspecialchars(!empty($pesanan['nama_kasir']) ? $pesanan['nama_kasir'] : ($_SESSION['username'] ?? '-')) ?></span></div>
    <div class="info-row"><span>Metode Bayar</span><span><?= htmlspecialchars($pesanan['metode_bayar'] ?? 'tunai') ?></span></div>

    <hr class="garis">

    <?php foreach ($rows as $i => $d): ?>
      <div class="item-nama"><?= ($i + 1) ?>. <?= htmlspecialchars($d['nama_menu']) ?></div>
      <div class="item-detail">
        <span><?= $d['jumlah'] ?> x Rp <?= number_format($d['harga'], 0, ',', '.') ?></span>
        <span>Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></span>
      </div>
      <?php if (!empty($d['catatan'])): ?>
        <div class="item-catatan" style="font-size:10px;color:#555;margin-left:4px;">Catatan: <?= htmlspecialchars($d['catatan']) ?></div>
      <?php endif; ?>
    <?php endforeach; ?>

    <hr class="garis">

    <div class="total-row">
      <span>Total</span>
      <span>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span>
    </div>
    <div class="bayar-row">
      <span>Uang Bayar</span>
      <span>Rp <?= number_format($pesanan['uang_bayar'] ?? 0, 0, ',', '.') ?></span>
    </div>
    <div class="bayar-row" style="font-weight:bold;">
      <span>Kembalian</span>
      <span>Rp <?= number_format(max(0, ($pesanan['uang_bayar'] ?? 0) - ($pesanan['total_harga'] ?? 0)), 0, ',', '.') ?></span>
    </div>
    <div class="bayar-row" style="margin-top:4px;">
      <span>Status</span>
      <span style="font-weight:bold;"><?= $pesanan['status_bayar'] === 'lunas' ? 'LUNAS ✓' : 'Belum Bayar' ?></span>
    </div>

    <hr class="garis">

    <div class="footer">
      Terima kasih sudah berkunjung!
    </div>

  </div>

</body>

</html>