<?php
require_once 'config.php';

$message = '';
$messageType = '';
$editMode = false;
$editCategory = null;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editId = $_GET['edit'];
    try {
        $query = "SELECT * FROM categories WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$editId]);
        $editCategory = $stmt->fetch();

        if ($editCategory) {
            $editMode = true;
        } else {
            $message = "Kategori tidak ditemukan!";
            $messageType = 'danger';
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'danger';
    }
}

if ($_POST && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        try {
            $checkQuery = "SELECT COUNT(*) FROM categories WHERE name = ?";
            $checkStmt = $pdo->prepare($checkQuery);
            $checkStmt->execute([$name]);

            if ($checkStmt->fetchColumn() > 0) {
                $message = "Kategori '$name' sudah ada!";
                $messageType = 'warning';
            } else {
                $insertQuery = "INSERT INTO categories (name) VALUES (?)";
                $insertStmt = $pdo->prepare($insertQuery);
                $insertStmt->execute([$name]);

                $message = "Kategori '$name' berhasil ditambahkan!";
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'danger';
        }
    } else {
        $message = "Nama kategori tidak boleh kosong!";
        $messageType = 'danger';
    }
}

if ($_POST && isset($_POST['update_category'])) {
    $id = $_POST['category_id'];
    $name = trim($_POST['name']);

    if (!empty($name)) {
        try {
            $checkQuery = "SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?";
            $checkStmt = $pdo->prepare($checkQuery);
            $checkStmt->execute([$name, $id]);

            if ($checkStmt->fetchColumn() > 0) {
                $message = "Kategori '$name' sudah ada!";
                $messageType = 'warning';
            } else {
                $updateQuery = "UPDATE categories SET name = ? WHERE id = ?";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute([$name, $id]);

                $message = "Kategori berhasil diupdate!";
                $messageType = 'success';
                $editMode = false;
                $editCategory = null;
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'danger';
        }
    } else {
        $message = "Nama kategori tidak boleh kosong!";
        $messageType = 'danger';
    }
}

if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $deleteId = $_GET['delete'];

    try {
        $checkTaskQuery = "SELECT COUNT(*) FROM tasks WHERE category_id = ?";
        $checkTaskStmt = $pdo->prepare($checkTaskQuery);
        $checkTaskStmt->execute([$deleteId]);
        $taskCount = $checkTaskStmt->fetchColumn();

        if ($taskCount > 0) {
            $message = "Kategori tidak dapat dihapus karena sedang digunakan oleh $taskCount tugas!";
            $messageType = 'warning';
        } else {
            $getNameQuery = "SELECT name FROM categories WHERE id = ?";
            $getNameStmt = $pdo->prepare($getNameQuery);
            $getNameStmt->execute([$deleteId]);
            $categoryName = $getNameStmt->fetchColumn();

            if ($categoryName) {
                $deleteQuery = "DELETE FROM categories WHERE id = ?";
                $deleteStmt = $pdo->prepare($deleteQuery);
                $deleteStmt->execute([$deleteId]);

                $message = "Kategori '$categoryName' berhasil dihapus!";
                $messageType = 'success';
            } else {
                $message = "Kategori tidak ditemukan!";
                $messageType = 'danger';
            }
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
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
    <title>Kelola Kategori - Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fb;
        }
        .card {
            border: 1px solid #20c997;
            box-shadow: 0 4px 12px rgba(32, 201, 151, 0.1);
        }
        .card-header {
            background-color: #20c997;
            color: white;
        }
        .btn-primary, .btn-success {
            background-color: #20c997;
            border-color: #20c997;
        }
        .btn-primary:hover, .btn-success:hover {
            background-color: #17b491;
            border-color: #17b491;
        }
        .form-control:focus, .form-select:focus {
            border-color: #20c997;
            box-shadow: 0 0 0 0.2rem rgba(32, 201, 151, 0.25);
        }
        .table-hover tbody tr:hover {
            background-color: #e9f8f3;
        }
        .table-info td {
            background-color: #d1f0ea !important;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-tags"></i> Kelola Kategori</h1>
                <div>
                    <?php if ($editMode): ?>
                        <a href="categories.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-times"></i> Batal Edit
                        </a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= $messageType == 'success' ? 'check-circle' : ($messageType == 'warning' ? 'exclamation-triangle' : 'exclamation-circle') ?>"></i>
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-<?= $editMode ? 'edit' : 'plus' ?>"></i> <?= $editMode ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php if ($editMode): ?>
                                    <input type="hidden" name="category_id" value="<?= $editCategory['id'] ?>">
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Kategori</label>
                                    <input type="text" class="form-control" id="name" name="name" required
                                           placeholder="Masukkan nama kategori"
                                           value="<?= $editMode ? htmlspecialchars($editCategory['name']) : '' ?>">
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" name="<?= $editMode ? 'update_category' : 'add_category' ?>"
                                            class="btn btn-<?= $editMode ? 'success' : 'primary' ?> w-100">
                                        <i class="fas fa-<?= $editMode ? 'save' : 'plus' ?>"></i>
                                        <?= $editMode ? 'Update Kategori' : 'Tambah Kategori' ?>
                                    </button>
                                    <?php if ($editMode): ?>
                                        <a href="categories.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Batal
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-list"></i> Daftar Kategori</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($categories)): ?>
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle"></i> Belum ada kategori. Tambahkan kategori baru.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Kategori</th>
                                                <th>Tanggal Dibuat</th>
                                                <th>Jumlah Tugas</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $index => $category): ?>
                                                <?php
                                                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE category_id = ?");
                                                $countStmt->execute([$category['id']]);
                                                $taskCount = $countStmt->fetchColumn();
                                                ?>
                                                <tr class="<?= $editMode && $editCategory['id'] == $category['id'] ? 'table-info' : '' ?>">
                                                    <td><?= $index + 1 ?></td>
                                                    <td>
                                                        <span class="badge text-bg-success fs-6"><?= htmlspecialchars($category['name']) ?></span>
                                                        <?php if ($editMode && $editCategory['id'] == $category['id']): ?>
                                                            <small class="text-info ms-2"><i class="fas fa-edit"></i> Sedang diedit</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= date('d/m/Y H:i', strtotime($category['created_at'])) ?></td>
                                                    <td><span class="badge bg-teal text-white" style="background-color: #20c997;"><?= $taskCount ?> tugas</span></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="categories.php?edit=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary <?= $editMode && $editCategory['id'] == $category['id'] ? 'active' : '' ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger <?= $taskCount > 0 ? 'disabled' : '' ?>"
                                                                    onclick="<?= $taskCount > 0 ? 'alertCannotDelete()' : 'confirmDelete(' . $category['id'] . ', \'' . htmlspecialchars($category['name']) . '\')' ?>">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle"></i> <strong>Informasi:</strong>
                <ul class="mb-0 mt-2">
                    <li>Kategori yang digunakan oleh tugas tidak bisa dihapus.</li>
                    <li>Klik tombol Edit untuk mengubah nama kategori.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Yakin ingin menghapus kategori "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#20c997',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `categories.php?delete=${id}`;
            }
        });
    }

    function alertCannotDelete() {
        Swal.fire({
            title: 'Tidak Dapat Dihapus',
            text: 'Kategori ini sedang digunakan oleh tugas.',
            icon: 'warning',
            confirmButtonColor: '#20c997',
            confirmButtonText: 'OK'
        });
    }
</script>
</body>
</html>
