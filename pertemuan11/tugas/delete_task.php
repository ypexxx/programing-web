<?php
require_once 'config.php';

// Cek apakah ada ID yang dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=no_id');
    exit;
}

$task_id = $_GET['id'];

try {
    // Ambil data tugas untuk konfirmasi
    $query = "SELECT title FROM tasks WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        header('Location: index.php?error=task_not_found');
        exit;
    }
    
    // Hapus tugas dari database
    $deleteQuery = "DELETE FROM tasks WHERE id = ?";
    $deleteStmt = $pdo->prepare($deleteQuery);
    $deleteStmt->execute([$task_id]);
    
    if ($deleteStmt->rowCount() > 0) {
        // Redirect dengan pesan sukses
        header('Location: index.php?success=deleted&title=' . urlencode($task['title']));
    } else {
        header('Location: index.php?error=delete_failed');
    }
    
} catch (PDOException $e) {
    header('Location: index.php?error=database&msg=' . urlencode($e->getMessage()));
}

exit;
?>