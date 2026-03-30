<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id) {
    // 检查是否已收藏
    $check = $conn->query("SELECT id FROM favorites WHERE user_id = $user_id AND product_id = $product_id");
    if ($check->num_rows > 0) {
        // 已收藏则取消
        $conn->query("DELETE FROM favorites WHERE user_id = $user_id AND product_id = $product_id");
        $msg = '已取消收藏';
    } else {
        // 未收藏则添加
        $conn->query("INSERT INTO favorites (user_id, product_id) VALUES ($user_id, $product_id)");
        $msg = '已添加收藏';
    }
}
// 跳转回原页面
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: $redirect");
exit;
?>