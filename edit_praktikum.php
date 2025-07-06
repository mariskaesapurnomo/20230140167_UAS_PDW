<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $kode_mk = trim($_POST['kode_mk']);
    $nama_mk = trim($_POST['nama_mk']);
    $deskripsi = trim($_POST['deskripsi']);
    $asisten_id = $_POST['asisten_id'] ? (int)$_POST['asisten_id'] : null; // Allow NULL for no assistant

    if (empty($id) || empty($kode_mk) || empty($nama_mk)) {
        $_SESSION['error'] = 'Data tidak lengkap untuk update.';
        header('Location: kelola_praktikum.php');
        exit();
    }

    // Check for duplicate kode_mk or nama_mk, excluding the current praktikum
    $check_sql = "SELECT COUNT(*) FROM mata_praktikum WHERE (kode_mk = ? OR nama_mk = ?) AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ssi", $kode_mk, $nama_mk, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_row();
    if ($row[0] > 0) {
        $_SESSION['error'] = 'Kode Mata Kuliah atau Nama Mata Praktikum sudah ada untuk praktikum lain.';
        header('Location: kelola_praktikum.php');
        exit();
    }
    $check_stmt->close();

    $sql = "UPDATE mata_praktikum SET kode_mk = ?, nama_mk = ?, deskripsi = ?, asisten_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Bind parameters dynamically based on whether asisten_id is null
    if ($asisten_id === null) {
        $stmt->bind_param("sssiN", $kode_mk, $nama_mk, $deskripsi, $id); // 'N' for NULL
    } else {
        $stmt->bind_param("sssii", $kode_mk, $nama_mk, $deskripsi, $asisten_id, $id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Mata Praktikum berhasil diperbarui.';
    } else {
        $_SESSION['error'] = 'Gagal memperbarui Mata Praktikum: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header('Location: kelola_praktikum.php');
    exit();
} else {
    header('Location: kelola_praktikum.php');
    exit();
}
?>