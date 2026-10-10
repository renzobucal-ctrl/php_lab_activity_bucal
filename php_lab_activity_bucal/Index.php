<?php
include 'dbcon.php';

$alert = null;
$alertType = 'success';
$statusMessages = [
    'added' => 'User added successfully.',
    'updated' => 'User updated successfully.',
    'deleted' => 'User deleted successfully.',
];
$showAddedAlert = false;

$messageKey = $_GET['status'] ?? $_GET['msg'] ?? null;
if (is_string($messageKey) && isset($statusMessages[$messageKey])) {
    if ($messageKey === 'added') {
        $showAddedAlert = true;
    } else {
        $alert = $statusMessages[$messageKey];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $email === '') {
        $alert = 'Please enter name and email.';
        $alertType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alert = 'Please enter a valid email address.';
        $alertType = 'danger';
    } else {
        $statement = $conn->prepare('INSERT INTO users (Name, Email) VALUES (?, ?)');

        if ($statement === false) {
            $alert = 'Unable to prepare the user insert: ' . $conn->error;
            $alertType = 'danger';
        } else {
            $statement->bind_param('ss', $name, $email);

            if ($statement->execute()) {
                $statement->close();
                header('Location: Index.php?status=added');
                exit;
            }

            $alert = 'Unable to add user: ' . $statement->error;
            $alertType = 'danger';
            $statement->close();
        }
    }
}

$users = $conn->query('SELECT id, Name AS name, Email AS email, Address AS joined_at FROM users ORDER BY id DESC');
if ($users === false) {
    $alert = 'Unable to load users: ' . $conn->error;
    $alertType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f3f4f6;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }

        .page-container { max-width: 1200px; }
    </style>
</head>
<body>
    <main class="container page-container py-5">
        <h1 class="text-center mb-4">User Management System</h1>

        <div class="row g-4 align-items-start">
            <div class="col-12 col-lg-4">
                <section class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h4 card-title mb-3">Add New User</h2>
                        <?php if ($alert !== null): ?>
                            <div class="alert alert-<?php echo htmlspecialchars($alertType, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($alert, ENT_QUOTES, 'UTF-8'); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <form method="post" action="Index.php" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter full name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </section>
            </div>

            <div class="col-12 col-lg-8">
                <section class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h4 card-title mb-3">Registered Users</h2>
                        <?php if ($users !== false): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">ID</th>
                                            <th scope="col">Full Name</th>
                                            <th scope="col">Email Address</th>
                                            <th scope="col">Joined</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($users->num_rows > 0): ?>
                                        <?php while ($user = $users->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars((string) $user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime((string) $user['joined_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <a class="btn btn-sm btn-warning" href="edit.php?id=<?php echo urlencode((string) $user['id']); ?>">Edit</a>
                                                    <a class="btn btn-sm btn-danger" href="delete.php?id=<?php echo urlencode((string) $user['id']); ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No users found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($showAddedAlert): ?>
        <script>
            alert('Successfully added!');
            window.history.replaceState(null, '', 'Index.php');
        </script>
    <?php endif; ?>
</body>
</html>