<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Optional: Check for associated modules or enrollments before deleting to prevent orphan records or cascade issues
    // For now, we proceed with deletion, assuming database handles cascade or user manages related data.
    // A robust application would check and inform the user if dependencies exist.

    $sql = "DELETE FROM mata_praktikum WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = 'Mata Praktikum berhasil dihapus.';
        } else {
            $_SESSION['error'] = 'Mata Praktikum tidak ditemukan.';
        }
    } else {
        $_SESSION['error'] = 'Gagal menghapus Mata Praktikum: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header('Location: kelola_praktikum.php');
    exit();
} else {
    $_SESSION['error'] = 'ID Mata Praktikum tidak valid untuk dihapus.';
    header('Location: kelola_praktikum.php');
    exit();
}
?>