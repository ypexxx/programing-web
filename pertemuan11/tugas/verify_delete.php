<?php
require_once 'config.php';

$message = '';
$messageType = '';

if (isset($_GET['check_id'])) {
    $check_id = $_GET['check_id'];
    try {
        $query = "SELECT * FROM tasks WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$check_id]);
        $task = $stmt->fetch();

        if ($task) {
            $message = "Data dengan ID <strong>$check_id</strong> masih ada di database!";
            $messageType = 'warning';
        } else {
            $message = "✅ KONFIRMASI: Data dengan ID <strong>$check_id</strong> sudah berhasil dihapus!";
            $messageType = 'success';
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'danger';
    }
}

// Ambil semua data
$query = "SELECT t.*, c.name as category_name 
          FROM tasks t 
          LEFT JOIN categories c ON t.category_id = c.id 
          ORDER BY t.id ASC";
$stmt = $pdo->query($query);
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Database - Task Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f9fc;
        }
        .card-header {
            background: linear-gradient(45deg, #0d6efd, #39c0ed);
            color: white;
        }
        .badge-status {
            padding: 0.5em 0.75em;
            font-size: 0.85rem;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="row">
        <div class="col-lg-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary"><i class="fas fa-database me-2"></i>Verifikasi Database</h2>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <!-- Alert -->
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'times-circle') ?>"></i>
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Form Cek ID -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-search"></i> Cek ID Tugas</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <label for="check_id" class="form-label">Masukkan ID yang ingin dicek:</label>
                            <input type="number" class="form-control" id="check_id" name="check_id" required
                                   placeholder="Contoh: 3"
                                   value="<?= isset($_GET['check_id']) ? htmlspecialchars($_GET['check_id']) : '' ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Cek Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Data Tugas Tersisa</h5>
                    <small>Total data: <strong><?= count($tasks) ?></strong></small>
                </div>
                <div class="card-body bg-white">
                    <?php if (empty($tasks)): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> Tidak ada data tugas dalam database.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Judul</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Waktu Dibuat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $task): ?>
                                        <?php
                                            $statusBadge = match($task['status']) {
                                                'pending' => 'bg-warning text-dark',
                                                'in_progress' => 'bg-primary',
                                                'completed' => 'bg-success'
                                            };
                                            $statusText = [
                                                'pending' => 'Pending',
                                                'in_progress' => 'In Progress',
                                                'completed' => 'Completed'
                                            ][$task['status']];
                                        ?>
                                        <tr>
                                            <td><strong><?= $task['id'] ?></strong></td>
                                            <td><?= htmlspecialchars($task['title']) ?></td>
                                            <td>
                                                <span class="badge bg-info text-dark">
                                                    <?= $task['category_name'] ?? 'Tidak ada' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-status <?= $statusBadge ?>"><?= $statusText ?></span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($task['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Section -->
            <div class="alert alert-info mt-4 shadow-sm">
                <i class="fas fa-lightbulb me-2"></i>
                <strong>Tips Penggunaan:</strong>
                <ol class="mb-0 mt-2">
                    <li>Catat ID tugas sebelum menghapus.</li>
                    <li>Setelah dihapus, buka halaman ini.</li>
                    <li>Masukkan ID tadi lalu klik <strong>Cek Data</strong>.</li>
                    <li>Jika data tidak ditemukan, berarti sudah terhapus dari database.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
