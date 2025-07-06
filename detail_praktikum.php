<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = 'Detail Praktikum';

// Ambil detail praktikum yang diikuti mahasiswa
$sql = "SELECT mp.*, u.nama as nama_asisten, COUNT(m.id) as jumlah_modul
        FROM mata_praktikum mp 
        LEFT JOIN users u ON mp.asisten_id = u.id 
        LEFT JOIN modul m ON mp.id = m.praktikum_id
        INNER JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id
        WHERE pp.mahasiswa_id = ?
        GROUP BY mp.id
        ORDER BY mp.nama_mk ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
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

        .praktikum-card {
            background: white;
            border-left: 6px solid var(--navy);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem; /* Added margin between cards */
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-border);
            margin-bottom: 1rem;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background-color: #0ea5e9; /* blue-500 */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--navy);
        }

        .card-subtitle {
            font-size: 1rem;
            color: var(--gray-text);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item p:first-child {
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--gray-text);
            margin-bottom: 0.25rem;
        }

        .info-item p:last-child {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .description-section {
            margin-bottom: 1.5rem;
        }

        .description-section h4 {
            font-size: 1rem;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--gray-text);
            margin-bottom: 0.5rem;
        }

        .modul-section {
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-border);
        }

        .modul-section h4 {
            font-size: 1.25rem;
            color: var(--navy);
            margin-bottom: 1rem;
        }

        .modul-item {
            padding-bottom: 0.75rem;
            margin-bottom: 0.75rem;
            border-bottom: 1px dashed #eee;
        }

        .modul-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .modul-item p:first-child {
            font-weight: bold;
            color: #333;
        }

        .modul-item p:last-child {
            font-size: 0.9rem;
            color: var(--gray-text);
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
        <a class="nav-link active" href="detail_praktikum.php"><i class="fa fa-info-circle"></i> Detail Praktikum</a>
        <a class="nav-link" href="upload_laporan.php"><i class="fa fa-upload"></i> Upload Laporan</a>
        <a class="nav-link" href="keluar_praktikum.php"><i class="fa fa-sign-out-alt"></i> Keluar Praktikum</a>
        <a class="nav-link" href="../logout.php" style="background-color: #dc3545; margin-top: 1rem;"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main>
        <h1><?= htmlspecialchars($page_title) ?></h1>
        <p style="color: var(--gray-text); margin-bottom: 1.5rem;">Informasi lengkap mengenai praktikum yang Anda ikuti.</p>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="praktikum-grid">
                <?php while($praktikum = $result->fetch_assoc()): ?>
                    <div class="praktikum-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-flask"></i>
                            </div>
                            <div>
                                <div class="card-title"><?= htmlspecialchars($praktikum['nama_mk']) ?></div>
                                <div class="card-subtitle"><?= htmlspecialchars($praktikum['kode_mk']) ?></div>
                            </div>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <p>Jumlah Modul</p>
                                <p><?= $praktikum['jumlah_modul'] ?> Modul</p>
                            </div>
                            <div class="info-item">
                                <p>Asisten Dosen</p>
                                <p><?= htmlspecialchars($praktikum['nama_asisten'] ?: 'N/A') ?></p>
                            </div>
                            <div class="info-item">
                                <p>Semester</p>
                                <p><?= htmlspecialchars($praktikum['semester'] ?: '-') ?></p>
                            </div>
                        </div>

                         <div class="description-section">
                            <h4>Deskripsi</h4>
                            <p style="color: var(--gray-text);"><?= htmlspecialchars($praktikum['deskripsi'] ?: 'Deskripsi belum tersedia.') ?></p>
                        </div>

                        <?php
                        $sql_modul = "SELECT * FROM modul WHERE praktikum_id = ? ORDER BY id ASC";
                        $stmt_modul = $conn->prepare($sql_modul);
                        $stmt_modul->bind_param("i", $praktikum['id']);
                        $stmt_modul->execute();
                        $modul_result = $stmt_modul->get_result();
                        if ($modul_result->num_rows > 0):
                        ?>
                            <div class="modul-section">
                                <h4>Daftar Modul</h4>
                                <div class="modul-list">
                                    <?php while($modul = $modul_result->fetch_assoc()): ?>
                                        <div class="modul-item">
                                            <p><?= htmlspecialchars($modul['judul_modul']) ?></p>
                                            <p><?= htmlspecialchars($modul['deskripsi_modul'] ?: 'Tidak ada deskripsi.') ?></p>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>Anda Belum Mengikuti Praktikum</h3>
                <p>Daftar praktikum terlebih dahulu untuk melihat detail lengkapnya di sini.</p>
                <div style="margin-top: 1.5rem;">
                    <a href="daftar_praktikum.php" class="action-btn">
                        Daftar Praktikum Sekarang
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>