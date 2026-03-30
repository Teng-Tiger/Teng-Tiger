<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

// 处理登录
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $res = $conn->query("SELECT * FROM users WHERE email='$email' AND status=1");
    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['email_verified']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_nickname'] = $user['nickname'];
                header('Location: index.php');
                exit;
            } else {
                $error = '请先验证邮箱。';
            }
        } else {
            $error = '邮箱或密码错误。';
        }
    } else {
        $error = '邮箱或密码错误。';
    }
}

// 处理邮箱验证链接（来自邮件）
if (isset($_GET['verify']) && isset($_GET['email'])) {
    $email = $conn->real_escape_string($_GET['email']);
    $code = $_GET['verify'];
    $res = $conn->query("SELECT * FROM users WHERE email='$email' AND email_verified=0");
    if ($res->num_rows == 1) {
        // 简单验证：实际应用中建议用更安全的验证码机制，这里简化
        // 假设验证码是用户id的哈希（仅为演示）
        $user = $res->fetch_assoc();
        $expected = md5($user['id'] . $user['email'] . 'secret');
        if ($code === $expected) {
            $conn->query("UPDATE users SET email_verified=1 WHERE id={$user['id']}");
            $success = '邮箱验证成功，请登录。';
        } else {
            $error = '验证链接无效。';
        }
    } else {
        $error = '用户不存在或已验证。';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录 - 腾虎运动器材</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo"><img src="uploads/logo-placeholder.png" alt="腾虎"></div>
            <nav><ul><li><a href="index.php">首页</a></li><li><a href="index.php#products">产品中心</a></li><li><a href="index.php#about">关于我们</a></li><li><a href="index.php#contact">联系我们</a></li></ul></nav>
            <div class="menu-toggle">☰</div>
        </div>
    </header>
    <div class="container auth-form">
        <h1>用户登录</h1>
        <?php if ($error): ?><p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($success): ?><p style="color:green"><?= htmlspecialchars($success) ?></p><?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="邮箱" required>
            <input type="password" name="password" placeholder="密码" required>
            <button type="submit" name="login">登录</button>
        </form>
        <p>还没有账号？<a href="register.php">立即注册</a></p>
        <p><a href="forgot_password.php">忘记密码？</a></p>
    </div>
    <footer><div class="container"><p>&copy; <?= date('Y') ?> 东莞市腾虎运动器材有限公司</p></div></footer>
</body>
</html>