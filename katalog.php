<?php
session_start();
require_once 'config.php';

$page_title = 'Katalog Praktikum';

$sql = "SELECT mp.*, u.nama as nama_asisten,
        (SELECT COUNT(*) FROM pendaftaran_praktikum pp WHERE pp.praktikum_id = mp.id) as jumlah_mahasiswa,
        (SELECT COUNT(*) FROM modul m WHERE m.praktikum_id = mp.id) as jumlah_modul
        FROM mata_praktikum mp 
        LEFT JOIN users u ON mp.asisten_id = u.id 
        ORDER BY mp.nama_mk ASC";
$result = $conn->query($sql);

$user_registered = [];
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa') {
    $user_id = $_SESSION['user_id'];
    $check_sql = "SELECT praktikum_id FROM pendaftaran_praktikum WHERE mahasiswa_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    while ($row = $check_result->fetch_assoc()) {
        $user_registered[] = $row['praktikum_id'];
    }
}
// --- AKHIR LOGIKA PHP ---\
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
            --border-color: #d0d3d4; /* Lighter border for overall clean look */
            --card-border-color: #b0b3b4; /* Slightly darker border for cards */
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
        /* Specific adjustments for Katalog */
        .bg-amber-50 {
            background-color: #f4f7f6;
        }
        .text-stone-800 {
            color: var(--text-dark);
        }
        .bg-amber-100 {
            background-color: var(--primary-navy); /* Header background */
            color: var(--text-light);
        }
        .border-stone-800 {
            border-color: var(--border-color); /* General border color */
        }
        .text-amber-900 {
            color: var(--accent-blue); /* Headings and accents */
        }
        .text-stone-700 {
            color: var(--text-light); /* Text in header */
        }
        .hover\:text-amber-900:hover {
            color: var(--accent-blue);
        }
        .border-b-2.border-amber-900 {
            border-color: var(--accent-blue);
        }
        .bg-stone-800 {
            background-color: var(--secondary-navy);
        }
        .hover\:bg-stone-700:hover {
            background-color: #002d5b; /* Darker secondary navy on hover */
        }
        .bg-teal-100 {
            background-color: #d4edda; 
            border-color: #28a745; /* Dark green border */
            color: #155724;
        .bg-white {
            background-color: white;
            border: 1px solid var(--card-border-color); /* Card border */
        }
        .hover\:transform.hover\:-translate-y-1.hover\:-translate-x-1.hover\:shadow-\[10px_10px_0px_var\(--retro-border\)\]:hover {
            transform: none; 
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .bg-yellow-400 {
            background-color: #f39c12; 
            color: white;
            border-color: #e67e22;
        }
        .bg-teal-500 {
            background-color: var(--accent-blue); /* 'Daftar Sekarang' button */
            border-color: var(--accent-blue);
        }
        .hover\:bg-teal-600:hover {
            background-color: #2980b9; /* Darker accent blue on hover */
        }
        .bg-stone-800 {
            background-color: var(--secondary-navy); /* 'Login untuk Daftar' button */
        }
        .hover\:bg-stone-700:hover {
            background-color: #002d5b;
        }
        .text-stone-400 {
            color: #95a5a6; 
        }
        .border-dashed.border-stone-300 {
            border-color: #e0e0e0;
        }
    </style>
</head>
<body class="text-stone-800">
    <header class="border-b sticky top-0 z-50" style="background-color: var(--primary-navy); border-color: var(--border-color);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="font-serif text-3xl font-bold" style="color: var(--accent-blue);">SIMPRAK</h1>
                </div>
                <nav class="hidden md:flex items-center space-x-6 text-sm font-bold uppercase tracking-wider">
                    <a href="index.php" class="text-stone-300 hover:text-accent-blue" style="color: var(--text-light);">Beranda</a>
                    <a href="katalog.php" class="border-b-2" style="color: var(--accent-blue); border-color: var(--accent-blue);">Katalog</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= $_SESSION['role'] === 'asisten' ? 'asisten/dashboard.php' : 'mahasiswa/dashboard.php' ?>" class="text-stone-300 hover:text-accent-blue" style="color: var(--text-light);">Dashboard</a>
                        <a href="logout.php" class="text-stone-300 hover:text-accent-blue" style="color: var(--text-light);">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-white px-4 py-2" style="background-color: var(--secondary-navy); hover:background-color: #002d5b;">Login</a>
                    <?php endif; ?>
                </nav>
                </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-10 text-center">
            <h1 class="font-serif text-5xl font-bold mb-2" style="color: var(--primary-navy);">Katalog Praktikum</h1>
            <p class="text-lg" style="color: var(--text-dark);">Don't forget to register!</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 border rounded retro-shadow" style="background-color: #d4edda; border-color: #28a745; color: #155724;">
                <i class="fas fa-check-circle mr-3"></i> <span class="font-bold"><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="bg-white border flex flex-col retro-shadow transition-all duration-200 hover:shadow-lg" style="border-color: var(--card-border-color);">
                        <div class="p-6 flex-grow">
                            <div class="mb-4">
                                <span class="font-bold border px-3 py-1 text-sm" style="background-color: #f39c12; border-color: #e67e22; color: white;">
                                    <?= htmlspecialchars($row['kode_mk']) ?>
                                </span>
                            </div>
                            
                            <h3 class="font-serif text-2xl font-bold mb-2" style="color: var(--primary-navy);">
                                <?= htmlspecialchars($row['nama_mk']) ?>
                            </h3>
                            
                            <p class="text-sm mb-4 h-20 overflow-hidden" style="color: var(--text-dark);">
                                <?= htmlspecialchars($row['deskripsi'] ?: 'Tidak ada deskripsi yang tersedia untuk praktikum ini.') ?>
                            </p>
                            
                            <div class="space-y-2 text-sm pt-4 border-t border-dashed" style="color: var(--text-dark); border-color: #e0e0e0;">
                                <p><i class="fas fa-user-tie w-5 mr-1"></i> Asisten: <strong><?= htmlspecialchars($row['nama_asisten'] ?: 'N/A') ?></strong></p>
                                <p><i class="fas fa-users w-5 mr-1"></i> Peserta: <strong><?= $row['jumlah_mahasiswa'] ?> Mahasiswa</strong></p>
                                <p><i class="fas fa-book w-5 mr-1"></i> Materi: <strong><?= $row['jumlah_modul'] ?> Modul</strong></p>
                            </div>
                        </div>
                        
                        <div class="p-4 border-t" style="background-color: #f8fbfb; border-color: var(--card-border-color);">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa'): ?>
                                <?php if (in_array($row['id'], $user_registered)): ?>
                                    <a href="mahasiswa/praktikum_saya.php" class="block w-full text-center p-3 font-bold border" style="background-color: #f39c12; color: white; border-color: #e67e22;">
                                        <i class="fas fa-check-circle"></i> SUDAH TERDAFTAR
                                    </a>
                                <?php else: ?>
                                    <a href="mahasiswa/daftar_praktikum.php" class="block w-full text-center p-3 font-bold text-white border transition-colors" style="background-color: var(--accent-blue); border-color: var(--accent-blue); hover:background-color: #2980b9;">
                                        <i class="fas fa-plus-circle"></i> DAFTAR SEKARANG
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="block w-full text-center p-3 font-bold text-white border transition-colors" style="background-color: var(--secondary-navy); border-color: var(--secondary-navy); hover:background-color: #002d5b;">
                                    <i class="fas fa-sign-in-alt"></i> LOGIN UNTUK DAFTAR
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="bg-white border p-12 text-center retro-shadow" style="border-color: var(--card-border-color);">
                <i class="fas fa-box-open text-7xl mb-4" style="color: #95a5a6;"></i>
                <h3 class="font-serif text-3xl font-bold" style="color: var(--primary-navy);">Belum Ada Praktikum</h3>
                <p class="text-lg" style="color: var(--text-dark);">Saat ini belum ada mata praktikum yang tersedia. Silakan cek kembali nanti.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer class="border-t mt-12" style="background-color: var(--primary-navy); border-color: var(--border-color);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center font-semibold" style="color: var(--text-light);">&copy; <?= date('Y') ?> SIMPRAK PDW</p>
        </div>
    </footer>
</body>
</html>