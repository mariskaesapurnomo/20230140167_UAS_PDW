<?php
session_start();
require_once 'config.php';

$page_title = 'Login';

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
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'asisten') {
                header('Location: asisten/dashboard.php');
            } else {
                header('Location: mahasiswa/dashboard.php');
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
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
            --primary-navy: #001f3f; /* Dark Navy */
            --secondary-navy: #003366; /* Slightly lighter Navy */
            --accent-blue: #3498db; /* A vibrant blue for accents */
            --text-light: #ecf0f1; /* Light text on dark backgrounds */
            --text-dark: #2c3e50; /* Dark text for light backgrounds */
            --border-color: #34495e; /* Border color */
        }
        body {
            font-family: 'Public Sans', sans-serif;
            background-color: #f4f7f6; /* Light gray background */
            color: var(--text-dark);
        }
        .font-serif {
            font-family: 'DM Serif Display', serif;
        }
        .retro-shadow {
            /* We can simplify or remove this for a more elegant look */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .retro-input {
            appearance: none;
            border-radius: 4px; /* Slightly rounded corners */
            border: 1px solid var(--border-color);
            padding: 0.75rem;
            width: 100%;
            background-color: white;
            color: var(--text-dark);
        }
        .retro-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px var(--accent-blue);
            border-color: var(--accent-blue);
        }
        .retro-btn {
            border-radius: 4px;
            border: none; /* No border for a cleaner look */
            font-weight: 700;
            padding: 0.75rem;
            width: 100%;
            color: white;
            background-color: var(--primary-navy);
            transition: all 0.2s ease;
        }
        .retro-btn:hover {
            background-color: var(--secondary-navy);
            transform: none; /* Remove retro transform */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Adjustments for the specific elements in login.php */
        .bg-amber-50 {
            background-color: #f4f7f6; /* Light gray to match body */
        }
        .text-amber-900 {
            color: var(--primary-navy); /* Headings in navy */
        }
        .text-stone-700 {
            color: var(--text-dark);
        }
        .bg-white {
            background-color: white;
        }
        .border-stone-800 {
            border-color: #e0e0e0; /* Lighter border for elegance */
        }
        .text-red-900 {
            color: #c0392b; /* Red for errors */
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
                <h2 class="font-serif text-3xl font-bold" style="color: var(--primary-navy);">Login Akun</h2>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="mb-6 p-4 bg-red-100 border text-red-900 rounded" style="border-color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle mr-2"></i><span class="font-bold"><?= $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-bold mb-2" style="color: var(--text-dark);">
                        Alamat Email
                    </label>
                    <input type="email" id="email" name="email" required 
                           class="retro-input"
                           placeholder="contoh@email.com">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-bold mb-2" style="color: var(--text-dark);">
                        Password
                    </label>
                    <input type="password" id="password" name="password" required 
                           class="retro-input"
                           placeholder="••••••••">
                </div>
                
                <button type="submit" class="retro-btn" style="background-color: var(--primary-navy);">
                    <i class="fas fa-sign-in-alt mr-2"></i>LOGIN
                </button>
            </form>
            
            <div class="text-center mt-6 pt-6 border-t border-dashed" style="border-color: #e0e0e0;">
                <p style="color: var(--text-dark);">
                    Belum punya akun? 
                    <a href="register.php" class="font-bold underline" style="color: var(--accent-blue); hover:color: var(--secondary-navy);">
                        Daftar di sini
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