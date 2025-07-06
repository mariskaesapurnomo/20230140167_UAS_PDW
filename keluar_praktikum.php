<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = 'Keluar Praktikum';

// Ambil praktikum yang diikuti mahasiswa
$sql = "SELECT pp.praktikum_id, mp.nama_mk, mp.kode_mk
        FROM pendaftaran_praktikum pp
        JOIN mata_praktikum mp ON pp.praktikum_id = mp.id
        WHERE pp.mahasiswa_id = ?
        ORDER BY mp.nama_mk ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['praktikum_id'])) {
    $praktikum_id = $_POST['praktikum_id'];
    
    // Hapus pendaftaran praktikum
    // Sebaiknya juga tambahkan logika untuk menghapus laporan terkait di sini
    $delete_sql = "DELETE FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $user_id, $praktikum_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = 'Berhasil keluar dari praktikum!';
    } else {
        $_SESSION['error'] = 'Gagal keluar dari praktikum.';
    }
    header('Location: keluar_praktikum.php');
    exit();
}
// --- AKHIR DARI FUNGSI PHP ANDA ---
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - SIMPRAK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #0b2545;
            --blue-light: #eaf1f8;
            --white: #ffffff;
            --gray-text: #555;
            --gray-border: #ccc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            background-color: #f4f6f8;
        }

        aside {
            width: 240px;
            background-color: var(--navy);
            min-height: 100vh;
            color: white;
            padding: 2rem 1rem;
            position: fixed; /* Added for fixed sidebar */
            height: 100%; /* Added for fixed sidebar */
            overflow-y: auto; /* Added for scrollable sidebar */
        }

        aside h2 {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .nav-link {
            display: flex; /* Changed from block to flex for icon alignment */
            align-items: center; /* Align items vertically */
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: #123c69;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px; /* Fixed width for icons */
            text-align: center; /* Center icon */
        }

        main {
            flex: 1;
            padding: 2rem;
            margin-left: 240px; /* Offset for fixed sidebar */
        }

        h1 {
            font-size: 2rem;
            color: var(--navy);
            margin-bottom: 1rem;
        }

        .card {
            background: white;
            border-left: 6px solid var(--navy);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem; /* Added margin between cards */
        }

        .message-box {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #333;
        }

        .message-box.info {
            background-color: #e0f2f7; /* Light blue */
            border: 1px solid #a7d9ee;
        }

        .message-box.warning {
            background-color: #fff3e0; /* Light orange */
            border: 1px solid #ffe0b2;
            color: #e65100; /* Dark orange text */
        }

        .message-box i {
            font-size: 1.5rem;
        }

        .list-item {
            background-color: var(--blue-light);
            border: 1px solid var(--gray-border);
            border-radius: 6px;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .list-item h3 {
            font-size: 1.1rem;
            color: var(--navy);
        }

        .list-item p {
            font-size: 0.9rem;
            color: var(--gray-text);
        }

        .delete-btn {
            background-color: #dc3545; /* Red for delete */
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .empty-state {
            text-align: center;
            background-color: white;
            padding: 4rem 2rem;
            border-radius: 8px;
            border-left: 6px solid var(--navy);
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }

        .empty-state i {
            font-size: 3rem;
            color: #bbb;
            margin-bottom: 1rem;
        }
        
        .action-btn {
            background-color: #0ea5e9;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .action-btn:hover {
            background-color: #0ea5e9;
        }
    </style>
</head>
<body>
    <aside>
        <h2>SIMPRAK</h2>
        <a class="nav-link" href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
        <a class="nav-link" href="praktikum_saya.php"><i class="fa fa-book-open"></i> Praktikum Saya</a>
        <a class="nav-link" href="daftar_praktikum.php"><i class="fa fa-plus-circle"></i> Daftar Praktikum</a>
        <a class="nav-link" href="detail_praktikum.php"><i class="fa fa-info-circle"></i> Detail Praktikum</a>
        <a class="nav-link" href="upload_laporan.php"><i class="fa fa-upload"></i> Upload Laporan</a>
        <a class="nav-link active" href="keluar_praktikum.php"><i class="fa fa-sign-out-alt"></i> Keluar Praktikum</a>
        <a class="nav-link" href="../logout.php" style="background-color: #dc3545; margin-top: 1rem;"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main>
        <h1><?= htmlspecialchars($page_title) ?></h1>
        <p style="color: var(--gray-text); margin-bottom: 1.5rem;">Pilih praktikum yang ingin Anda tinggalkan.</p>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="message-box info">
            <i class="fas fa-check-circle"></i> <span><span style="font-weight: bold;"><?= htmlspecialchars($_SESSION['success']) ?></span></span>
        </div>
        <?php unset($_SESSION['success']); endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
        <div class="message-box warning">
            <i class="fas fa-exclamation-triangle"></i> <span><span style="font-weight: bold;"><?= htmlspecialchars($_SESSION['error']) ?></span></span>
        </div>
        <?php unset($_SESSION['error']); endif; ?>


        <div class="card">
            <div class="message-box warning" style="border-left: none; margin-bottom: 1.5rem;">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <h3 style="font-weight: bold; font-size: 1.1rem;">Peringatan Penting</h3>
                    <p style="font-size: 0.9rem;">Keluar dari praktikum adalah tindakan permanen dan akan menghapus semua data pendaftaran Anda. Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <h2 style="font-size: 1.5rem; color: var(--navy); margin-bottom: 1rem;">Praktikum yang Anda Ikuti</h2>
            
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while($praktikum = $result->fetch_assoc()): ?>
                        <div class="list-item">
                            <div>
                                <h3><?= htmlspecialchars($praktikum['nama_mk']) ?></h3>
                                <p><?= htmlspecialchars($praktikum['kode_mk']) ?></p>
                            </div>
                            <form method="post">
                                <input type="hidden" name="praktikum_id" value="<?= $praktikum['praktikum_id'] ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('Anda yakin ingin keluar dari praktikum \'<?= htmlspecialchars($praktikum['nama_mk']) ?>\'? Tindakan ini tidak dapat dibatalkan.')">
                                    <i class="fas fa-times-circle" style="margin-right: 5px;"></i>KELUAR
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" style="border: 1px dashed var(--gray-border); border-left: 6px solid var(--navy); padding: 2rem 1rem;">
                    <i class="fas fa-check-circle"></i>
                    <h3>Aman!</h3>
                    <p style="color: var(--gray-text);">Anda saat ini tidak terdaftar di praktikum mana pun.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>