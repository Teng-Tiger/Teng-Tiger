<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $rating = intval($_POST['rating']);
    $content = $conn->real_escape_string(substr($_POST['content'], 0, 50));
    $user_id = $_SESSION['user_id'];
    $conn->query("INSERT INTO reviews (product_id, user_id, name, rating, content) VALUES ($product_id, $user_id, '$name', $rating, '$content')");
}
header('Location: product.php?id=' . $product_id);
exit;
?>