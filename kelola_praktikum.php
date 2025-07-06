<?php
// --- FUNGSI PHP ANDA, TIDAK DIUBAH SAMA SEKALI ---
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan role-nya asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Kelola Mata Praktikum';

// Query untuk mengambil semua mata praktikum
$sql = "SELECT mp.*, u.nama as nama_asisten,
        (SELECT COUNT(*) FROM pendaftaran_praktikum pp WHERE pp.praktikum_id = mp.id) as jumlah_mahasiswa,
        (SELECT COUNT(*) FROM modul m WHERE m.praktikum_id = mp.id) as jumlah_modul
        FROM mata_praktikum mp 
        LEFT JOIN users u ON mp.asisten_id = u.id 
        ORDER BY mp.nama_mk ASC";
$result = $conn->query($sql);

// Query untuk mengambil semua asisten untuk dropdown
$asisten_sql = "SELECT id, nama FROM users WHERE role = 'asisten' ORDER BY nama ASC";
$asisten_result = $conn->query($asisten_sql);
$asisten_list = [];
if ($asisten_result->num_rows > 0) {
    while($row = $asisten_result->fetch_assoc()) {
        $asisten_list[] = $row;
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
            --light-bg: #f4f6f8;
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

        /* General button styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.25rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, opacity 0.2s;
            text-decoration: none;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: #0ea5e9; /* A shade of blue */
            color: white;
        }
        .btn-primary:hover {
            background-color: #0284c7; /* Darker blue */
        }

        .btn-secondary {
            background-color: #64748b; /* A shade of gray */
            color: white;
        }
        .btn-secondary:hover {
            background-color: #475569; /* Darker gray */
        }

        /* Alert styles */
        .alert {
            padding: 0.75rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #d1fae5; /* green-100 */
            border-color: #34d399; /* green-400 */
            color: #065f46; /* green-700 */
        }

        .alert-error {
            background-color: #fee2e2; /* red-100 */
            border-color: #ef4444; /* red-400 */
            color: #b91c1c; /* red-700 */
        }

        /* Table styles */
        .table-container {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background-color: var(--blue-light); /* Light blue background for header */
        }

        .data-table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--navy); /* Darker text for headers */
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--gray-border);
        }

        .data-table tbody tr {
            border-bottom: 1px solid #eee;
        }

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: #fcfcfc; /* Subtle hover effect */
        }

        .data-table td {
            padding: 1rem 1.5rem;
            font-size: 0.9rem;
            color: #333; /* Default text color */
            vertical-align: top;
        }
        
        .code-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px; /* full rounded */
            font-size: 0.75rem;
            font-weight: 500;
            background-color: var(--blue-light);
            color: var(--navy);
        }

        .table-action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .table-action-buttons .action-link {
            color: #0ea5e9; /* Blue for edit */
            text-decoration: none;
            transition: color 0.2s;
            cursor: pointer; /* Add cursor pointer for buttons */
        }
        .table-action-buttons .action-link:hover {
            color: #0284c7;
        }
        .table-action-buttons .action-link.delete {
            color: #ef4444; /* Red for delete */
        }
        .table-action-buttons .action-link.delete:hover {
            color: #b91c1c;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--gray-text);
        }
        .empty-state .icon {
            font-size: 3.5rem;
            color: #bbb;
            margin-bottom: 1rem;
        }
        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            font-size: 0.9rem;
            color: var(--gray-text);
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
            max-width: 450px;
            position: relative;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--gray-border);
            padding-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-border);
            border-radius: 6px;
            font-size: 0.9rem;
            color: #333;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.2);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .btn-cancel {
            background-color: #e2e8f0; /* slategray-200 */
            color: #4a5568; /* slategray-700 */
        }
        .btn-cancel:hover {
            background-color: #cbd5e1; /* slategray-300 */
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
        <a class="nav-link active" href="kelola_praktikum.php"><i class="fa fa-flask"></i> Kelola Praktikum</a>
        <a class="nav-link" href="kelola_modul.php"><i class="fa fa-book"></i> Kelola Modul</a>
        <a class="nav-link" href="laporan_masuk.php"><i class="fa fa-file-alt"></i> Laporan Masuk</a>
        <a class="nav-link" href="kelola_pengguna.php"><i class="fa fa-users"></i> Kelola Pengguna</a>
        <a class="nav-link" href="../logout.php" style="background-color: #dc3545; margin-top: 1rem;"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1>Kelola Mata Praktikum</h1>
                <p class="description-text">Tambah, edit, atau hapus mata praktikum.</p>
            </div>
            <button onclick="openAddModal()" class="btn btn-primary">
                <i class="fas fa-plus" style="margin-right: 0.5rem;"></i>Tambah Praktikum
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode MK</th>
                        <th>Nama Mata Praktikum</th>
                        <th>Deskripsi</th>
                        <th>Asisten</th>
                        <th>Statistik</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="code-badge">
                                        <?php echo htmlspecialchars($row['kode_mk']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 500; color: var(--navy);">
                                        <?php echo htmlspecialchars($row['nama_mk']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars($row['deskripsi'] ?: '-'); ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?php echo htmlspecialchars($row['nama_asisten'] ?: '-'); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                        <span style="color: #0ea5e9;">
                                            <i class="fas fa-users" style="margin-right: 0.25rem;"></i><?php echo $row['jumlah_mahasiswa']; ?> Mahasiswa
                                        </span>
                                        <span style="color: #22c55e;">
                                            <i class="fas fa-book" style="margin-right: 0.25rem;"></i><?php echo $row['jumlah_modul']; ?> Modul
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-action-buttons">
                                        <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['kode_mk']); ?>', '<?php echo htmlspecialchars($row['nama_mk']); ?>', '<?php echo htmlspecialchars($row['deskripsi']); ?>', <?php echo json_encode($row['asisten_id']); ?>)" 
                                                class="action-link">
                                            <i class="fas fa-edit" style="margin-right: 0.25rem;"></i>Edit
                                        </button>
                                        <button onclick="if(confirm('Apakah Anda yakin ingin menghapus praktikum ini?')) window.location.href='hapus_praktikum.php?id=<?php echo $row['id']; ?>'" 
                                                class="action-link delete">
                                            <i class="fas fa-trash" style="margin-right: 0.25rem;"></i>Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="icon">📚</div>
                                    <h3>Belum ada mata praktikum</h3>
                                    <p>Mulai dengan menambahkan mata praktikum pertama.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="addModal" class="modal-overlay hidden">
            <div class="modal-content">
                <div style="padding-bottom: 1rem;">
                    <h3 class="modal-title">Tambah Mata Praktikum</h3>
                    <form action="tambah_praktikum.php" method="POST">
                        <div class="form-group">
                            <label for="kode_mk" class="form-label">Kode Mata Kuliah</label>
                            <input type="text" id="kode_mk" name="kode_mk" required
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="nama_mk" class="form-label">Nama Mata Praktikum</label>
                            <input type="text" id="nama_mk" name="nama_mk" required
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" rows="3"
                                           class="form-textarea"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="asisten_id" class="form-label">Asisten Penanggung Jawab</label>
                            <select id="asisten_id" name="asisten_id" class="form-select">
                                <option value="">-- Pilih Asisten --</option>
                                <?php foreach ($asisten_list as $asisten): ?>
                                    <option value="<?= htmlspecialchars($asisten['id']) ?>">
                                        <?= htmlspecialchars($asisten['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="modal-actions">
                            <button type="button" onclick="closeAddModal()" 
                                    class="btn btn-secondary btn-cancel">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="btn btn-primary">
                                Tambah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="editModal" class="modal-overlay hidden">
            <div class="modal-content">
                <div style="padding-bottom: 1rem;">
                    <h3 class="modal-title">Edit Mata Praktikum</h3>
                    <form action="edit_praktikum.php" method="POST">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group">
                            <label for="edit_kode_mk" class="form-label">Kode Mata Kuliah</label>
                            <input type="text" id="edit_kode_mk" name="kode_mk" required
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="edit_nama_mk" class="form-label">Nama Mata Praktikum</label>
                            <input type="text" id="edit_nama_mk" name="nama_mk" required
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                            <textarea id="edit_deskripsi" name="deskripsi" rows="3"
                                            class="form-textarea"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_asisten_id" class="form-label">Asisten Penanggung Jawab</label>
                            <select id="edit_asisten_id" name="asisten_id" class="form-select">
                                <option value="">-- Pilih Asisten --</option>
                                <?php foreach ($asisten_list as $asisten): ?>
                                    <option value="<?= htmlspecialchars($asisten['id']) ?>">
                                        <?= htmlspecialchars($asisten['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="modal-actions">
                            <button type="button" onclick="closeEditModal()" 
                                    class="btn btn-secondary btn-cancel">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="btn btn-primary">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            // Reset form fields when closing the add modal
            document.querySelector('#addModal form').reset();
        }

        function openEditModal(id, kode_mk, nama_mk, deskripsi, asisten_id) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_kode_mk').value = kode_mk;
            document.getElementById('edit_nama_mk').value = nama_mk;
            document.getElementById('edit_deskripsi').value = deskripsi;
            
            const editAsistenSelect = document.getElementById('edit_asisten_id');
            // Ensure asisten_id is handled correctly, even if it's null or not set
            if (asisten_id !== null && asisten_id !== undefined) {
                editAsistenSelect.value = asisten_id;
            } else {
                editAsistenSelect.value = ''; // Select the 'Pilih Asisten' option
            }

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            // Reset form fields when closing the edit modal
            document.querySelector('#editModal form').reset();
        }

        // Close modals if clicking outside
        document.addEventListener('click', function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');

            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        });
        </script>
    </main>
</body>
</html>