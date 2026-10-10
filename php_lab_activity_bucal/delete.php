<?php
include 'dbcon.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($id === false || $id === null) {
    http_response_code(400);
    exit('A valid user ID is required.');
}

$statement = $conn->prepare('DELETE FROM users WHERE id = ?');
if ($statement === false) {
    http_response_code(500);
    exit('Unable to prepare the user deletion: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}

$statement->bind_param('i', $id);
if (!$statement->execute()) {
    $error = htmlspecialchars($statement->error, ENT_QUOTES, 'UTF-8');
    $statement->close();
    http_response_code(500);
    exit('Unable to delete user: ' . $error);
}

$statement->close();
header('Location: Index.php?msg=deleted');
exit;
