<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $nickname = $conn->real_escape_string($_POST['nickname']);

    // 验证邮箱格式
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '邮箱格式不正确。';
    } elseif ($password !== $confirm) {
        $error = '两次输入的密码不一致。';
    } elseif (strlen($password) < 6) {
        $error = '密码长度至少6位。';
    } else {
        // 检查邮箱是否已存在
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $error = '该邮箱已被注册。';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            // 生成验证码（简单示例）
            $code = md5(uniqid() . $email);
            $conn->query("INSERT INTO users (email, password, nickname, email_verified) VALUES ('$email', '$hashed', '$nickname', 0)");
            $user_id = $conn->insert_id;
            // 实际生产环境应发送邮件，这里仅生成验证链接并提示
            $verify_link = SITE_URL . "/login.php?verify=" . md5($user_id . $email . 'secret') . "&email=" . urlencode($email);
            // 模拟发送邮件（实际应调用邮件发送函数）
            $success = "注册成功！请点击以下链接验证邮箱（生产环境会发邮件）：<br><a href='$verify_link'>$verify_link</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册 - 腾虎运动器材</title>
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
        <h1>用户注册</h1>
        <?php if ($error): ?><p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="邮箱" required>
            <input type="text" name="nickname" placeholder="昵称" required>
            <input type="password" name="password" placeholder="密码（至少6位）" required>
            <input type="password" name="confirm_password" placeholder="确认密码" required>
            <button type="submit" name="register">注册</button>
        </form>
        <p>已有账号？<a href="login.php">立即登录</a></p>
    </div>
    <footer><div class="container"><p>&copy; <?= date('Y') ?> 东莞市腾虎运动器材有限公司</p></div></footer>
</body>
</html>