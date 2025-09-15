<?php
require '../config.php';
require_once 'auth_admin.php';

// ลบสมาชิก
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
        $stmt->execute([$user_id]);
    }
    header("Location: users.php");
    exit;
}

// ดึงข้อมูลสมาชิก
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการสมาชิก</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    body {
        background: linear-gradient(135deg, #ffe6f2, #f2e6ff, #e6faff, #fffbe6);
        font-family: 'Kanit', sans-serif;
        min-height: 100vh;
    }
    h2 {
        color: #d63384; /* ชมพูสด */
        font-weight: bold;
        margin-bottom: 20px;
    }
    .table {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    thead {
        background: linear-gradient(90deg, #f8bbd0, #ba68c8, #81d4fa, #fff176);
        color: #fff;
    }
    .btn-secondary {
        background-color: #ba68c8;
        border: none;
    }
    .btn-secondary:hover {
        background-color: #9c27b0;
    }
    .btn-warning {
        background-color: #ffeb3b;
        border: none;
        color: #333;
    }
    .btn-warning:hover {
        background-color: #fdd835;
    }
    .btn-danger {
        background-color: #ff66b2;
        border: none;
    }
    .btn-danger:hover {
        background-color: #e0559d;
    }
</style>
</head>
<body class="container mt-4">

<h2>🌸 จัดการสมาชิก 🌈</h2>
<a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>

<?php if (count($users) === 0): ?>
    <div class="alert alert-warning">ยังไม่มีสมาชิกในระบบ</div>
<?php else: ?>
    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>ชื่อผู้ใช้</th>
                <th>ชื่อ - นามสกุล</th>
                <th>อีเมล</th>
                <th>วันที่สมัคร</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['full_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['created_at'] ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                    <form action="delUser_Sweet.php" method="POST" style="display:inline;">
                        <input type="hidden" name="u_id" value="<?= $user['user_id'] ?>">
                        <button type="button" class="delete-button btn btn-danger btn-sm" data-user-id="<?= $user['user_id'] ?>">ลบ</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
    // SweetAlert2
    function showDeleteConfirmation(userId) {
        Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: 'คุณจะไม่สามารถกู้คืนข้อมูลได้!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delUser_Sweet.php';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'u_id';
                input.value = userId;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    document.querySelectorAll('.delete-button').forEach((button) => {
        button.addEventListener('click', () => {
            const userId = button.getAttribute('data-user-id');
            showDeleteConfirmation(userId);
        });
    });
</script>

</body>
</html>
