<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Laporan Masuk';

// Ambil daftar praktikum
$praktikum = [];
$res = $conn->query("SELECT id, nama_mk FROM mata_praktikum ORDER BY nama_mk ASC");
while ($row = $res->fetch_assoc()) $praktikum[] = $row;
$praktikum_id = isset($_GET['praktikum_id']) ? (int)$_GET['praktikum_id'] : ($praktikum[0]['id'] ?? 0);

// Ambil daftar modul untuk praktikum terpilih
$modul = [];
if ($praktikum_id) {
    $res = $conn->query("SELECT id, judul_modul FROM modul WHERE praktikum_id=$praktikum_id ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) $modul[] = $row;
}
$modul_id = isset($_GET['modul_id']) ? (int)$_GET['modul_id'] : 0;

// Proses penilaian
if (isset($_POST['aksi']) && $_POST['aksi']==='nilai') {
    $id = (int)$_POST['id'];
    $nilai = (int)$_POST['nilai'];
    $feedback = trim($_POST['feedback']);
    $conn->query("UPDATE laporan SET nilai=$nilai, feedback='".$conn->real_escape_string($feedback)."' WHERE id=$id");
    header("Location: laporan_masuk.php?praktikum_id=$praktikum_id&modul_id=$modul_id"); exit();
}

// Ambil daftar laporan
$sql = "SELECT l.*, m.judul_modul, mp.nama_mk, u.nama as nama_mhs, u.email FROM laporan l JOIN modul m ON l.modul_id=m.id JOIN users u ON l.mahasiswa_id=u.id JOIN mata_praktikum mp ON m.praktikum_id=mp.id";
if ($praktikum_id) $sql .= " WHERE mp.id=$praktikum_id";
if ($modul_id) $sql .= ($praktikum_id ? " AND" : " WHERE") . " m.id=$modul_id";
$sql .= " ORDER BY l.tgl_kumpul DESC";
$laporan = [];
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) $laporan[] = $row;

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
            --navy: #0b2545; /* Main dark color for sidebar, titles */
            --light-bg: #f8f9fa; /* Page background */
            --white: #ffffff;
            --text-dark: #343a40; /* Dark charcoal for general text/titles */
            --text-medium: #6c757d; /* Medium gray for descriptions */
            --border-color: #dee2e6; /* Light gray for general borders */
            --table-header-bg: #e9ecef; /* Slightly darker light gray for table header */
            --primary-accent: #0d6efd; /* Vibrant blue for links/buttons */
            --primary-accent-hover: #0a58ca; /* Darker primary accent */
            --danger-color: #dc3545; /* Red for delete */
            --danger-color-hover: #bb2d3b; /* Darker red */
            --cancel-bg: #6c757d; /* Gray for cancel button */
            --cancel-hover: #5c636a; /* Darker gray for cancel hover */
            --success-badge-bg: #d1e7dd; /* Light green for success badge */
            --success-badge-text: #0f5132; /* Dark green for success badge text */
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            background-color: var(--light-bg);
            color: var(--text-dark);
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
            background-color: #123c69; /* Darker navy for hover/active */
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
            margin-bottom: 1.5rem;
        }

        .section-box {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Form styling */
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.9rem;
            color: var(--text-dark);
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: var(--white);
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
        }

        .flex-container {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* Table styles */
        .table-header-section {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background-color: var(--table-header-bg);
        }

        .data-table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-medium);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--border-color);
        }

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: #fcfcfc;
        }

        .data-table td {
            padding: 1rem 1.5rem;
            font-size: 0.9rem;
            color: var(--text-dark);
            vertical-align: top;
        }
        
        .action-link {
            color: var(--primary-accent);
            text-decoration: none;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
        }
        .action-link:hover {
            color: var(--primary-accent-hover);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem; /* px-2.5 py-0.5 */
            border-radius: 9999px; /* rounded-full */
            font-size: 0.75rem; /* text-xs */
            font-weight: 500; /* font-medium */
        }
        .badge-success {
            background-color: var(--success-badge-bg);
            color: var(--success-badge-text);
        }

        .text-truncate {
            max-width: 200px; /* Equivalent to max-w-xs, adjust as needed */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .text-muted {
            color: var(--text-medium);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--text-medium);
        }
        .empty-state .icon {
            font-size: 3.5rem;
            color: #bbb; /* Adjusted from gray-400 */
            margin-bottom: 1rem;
        }
        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            font-size: 0.9rem;
            color: var(--text-medium);
        }

        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 450px; /* Equivalent to w-96 */
            position: relative;
        }

        .modal-title {
            font-size: 1.25rem; /* text-lg font-medium */
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-actions {
            display: flex;
            gap: 0.75rem; /* gap-2 */
            margin-top: 1.5rem; /* mt-6 */
            justify-content: flex-end; /* Added for consistency with other modals */
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1rem; /* px-4 py-2 */
            border-radius: 6px; /* rounded-md */
            font-weight: 600; /* font-medium */
            cursor: pointer;
            transition: background-color 0.2s, opacity 0.2s;
            text-decoration: none;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary-accent);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--primary-accent-hover);
        }

        .btn-cancel {
            background-color: var(--cancel-bg);
            color: white;
        }
        .btn-cancel:hover {
            background-color: var(--cancel-hover);
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <aside>
        <h2>SIMPRAK</h2>
        <a class="nav-link" href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
        <a class="nav-link" href="kelola_praktikum.php"><i class="fa fa-flask"></i> Kelola Praktikum</a>
        <a class="nav-link" href="kelola_modul.php"><i class="fa fa-book"></i> Kelola Modul</a>
        <a class="nav-link active" href="laporan_masuk.php"><i class="fa fa-file-alt"></i> Laporan Masuk</a>
        <a class="nav-link" href="kelola_pengguna.php"><i class="fa fa-users"></i> Kelola Pengguna</a> <a class="nav-link" href="../logout.php" style="background-color: var(--danger-color); margin-top: 1rem;"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main>
        <h1>Laporan Masuk</h1>

        <div class="section-box">
            <form method="get" class="flex-container">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Praktikum:</label>
                    <select name="praktikum_id" onchange="this.form.submit()" class="form-select" style="width: auto;">
                        <?php foreach ($praktikum as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $praktikum_id==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nama_mk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Modul:</label>
                    <select name="modul_id" onchange="this.form.submit()" class="form-select" style="width: auto;">
                        <option value="0">Semua</option>
                        <?php foreach ($modul as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $modul_id==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['judul_modul']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <div class="section-box" style="padding: 0; overflow: hidden;">
            <div class="table-header-section">
                <h2 class="section-title">Daftar Laporan</h2>
            </div>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Praktikum</th>
                            <th>Modul</th>
                            <th>File</th>
                            <th style="text-align: center;">Nilai</th>
                            <th>Feedback</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan as $l): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 500;"><?= htmlspecialchars($l['nama_mhs']) ?></div>
                                <div class="text-sm text-muted"><?= htmlspecialchars($l['email']) ?></div>
                            </td>
                            <td>
                                <div class="text-sm"><?= htmlspecialchars($l['nama_mk']) ?></div>
                            </td>
                            <td>
                                <div class="text-sm"><?= htmlspecialchars($l['judul_modul']) ?></div>
                            </td>
                            <td>
                                <a href="../uploads/laporan/<?= urlencode($l['file_laporan']) ?>" target="_blank" class="action-link">
                                    <i class="fas fa-download" style="margin-right: 0.25rem;"></i>Download
                                </a>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($l['nilai'] !== null): ?>
                                    <span class="badge badge-success">
                                        <?= $l['nilai'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-sm text-truncate">
                                    <?= htmlspecialchars($l['feedback'] ?: '-') ?>
                                </div>
                            </td>
                            <td>
                                <button onclick="nilaiLaporan(<?= $l['id'] ?>, <?= (int)$l['nilai'] ?>, '<?= htmlspecialchars(addslashes($l['feedback'])) ?>')" class="action-link">
                                    <i class="fas fa-star" style="margin-right: 0.25rem;"></i>Nilai
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($laporan)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="icon">📄</div>
                                    <h3>Belum ada laporan</h3>
                                    <p>Laporan yang masuk akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="nilaiModal" class="modal-overlay hidden">
            <form method="post" class="modal-content">
                <input type="hidden" name="aksi" value="nilai">
                <input type="hidden" name="id" id="nilai_id">
                
                <h3 class="modal-title">Nilai Laporan</h3>
                
                <div class="form-group">
                    <label for="nilai_nilai" class="form-label">Nilai (0-100)</label>
                    <input type="number" name="nilai" id="nilai_nilai" min="0" max="100" required 
                            class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="nilai_feedback" class="form-label">Feedback</label>
                    <textarea name="feedback" id="nilai_feedback" rows="3"
                                  class="form-textarea"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeNilai()" class="btn btn-cancel">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        <script>
        function nilaiLaporan(id, nilai, feedback) {
            document.getElementById('nilai_id').value = id;
            document.getElementById('nilai_nilai').value = nilai || '';
            document.getElementById('nilai_feedback').value = feedback || '';
            document.getElementById('nilaiModal').classList.remove('hidden');
        }

        function closeNilai() {
            document.getElementById('nilaiModal').classList.add('hidden');
        }
        </script>
    </main>
</body>
</html>