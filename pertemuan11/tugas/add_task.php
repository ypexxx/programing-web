<?php
require_once 'config.php';

$message = '';
$messageType = '';

if ($_POST && isset($_POST['add_task'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];
    $status = 'pending';

    if (!empty($title)) {
        try {
            $insertQuery = "INSERT INTO tasks (title, description, category_id, status) VALUES (?, ?, ?, ?)";
            $insertStmt = $pdo->prepare($insertQuery);
            $insertStmt->execute([$title, $description, $category_id, $status]);

            header('Location: index.php?success=1');
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

$query = "SELECT * FROM categories ORDER BY name";
$stmt = $pdo->query($query);
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tugas - Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
        }
        .card {
            border: 1px solid #20c997;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .card-header {
            background-color: #20c997;
            color: white;
        }
        .btn-primary {
            background-color: #20c997;
            border-color: #20c997;
        }
        .btn-primary:hover {
            background-color: #17b491;
            border-color: #17b491;
        }
        .form-control:focus, .form-select:focus {
            border-color: #20c997;
            box-shadow: 0 0 0 0.2rem rgba(32, 201, 151, 0.25);
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-plus-circle"></i> Tambah Tugas Baru</h1>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= $messageType == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-tasks"></i> Form Tambah Tugas</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading"></i> Judul Tugas <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="title" name="title"
                                   placeholder="Masukkan judul tugas" required
                                   value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left"></i> Deskripsi
                            </label>
                            <textarea class="form-control" id="description" name="description"
                                      rows="4" placeholder="Masukkan deskripsi tugas (opsional)"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">
                                <i class="fas fa-tags"></i> Kategori
                            </label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"
                                        <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                Tidak ada kategori yang sesuai?
                                <a href="categories.php">Tambah kategori baru</a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-flag"></i> Status
                            </label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-info text-dark">Pending</span>
                                <small class="text-muted ms-2">(Status default untuk tugas baru)</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" name="add_task" class="btn btn-primary w-100 shadow-sm">
                                    <i class="fas fa-save"></i> Simpan Tugas
                                </button>
                            </div>
                            <div class="col-md-6">
                                <a href="index.php" class="btn btn-outline-secondary w-100 shadow-sm">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
