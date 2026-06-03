<?php
// function/function_warehouse/bahan/delete_bahan.php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../auth.php';
checkRole(['admin', 'owner']);
include '../../connect.php';

if (isset($_GET['id'])) {
    $id_bahan = intval($_GET['id']);

    // Begin transaction for safe cascade deletion
    mysqli_begin_transaction($koneksi);
    try {
        // 1. Hapus dari tb_stok_log
        $stmt_log = mysqli_prepare($koneksi, "DELETE FROM tb_stok_log WHERE id_bahan = ?");
        mysqli_stmt_bind_param($stmt_log, "i", $id_bahan);
        mysqli_stmt_execute($stmt_log);
        mysqli_stmt_close($stmt_log);

        // 2. Hapus dari tb_resep
        $stmt_resep = mysqli_prepare($koneksi, "DELETE FROM tb_resep WHERE id_bahan = ?");
        mysqli_stmt_bind_param($stmt_resep, "i", $id_bahan);
        mysqli_stmt_execute($stmt_resep);
        mysqli_stmt_close($stmt_resep);

        // 3. Hapus dari tb_bahan
        $stmt_bahan = mysqli_prepare($koneksi, "DELETE FROM tb_bahan WHERE id_bahan = ?");
        mysqli_stmt_bind_param($stmt_bahan, "i", $id_bahan);
        $result = mysqli_stmt_execute($stmt_bahan);
        mysqli_stmt_close($stmt_bahan);

        if (!$result) {
            throw new Exception("Gagal menghapus bahan dari tb_bahan.");
        }

        // 4. Update session alert_stok
        $q_alert = mysqli_query($koneksi, "SELECT COUNT(*) AS total_menipis FROM tb_bahan WHERE stok <= stok_minimum");
        $_SESSION['alert_stok'] = (int) mysqli_fetch_assoc($q_alert)['total_menipis'];

        // Commit transaction
        mysqli_commit($koneksi);

        echo "<script>alert('Bahan berhasil dihapus beserta log dan resep terkait!'); window.location='index.php';</script>";
        exit;
    } catch (Exception $e) {
        // Rollback on failure
        mysqli_rollback($koneksi);
        $error_msg = addslashes($e->getMessage());
        echo "<script>alert('Gagal menghapus bahan: $error_msg'); window.location='index.php';</script>";
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
