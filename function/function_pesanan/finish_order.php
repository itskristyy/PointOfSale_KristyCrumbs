<?php
include '../auth.php';
checkRole(['dapur', 'admin']);
include '../connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Ambil no_meja dari pesanan lalu update status meja menjadi kosong
    $stmt_meja = mysqli_prepare($koneksi, "SELECT no_meja FROM tb_pesanan WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt_meja, "i", $id);
    mysqli_stmt_execute($stmt_meja);
    $pesanan_res = mysqli_stmt_get_result($stmt_meja);
    $pesanan_data = mysqli_fetch_assoc($pesanan_res);
    mysqli_stmt_close($stmt_meja);

    if ($pesanan_data) {
        $no_meja = intval($pesanan_data['no_meja']);
        $update_meja = mysqli_prepare($koneksi, "UPDATE tb_meja SET status = 'kosong' WHERE id_meja = ?");
        mysqli_stmt_bind_param($update_meja, "i", $no_meja);
        mysqli_stmt_execute($update_meja);
        mysqli_stmt_close($update_meja);
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE tb_pesanan SET status_pesanan = 'selesai' WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($result) {
        // Panggil autoKurangStok untuk mengurangi bahan baku berdasarkan resep
        include_once '../function_warehouse/functions/auto_kurang_stok.php';
        $res_stok = autoKurangStok($koneksi, $id);
        
        // Log error jika autoKurangStok gagal
        if (isset($res_stok['success']) && !$res_stok['success']) {
            error_log("[WAREHOUSE] Pesanan #$id: " . $res_stok['message']);
        }
        
        echo "<script>alert('Pesanan telah siap saji!'); window.location='../../dapur.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui status pesanan!'); window.location='../../dapur.php';</script>";
    }
} else {
    header("Location: ../../dapur.php");
    exit;
}
