<?php
session_start();
require_once 'config.php';

// 未登录跳转登录页
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 处理添加商品到购物车（通过GET参数add）
if (isset($_GET['add']) && is_numeric($_GET['add'])) {
    $product_id = intval($_GET['add']);
    $quantity = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
    if ($quantity < 1) $quantity = 1;
    // 检查购物车是否已有该商品
    $check = $conn->query("SELECT id, quantity FROM cart WHERE user_id=$user_id AND product_id=$product_id");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $new_qty = $row['quantity'] + $quantity;
        $conn->query("UPDATE cart SET quantity=$new_qty WHERE id={$row['id']}");
    } else {
        $conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)");
    }
    header('Location: cart.php');
    exit;
}

// 处理更新数量
if (isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $cart_id => $qty) {
        $cart_id = intval($cart_id);
        $qty = intval($qty);
        if ($qty <= 0) {
            $conn->query("DELETE FROM cart WHERE id=$cart_id AND user_id=$user_id");
        } else {
            $conn->query("UPDATE cart SET quantity=$qty WHERE id=$cart_id AND user_id=$user_id");
        }
    }
    header('Location: cart.php');
    exit;
}

// 处理删除单个商品
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $conn->query("DELETE FROM cart WHERE id=$cart_id AND user_id=$user_id");
    header('Location: cart.php');
    exit;
}

// 获取购物车内容
$cart_items = [];
$total = 0;
$sql = "SELECT c.id as cart_id, c.product_id, c.quantity, p.name, p.price, p.main_image, p.spec
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = $user_id";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $cart_items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>购物车 - 腾虎运动器材</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo"><img src="uploads/logo-placeholder.png" alt="腾虎"></div>
            <nav>
                <ul>
                    <li><a href="index.php">首页</a></li>
                    <li><a href="index.php#products">产品中心</a></li>
                    <li><a href="index.php#about">关于我们</a></li>
                    <li><a href="index.php#contact">联系我们</a></li>
                </ul>
                <div class="nav-buttons">
                    <a href="user.php" class="user-avatar">个人中心</a>
                    <a href="cart.php" class="cart-icon">🛒</a>
                    <a href="logout.php">退出</a>
                </div>
            </nav>
            <div class="menu-toggle">☰</div>
        </div>
    </header>

    <div class="container cart-page">
        <h1>购物车</h1>
        <?php if (empty($cart_items)): ?>
            <p>购物车为空，<a href="index.php#products">去逛逛</a></p>
        <?php else: ?>
            <form method="post">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>商品</th>
                            <th>单价</th>
                            <th>数量</th>
                            <th>小计</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td class="cart-product">
                                <img src="<?= htmlspecialchars($item['main_image'] ?: 'https://placehold.co/80x80?text=暂无图片') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-img">
                                <div class="cart-info">
                                    <a href="product.php?id=<?= $item['product_id'] ?>"><?= htmlspecialchars($item['name']) ?></a>
                                    <div class="cart-spec"><?= htmlspecialchars($item['spec']) ?></div>
                                </div>
                            </td>
                            <td class="cart-price">¥<?= number_format($item['price'], 2) ?></td>
                            <td class="cart-qty">
                                <input type="number" name="qty[<?= $item['cart_id'] ?>]" value="<?= $item['quantity'] ?>" min="0" step="1">
                            </td>
                            <td class="cart-subtotal">¥<?= number_format($item['subtotal'], 2) ?></td>
                            <td><a href="?remove=<?= $item['cart_id'] ?>" class="remove-link" onclick="return confirm('确定删除？')">删除</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="cart-total-label">总计：</td>
                            <td class="cart-total">¥<?= number_format($total, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="btn">更新购物车</button>
                    <a href="checkout.php" class="btn btn-primary">去结算</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> 东莞市腾虎运动器材有限公司</p>
        </div>
    </footer>

    <style>
        .cart-page { padding: 40px 0; }
        .cart-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .cart-table th, .cart-table td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: middle; }
        .cart-product { display: flex; align-items: center; gap: 15px; }
        .cart-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .cart-info a { text-decoration: none; color: #333; font-weight: 500; }
        .cart-spec { font-size: 12px; color: #666; }
        .cart-qty input { width: 60px; padding: 5px; text-align: center; }
        .cart-total { font-size: 18px; font-weight: bold; color: #ff6600; }
        .cart-actions { text-align: right; }
        .btn-primary { background: #ff6600; color: white; }
        .remove-link { color: #ff6600; text-decoration: none; }
    </style>
</body>
</html>