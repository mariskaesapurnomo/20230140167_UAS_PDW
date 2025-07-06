<?php
session_start();
require_once 'config.php';

$page_title = 'Register';

// --- LOGIKA PHP ANDA (TIDAK DIUBAH) ---
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'asisten') {
        header('Location: asisten/dashboard.php');
    } else {
        header('Location: mahasiswa/dashboard.php');
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $error = "Semua field harus diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssss", $nama, $email, $hashed_password, $role);
            
            if ($insert_stmt->execute()) {
                $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
                header('Location: login.php');
                exit();
            } else {
                $error = "Gagal mendaftar. Silakan coba lagi.";
            }
        }
    }
}
// --- AKHIR LOGIKA PHP ---
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Public+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-navy: #001f3f;
            --secondary-navy: #003366;
            --accent-blue: #3498db;
            --text-light: #ecf0f1;
            --text-dark: #2c3e50;
            --border-color: #34495e;
        }
        body {
            font-family: 'Public Sans', sans-serif;
            background-color: #f4f7f6;
            color: var(--text-dark);
        }
        .font-serif {
            font-family: 'DM Serif Display', serif;
        }
        .retro-shadow {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .retro-input {
            appearance: none;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            padding: 0.75rem;
            width: 100%;
            background-color: white;
            color: var(--text-dark);
            transition: all 0.2s ease;
        }
        .retro-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px var(--accent-blue);
            border-color: var(--accent-blue);
        }
        .retro-btn {
            border-radius: 4px;
            border: none;
            font-weight: 700;
            padding: 0.75rem;
            width: 100%;
            color: white;
            transition: all 0.2s ease;
        }
        .retro-btn.primary {
            background-color: var(--accent-blue); /* Primary button in accent blue */
        }
        .retro-btn.primary:hover {
            background-color: #2980b9; /* Darker accent blue on hover */
            transform: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Adjustments for specific elements in register.php */
        .bg-amber-50 {
            background-color: #f4f7f6;
        }
        .text-amber-900 {
            color: var(--primary-navy);
        }
        .text-stone-700 {
            color: var(--text-dark);
        }
        .bg-white {
            background-color: white;
        }
        .border-stone-800 {
            border-color: #e0e0e0;
        }
        .text-red-900 {
            color: #c0392b;
        }
        .border-red-800 {
            border-color: #e74c3c;
        }
        .bg-red-100 {
            background-color: #fdedee;
        }
        .text-amber-800 {
            color: var(--accent-blue);
        }
        .border-dashed {
            border-color: #e0e0e0;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full space-y-6">
        <div class="text-center">
            <h1 class="font-serif text-5xl font-bold" style="color: var(--primary-navy);">SIMPRAK</h1>
            <p class="mt-1 text-lg" style="color: var(--text-dark);">Sistem Informasi Manajemen Praktikum</p>
        </div>
        
        <div class="bg-white border p-8 retro-shadow" style="border-color: var(--border-color);">
            <div class="text-center mb-8">
                <h2 class="font-serif text-3xl font-bold" style="color: var(--primary-navy);">Buat Akun Baru</h2>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="mb-6 p-4 bg-red-100 border text-red-900 rounded" style="border-color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle mr-2"></i><span class="font-bold"><?= $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label for="nama" class="block text-sm font-bold mb-2" style="color: var(--text-dark);">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" required class="retro-input" placeholder="Nama Anda" value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-bold mb-2" style="color: var(--text-dark);">Alamat Email</label>
                    <input type="email" id="email" name="email" required class="retro-input" placeholder="contoh@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div>
                    <label for="role" class="block text-sm font-bold mb-2" style="color: var(--text-dark);">Daftar sebagai</label>
                    <select id="role" name="role" required class="retro-input">
                        <option value="" disabled <?= !isset($_POST['role']) ? 'selected' : '' ?>>-- Pilih role --</option>
                        <option value="mahasiswa" <?= (isset($_POST['role']) && $_POST['role'] === 'mahasiswa') ? 'selected' : '' ?>>Mahasiswa</option>
                        <option value="asisten" <?= (isset($_POST['role']) && $_POST['role'] === 'asisten') ? 'selected' : '' ?>>Asisten</option>
                    </select>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-bold mb-2" style="color: var(--text-dark);">Password</label>
                    <input type="password" id="password" name="password" required class="retro-input" placeholder="Minimal 6 karakter">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-bold mb-2" style="color: var(--text-dark);">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="retro-input" placeholder="Ulangi password">
                </div>
                
                <button type="submit" class="retro-btn primary mt-4" style="background-color: var(--accent-blue);">
                    <i class="fas fa-user-plus mr-2"></i>REGISTER
                </button>
            </form>
            
            <div class="text-center mt-6 pt-6 border-t border-dashed" style="border-color: #e0e0e0;">
                <p style="color: var(--text-dark);">
                    Sudah punya akun? 
                    <a href="login.php" class="font-bold underline" style="color: var(--accent-blue); hover:color: var(--secondary-navy);">
                        Login di sini
                    </a>
                </p>
            </div>
        </div>
        
        <div class="text-center">
            <a href="index.php" class="font-semibold" style="color: var(--text-dark); hover:color: var(--accent-blue);">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>