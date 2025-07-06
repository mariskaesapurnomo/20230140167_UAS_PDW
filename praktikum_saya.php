<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = 'Praktikum Saya'; // Define page title for consistency

$sql_praktikum = "SELECT pp.praktikum_id, mp.nama_mk, mp.kode_mk
    FROM pendaftaran_praktikum pp
    JOIN mata_praktikum mp ON pp.praktikum_id = mp.id
    WHERE pp.mahasiswa_id = ?
    ORDER BY mp.nama_mk ASC";
$stmt = $conn->prepare($sql_praktikum);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$praktikum_result = $stmt->get_result();
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
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); /* Adjusted grid for better responsiveness */
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
            justify-content: space-between; /* Push content to top and button to bottom */
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
            margin-bottom: 1rem;
        }

        .modul-list {
            margin-top: 1rem;
            border-top: 1px solid var(--gray-border);
            padding-top: 1rem;
            flex-grow: 1; /* Allow modul list section to grow */
        }
        
        .modul-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px dashed #eee; /* Light dashed border for separation */
        }
        .modul-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .modul-title {
            font-weight: 500;
            color: #333;
            flex-grow: 1;
            margin-right: 10px;
        }

        .modul-actions {
            display: flex;
            align-items: center;
            flex-shrink: 0; /* Prevent actions from shrinking */
        }

        .status-badge { /* Unified status badge style */
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.8rem; /* Smaller font size for badges */
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 8px; /* Space between badge and button */
        }

        .status-badge.belum {
            background-color: #fee2e2; /* red-100 */
            color: #991b1b; /* red-800 */
        }

        .status-badge.menunggu {
            background-color: #fffac2; /* yellow-100 */
            color: #78350f; /* yellow-800 */
        }

        .status-badge.nilai {
            background-color: #d1fae5; /* green-100 */
            color: #065f46; /* green-800 */
        }

        .action-btn { /* Unified button style for actions within module item */
            background-color: #0ea5e9; /* blue-500 default */
            color: white;
            padding: 0.6rem 1.2rem; /* Slightly smaller padding */
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem; /* Smaller font size */
        }
        .action-btn i {
            margin-right: 6px; /* Smaller margin for icons */
        }
        .action-btn:hover {
            opacity: 0.9;
        }
        .action-btn.upload { background-color: #0ea5e9; } /* blue */
        .action-btn.update { background-color: #f59e0b; } /* amber */
        .action-btn.lihat { background-color: #10b981; } /* emerald */

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
        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--navy);
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            color: var(--gray-text);
            margin-bottom: 1.5rem;
        }
        .empty-state .action-btn {
            margin-top: 0; /* Remove top margin for action-btn in empty state */
        }
    </style>
</head>
<body>
    <aside>
        <h2>SIMPRAK</h2>
        <a class="nav-link" href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
        <a class="nav-link active" href="praktikum_saya.php"><i class="fa fa-book-open"></i> Praktikum Saya</a>
        <a class="nav-link" href="daftar_praktikum.php"><i class="fa fa-plus-circle"></i> Daftar Praktikum</a>
        <a class="nav-link" href="detail_praktikum.php"><i class="fa fa-info-circle"></i> Detail Praktikum</a>
        <a class="nav-link" href="upload_laporan.php"><i class="fa fa-upload"></i> Upload Laporan</a>
        <a class="nav-link" href="keluar_praktikum.php"><i class="fa fa-sign-out-alt"></i> Keluar Praktikum</a>
        <a class="nav-link" href="../logout.php" style="background-color: #dc3545; margin-top: 1rem;"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main>
        <h1><?= htmlspecialchars($page_title) ?></h1>
        <p style="color: var(--gray-text); margin-bottom: 1.5rem;">Daftar praktikum yang sedang Anda ikuti beserta status laporan modul.</p>

        <?php if ($praktikum_result->num_rows > 0): ?>
            <div class="praktikum-grid">
                <?php while ($praktikum = $praktikum_result->fetch_assoc()): ?>
                    <div class="praktikum-card">
                        <div>
                            <div class="praktikum-title"><?= htmlspecialchars($praktikum['nama_mk']) ?></div>
                            <div class="praktikum-code"><?= htmlspecialchars($praktikum['kode_mk']) ?></div>
                        </div>

                        <?php
                            $sql_modul = "SELECT id, judul_modul FROM modul WHERE praktikum_id = ?";
                            $stmt_modul = $conn->prepare($sql_modul);
                            $stmt_modul->bind_param("i", $praktikum['praktikum_id']);
                            $stmt_modul->execute();
                            $modul_result = $stmt_modul->get_result();
                        ?>

                        <?php if ($modul_result->num_rows > 0): ?>
                            <div class="modul-list">
                                <?php while ($modul = $modul_result->fetch_assoc()): ?>
                                    <div class="modul-item">
                                        <div class="modul-title"><?= htmlspecialchars($modul['judul_modul']) ?></div>
                                        <div class="modul-actions">
                                            <?php
                                                $sql_laporan = "SELECT nilai FROM laporan WHERE mahasiswa_id = ? AND modul_id = ?";
                                                $stmt_laporan = $conn->prepare($sql_laporan);
                                                $stmt_laporan->bind_param("ii", $user_id, $modul['id']);
                                                $stmt_laporan->execute();
                                                $laporan_result = $stmt_laporan->get_result();
                                                $laporan = $laporan_result->fetch_assoc();

                                                if ($laporan) {
                                                    if ($laporan['nilai'] !== null) {
                                                        echo '<span class="status-badge nilai">Nilai: ' . htmlspecialchars($laporan['nilai']) . '</span>';
                                                        echo '<a class="action-btn lihat" href="upload_laporan.php?modul_id=' . $modul['id'] . '"><i class="fas fa-eye"></i> Lihat</a>';
                                                    } else {
                                                        echo '<span class="status-badge menunggu">Menunggu Nilai</span>';
                                                        echo '<a class="action-btn update" href="upload_laporan.php?modul_id=' . $modul['id'] . '"><i class="fas fa-edit"></i> Update</a>';
                                                    }
                                                } else {
                                                    echo '<span class="status-badge belum">Belum Upload</span>';
                                                    echo '<a class="action-btn upload" href="upload_laporan.php?modul_id=' . $modul['id'] . '"><i class="fas fa-upload"></i> Upload</a>';
                                                }
                                            ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="modul-list">
                                <div class="modul-item" style="justify-content: center; color: var(--gray-text);">Tidak ada modul untuk praktikum ini.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-folder-open"></i>
                <h3>Belum Ada Praktikum</h3>
                <p>Anda belum mengikuti praktikum mana pun saat ini.</p>
                <a href="daftar_praktikum.php" class="action-btn upload"><i class="fas fa-plus-circle"></i> Daftar Praktikum Sekarang</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>