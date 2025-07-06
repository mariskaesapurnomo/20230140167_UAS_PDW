<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Kelola Pengguna';

// Tambah user
if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $email, $password, $role);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Pengguna berhasil ditambahkan!';
    } else {
        $_SESSION['error'] = 'Gagal menambahkan pengguna: ' . $conn->error;
    }
    $stmt->close();
    header('Location: kelola_pengguna.php');
    exit();
}

// Edit user
if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id = (int)$_POST['id'];
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password_field = $_POST['password'];

    if (!empty($password_field)) {
        $password = password_hash($password_field, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET nama=?, email=?, password=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $nama, $email, $password, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET nama=?, email=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $nama, $email, $role, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Pengguna berhasil diperbarui!';
    } else {
        $_SESSION['error'] = 'Gagal memperbarui pengguna: ' . $conn->error;
    }
    $stmt->close();
    header('Location: kelola_pengguna.php');
    exit();
}

// Hapus user
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Pengguna berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus pengguna: ' . $conn->error;
    }
    $stmt->close();
    header('Location: kelola_pengguna.php');
    exit();
}

// Ambil data user
$result = $conn->query("SELECT * FROM users ORDER BY nama ASC");
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
        
        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px; /* full rounded */
            font-size: 0.75rem;
            font-weight: 500;
        }
        .role-badge.mahasiswa {
            background-color: #d1fae5; /* green-100 */
            color: #065f46; /* green-700 */
        }
        .role-badge.asisten {
            background-color: #ede9fe; /* purple-100 */
            color: #6d28d9; /* purple-700 */
        }
        .role-badge.lain {
            background-color: #e2e8f0; /* gray-200 */
            color: #4a5568; /* gray-700 */
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
        <a class="nav-link" href="kelola_praktikum.php"><i class="fa fa-flask"></i> Kelola Praktikum</a>
        <a class="nav-link" href="kelola_modul.php"><i class="fa fa-book"></i> Kelola Modul</a>
        <a class="nav-link" href="laporan_masuk.php"><i class="fa fa-file-alt"></i> Laporan Masuk</a>
        <a class="nav-link active" href="kelola_pengguna.php"><i class="fa fa-users"></i> Kelola Pengguna</a>
        <a class="nav-link" href="../logout.php" style="background-color: #dc3545; margin-top: 1rem;"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1>Kelola Pengguna</h1>
                <p class="description-text">Tambah, edit, atau hapus pengguna sistem.</p>
            </div>
            <button onclick="openAddModal()" class="btn btn-primary">
                <i class="fas fa-plus" style="margin-right: 0.5rem;"></i>Tambah Pengguna
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
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 500; color: var(--navy);">
                                        <?php echo htmlspecialchars($row['nama']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </td>
                                <td>
                                    <?php
                                    $role_class = '';
                                    if ($row['role'] == 'mahasiswa') {
                                        $role_class = 'mahasiswa';
                                    } else if ($row['role'] == 'asisten') {
                                        $role_class = 'asisten';
                                    } else {
                                        $role_class = 'lain';
                                    }
                                    ?>
                                    <span class="role-badge <?= $role_class ?>">
                                        <?= htmlspecialchars(ucfirst($row['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-action-buttons">
                                        <button onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama']) ?>', '<?= htmlspecialchars($row['email']) ?>', '<?= htmlspecialchars($row['role']) ?>')"
                                                class="action-link">
                                            <i class="fas fa-edit" style="margin-right: 0.25rem;"></i>Edit
                                        </button>
                                        <button onclick="if(confirm('Anda yakin ingin menghapus pengguna ini?')) window.location.href='kelola_pengguna.php?aksi=hapus&id=<?= $row['id'] ?>'"
                                                class="action-link delete">
                                            <i class="fas fa-trash" style="margin-right: 0.25rem;"></i>Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="icon">👥</div>
                                    <h3>Belum ada pengguna</h3>
                                    <p>Mulai dengan menambahkan pengguna pertama.</p>
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
                    <h3 class="modal-title">Tambah Pengguna Baru</h3>
                    <form action="kelola_pengguna.php" method="POST">
                        <input type="hidden" name="aksi" value="tambah">
                        <div class="form-group">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" id="nama" name="nama" required
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" required
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" required
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" required
                                    class="form-select">
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="asisten">Asisten</option>
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
                    <h3 class="modal-title">Edit Pengguna</h3>
                    <form action="kelola_pengguna.php" method="POST">
                        <input type="hidden" name="aksi" value="edit">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group">
                            <label for="edit_nama" class="form-label">Nama</label>
                            <input type="text" id="edit_nama" name="nama" required
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" id="edit_email" name="email" required
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="edit_role" class="form-label">Role</label>
                            <select id="edit_role" name="role" required
                                    class="form-select">
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="asisten">Asisten</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_password" class="form-label">Password (isi jika ingin ganti)</label>
                            <input type="password" id="edit_password" name="password" placeholder="Password baru" 
                                   class="form-input">
                        </div>
                        <div class="modal-actions">
                            <button type="button" onclick="closeEditModal()" 
                                    class="btn btn-secondary btn-cancel">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="btn btn-primary">
                                Simpan
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

        function openEditModal(id, nama, email, role) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_password').value = ''; // Clear password field on opening edit modal
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