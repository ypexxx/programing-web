<?php
require_once 'config.php';

// Cek apakah ada ID yang dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: categories.php?error=no_id');
    exit;
}

$category_id = $_GET['id'];

try {
    // Cek apakah kategori sedang digunakan oleh tugas
    $checkTaskQuery = "SELECT COUNT(*) FROM tasks WHERE category_id = ?";
    $checkTaskStmt = $pdo->prepare($checkTaskQuery);
    $checkTaskStmt->execute([$category_id]);
    $taskCount = $checkTaskStmt->fetchColumn();
    
    if ($taskCount > 0) {
        header('Location: categories.php?error=category_in_use&count=' . $taskCount);
        exit;
    }
    
    // Ambil nama kategori untuk konfirmasi
    $query = "SELECT name FROM categories WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        header('Location: categories.php?error=category_not_found');
        exit;
    }
    
    // Hapus kategori dari database
    $deleteQuery = "DELETE FROM categories WHERE id = ?";
    $deleteStmt = $pdo->prepare($deleteQuery);
    $deleteStmt->execute([$category_id]);
    
    if ($deleteStmt->rowCount() > 0) {
        // Redirect dengan pesan sukses
        header('Location: categories.php?success=deleted&name=' . urlencode($category['name']));
    } else {
        header('Location: categories.php?error=delete_failed');
    }
    
} catch (PDOException $e) {
    header('Location: categories.php?error=database&msg=' . urlencode($e->getMessage()));
}

exit;
?>