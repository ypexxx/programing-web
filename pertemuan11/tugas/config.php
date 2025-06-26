<?php
// config.php - Database Configuration
$host = 'localhost';
$dbname = 'tugas11';
$username = 'root'; // sesuaikan dengan username database Anda
$password = '';     // sesuaikan dengan password database Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set timezone
    $pdo->exec("SET time_zone = '+07:00'");
    
} catch(PDOException $e) {
    // Log error untuk debugging
    error_log("Database connection failed: " . $e->getMessage());
    
    // Tampilkan pesan error yang user-friendly
    die("
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f8f9fa;'>
        <h2 style='color: #dc3545; margin-bottom: 20px;'>Database Connection Error</h2>
        <p style='margin-bottom: 15px;'>Tidak dapat terhubung ke database. Silakan periksa:</p>
        <ul style='margin-bottom: 20px; padding-left: 20px;'>
            <li>Apakah server MySQL sudah berjalan?</li>
            <li>Apakah database 'task_management' sudah dibuat?</li>
            <li>Apakah username dan password database sudah benar?</li>
        </ul>
        <p style='margin-bottom: 15px;'><strong>Langkah-langkah setup:</strong></p>
        <ol style='margin-bottom: 20px; padding-left: 20px;'>
            <li>Buat database dengan nama 'task_management'</li>
            <li>Import file SQL untuk membuat tabel</li>
            <li>Sesuaikan username dan password di config.php</li>
        </ol>
        <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
            Error detail: " . $e->getMessage() . "
        </p>
    </div>
    ");
}

// Function helper untuk format tanggal Indonesia
function formatTanggalIndonesia($datetime) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($datetime);
    $hari = date('d', $timestamp);
    $bulan_num = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);
    
    return $hari . ' ' . $bulan[$bulan_num] . ' ' . $tahun . ' ' . $jam;
}

// Function untuk sanitasi input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function untuk validasi ID
function validateId($id) {
    return filter_var($id, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)));
}
?>