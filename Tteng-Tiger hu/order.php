<?php
session_start();
require_once 'config.php';

// 未登录则跳转
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 获取用户地址列表
$addresses = $conn->query("SELECT * FROM addresses WHERE user_id = $user_id ORDER BY is_default DESC, id DESC");

// 获取购物车数据（如果是从购物车过来）
$cart_items = [];
$total_price = 0;
if (isset($_GET['from_cart']) && $_GET['from_cart'] == 1) {
    $res = $conn->query("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.main_image 
                         FROM cart c 
                         JOIN products p ON c.product_id = p.id 
                         WHERE c.user_id = $user_id");
    while ($row = $res->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total_price += $row['subtotal'];
        $cart_items[] = $row;
    }
    if (empty($cart_items)) {
        header('Location: cart.php');
        exit;
    }
} elseif (isset($_GET['product_id'])) {
    // 直接购买单个商品
    $product_id = intval($_GET['product_id']);
    $res = $conn->query("SELECT * FROM products WHERE id = $product_id");
    $product = $res->fetch_assoc();
    if (!$product) {
        die('商品不存在');
    }
    $cart_items[] = [
        'product_id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => 1,
        'subtotal' => $product['price']
    ];
    $total_price = $product['price'];
} else {
    die('无效的请求');
}

// 处理订单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_order'])) {
    $address_id = intval($_POST['address_id']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    $remark = $conn->real_escape_string($_POST['remark']);
    // 计算运费
    $shipping_fee = 0;
    if (stripos($remark, '顺丰陆运') !== false) $shipping_fee = 30;
    elseif (stripos($remark, '顺丰航空') !== false) $shipping_fee = 50;
    // 获取地址信息
    $addr_res = $conn->query("SELECT * FROM addresses WHERE id = $address_id AND user_id = $user_id");
    if ($addr_res->num_rows == 0) {
        die('请选择有效的地址');
    }
    $addr = $addr_res->fetch_assoc();
    $customer_name = $addr['consignee'];
    $customer_phone = $addr['phone'];
    $customer_address = $addr['province'] . $addr['city'] . $addr['district'] . $addr['detail'];

    // 生成订单号
    $order_no = 'TH' . date('YmdHis') . rand(100, 999);

    // 遍历购物车或单个商品，生成订单（这里简化，一个订单只包含一个商品，因为我们是简单电商）
    // 更复杂的订单需要循环插入多条，但为简化，我们按每个购物车项创建独立订单，或者合并为一个订单。为了简单，这里每个商品生成独立订单。
    $success_count = 0;
    foreach ($cart_items as $item) {
        $product_id = $item['product_id'];
        $product_name = $conn->real_escape_string($item['name']);
        $price = $item['price'];
        $quantity = $item['quantity'];
        $spec_selected = isset($item['spec_selected']) ? $conn->real_escape_string($item['spec_selected']) : '';
        $order_no_item = $order_no . '_' . $product_id; // 保证每个订单号唯一
        $sql = "INSERT INTO orders (order_no, user_id, address_id, product_id, product_name, price, quantity, spec_selected, 
                                    customer_name, customer_phone, customer_address, remark, shipping_fee, payment_method, status)
                VALUES ('$order_no_item', $user_id, $address_id, $product_id, '$product_name', $price, $quantity, '$spec_selected',
                        '$customer_name', '$customer_phone', '$customer_address', '$remark', $shipping_fee, '$payment_method', 'pending')";
        if ($conn->query($sql)) {
            $success_count++;
        }
    }
    if ($success_count > 0) {
        // 清空购物车（如果是购物车结算）
        if (isset($_GET['from_cart'])) {
            $conn->query("DELETE FROM cart WHERE user_id = $user_id");
        }
        $success_msg = "订单已提交！订单号：" . $order_no . "（每个商品生成独立订单，总数量：$success_count）。请保存订单号以便查询。";
    } else {
        $error_msg = "订单提交失败，请重试。";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单结算 - 腾虎运动器材</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo"><img src="uploads/logo-placeholder.png" alt="腾虎"></div>
            <nav><ul><li><a href="index.php">首页</a></li><li><a href="index.php#products">产品中心</a></li><li><a href="index.php#about">关于我们</a></li><li><a href="index.php#contact">联系我们</a></li></ul>
            <div class="nav-buttons"><a href="cart.php">🛒</a><a href="user.php">个人中心</a><a href="logout.php">退出</a></div>
            <div class="menu-toggle">☰</div>
        </div>
    </header>

    <div class="container order-form">
        <h1>订单结算</h1>
        <?php if (isset($success_msg)): ?>
            <div style="background:#d4edda; padding:15px; border-radius:5px;"><?= $success_msg ?></div>
            <p><a href="user.php">查看我的订单</a> | <a href="index.php">继续购物</a></p>
        <?php else: ?>
            <?php if (isset($error_msg)) echo "<p style='color:red'>$error_msg</p>"; ?>
            <form method="post">
                <h3>收货地址</h3>
                <?php if ($addresses->num_rows == 0): ?>
                    <p>您还没有收货地址，请先 <a href="user.php#addresses">添加地址</a>。</p>
                <?php else: ?>
                    <?php while($addr = $addresses->fetch_assoc()): ?>
                    <label>
                        <input type="radio" name="address_id" value="<?= $addr['id'] ?>" <?= $addr['is_default'] ? 'checked' : '' ?> required>
                        <strong><?= htmlspecialchars($addr['consignee']) ?></strong> <?= htmlspecialchars($addr['phone']) ?><br>
                        <?= htmlspecialchars($addr['province'] . $addr['city'] . $addr['district'] . $addr['detail']) ?>
                    </label><br>
                    <?php endwhile; ?>
                <?php endif; ?>
                <h3>商品清单</h3>
                <table class="cart-table">
                    <thead><tr><th>商品</th><th>单价</th><th>数量</th><th>小计</th></tr></thead>
                    <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td>¥<?= number_format($item['price'], 2) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>¥<?= number_format($item['subtotal'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td colspan="3" style="text-align:right">总计：</td><td><strong>¥<?= number_format($total_price, 2) ?></strong></td></tr></tfoot>
                </table>
                <h3>支付方式</h3>
                <select name="payment_method" required>
                    <option value="wechat">微信支付</option>
                    <option value="alipay">支付宝</option>
                </select>
                <h3>备注（选填）</h3>
                <textarea name="remark" rows="2" placeholder="如需顺丰加急，请注明“顺丰陆运+30元”或“顺丰航空+50元”"></textarea>
                <p><small>默认圆通快递，3-5天送达，包邮。顺丰加急额外运费见备注说明。</small></p>
                <button type="submit" name="submit_order" class="btn">提交订单</button>
            </form>
        <?php endif; ?>
    </div>

    <footer><div class="container"><p>&copy; <?= date('Y') ?> 东莞市腾虎运动器材有限公司</p></div></footer>
</body>
</html>