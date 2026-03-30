<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// 获取订单列表
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC");

// 获取收藏列表
$favorites = $conn->query("SELECT p.id, p.name, p.price, p.main_image 
                            FROM favorites f 
                            JOIN products p ON f.product_id = p.id 
                            WHERE f.user_id = $user_id");

// 获取地址列表
$addresses = $conn->query("SELECT * FROM addresses WHERE user_id = $user_id ORDER BY is_default DESC, id DESC");

// 更新个人信息
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nickname = $conn->real_escape_string($_POST['nickname']);
    $conn->query("UPDATE users SET nickname = '$nickname' WHERE id = $user_id");
    $_SESSION['user_nickname'] = $nickname;
    header('Location: user.php');
    exit;
}

// 修改密码
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    if (password_verify($old, $user['password'])) {
        if ($new === $confirm && strlen($new) >= 6) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$hashed' WHERE id = $user_id");
            $success = "密码修改成功。";
        } else {
            $error = "新密码长度至少6位或两次输入不一致。";
        }
    } else {
        $error = "原密码错误。";
    }
}

// 添加地址
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_address'])) {
    $consignee = $conn->real_escape_string($_POST['consignee']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $province = $conn->real_escape_string($_POST['province']);
    $city = $conn->real_escape_string($_POST['city']);
    $district = $conn->real_escape_string($_POST['district']);
    $detail = $conn->real_escape_string($_POST['detail']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    if ($is_default) {
        $conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = $user_id");
    }
    $conn->query("INSERT INTO addresses (user_id, consignee, phone, province, city, district, detail, is_default) 
                  VALUES ($user_id, '$consignee', '$phone', '$province', '$city', '$district', '$detail', $is_default)");
    header('Location: user.php');
    exit;
}

// 删除地址
if (isset($_GET['del_addr'])) {
    $id = intval($_GET['del_addr']);
    $conn->query("DELETE FROM addresses WHERE id = $id AND user_id = $user_id");
    header('Location: user.php');
    exit;
}

// 设置默认地址
if (isset($_GET['set_default'])) {
    $id = intval($_GET['set_default']);
    $conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = $user_id");
    $conn->query("UPDATE addresses SET is_default = 1 WHERE id = $id AND user_id = $user_id");
    header('Location: user.php');
    exit;
}

// 取消收藏
if (isset($_GET['unfav'])) {
    $product_id = intval($_GET['unfav']);
    $conn->query("DELETE FROM favorites WHERE user_id = $user_id AND product_id = $product_id");
    header('Location: user.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人中心 - 腾虎运动器材</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .user-container { max-width: 1000px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; }
        .user-tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #ddd; }
        .user-tab { padding: 10px 20px; cursor: pointer; background: #f5f5f5; border-radius: 8px 8px 0 0; }
        .user-tab.active { background: #ff6600; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .address-item { border: 1px solid #eee; padding: 10px; margin-bottom: 10px; border-radius: 8px; }
        .address-default { background: #f9f9f9; border-left: 4px solid #ff6600; }
        .order-item { border-bottom: 1px solid #eee; padding: 10px; }
        .fav-item { display: inline-block; width: 200px; margin: 10px; text-align: center; }
        .fav-img { width: 100px; height: 100px; object-fit: cover; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo"><img src="uploads/logo-placeholder.png" alt="腾虎"></div>
            <nav><ul><li><a href="index.php">首页</a></li><li><a href="index.php#products">产品中心</a></li><li><a href="index.php#about">关于我们</a></li><li><a href="index.php#contact">联系我们</a></li></ul>
            <div class="nav-buttons"><a href="cart.php">🛒</a><a href="logout.php">退出</a></div></nav>
            <div class="menu-toggle">☰</div>
        </div>
    </header>

    <div class="user-container">
        <h1>个人中心</h1>
        <div class="user-tabs">
            <div class="user-tab active" data-tab="profile">个人信息</div>
            <div class="user-tab" data-tab="orders">我的订单</div>
            <div class="user-tab" data-tab="addresses">收货地址</div>
            <div class="user-tab" data-tab="favorites">我的收藏</div>
        </div>

        <!-- 个人信息 -->
        <div id="profile" class="tab-content active">
            <h3>修改资料</h3>
            <form method="post">
                <label>昵称：<input type="text" name="nickname" value="<?= htmlspecialchars($user['nickname']) ?>"></label>
                <button type="submit" name="update_profile">保存</button>
            </form>
            <h3>修改密码</h3>
            <form method="post">
                <input type="password" name="old_password" placeholder="原密码" required>
                <input type="password" name="new_password" placeholder="新密码（至少6位）" required>
                <input type="password" name="confirm_password" placeholder="确认新密码" required>
                <button type="submit" name="change_password">修改密码</button>
                <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
                <?php if (isset($success)) echo "<p style='color:green'>$success</p>"; ?>
            </form>
        </div>

        <!-- 我的订单 -->
        <div id="orders" class="tab-content">
            <?php if ($orders->num_rows == 0): ?>
                <p>暂无订单。</p>
            <?php else: ?>
                <?php while($order = $orders->fetch_assoc()): ?>
                <div class="order-item">
                    <strong>订单号：<?= $order['order_no'] ?></strong><br>
                    商品：<?= htmlspecialchars($order['product_name']) ?> x<?= $order['quantity'] ?><br>
                    金额：¥<?= number_format($order['price'] * $order['quantity'] + $order['shipping_fee'], 2) ?><br>
                    状态：
                    <?php
                    $status_map = ['pending'=>'待支付','paid'=>'已支付','shipped'=>'已发货','completed'=>'已完成','cancelled'=>'已取消'];
                    echo $status_map[$order['status']] ?? $order['status'];
                    ?><br>
                    <?php if ($order['tracking_no']): ?>
                    快递单号：<?= $order['tracking_no'] ?><br>
                    <?php endif; ?>
                    <?php if ($order['status'] == 'paid'): ?>
                        <a href="track.php?order_no=<?= $order['order_no'] ?>" class="btn-sm">查询物流</a>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- 收货地址 -->
        <div id="addresses" class="tab-content">
            <h3>添加新地址</h3>
            <form method="post">
                <input type="text" name="consignee" placeholder="收货人" required>
                <input type="text" name="phone" placeholder="手机号" required>
                <input type="text" name="province" placeholder="省份" required>
                <input type="text" name="city" placeholder="城市" required>
                <input type="text" name="district" placeholder="区/县" required>
                <input type="text" name="detail" placeholder="详细地址" required>
                <label><input type="checkbox" name="is_default"> 设为默认地址</label>
                <button type="submit" name="add_address">添加</button>
            </form>
            <h3>已有地址</h3>
            <?php while($addr = $addresses->fetch_assoc()): ?>
            <div class="address-item <?= $addr['is_default'] ? 'address-default' : '' ?>">
                <strong><?= htmlspecialchars($addr['consignee']) ?></strong> <?= htmlspecialchars($addr['phone']) ?><br>
                <?= htmlspecialchars($addr['province'] . $addr['city'] . $addr['district'] . $addr['detail']) ?>
                <?php if (!$addr['is_default']): ?>
                    <a href="?set_default=<?= $addr['id'] ?>" class="btn-sm">设为默认</a>
                <?php else: ?>
                    <span>【默认】</span>
                <?php endif; ?>
                <a href="?del_addr=<?= $addr['id'] ?>" class="btn-sm" onclick="return confirm('删除地址？')">删除</a>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- 我的收藏 -->
        <div id="favorites" class="tab-content">
            <?php if ($favorites->num_rows == 0): ?>
                <p>暂无收藏。</p>
            <?php else: ?>
                <div style="display:flex; flex-wrap:wrap;">
                <?php while($fav = $favorites->fetch_assoc()): ?>
                <div class="fav-item">
                    <a href="product.php?id=<?= $fav['id'] ?>">
                        <img src="<?= htmlspecialchars($fav['main_image'] ?: 'https://placehold.co/150x150') ?>" class="fav-img"><br>
                        <?= htmlspecialchars($fav['name']) ?><br>
                        ¥<?= number_format($fav['price'], 2) ?>
                    </a><br>
                    <a href="?unfav=<?= $fav['id'] ?>" class="btn-sm">取消收藏</a>
                </div>
                <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer><div class="container"><p>&copy; <?= date('Y') ?> 东莞市腾虎运动器材有限公司</p></div></footer>

    <script>
        document.querySelectorAll('.user-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.user-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const tabId = tab.getAttribute('data-tab');
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html>