<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_mk = trim($_POST['kode_mk']);
    $nama_mk = trim($_POST['nama_mk']);
    $deskripsi = trim($_POST['deskripsi']);
    $asisten_id = $_POST['asisten_id'] ? (int)$_POST['asisten_id'] : null; // Allow NULL for no assistant

    if (empty($kode_mk) || empty($nama_mk)) {
        $_SESSION['error'] = 'Kode Mata Kuliah dan Nama Mata Praktikum tidak boleh kosong.';
        header('Location: kelola_praktikum.php');
        exit();
    }

    // Check if kode_mk or nama_mk already exists
    $check_sql = "SELECT COUNT(*) FROM mata_praktikum WHERE kode_mk = ? OR nama_mk = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $kode_mk, $nama_mk);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_row();
    if ($row[0] > 0) {
        $_SESSION['error'] = 'Kode Mata Kuliah atau Nama Mata Praktikum sudah ada.';
        header('Location: kelola_praktikum.php');
        exit();
    }
    $check_stmt->close();

    $sql = "INSERT INTO mata_praktikum (kode_mk, nama_mk, deskripsi, asisten_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Bind parameters dynamically based on whether asisten_id is null
    if ($asisten_id === null) {
        $stmt->bind_param("sssN", $kode_mk, $nama_mk, $deskripsi, $asisten_id); // 'N' for NULL
    } else {
        $stmt->bind_param("sssi", $kode_mk, $nama_mk, $deskripsi, $asisten_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Mata Praktikum berhasil ditambahkan.';
    } else {
        $_SESSION['error'] = 'Gagal menambahkan Mata Praktikum: ' . $stmt->error;
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