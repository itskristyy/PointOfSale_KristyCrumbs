<?php 
include '../auth.php';
checkRole(['admin']);
include '../connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Mulai transaksi untuk cascade delete resep menu
    mysqli_begin_transaction($koneksi);
    try {
        // 1. Hapus resep yang berelasi dengan id_menu ini di tb_resep
        $stmt_resep = mysqli_prepare($koneksi, "DELETE FROM tb_resep WHERE id_menu = ?");
        mysqli_stmt_bind_param($stmt_resep, "i", $id);
        mysqli_stmt_execute($stmt_resep);
        mysqli_stmt_close($stmt_resep);

        // 2. Hapus menu dari tb_menu
        $stmt_menu = mysqli_prepare($koneksi, "DELETE FROM tb_menu WHERE id_menu = ?");
        mysqli_stmt_bind_param($stmt_menu, "i", $id);
        $result = mysqli_stmt_execute($stmt_menu);
        mysqli_stmt_close($stmt_menu);

        if (!$result) {
            throw new Exception("Gagal menghapus menu dari database.");
        }

        mysqli_commit($koneksi);
        echo "<script>alert('Menu beserta resep terkait berhasil dihapus!'); window.location='../../admin.php?tab=menu';</script>";
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $error_msg = addslashes($e->getMessage());
        echo "<script>alert('Gagal menghapus menu: $error_msg'); window.location='../../admin.php?tab=menu';</script>";
        exit;
    }
} else {
    header("Location: ../../admin.php");
    exit;
}
?>
