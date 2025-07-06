<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = 'Upload Laporan';

// Ambil semua modul dari praktikum yang diikuti mahasiswa
$sql = "SELECT m.id, m.judul_modul, m.deskripsi_modul, mp.nama_mk, mp.kode_mk,
        (SELECT COUNT(*) FROM laporan l WHERE l.modul_id = m.id AND l.mahasiswa_id = ?) as sudah_upload,
        (SELECT l.nilai FROM laporan l WHERE l.modul_id = m.id AND l.mahasiswa_id = ? LIMIT 1) as nilai
        FROM modul m
        JOIN mata_praktikum mp ON m.praktikum_id = mp.id
        JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id
        WHERE pp.mahasiswa_id = ?
        ORDER BY mp.nama_mk ASC, m.id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$modul_list_result = $stmt->get_result();
$modul_options = $modul_list_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modul_id']) && isset($_FILES['laporan'])) {
    $modul_id = $_POST['modul_id'];
    $file = $_FILES['laporan'];
    
    // Validasi file
    $allowed_types = ['pdf', 'doc', 'docx'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        echo "<script>alert('Format file tidak didukung. Gunakan PDF, DOC, atau DOCX.');</script>";
    } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        echo "<script>alert('Ukuran file terlalu besar. Maksimal 10MB.');</script>";
    } else {
        // Generate unique filename
        $filename = time() . '_' . $user_id . '_' . $modul_id . '.' . $file_extension;
        $upload_path = '../uploads/laporan/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Check if already uploaded
            $check_sql = "SELECT id FROM laporan WHERE mahasiswa_id = ? AND modul_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $user_id, $modul_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Update existing laporan
                $update_sql = "UPDATE laporan SET file_laporan = ?, tanggal_upload = NOW(), nilai = NULL WHERE mahasiswa_id = ? AND modul_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sii", $filename, $user_id, $modul_id);
                
                if ($update_stmt->execute()) {
                    echo "<script>alert('Laporan berhasil diperbarui!'); window.location.href='upload_laporan.php';</script>";
                } else {
                    echo "<script>alert('Gagal memperbarui laporan. Silakan coba lagi.');</script>";
                }
            } else {
                // Insert new laporan
                $insert_sql = "INSERT INTO laporan (mahasiswa_id, modul_id, file_laporan, tanggal_upload) VALUES (?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iis", $user_id, $modul_id, $filename);
                
                if ($insert_stmt->execute()) {
                    echo "<script>alert('Laporan berhasil diupload!'); window.location.href='upload_laporan.php';</script>";
                } else {
                    echo "<script>alert('Gagal upload laporan. Silakan coba lagi.');</script>";
                }
            }
        } else {
            echo "<script>alert('Gagal upload file. Silakan coba lagi.');</script>";
        }
    }
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

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        select, input[type="file"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-border);
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .upload-dropzone {
            border: 2px dashed var(--gray-border);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.2s ease;
            margin-bottom: 1rem;
            color: var(--gray-text);
        }

        .upload-dropzone:hover {
            background-color: var(--blue-light);
        }

        .upload-dropzone i {
            font-size: 3rem;
            color: #bbb;
            margin-bottom: 1rem;
        }

        .upload-dropzone p {
            margin-bottom: 0.5rem;
        }

        .upload-dropzone label {
            display: inline-block;
            background-color: #0ea5e9; /* blue-500 equivalent */
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .upload-dropzone label:hover {
            background-color: #0ea5e9; /* Slightly darker blue on hover */
        }

        .file-info {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #333;
            font-weight: bold;
        }

        .submit-btn {
            background-color: #065f46; /* green-700 equivalent */
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.2s;
            float: right; /* Align to right */
        }

        .submit-btn:hover {
            background-color: #047857; /* Slightly darker green on hover */
        }

        .submit-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
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

        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pill.belum {
            background: #ffe0e0;
            color: #b91c1c;
        }

        .status-pill.menunggu {
            background: #fffacc;
            color: #a16207;
        }

        .status-pill.nilai {
            background: #d1fae5;
            color: #065f46;
        }

        .detail-info {
            background-color: var(--blue-light);
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .detail-info h3 {
            font-size: 1.1rem;
            color: var(--navy);
        }
        .detail-info p {
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
        <a class="nav-link" href="detail_praktikum.php"><i class="fa fa-info-circle"></i> Detail Praktikum</a>
        <a class="nav-link active" href="upload_laporan.php"><i class="fa fa-upload"></i> Upload Laporan</a>
        <a class="nav-link" href="keluar_praktikum.php"><i class="fa fa-sign-out-alt"></i> Keluar Praktikum</a>
        <a class="nav-link" href="../logout.php" style="background-color: #dc3545; margin-top: 1rem;"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main>
        <h1><?= htmlspecialchars($page_title) ?></h1>
        <p style="color: var(--gray-text); margin-bottom: 1.5rem;">Pilih modul dan upload file laporan praktikum Anda.</p>

        <?php if (count($modul_options) > 0): ?>
            <div class="card">
                <label for="modul-select">Pilih modul yang akan diupload laporannya:</label>
                <select id="modul-select" onchange="showModulInfo()">
                    <option value="">-- Silakan Pilih Modul --</option>
                    <?php 
                    $current_praktikum = '';
                    foreach ($modul_options as $row) {
                        if ($current_praktikum != $row['nama_mk']) {
                            if ($current_praktikum != '') echo '</optgroup>';
                            echo '<optgroup label="' . htmlspecialchars($row['nama_mk']) . '">';
                            $current_praktikum = $row['nama_mk'];
                        }
                        $status_text = '';
                        if ($row['sudah_upload'] > 0) {
                            $status_text = ' - ' . ($row['nilai'] !== null ? 'Nilai: ' . $row['nilai'] : 'Menunggu Penilaian');
                        }
                        echo '<option value="' . $row['id'] . '" ' . 
                             'data-title="' . htmlspecialchars($row['judul_modul']) . '" ' .
                             'data-praktikum="' . htmlspecialchars($row['nama_mk']) . '" ' .
                             'data-description="' . htmlspecialchars($row['deskripsi_modul'] ?: 'Deskripsi belum tersedia') . '" ' .
                             'data-uploaded="' . $row['sudah_upload'] . '" ' .
                             'data-nilai="' . $row['nilai'] . '">' .
                             htmlspecialchars($row['judul_modul']) . $status_text .
                             '</option>';
                    }
                    if ($current_praktikum != '') echo '</optgroup>';
                    ?>
                </select>
            </div>
            
            <div id="upload-section" class="card" style="display: none;">
                <h2 style="font-size: 1.5rem; color: var(--navy); margin-bottom: 1rem;">Detail & Upload</h2>
                <div class="detail-info">
                    <div>
                        <h3 id="modul-title"></h3>
                        <p id="modul-praktikum-title"></p>
                    </div>
                    <div id="modul-status"></div>
                </div>
                
                <form method="post" enctype="multipart/form-data" id="laporan-form">
                    <input type="hidden" name="modul_id" id="selected-modul-id">
                    <div class="upload-dropzone" id="dropzone">
                        <input type="file" name="laporan" id="file-input" style="display: none;" accept=".pdf,.doc,.docx" onchange="updateFileName(this)">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p style="font-weight: bold;">Seret & lepas file, atau klik tombol di bawah</p>
                        <p style="font-size: 0.9rem; margin-bottom: 1rem;">Format: PDF, DOC, DOCX (Maks. 10MB)</p>
                        <label for="file-input">Pilih File</label>
                        <p id="selected-file-info" class="file-info" style="display: none;"></p>
                    </div>
                    <div style="overflow: hidden;">
                        <button type="submit" class="submit-btn" id="submit-btn" disabled>
                            <i class="fas fa-check-circle" style="margin-right: 5px;"></i> UPLOAD SEKARANG
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>Anda Belum Mengikuti Praktikum</h3>
                <p>Daftar untuk praktikum terlebih dahulu untuk bisa mengupload laporan.</p>
                <div style="margin-top: 1.5rem;">
                    <a href="daftar_praktikum.php" class="action-btn">
                        Daftar Praktikum Sekarang
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // --- JAVASCRIPT ANDA, TIDAK DIUBAH SAMA SEKALI ---
        function showModulInfo() {
            const select = document.getElementById('modul-select');
            const uploadSection = document.getElementById('upload-section');
            const selectedOption = select.options[select.selectedIndex];
            
            if (!select.value) {
                uploadSection.style.display = 'none';
                return;
            }

            const title = selectedOption.dataset.title;
            const praktikum = selectedOption.dataset.praktikum;
            const uploaded = selectedOption.dataset.uploaded;
            const nilai = selectedOption.dataset.nilai;

            document.getElementById('modul-title').textContent = title;
            document.getElementById('modul-praktikum-title').textContent = praktikum;
            document.getElementById('selected-modul-id').value = select.value;
            
            let statusClass = 'status-pill ';
            let statusText = '';
            if (uploaded === '1') {
                if (nilai) {
                    statusClass += 'nilai';
                    statusText = `Nilai: ${nilai}`;
                } else {
                    statusClass += 'menunggu';
                    statusText = 'Menunggu Penilaian';
                }
            } else {
                statusClass += 'belum';
                statusText = 'Belum Upload';
            }
            document.getElementById('modul-status').innerHTML = `<span class="${statusClass}">${statusText}</span>`;
            
            document.getElementById('laporan-form').reset();
            updateFileName(document.getElementById('file-input'));
            uploadSection.style.display = 'block';
        }
        
        function updateFileName(input) {
            const selectedFileInfo = document.getElementById('selected-file-info');
            const submitBtn = document.getElementById('submit-btn');
            
            if (input.files.length > 0) {
                selectedFileInfo.innerHTML = `<i class="fas fa-file-alt"></i> ${input.files[0].name}`;
                selectedFileInfo.style.display = 'block';
                submitBtn.disabled = false;
            } else {
                selectedFileInfo.style.display = 'none';
                submitBtn.disabled = true;
            }
        }
        
        const dropzone = document.getElementById('dropzone');
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('hover'); // Add a class for hover state
        });
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('hover');
        });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('hover');
            const fileInput = document.getElementById('file-input');
            fileInput.files = e.dataTransfer.files;
            updateFileName(fileInput);
        });
    </script>
</body>
</html>