<?php
require_once 'config.php';

$message = '';
$messageType = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case '1':
            $message = 'Tugas berhasil ditambahkan!';
            $messageType = 'success';
            break;
        case 'updated':
            $message = 'Tugas berhasil diupdate!';
            $messageType = 'success';
            break;
        case 'deleted':
            $taskTitle = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : 'Tugas';
            $message = "Tugas '$taskTitle' berhasil dihapus!";
            $messageType = 'success';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'task_not_found':
            $message = 'Tugas tidak ditemukan!';
            $messageType = 'danger';
            break;
        case 'task_completed':
            $message = 'Tugas yang sudah completed tidak dapat diedit!';
            $messageType = 'warning';
            break;
        case 'delete_failed':
            $message = 'Gagal menghapus tugas!';
            $messageType = 'danger';
            break;
        case 'database':
            $message = 'Terjadi kesalahan database!';
            $messageType = 'danger';
            break;
    }
}

// Ambil data tugas
$query = "SELECT t.*, c.name as category_name 
          FROM tasks t 
          LEFT JOIN categories c ON t.category_id = c.id 
          ORDER BY t.created_at DESC";
$stmt = $pdo->query($query);
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }
        .card-header {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4 text-primary">
                    <i class="fas fa-tasks me-2"></i>Task Management System
                </h1>

                <!-- Alert -->
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?= $messageType == 'success' ? 'check-circle' : ($messageType == 'warning' ? 'exclamation-triangle' : 'exclamation-circle') ?>"></i>
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Tombol Navigasi -->
                <div class="mb-3 d-flex gap-2">
                    <a href="add_task.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Tambah Tugas
                    </a>
                    <a href="categories.php" class="btn btn-outline-primary">
                        <i class="fas fa-tags"></i> Kelola Kategori
                    </a>
                </div>

                <!-- Tabel -->
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Daftar Tugas</h5>
                    </div>
                    <div class="card-body bg-white">
                        <?php if (empty($tasks)): ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i>
                                <strong>Belum ada tugas!</strong><br>
                                Tambahkan tugas menggunakan tombol di atas.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Judul</th>
                                            <th>Deskripsi</th>
                                            <th>Kategori</th>
                                            <th>Status</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tasks as $index => $task): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><strong><?= htmlspecialchars($task['title']) ?></strong></td>
                                                <td><?= htmlspecialchars(substr($task['description'], 0, 100)) ?><?= strlen($task['description']) > 100 ? '...' : '' ?></td>
                                                <td>
                                                    <span class="badge bg-info text-dark">
                                                        <?= $task['category_name'] ? htmlspecialchars($task['category_name']) : 'Tidak ada kategori' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = match ($task['status']) {
                                                        'pending' => 'bg-warning text-dark',
                                                        'in_progress' => 'bg-primary',
                                                        'completed' => 'bg-success'
                                                    };
                                                    $statusLabel = [
                                                        'pending' => 'Pending',
                                                        'in_progress' => 'In Progress',
                                                        'completed' => 'Completed'
                                                    ][$task['status']];
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group">
                                                        <a href="edit_task.php?id=<?= $task['id'] ?>" 
                                                           class="btn btn-sm btn-outline-primary <?= $task['status'] == 'completed' ? 'disabled' : '' ?>" 
                                                           <?= $task['status'] == 'completed' ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                                                            <i class="fas fa-pen"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmDelete(<?= $task['id'] ?>, '<?= htmlspecialchars($task['title']) ?>')">
                                                            <i class="fas fa-trash-alt"></i>
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
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmDelete(id, title) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Yakin ingin menghapus tugas "${title}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete_task.php?id=${id}`;
                }
            });
        }
    </script>
</body>
</html>
