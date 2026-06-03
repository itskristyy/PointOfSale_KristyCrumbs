<?php
// function/function_warehouse/functions/auto_kurang_stok.php

/**
 * Mengurangi stok bahan baku secara otomatis berdasarkan resep menu yang dipesan.
 * 
 * @param mysqli $koneksi
 * @param int $id_pesanan
 * @return array
 */
function autoKurangStok($koneksi, $id_pesanan) {
    $warnings = [];
    
    // 1. Mulai transaksi
    mysqli_begin_transaction($koneksi);
    
    try {
        // 2. Ambil detail pesanan (id_menu dan jumlah porsi)
        $query_detail = "SELECT id_menu, jumlah FROM tb_detail_pesanan WHERE id_pesanan = ?";
        $stmt_detail = mysqli_prepare($koneksi, $query_detail);
        if (!$stmt_detail) {
            throw new Exception("Gagal menyiapkan statement detail pesanan: " . mysqli_error($koneksi));
        }
        mysqli_stmt_bind_param($stmt_detail, "i", $id_pesanan);
        mysqli_stmt_execute($stmt_detail);
        $res_detail = mysqli_stmt_get_result($stmt_detail);
        
        $items = [];
        while ($row = mysqli_fetch_assoc($res_detail)) {
            $items[] = $row;
        }
        mysqli_stmt_close($stmt_detail);
        
        if (empty($items)) {
            mysqli_commit($koneksi);
            return [
                'success' => true,
                'message' => 'Tidak ada item pesanan.',
                'warnings' => []
            ];
        }
        
        // 3. Loop setiap item pesanan
        foreach ($items as $item) {
            $id_menu = (int)$item['id_menu'];
            $jumlah_pesan = (int)$item['jumlah'];
            
            // Ambil bahan-bahan di resep menu ini
            $query_resep = "SELECT r.id_bahan, r.jumlah AS jumlah_per_porsi, b.stok, b.nama_bahan 
                            FROM tb_resep r 
                            JOIN tb_bahan b ON r.id_bahan = b.id_bahan 
                            WHERE r.id_menu = ?";
            $stmt_resep = mysqli_prepare($koneksi, $query_resep);
            if (!$stmt_resep) {
                throw new Exception("Gagal menyiapkan statement resep: " . mysqli_error($koneksi));
            }
            mysqli_stmt_bind_param($stmt_resep, "i", $id_menu);
            mysqli_stmt_execute($stmt_resep);
            $res_resep = mysqli_stmt_get_result($stmt_resep);
            
            $reseps = [];
            while ($row = mysqli_fetch_assoc($res_resep)) {
                $reseps[] = $row;
            }
            mysqli_stmt_close($stmt_resep);
            
            // Loop setiap bahan dalam resep
            foreach ($reseps as $r) {
                $id_bahan = (int)$r['id_bahan'];
                $nama_bahan = $r['nama_bahan'];
                $jumlah_per_porsi = (float)$r['jumlah_per_porsi'];
                $stok_sekarang = (float)$r['stok'];
                
                $jumlah_kurang = $jumlah_per_porsi * $jumlah_pesan;
                $stok_baru = $stok_sekarang - $jumlah_kurang;
                
                // Jika stok menjadi minus, beri warning tapi tetap lanjutkan
                if ($stok_baru < 0) {
                    $keterangan = "Stok minus - cek supplier (Pesanan #$id_pesanan)";
                    $warnings[] = "⚠️ Stok bahan '$nama_bahan' kurang/minus saat menyelesaikan pesanan #$id_pesanan.";
                } else {
                    $keterangan = "Auto dari pesanan #$id_pesanan";
                }
                
                // UPDATE tb_bahan
                $query_update = "UPDATE tb_bahan SET stok = stok - ? WHERE id_bahan = ?";
                $stmt_update = mysqli_prepare($koneksi, $query_update);
                if (!$stmt_update) {
                    throw new Exception("Gagal menyiapkan statement update stok: " . mysqli_error($koneksi));
                }
                mysqli_stmt_bind_param($stmt_update, "di", $jumlah_kurang, $id_bahan);
                $exec_update = mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
                
                if (!$exec_update) {
                    throw new Exception("Gagal update stok bahan ID $id_bahan");
                }
                
                // INSERT tb_stok_log
                $query_log = "INSERT INTO tb_stok_log (id_bahan, jenis, jumlah, keterangan, id_pesanan) 
                              VALUES (?, 'terpakai', ?, ?, ?)";
                $stmt_log = mysqli_prepare($koneksi, $query_log);
                if (!$stmt_log) {
                    throw new Exception("Gagal menyiapkan statement log: " . mysqli_error($koneksi));
                }
                mysqli_stmt_bind_param($stmt_log, "idsi", $id_bahan, $jumlah_kurang, $keterangan, $id_pesanan);
                $exec_log = mysqli_stmt_execute($stmt_log);
                mysqli_stmt_close($stmt_log);
                
                if (!$exec_log) {
                    throw new Exception("Gagal insert stok log bahan ID $id_bahan");
                }
            }
        }
        
        // 4. Update session alert_stok
        $q_alert = mysqli_query($koneksi, "SELECT COUNT(*) AS total_menipis FROM tb_bahan WHERE stok <= stok_minimum");
        if ($q_alert) {
            $alert_row = mysqli_fetch_assoc($q_alert);
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['alert_stok'] = (int)$alert_row['total_menipis'];
        }
        
        // Commit
        mysqli_commit($koneksi);
        
        return [
            'success' => true,
            'message' => 'Stok berhasil dikurangi.',
            'warnings' => $warnings
        ];
    } catch (Exception $e) {
        // Rollback
        mysqli_rollback($koneksi);
        return [
            'success' => false,
            'message' => 'Gagal kurangi stok: ' . $e->getMessage(),
            'warnings' => []
        ];
    }
}
?>
