<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Dashboard Asisten'; // Perbarui judul halaman

// Query untuk mengambil statistik asisten
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM modul m JOIN mata_praktikum mp ON m.praktikum_id = mp.id WHERE mp.asisten_id = ?) as total_modul,
    (SELECT COUNT(*) FROM laporan l JOIN modul m ON l.modul_id = m.id JOIN mata_praktikum mp ON m.praktikum_id = mp.id WHERE mp.asisten_id = ?) as total_laporan,
    (SELECT COUNT(*) FROM laporan l JOIN modul m ON l.modul_id = m.id JOIN mata_praktikum mp ON m.praktikum_id = mp.id WHERE mp.asisten_id = ? AND l.nilai IS NULL) as menunggu_nilai";
$stmt = $conn->prepare($stats_sql);
$stmt->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
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
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }

        aside h2 {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .nav-link {
            display: flex;
            align-items: center;
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
            width: 20px;
            text-align: center;
        }

        main {
            flex: 1;
            padding: 2rem;
            margin-left: 240px;
        }

        h1 {
            font-size: 2rem;
            color: var(--navy);
            margin-bottom: 1rem;
        }

        .description-text {
            color: var(--gray-text);
            margin-bottom: 1.5rem;
        }

        .info-card {
            background: var(--white);
            border-left: 6px solid var(--navy);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .stat-item {
            background: var(--white);
            border-left: 6px solid var(--navy);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--navy);
            background-color: var(--blue-light);
        }

        .stat-item .label {
            font-size: 0.9rem;
            color: var(--gray-text);
            margin-bottom: 0.25rem;
        }

        .stat-item .value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--navy);
            line-height: 1;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px dashed #eee;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--blue-light);
            color: var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        .activity-info p {
            margin: 0;
        }
        .activity-info .main-text {
            color: #333;
            font-size: 0.95rem;
        }
        .activity-info .time-text {
            font-size: 0.8rem;
            color: var(--gray-text);
        }
    </style>
</head>
<body>
    <aside>
        <h2>SIMPRAK</h2>
        <a class="nav-link active" href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
        <a class="nav-link" href="kelola_praktikum.php"><i class="fa fa-flask"></i> Kelola Praktikum</a>
        <a class="nav-link" href="kelola_modul.php"><i class="fa fa-book"></i> Kelola Modul</a>
        <a class="nav-link" href="laporan_masuk.php"><i class="fa fa-file-alt"></i> Laporan Masuk</a>
        <a class="nav-link" href="kelola_pengguna.php"><i class="fa fa-users"></i> Kelola Pengguna</a>
        <a class="nav-link" href="../logout.php" style="background-color: #dc3545; margin-top: 1rem;"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main>
        <h1>Selamat Datang, Asisten!</h1>
        <p class="description-text">Ringkasan aktivitas dan statistik terbaru.</p>

        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <p class="label">Total Modul Diajarkan</p>
                    <p class="value"><?php echo $stats['total_modul']; ?></p>
                </div>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-inbox"></i>
                </div>
                <div>
                    <p class="label">Total Laporan Masuk</p>
                    <p class="value"><?php echo $stats['total_laporan']; ?></p>
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p class="label">Laporan Belum Dinilai</p>
                    <p class="value"><?php echo $stats['menunggu_nilai']; ?></p>
                </div>
            </div>
        </div>

        <div class="info-card" style="margin-top: 2rem;">
            <h3 style="font-size: 1.25rem; color: var(--navy); margin-bottom: 1rem; border-bottom: 1px solid var(--gray-border); padding-bottom: 0.75rem;">Aktivitas Laporan Terbaru</h3>
            <div class="space-y-4">
                <div class="activity-item">
                    <div class="activity-avatar">
                        <span>S</span>
                    </div>
                    <div class="activity-info">
                        <p class="main-text"><strong>Sakura</strong> mengumpulkan laporan untuk <strong>Modul 3</strong></p>
                        <p class="time-text">10 menit lalu</p>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-avatar">
                        <span>R</span>
                    </div>
                    <div class="activity-info">
                        <p class="main-text"><strong>Rose</strong> mengumpulkan laporan untuk <strong>Modul 2</strong></p>
                        <p class="time-text">45 menit lalu</p>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-avatar">
                        <span>B</span>
                    </div>
                    <div class="activity-info">
                        <p class="main-text"><strong>Bintang</strong> mengumpulkan laporan untuk <strong>Modul 1</strong></p>
                        <p class="time-text">1 jam lalu</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>