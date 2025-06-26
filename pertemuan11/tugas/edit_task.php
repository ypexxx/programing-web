<?php
require_once 'config.php';

$message = '';
$messageType = '';
$task = null;

// Cek ID
if (!isset($_GET['id']) || empty($_GET['id']) || !validateId($_GET['id'])) {
    header('Location: index.php?type=danger&msg=ID%20tugas%20tidak%20valid');
    exit;
}

$task_id = $_GET['id'];

// Ambil data tugas
try {
    $query = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();

    if (!$task) {
        header('Location: index.php?type=warning&msg=Tugas%20tidak%20ditemukan');
        exit;
    }

    if ($task['status'] == 'completed') {
        header('Location: index.php?type=info&msg=Tugas%20yang%20sudah%20selesai%20tidak%20bisa%20diedit');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php?type=danger&msg=Terjadi%20kesalahan%20database');
    exit;
}

// Proses Update
if ($_POST && isset($_POST['update_task'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];
    $status = $_POST['status'];

    if (!empty($title)) {
        try {
            $updateQuery = "UPDATE tasks SET title = ?, description = ?, category_id = ?, status = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([$title, $description, $category_id, $status, $task_id]);

            header('Location: index.php?type=success&msg=Tugas%20berhasil%20diupdate');
            exit;
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'danger';
        }
    } else {
        $message = "Judul tugas tidak boleh kosong!";
        $messageType = 'danger';
    }
}

// Ambil semua kategori
$query = "SELECT * FROM categories ORDER BY name";
$stmt = $pdo->query($query);
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas - Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-primary"><i class="fas fa-edit"></i> Edit Tugas</h1>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <!-- Alert Message -->
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= $messageType == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-tasks me-1"></i> Form Edit Tugas</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label"><i class="fas fa-heading"></i> Judul Tugas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required
                                   value="<?= htmlspecialchars($task['title']) ?>" placeholder="Masukkan judul tugas">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label"><i class="fas fa-align-left"></i> Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                      placeholder="Masukkan deskripsi"><?= htmlspecialchars($task['description']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label"><i class="fas fa-tags"></i> Kategori</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $task['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> Belum ada kategori yang cocok? <a href="categories.php">Tambah baru</a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label"><i class="fas fa-flag"></i> Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?= $task['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" name="update_task" class="btn btn-success w-100">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                            <div class="col-md-6">
                                <a href="index.php" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="alert alert-secondary mt-4 small">
                <i class="fas fa-info-circle"></i> Dibuat pada <strong><?= date('d/m/Y H:i', strtotime($task['created_at'])) ?></strong>
                <?php if ($task['updated_at'] != $task['created_at']): ?>
                    , terakhir diubah <strong><?= date('d/m/Y H:i', strtotime($task['updated_at'])) ?></strong>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
