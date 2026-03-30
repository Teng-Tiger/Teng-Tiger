<?php
session_start();
require_once 'config.php';

$order_no = isset($_GET['order_no']) ? $conn->real_escape_string($_GET['order_no']) : '';
$order = null;
$phone = isset($_GET['phone']) ? $conn->real_escape_string($_GET['phone']) : '';

if ($order_no) {
    // 如果已登录，可关联用户ID查询；否则通过手机号验证
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $res = $conn->query("SELECT * FROM orders WHERE order_no = '$order_no' AND user_id = $user_id");
        if ($res->num_rows) $order = $res->fetch_assoc();
    } elseif ($phone) {
        $res = $conn->query("SELECT * FROM orders WHERE order_no = '$order_no' AND customer_phone = '$phone'");
        if ($res->num_rows) $order = $res->fetch_assoc();
    } else {
        // 未登录未提供手机号，提示
        $error = "请提供手机号以验证身份。";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单查询 - 腾虎运动器材</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo"><img src="uploads/logo-placeholder.png" alt="腾虎"></div>
            <nav><ul><li><a href="index.php">首页</a></li><li><a href="index.php#products">产品中心</a></li><li><a href="index.php#about">关于我们</a></li><li><a href="index.php#contact">联系我们</a></li></ul>
            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="user.php">个人中心</a> <a href="cart.php">🛒</a> <a href="logout.php">退出</a>
                <?php else: ?>
                    <a href="login.php">登录</a> <a href="register.php">注册</a>
                <?php endif; ?>
            </div>
            <div class="menu-toggle">☰</div>
        </div>
    </header>

    <div class="container track-form">
        <h1>订单查询</h1>
        <form method="get">
            <input type="text" name="order_no" placeholder="订单号" value="<?= htmlspecialchars($order_no) ?>" required>
            <?php if (!isset($_SESSION['user_id'])): ?>
            <input type="tel" name="phone" placeholder="下单手机号" value="<?= htmlspecialchars($phone) ?>" required>
            <?php endif; ?>
            <button type="submit">查询</button>
        </form>
        <?php if (isset($error)): ?>
            <p style="color:red"><?= $error ?></p>
        <?php elseif ($order_no && !$order): ?>
            <p style="color:red">未找到该订单，请检查订单号和手机号是否正确。</p>
        <?php elseif ($order): ?>
            <div class="order-detail">
                <p><strong>订单号：</strong><?= $order['order_no'] ?></p>
                <p><strong>商品：</strong><?= htmlspecialchars($order['product_name']) ?> x<?= $order['quantity'] ?></p>
                <p><strong>金额：</strong>¥<?= number_format($order['price'] * $order['quantity'] + $order['shipping_fee'], 2) ?></p>
                <p><strong>收货人：</strong><?= htmlspecialchars($order['customer_name']) ?> / <?= $order['customer_phone'] ?></p>
                <p><strong>地址：</strong><?= htmlspecialchars($order['customer_address']) ?></p>
                <p><strong>备注：</strong><?= htmlspecialchars($order['remark']) ?></p>
                <p><strong>状态：</strong>
                    <?php
                    $status_map = ['pending'=>'待支付','paid'=>'已支付','shipped'=>'已发货','completed'=>'已完成','cancelled'=>'已取消'];
                    echo $status_map[$order['status']] ?? $order['status'];
                    ?>
                </p>
                <?php if ($order['tracking_no']): ?>
                <p><strong>快递单号：</strong><?= htmlspecialchars($order['tracking_no']) ?></p>
                <p>您可复制单号到快递官网查询。</p>
                <?php endif; ?>
                <p><strong>下单时间：</strong><?= $order['created_at'] ?></p>
            </div>
        <?php endif; ?>
    </div>

    <footer><div class="container"><p>&copy; <?= date('Y') ?> 东莞市腾虎运动器材有限公司</p></div></footer>
</body>
</html><?php
session_start();
require_once 'config.php';

$order_no = isset($_GET['order_no']) ? $conn->real_escape_string($_GET['order_no']) : '';
$order = null;
$phone = isset($_GET['phone']) ? $conn->real_escape_string($_GET['phone']) : '';

if ($order_no) {
    // 如果已登录，可关联用户ID查询；否则通过手机号验证
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $res = $conn->query("SELECT * FROM orders WHERE order_no = '$order_no' AND user_id = $user_id");
        if ($res->num_rows) $order = $res->fetch_assoc();
    } elseif ($phone) {
        $res = $conn->query("SELECT * FROM orders WHERE order_no = '$order_no' AND customer_phone = '$phone'");
        if ($res->num_rows) $order = $res->fetch_assoc();
    } else {
        // 未登录未提供手机号，提示
        $error = "请提供手机号以验证身份。";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单查询 - 腾虎运动器材</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo"><img src="uploads/logo-placeholder.png" alt="腾虎"></div>
            <nav><ul><li><a href="index.php">首页</a></li><li><a href="index.php#products">产品中心</a></li><li><a href="index.php#about">关于我们</a></li><li><a href="index.php#contact">联系我们</a></li></ul>
            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="user.php">个人中心</a> <a href="cart.php">🛒</a> <a href="logout.php">退出</a>
                <?php else: ?>
                    <a href="login.php">登录</a> <a href="register.php">注册</a>
                <?php endif; ?>
            </div>
            <div class="menu-toggle">☰</div>
        </div>
    </header>

    <div class="container track-form">
        <h1>订单查询</h1>
        <form method="get">
            <input type="text" name="order_no" placeholder="订单号" value="<?= htmlspecialchars($order_no) ?>" required>
            <?php if (!isset($_SESSION['user_id'])): ?>
            <input type="tel" name="phone" placeholder="下单手机号" value="<?= htmlspecialchars($phone) ?>" required>
            <?php endif; ?>
            <button type="submit">查询</button>
        </form>
        <?php if (isset($error)): ?>
            <p style="color:red"><?= $error ?></p>
        <?php elseif ($order_no && !$order): ?>
            <p style="color:red">未找到该订单，请检查订单号和手机号是否正确。</p>
        <?php elseif ($order): ?>
            <div class="order-detail">
                <p><strong>订单号：</strong><?= $order['order_no'] ?></p>
                <p><strong>商品：</strong><?= htmlspecialchars($order['product_name']) ?> x<?= $order['quantity'] ?></p>
                <p><strong>金额：</strong>¥<?= number_format($order['price'] * $order['quantity'] + $order['shipping_fee'], 2) ?></p>
                <p><strong>收货人：</strong><?= htmlspecialchars($order['customer_name']) ?> / <?= $order['customer_phone'] ?></p>
                <p><strong>地址：</strong><?= htmlspecialchars($order['customer_address']) ?></p>
                <p><strong>备注：</strong><?= htmlspecialchars($order['remark']) ?></p>
                <p><strong>状态：</strong>
                    <?php
                    $status_map = ['pending'=>'待支付','paid'=>'已支付','shipped'=>'已发货','completed'=>'已完成','cancelled'=>'已取消'];
                    echo $status_map[$order['status']] ?? $order['status'];
                    ?>
                </p>
                <?php if ($order['tracking_no']): ?>
                <p><strong>快递单号：</strong><?= htmlspecialchars($order['tracking_no']) ?></p>
                <p>您可复制单号到快递官网查询。</p>
                <?php endif; ?>
                <p><strong>下单时间：</strong><?= $order['created_at'] ?></p>
            </div>
        <?php endif; ?>
    </div>

    <footer><div class="container"><p>&copy; <?= date('Y') ?> 东莞市腾虎运动器材有限公司</p></div></footer>
</body>
</html>