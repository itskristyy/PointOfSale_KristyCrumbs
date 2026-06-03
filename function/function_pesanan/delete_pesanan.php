<?php 
include '../auth.php';
checkRole(['admin']);
include '../connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    mysqli_begin_transaction($koneksi);
    
    try {
        // Kembalikan stok
        $stmt_detail = mysqli_prepare($koneksi, "SELECT id_menu, jumlah FROM tb_detail_pesanan WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($stmt_detail, "i", $id);
        mysqli_stmt_execute($stmt_detail);
        $details = mysqli_stmt_get_result($stmt_detail);
        
        while ($row = mysqli_fetch_assoc($details)) {
            $stok_stmt = mysqli_prepare($koneksi, "UPDATE tb_menu SET stok = stok + ? WHERE id_menu = ?");
            mysqli_stmt_bind_param($stok_stmt, "ii", $row['jumlah'], $row['id_menu']);
            mysqli_stmt_execute($stok_stmt);
            mysqli_stmt_close($stok_stmt);
        }
        mysqli_stmt_close($stmt_detail);

        // Hapus detail pesanan
        $del_detail = mysqli_prepare($koneksi, "DELETE FROM tb_detail_pesanan WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($del_detail, "i", $id);
        mysqli_stmt_execute($del_detail);
        mysqli_stmt_close($del_detail);

        // Hapus stok log yang terkait pesanan ini (FK constraint)
        $del_log = mysqli_prepare($koneksi, "DELETE FROM tb_stok_log WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($del_log, "i", $id);
        mysqli_stmt_execute($del_log);
        mysqli_stmt_close($del_log);

        // Ambil no_meja dari pesanan lalu update status meja menjadi kosong
        $meja_stmt = mysqli_prepare($koneksi, "SELECT no_meja FROM tb_pesanan WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($meja_stmt, "i", $id);
        mysqli_stmt_execute($meja_stmt);
        $pesanan_res = mysqli_stmt_get_result($meja_stmt);
        $pesanan_data = mysqli_fetch_assoc($pesanan_res);
        mysqli_stmt_close($meja_stmt);
        
        if ($pesanan_data) {
            $no_meja = intval($pesanan_data['no_meja']);
            $update_meja = mysqli_prepare($koneksi, "UPDATE tb_meja SET status = 'kosong' WHERE id_meja = ?");
            mysqli_stmt_bind_param($update_meja, "i", $no_meja);
            mysqli_stmt_execute($update_meja);
            mysqli_stmt_close($update_meja);
        }

        // Hapus pesanan
        $del_pesanan = mysqli_prepare($koneksi, "DELETE FROM tb_pesanan WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($del_pesanan, "i", $id);
        mysqli_stmt_execute($del_pesanan);
        mysqli_stmt_close($del_pesanan);

        mysqli_commit($koneksi);
        echo "<script>alert('Pesanan berhasil dibatalkan dan stok dikembalikan!'); window.location='../../admin.php?tab=pesanan';</script>";
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>alert('Gagal membatalkan pesanan!'); window.location='../../admin.php?tab=pesanan';</script>";
    }
} else {
    header("Location: ../../admin.php");
    exit;
}
?>
