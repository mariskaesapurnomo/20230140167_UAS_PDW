<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = 'Daftar Praktikum';

$sql = "SELECT mp.*, u.nama as nama_asisten,
        (SELECT COUNT(*) FROM pendaftaran_praktikum pp WHERE pp.praktikum_id = mp.id) as jumlah_mahasiswa,
        (SELECT COUNT(*) FROM modul m WHERE m.praktikum_id = mp.id) as jumlah_modul
        FROM mata_praktikum mp 
        LEFT JOIN users u ON mp.asisten_id = u.id 
        ORDER BY mp.nama_mk ASC";
$result = $conn->query($sql);

$sql_enrolled = "SELECT praktikum_id FROM pendaftaran_praktikum WHERE mahasiswa_id = ?";
$stmt_enrolled = $conn->prepare($sql_enrolled);
$stmt_enrolled->bind_param("i", $user_id);
$stmt_enrolled->execute();
$enrolled_result = $stmt_enrolled->get_result();
$enrolled_praktikum = [];
while($row = $enrolled_result->fetch_assoc()) {
    $enrolled_praktikum[] = $row['praktikum_id'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['praktikum_id'])) {
    $praktikum_id_to_enroll = $_POST['praktikum_id'];
    
    $check_sql = "SELECT id FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $praktikum_id_to_enroll);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        $enroll_sql = "INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)";
        $enroll_stmt = $conn->prepare($enroll_sql);
        $enroll_stmt->bind_param("ii", $user_id, $praktikum_id_to_enroll);
        
        if ($enroll_stmt->execute()) {
            echo "<script>alert('Berhasil mendaftar praktikum!'); window.location.href='daftar_praktikum.php';</script>";
        } else {
            echo "<script>alert('Gagal mendaftar praktikum. Silakan coba lagi.');</script>";
        }
    } else {
        echo "<script>alert('Anda sudah terdaftar di praktikum ini.');</script>";
    }
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

        .praktikum-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Adjusted grid for better responsiveness */
            gap: 1.5rem;
        }

        .praktikum-card {
            background: white;
            border-left: 6px solid var(--navy);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            display: flex; /* Added flex for internal layout */
            flex-direction: column; /* Stack items vertically */
            justify-content: space-between; /* Push button to bottom */
        }

        .praktikum-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--navy);
            margin-bottom: 0.25rem;
        }

        .praktikum-code {
            font-size: 0.9rem;
            color: var(--gray-text);
            margin-bottom: 0.5rem;
        }

        .praktikum-detail {
            font-size: 0.9rem;
            color: #333;
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--gray-border);
            flex-grow: 1; /* Allow details section to grow */
        }

        .asisten {
            margin-top: 6px;
            font-weight: 500;
        }

        .action-btn { /* Unified button style */
            background-color: #0ea5e9; /* blue-500 */
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
            display: inline-flex; /* Use flex for icon alignment */
            align-items: center;
            justify-content: center;
            margin-top: 1.5rem; /* Add margin to separate from details */
        }
        .action-btn i {
            margin-right: 8px;
        }
        .action-btn:hover {
            background-color: #0284c7; /* darker blue */
        }
        .status-badge { /* Unified status badge style */
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: bold;
            margin-top: 1.5rem;
        }
        .status-badge i {
            margin-right: 8px;
        }

        .status-badge.terdaftar {
            background-color: #d1fae5; /* green-100 */
            color: #065f46; /* green-800 */
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
    </style>
</head>
<body>
    <aside>
        <h2>SIMPRAK</h2>
        <a class="nav-link" href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
        <a class="nav-link" href="praktikum_saya.php"><i class="fa fa-book-open"></i> Praktikum Saya</a>
        <a class="nav-link active" href="daftar_praktikum.php"><i class="fa fa-plus-circle"></i> Daftar Praktikum</a>
        <a class="nav-link" href="detail_praktikum.php"><i class="fa fa-info-circle"></i> Detail Praktikum</a>
        <a class="nav-link" href="upload_laporan.php"><i class="fa fa-upload"></i> Upload Laporan</a>
        <a class="nav-link" href="keluar_praktikum.php"><i class="fa fa-sign-out-alt"></i> Keluar Praktikum</a>
        <a class="nav-link" href="../logout.php" style="background-color: #dc3545; margin-top: 1rem;"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main>
        <h1><?= htmlspecialchars($page_title) ?></h1>
        <p style="color: var(--gray-text); margin-bottom: 1.5rem;">Pilih praktikum yang tersedia dan daftar jika Anda belum terdaftar.</p>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="praktikum-grid">
                <?php while($praktikum = $result->fetch_assoc()): ?>
                    <div class="praktikum-card">
                        <div>
                            <div class="praktikum-title"><?= htmlspecialchars($praktikum['nama_mk']) ?></div>
                            <div class="praktikum-code">Kode: <?= htmlspecialchars($praktikum['kode_mk']) ?></div>
                            <div class="praktikum-detail">
                                <div>Jumlah Modul: <?= $praktikum['jumlah_modul'] ?></div>
                                <div class="asisten">Asisten: <?= htmlspecialchars($praktikum['nama_asisten'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <?php if (in_array($praktikum['id'], $enrolled_praktikum)): ?>
                            <div class="status-badge terdaftar"><i class="fa fa-check-circle"></i> Terdaftar</div>
                        <?php else: ?>
                            <form method="post" action="daftar_praktikum.php">
                                <input type="hidden" name="praktikum_id" value="<?= $praktikum['id'] ?>">
                                <button type="submit" class="action-btn"><i class="fa fa-plus-circle"></i> Daftar Sekarang</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>Tidak Ada Praktikum Tersedia</h3>
                <p>Belum ada praktikum yang dibuka untuk pendaftaran saat ini.</p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>