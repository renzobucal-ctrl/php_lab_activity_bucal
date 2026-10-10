<?php
include 'dbcon.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$name = '';
$email = '';
$alert = null;
$alertType = 'danger';
$userFound = false;

if ($id === false || $id === null) {
    http_response_code(400);
    $alert = 'A valid user ID is required.';
} else {
    $select = $conn->prepare('SELECT name, email FROM users WHERE id = ?');

    if ($select === false) {
        $alert = 'Unable to prepare the user lookup: ' . $conn->error;
    } else {
        $select->bind_param('i', $id);

        if (!$select->execute()) {
            $alert = 'Unable to load user: ' . $select->error;
        } else {
            $select->bind_result($name, $email);
            $userFound = $select->fetch();
            if (!$userFound) {
                http_response_code(404);
                $alert = 'User not found.';
            }
        }

        $select->close();
    }

    if ($userFound && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '' || $email === '') {
            $alert = 'Please enter name and email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $alert = 'Please enter a valid email address.';
        } else {
            $update = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');

            if ($update === false) {
                $alert = 'Unable to prepare the user update: ' . $conn->error;
            } else {
                $update->bind_param('ssi', $name, $email, $id);

                if ($update->execute()) {
                    $update->close();
                    header('Location: Index.php?msg=updated');
                    exit;
                }

                $alert = 'Unable to update user: ' . $update->error;
                $update->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 640px;">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Edit User</h1>

                <?php if ($alert !== null): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($alertType, ENT_QUOTES, 'UTF-8'); ?>" role="alert">
                        <?php echo htmlspecialchars($alert, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($userFound): ?>
                    <form method="post" action="edit.php?id=<?php echo urlencode((string) $id); ?>" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="Index.php" class="btn btn-secondary">Cancel</a>
                    </form>
                <?php else: ?>
                    <a href="Index.php" class="btn btn-secondary">Back to Users</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
