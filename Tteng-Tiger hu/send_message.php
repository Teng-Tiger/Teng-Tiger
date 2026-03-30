<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);
    $conn->query("INSERT INTO messages (name, email, message) VALUES ('$name', '$email', '$message')");
}
header('Location: index.php#contact');
exit;
?>