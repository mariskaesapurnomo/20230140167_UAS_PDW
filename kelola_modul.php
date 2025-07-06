<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$asisten_id = $_SESSION['user_id'];
$page_title = 'Kelola Modul';

// Ambil semua praktikum yang diasuh oleh asisten ini
$praktikum_query = $conn->prepare("SELECT * FROM mata_praktikum WHERE asisten_id = ?");
$praktikum_query->bind_param("i", $asisten_id);
$praktikum_query->execute();
$praktikum_result = $praktikum_query->get_result();

$praktikum_list = [];
while ($row = $praktikum_result->fetch_assoc()) {
    $praktikum_list[$row['id']] = $row['nama_mk'];
}

$selected_praktikum_id = $_GET['praktikum_id'] ?? array_key_first($praktikum_list);

$modul_query = $conn->prepare("SELECT * FROM modul WHERE praktikum_id = ?");
$modul_query->bind_param("i", $selected_praktikum_id);
$modul_query->execute();
$modul_result = $modul_query->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?> - SIMPRAK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0b2545;
            --secondary: #0ea5e9;
            --secondary-dark: #0284c7;
            --light: #f4f6f8;
            --white: #fff;
            --border: #ddd;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--light);
            padding: 2rem;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background-color: var(--white);
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            border-radius: 10px;
        }

        h1 {
            background-color: var(--primary);
            color: white;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 6px;
            font-size: 1.5rem;
        }

        h2 {
            color: var(--primary);
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 0.5rem;
        }

        select, input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 1rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            background-color: var(--secondary);
            color: var(--white);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn:hover {
            background-color: var(--secondary-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background-color: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background-color: #eaf1f8;
            color: var(--primary);
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        td a {
            color: var(--secondary);
            text-decoration: none;
        }

        td a:hover {
            color: var(--secondary-dark);
        }

        .form-section {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kelola Modul</h1>

        <form method="get" action="">
            <label for="praktikum_id">Pilih Praktikum:</label>
            <select name="praktikum_id" id="praktikum_id" onchange="this.form.submit()">
                <?php foreach ($praktikum_list as $id => $nama): ?>
                    <option value="<?= $id ?>" <?= $id == $selected_praktikum_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($nama) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <h2>Daftar Modul</h2>
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Deskripsi</th>
                    <th>File</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($modul = $modul_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($modul['judul']) ?></td>
                        <td><?= htmlspecialchars($modul['deskripsi']) ?></td>
                        <td>
                            <?php if ($modul['file_materi']): ?>
                                <a href="<?= htmlspecialchars($modul['file_materi']) ?>" target="_blank">
                                    <i class="fa fa-file"></i> Lihat File
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_modul.php?id=<?= $modul['id'] ?>"><i class="fa fa-edit"></i> Edit</a> |
                            <a href="hapus_modul.php?id=<?= $modul['id'] ?>&praktikum_id=<?= $selected_praktikum_id ?>"
                               onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fa fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="form-section">
            <h2>Tambah Modul</h2>
            <form action="tambah_modul.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="praktikum_id" value="<?= $selected_praktikum_id ?>">

                <label for="judul">Judul:</label>
                <input type="text" id="judul" name="judul" required>

                <label for="deskripsi">Deskripsi:</label>
                <textarea id="deskripsi" name="deskripsi"></textarea>

                <label for="materi_file">File Materi (PDF/DOCX):</label>
                <input type="file" id="materi_file" name="materi_file" accept=".pdf,.docx" required>

                <button type="submit" class="btn">
                    <i class="fa fa-plus"></i> Tambah Modul
                </button>
            </form>
        </div>
    </div>
</body>
</html>
