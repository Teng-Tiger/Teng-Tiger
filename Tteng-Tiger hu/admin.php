<?php
session_start();
require_once 'config.php';

// 登录验证
if (isset($_POST['login'])) {
    $password = $_POST['password'];
    if ($password === ADMIN_PASS) {
        $_SESSION['admin'] = true;
    } else {
        $error = "密码错误";
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
if (!isset($_SESSION['admin'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>后台登录</title><link rel="stylesheet" href="css/style.css"></head>
    <body style="background:#f4f4f4; text-align:center; padding-top:100px;">
        <div style="background:white; width:300px; margin:auto; padding:20px; border-radius:10px;">
            <h2>管理员登录</h2>
            <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
            <form method="post">
                <input type="password" name="password" placeholder="密码" style="width:100%; padding:8px; margin:10px 0;">
                <button type="submit" name="login">登录</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ========== 商品管理 ==========
if (isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $spec = $conn->real_escape_string($_POST['spec']);
    $keywords = $conn->real_escape_string($_POST['keywords']);
    $category = $conn->real_escape_string($_POST['category']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $sort_order = intval($_POST['sort_order']);
    $conn->query("INSERT INTO products (name, price, spec, keywords, category, is_featured, sort_order) VALUES ('$name', $price, '$spec', '$keywords', '$category', $is_featured, $sort_order)");
    header('Location: admin.php');
    exit;
}
if (isset($_POST['update_product'])) {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $spec = $conn->real_escape_string($_POST['spec']);
    $keywords = $conn->real_escape_string($_POST['keywords']);
    $category = $conn->real_escape_string($_POST['category']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $sort_order = intval($_POST['sort_order']);
    $conn->query("UPDATE products SET name='$name', price=$price, spec='$spec', keywords='$keywords', category='$category', is_featured=$is_featured, sort_order=$sort_order WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    $conn->query("DELETE FROM products WHERE id=$id");
    header('Location: admin.php');
    exit;
}
// 批量操作
if (isset($_POST['batch_action']) && isset($_POST['product_ids'])) {
    $ids = array_map('intval', $_POST['product_ids']);
    $ids_str = implode(',', $ids);
    if ($_POST['batch_action'] == 'delete') {
        $conn->query("DELETE FROM products WHERE id IN ($ids_str)");
    } elseif ($_POST['batch_action'] == 'feature_on') {
        $conn->query("UPDATE products SET is_featured=1 WHERE id IN ($ids_str)");
    } elseif ($_POST['batch_action'] == 'feature_off') {
        $conn->query("UPDATE products SET is_featured=0 WHERE id IN ($ids_str)");
    }
    header('Location: admin.php');
    exit;
}

// 商品图片上传
if (isset($_FILES['main_image_file']) && $_FILES['main_image_file']['error'] == 0) {
    $id = intval($_POST['product_id']);
    $file = $_FILES['main_image_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($ext, $allowed)) {
        $filename = 'product_' . $id . '_main_' . time() . '.' . $ext;
        $target = 'uploads/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $image_url = SITE_URL . '/' . $target;
            $conn->query("UPDATE products SET main_image='$image_url' WHERE id=$id");
        }
    }
    header('Location: admin.php');
    exit;
}
if (isset($_FILES['detail_images_files']) && count($_FILES['detail_images_files']['name']) > 0) {
    $id = intval($_POST['product_id']);
    $uploaded_urls = [];
    foreach ($_FILES['detail_images_files']['tmp_name'] as $i => $tmp) {
        if ($_FILES['detail_images_files']['error'][$i] == 0) {
            $ext = strtolower(pathinfo($_FILES['detail_images_files']['name'][$i], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $filename = 'product_' . $id . '_detail_' . time() . '_' . $i . '.' . $ext;
                $target = 'uploads/' . $filename;
                if (move_uploaded_file($tmp, $target)) {
                    $uploaded_urls[] = SITE_URL . '/' . $target;
                }
            }
        }
    }
    if (!empty($uploaded_urls)) {
        $existing = $conn->query("SELECT detail_images FROM products WHERE id=$id")->fetch_assoc()['detail_images'];
        $existing_arr = $existing ? explode(',', $existing) : [];
        $all = array_merge($existing_arr, $uploaded_urls);
        $images_str = implode(',', $all);
        $conn->query("UPDATE products SET detail_images='$images_str' WHERE id=$id");
    }
    header('Location: admin.php');
    exit;
}
if (isset($_GET['remove_detail_image'])) {
    $id = intval($_GET['product_id']);
    $url_to_remove = $_GET['remove_detail_image'];
    $res = $conn->query("SELECT detail_images FROM products WHERE id=$id");
    $row = $res->fetch_assoc();
    $arr = explode(',', $row['detail_images']);
    $arr = array_filter($arr, function($u) use ($url_to_remove) { return $u !== $url_to_remove; });
    $new_str = implode(',', $arr);
    $conn->query("UPDATE products SET detail_images='$new_str' WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['delete_main_image'])) {
    $id = intval($_GET['product_id']);
    $conn->query("UPDATE products SET main_image=NULL WHERE id=$id");
    header('Location: admin.php');
    exit;
}

// ========== 公告管理 ==========
if (isset($_POST['add_announcement'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $conn->query("INSERT INTO announcements (title, content) VALUES ('$title', '$content')");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['delete_announcement'])) {
    $id = intval($_GET['delete_announcement']);
    $conn->query("DELETE FROM announcements WHERE id=$id");
    header('Location: admin.php');
    exit;
}

// ========== 客服消息 ==========
if (isset($_POST['reply_message'])) {
    $msg_id = intval($_POST['msg_id']);
    $reply = $conn->real_escape_string($_POST['reply']);
    $conn->query("UPDATE messages SET reply='$reply', status=1 WHERE id=$msg_id");
    header('Location: admin.php');
    exit;
}

// ========== 订单管理 ==========
if (isset($_POST['update_order'])) {
    $id = intval($_POST['order_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $tracking_no = $conn->real_escape_string($_POST['tracking_no']);
    $conn->query("UPDATE orders SET status='$status', tracking_no='$tracking_no' WHERE id=$id");
    header('Location: admin.php');
    exit;
}

// ========== 退换货管理 ==========
if (isset($_GET['approve_return'])) {
    $id = intval($_GET['approve_return']);
    $conn->query("UPDATE returns SET status='approved' WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['reject_return'])) {
    $id = intval($_GET['reject_return']);
    $conn->query("UPDATE returns SET status='rejected' WHERE id=$id");
    header('Location: admin.php');
    exit;
}

// ========== 评论管理 ==========
if (isset($_POST['reply_review'])) {
    $review_id = intval($_POST['review_id']);
    $reply = $conn->real_escape_string($_POST['reply']);
    $conn->query("UPDATE reviews SET reply='$reply' WHERE id=$review_id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['hide_review'])) {
    $id = intval($_GET['hide_review']);
    $conn->query("UPDATE reviews SET status=0 WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['show_review'])) {
    $id = intval($_GET['show_review']);
    $conn->query("UPDATE reviews SET status=1 WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['delete_review'])) {
    $id = intval($_GET['delete_review']);
    $conn->query("DELETE FROM reviews WHERE id=$id");
    header('Location: admin.php');
    exit;
}

// ========== 用户管理 ==========
if (isset($_GET['disable_user'])) {
    $id = intval($_GET['disable_user']);
    $conn->query("UPDATE users SET status=0 WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['enable_user'])) {
    $id = intval($_GET['enable_user']);
    $conn->query("UPDATE users SET status=1 WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['reset_password'])) {
    $id = intval($_GET['reset_password']);
    $new_pass = password_hash('123456', PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password='$new_pass' WHERE id=$id");
    header('Location: admin.php');
    exit;
}

// ========== 内容管理（轮播图、品牌、工厂实拍、菜单） ==========
// 轮播图
if (isset($_POST['add_carousel'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $image = $conn->real_escape_string($_POST['image']);
    $link = $conn->real_escape_string($_POST['link']);
    $sort = intval($_POST['sort']);
    $conn->query("INSERT INTO carousels (title, image, link, sort) VALUES ('$title', '$image', '$link', $sort)");
    header('Location: admin.php');
    exit;
}
if (isset($_POST['update_carousel'])) {
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string($_POST['title']);
    $image = $conn->real_escape_string($_POST['image']);
    $link = $conn->real_escape_string($_POST['link']);
    $sort = intval($_POST['sort']);
    $status = isset($_POST['status']) ? 1 : 0;
    $conn->query("UPDATE carousels SET title='$title', image='$image', link='$link', sort=$sort, status=$status WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['delete_carousel'])) {
    $id = intval($_GET['delete_carousel']);
    $conn->query("DELETE FROM carousels WHERE id=$id");
    header('Location: admin.php');
    exit;
}
// 合作品牌
if (isset($_POST['add_brand'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $logo = $conn->real_escape_string($_POST['logo']);
    $link = $conn->real_escape_string($_POST['link']);
    $sort = intval($_POST['sort']);
    $conn->query("INSERT INTO brands (name, logo, link, sort) VALUES ('$name', '$logo', '$link', $sort)");
    header('Location: admin.php');
    exit;
}
if (isset($_POST['update_brand'])) {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $logo = $conn->real_escape_string($_POST['logo']);
    $link = $conn->real_escape_string($_POST['link']);
    $sort = intval($_POST['sort']);
    $status = isset($_POST['status']) ? 1 : 0;
    $conn->query("UPDATE brands SET name='$name', logo='$logo', link='$link', sort=$sort, status=$status WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['delete_brand'])) {
    $id = intval($_GET['delete_brand']);
    $conn->query("DELETE FROM brands WHERE id=$id");
    header('Location: admin.php');
    exit;
}
// 工厂实拍
if (isset($_POST['add_factory_image'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $image = $conn->real_escape_string($_POST['image']);
    $sort = intval($_POST['sort']);
    $conn->query("INSERT INTO factory_images (title, image, sort) VALUES ('$title', '$image', $sort)");
    header('Location: admin.php');
    exit;
}
if (isset($_POST['update_factory_image'])) {
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string($_POST['title']);
    $image = $conn->real_escape_string($_POST['image']);
    $sort = intval($_POST['sort']);
    $status = isset($_POST['status']) ? 1 : 0;
    $conn->query("UPDATE factory_images SET title='$title', image='$image', sort=$sort, status=$status WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['delete_factory_image'])) {
    $id = intval($_GET['delete_factory_image']);
    $conn->query("DELETE FROM factory_images WHERE id=$id");
    header('Location: admin.php');
    exit;
}
// 菜单
if (isset($_POST['add_menu'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $link = $conn->real_escape_string($_POST['link']);
    $parent_id = intval($_POST['parent_id']);
    $sort = intval($_POST['sort']);
    $conn->query("INSERT INTO menus (name, link, parent_id, sort) VALUES ('$name', '$link', $parent_id, $sort)");
    header('Location: admin.php');
    exit;
}
if (isset($_POST['update_menu'])) {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $link = $conn->real_escape_string($_POST['link']);
    $parent_id = intval($_POST['parent_id']);
    $sort = intval($_POST['sort']);
    $status = isset($_POST['status']) ? 1 : 0;
    $conn->query("UPDATE menus SET name='$name', link='$link', parent_id=$parent_id, sort=$sort, status=$status WHERE id=$id");
    header('Location: admin.php');
    exit;
}
if (isset($_GET['delete_menu'])) {
    $id = intval($_GET['delete_menu']);
    $conn->query("DELETE FROM menus WHERE id=$id");
    header('Location: admin.php');
    exit;
}

// ========== 网站设置 ==========
if (isset($_POST['update_settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $key = $conn->real_escape_string($key);
        $value = $conn->real_escape_string($value);
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value') ON DUPLICATE KEY UPDATE setting_value='$value'");
    }
    header('Location: admin.php');
    exit;
}

// 获取所有数据
$products = $conn->query("SELECT * FROM products ORDER BY sort_order ASC, id DESC");
$messages = $conn->query("SELECT * FROM messages ORDER BY id DESC");
$announcements = $conn->query("SELECT * FROM announcements ORDER BY id DESC");
$orders = $conn->query("SELECT * FROM orders ORDER BY id DESC");
$returns = $conn->query("SELECT * FROM returns ORDER BY id DESC");
$reviews = $conn->query("SELECT * FROM reviews ORDER BY id DESC");
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
$carousels = $conn->query("SELECT * FROM carousels ORDER BY sort ASC");
$brands = $conn->query("SELECT * FROM brands ORDER BY sort ASC");
$factory_images = $conn->query("SELECT * FROM factory_images ORDER BY sort ASC");
$menus = $conn->query("SELECT * FROM menus ORDER BY parent_id ASC, sort ASC");
$settings = [];
$res = $conn->query("SELECT setting_key, setting_value FROM settings");
while($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>腾虎后台管理</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-container { max-width:1400px; margin:20px auto; background:white; padding:20px; border-radius:10px; }
        .admin-section { margin-bottom:40px; border-bottom:1px solid #eee; padding-bottom:20px; }
        table { width:100%; border-collapse:collapse; }
        th, td { border:1px solid #ddd; padding:8px; text-align:left; }
        th { background:#f2f2f2; }
        .image-preview { max-width:100px; max-height:100px; }
        .upload-form { margin-top:5px; }
        .upload-form input[type="text"] { width:80%; padding:4px; }
        .upload-form button { padding:4px 8px; }
        .product-form, .item-form { background:#f9f9f9; padding:15px; margin-bottom:20px; border-radius:5px; }
        .product-form input, .product-form textarea, .item-form input { margin-bottom:10px; width:100%; padding:8px; }
        .batch-form { margin-bottom:15px; }
        .tabs { display:flex; gap:10px; margin-bottom:20px; border-bottom:1px solid #ddd; }
        .tab-btn { background:#f5f5f5; border:none; padding:10px 20px; cursor:pointer; }
        .tab-btn.active { background:#ff6600; color:white; }
        .tab-content { display:none; }
        .tab-content.active { display:block; }
    </style>
</head>
<body>
<div class="admin-container">
    <h1>腾虎后台管理 <a href="?logout=1" style="float:right; font-size:14px;">退出登录</a></h1>

    <!-- 统计看板 -->
    <div class="admin-section">
        <h2>数据看板</h2>
        <div style="display:flex; gap:20px; flex-wrap:wrap;">
            <div style="background:#f5f5f5; padding:15px; border-radius:8px; flex:1; text-align:center;">
                <div style="font-size:28px;"><?= $products->num_rows ?></div>
                <div>商品总数</div>
            </div>
            <div style="background:#f5f5f5; padding:15px; border-radius:8px; flex:1; text-align:center;">
                <div style="font-size:28px;"><?= $orders->num_rows ?></div>
                <div>订单总数</div>
            </div>
            <div style="background:#f5f5f5; padding:15px; border-radius:8px; flex:1; text-align:center;">
                <div style="font-size:28px;"><?= $users->num_rows ?></div>
                <div>用户总数</div>
            </div>
        </div>
    </div>

    <!-- 网站设置 -->
    <div class="admin-section">
        <h2>网站设置</h2>
        <form method="post">
            <table>
                <?php
                $setting_fields = [
                    'site_name' => '网站名称',
                    'hero_title' => '主标题',
                    'hero_subtitle' => '副标题',
                    'hero_image' => '备用主图URL',
                    'company_intro' => '公司介绍',
                    'contact_phone' => '联系电话',
                    'contact_email' => '联系邮箱',
                    'auto_reply' => '自动回复内容',
                    'douyin_live_url' => '抖音直播间/主页链接',
                    'live_cover' => '直播间封面图URL',
                    'live_title' => '直播标题',
                    'live_host' => '主播名',
                    'live_chat_code' => '第三方客服代码'
                ];
                foreach ($setting_fields as $key => $label): ?>
                <tr>
                    <td style="width:150px"><?= $label ?></td>
                    <td>
                        <?php if ($key == 'company_intro' || $key == 'auto_reply' || $key == 'live_chat_code'): ?>
                            <textarea name="settings[<?= $key ?>]" rows="3" style="width:100%"><?= htmlspecialchars($settings[$key] ?? '') ?></textarea>
                        <?php else: ?>
                            <input type="text" name="settings[<?= $key ?>]" value="<?= htmlspecialchars($settings[$key] ?? '') ?>" style="width:100%">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <button type="submit" name="update_settings">保存设置</button>
        </form>
    </div>

    <!-- 标签页管理（商品、内容等） -->
    <div class="tabs">
        <button class="tab-btn active" data-tab="product">商品管理</button>
        <button class="tab-btn" data-tab="order">订单管理</button>
        <button class="tab-btn" data-tab="user">用户管理</button>
        <button class="tab-btn" data-tab="content">内容管理</button>
        <button class="tab-btn" data-tab="review">评论管理</button>
        <button class="tab-btn" data-tab="message">客服消息</button>
        <button class="tab-btn" data-tab="return">退换货</button>
    </div>

    <!-- 商品管理标签页 -->
    <div id="product" class="tab-content active">
        <div class="product-form">
            <h3>添加新商品</h3>
            <form method="post">
                <input type="text" name="name" placeholder="商品名称" required>
                <input type="text" name="price" placeholder="价格" required>
                <textarea name="spec" rows="2" placeholder="规格参数"></textarea>
                <input type="text" name="keywords" placeholder="关键词，逗号分隔（如：双排,花样）">
                <input type="text" name="category" placeholder="分类（双排轮滑鞋/单排轮滑鞋/配件）">
                <label><input type="checkbox" name="is_featured"> 设为爆款</label>
                <input type="text" name="sort_order" placeholder="排序值（越小越靠前）" value="0">
                <button type="submit" name="add_product">添加商品</button>
            </form>
        </div>
        <form method="post" class="batch-form">
            <label>批量操作：</label>
            <select name="batch_action">
                <option value="delete">删除选中</option>
                <option value="feature_on">设为爆款</option>
                <option value="feature_off">取消爆款</option>
            </select>
            <button type="submit">执行</button>
            <table>
                <tr><th><input type="checkbox" id="selectAll"></th><th>ID</th><th>名称</th><th>价格</th><th>关键词</th><th>分类</th><th>爆款</th><th>排序</th><th>主图</th><th>详情图</th><th>操作</th></tr>
                <?php while($p = $products->fetch_assoc()): ?>
                <tr>
                    <td><input type="checkbox" name="product_ids[]" value="<?= $p['id'] ?>"></td>
                    <td><?= $p['id'] ?></td>
                    <td>
                        <form method="post" style="display:inline-block">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" style="width:200px">
                            <input type="text" name="price" value="<?= $p['price'] ?>" style="width:80px">
                            <textarea name="spec" rows="1" style="width:150px"><?= htmlspecialchars($p['spec']) ?></textarea>
                            <input type="text" name="keywords" value="<?= htmlspecialchars($p['keywords']) ?>" style="width:120px">
                            <input type="text" name="category" value="<?= htmlspecialchars($p['category']) ?>" style="width:100px">
                            <label><input type="checkbox" name="is_featured" <?= $p['is_featured'] ? 'checked' : '' ?>> 爆款</label>
                            <input type="text" name="sort_order" value="<?= $p['sort_order'] ?>" style="width:50px">
                            <button type="submit" name="update_product">更新</button>
                            <a href="?delete_product=<?= $p['id'] ?>" onclick="return confirm('确定删除？')">删除</a>
                        </form>
                    </td>
                    <td><?= $p['price'] ?></td>
                    <td><?= $p['keywords'] ?></td>
                    <td><?= $p['category'] ?></td>
                    <td><?= $p['is_featured'] ? '是' : '否' ?></td>
                    <td><?= $p['sort_order'] ?></td>
                    <td>
                        <?php if($p['main_image']): ?>
                            <img src="<?= htmlspecialchars($p['main_image']) ?>" class="image-preview"><br>
                            <a href="?delete_main_image=<?= $p['id'] ?>&product_id=<?= $p['id'] ?>" onclick="return confirm('删除主图？')">删除主图</a>
                        <?php endif; ?>
                        <form method="post" enctype="multipart/form-data" class="upload-form">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="file" name="main_image_file" accept="image/*">
                            <button type="submit" name="update_main_image_submit">上传主图</button>
                        </form>
                    </td>
                    <td>
                        <?php 
                            $detail_arr = $p['detail_images'] ? explode(',', $p['detail_images']) : [];
                            foreach($detail_arr as $img): ?>
                                <img src="<?= htmlspecialchars($img) ?>" class="image-preview" style="max-width:50px;"><br>
                                <a href="?remove_detail_image=<?= urlencode($img) ?>&product_id=<?= $p['id'] ?>" onclick="return confirm('删除此详情图？')">删除</a><br>
                        <?php endforeach; ?>
                        <form method="post" enctype="multipart/form-data" class="upload-form">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="file" name="detail_images_files[]" multiple accept="image/*">
                            <button type="submit" name="update_detail_images_submit">上传详情图</button>
                        </form>
                    </td>
                    <td><a href="?delete_product=<?= $p['id'] ?>" onclick="return confirm('确定删除？')">删除</a></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </form>
    </div>

    <!-- 订单管理标签页 -->
    <div id="order" class="tab-content">
        <table>
            <tr><th>订单号</th><th>商品</th><th>数量</th><th>金额</th><th>姓名</th><th>电话</th><th>地址</th><th>备注</th><th>运费</th><th>快递单号</th><th>状态</th><th>操作</th></tr>
            <?php while($order = $orders->fetch_assoc()): ?>
            <tr>
                <td><?= $order['order_no'] ?></td>
                <td><?= htmlspecialchars($order['product_name']) ?></td>
                <td><?= $order['quantity'] ?></td>
                <td>¥<?= $order['price'] ?></td>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                <td><?= htmlspecialchars($order['customer_address']) ?></td>
                <td><?= htmlspecialchars($order['remark']) ?></td>
                <td><?= $order['shipping_fee'] ?></td>
                <td>
                    <form method="post" style="display:inline">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="text" name="tracking_no" value="<?= htmlspecialchars($order['tracking_no']) ?>" placeholder="快递单号">
                        <select name="status">
                            <option value="pending" <?= $order['status']=='pending'?'selected':'' ?>>待支付</option>
                            <option value="paid" <?= $order['status']=='paid'?'selected':'' ?>>已支付</option>
                            <option value="shipped" <?= $order['status']=='shipped'?'selected':'' ?>>已发货</option>
                            <option value="completed" <?= $order['status']=='completed'?'selected':'' ?>>已完成</option>
                            <option value="cancelled" <?= $order['status']=='cancelled'?'selected':'' ?>>已取消</option>
                        </select>
                        <button type="submit" name="update_order">更新</button>
                    </form>
                </td>
                <td><?= $order['status'] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- 用户管理标签页 -->
    <div id="user" class="tab-content">
        <table>
            <tr><th>ID</th><th>邮箱</th><th>昵称</th><th>状态</th><th>邮箱验证</th><th>注册时间</th><th>操作</th></tr>
            <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['nickname']) ?></td>
                <td><?= $u['status'] ? '启用' : '禁用' ?></td>
                <td><?= $u['email_verified'] ? '已验证' : '未验证' ?></td>
                <td><?= $u['created_at'] ?></td>
                <td>
                    <?php if($u['status']): ?>
                        <a href="?disable_user=<?= $u['id'] ?>" onclick="return confirm('禁用此用户？')">禁用</a>
                    <?php else: ?>
                        <a href="?enable_user=<?= $u['id'] ?>" onclick="return confirm('启用此用户？')">启用</a>
                    <?php endif; ?>
                    <a href="?reset_password=<?= $u['id'] ?>" onclick="return confirm('重置密码为123456？')">重置密码</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- 内容管理标签页（轮播图、品牌、工厂实拍、菜单） -->
    <div id="content" class="tab-content">
        <!-- 轮播图管理 -->
        <h3>轮播图管理</h3>
        <div class="item-form">
            <form method="post">
                <input type="text" name="title" placeholder="标题">
                <input type="text" name="image" placeholder="图片URL">
                <input type="text" name="link" placeholder="链接（可选）">
                <input type="text" name="sort" placeholder="排序" value="0">
                <button type="submit" name="add_carousel">添加轮播图</button>
            </form>
        </div>
        <table>
            <tr><th>ID</th><th>标题</th><th>图片</th><th>链接</th><th>排序</th><th>状态</th><th>操作</th></tr>
            <?php while($c = $carousels->fetch_assoc()): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><input type="text" form="update_carousel_<?= $c['id'] ?>" name="title" value="<?= htmlspecialchars($c['title']) ?>"></td>
                <td><input type="text" form="update_carousel_<?= $c['id'] ?>" name="image" value="<?= htmlspecialchars($c['image']) ?>" style="width:200px"></td>
                <td><input type="text" form="update_carousel_<?= $c['id'] ?>" name="link" value="<?= htmlspecialchars($c['link']) ?>"></td>
                <td><input type="text" form="update_carousel_<?= $c['id'] ?>" name="sort" value="<?= $c['sort'] ?>" style="width:50px"></td>
                <td><input type="checkbox" form="update_carousel_<?= $c['id'] ?>" name="status" <?= $c['status'] ? 'checked' : '' ?>> 显示</td>
                <td>
                    <form id="update_carousel_<?= $c['id'] ?>" method="post" style="display:inline">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" name="update_carousel">更新</button>
                    </form>
                    <a href="?delete_carousel=<?= $c['id'] ?>" onclick="return confirm('删除？')">删除</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h3>合作品牌</h3>
        <div class="item-form">
            <form method="post">
                <input type="text" name="name" placeholder="品牌名称">
                <input type="text" name="logo" placeholder="Logo URL">
                <input type="text" name="link" placeholder="官网链接">
                <input type="text" name="sort" placeholder="排序" value="0">
                <button type="submit" name="add_brand">添加品牌</button>
            </form>
        </div>
        <table>
            <tr><th>ID</th><th>名称</th><th>Logo</th><th>链接</th><th>排序</th><th>状态</th><th>操作</th></tr>
            <?php while($b = $brands->fetch_assoc()): ?>
            <tr>
                <td><?= $b['id'] ?></td>
                <td><input type="text" form="update_brand_<?= $b['id'] ?>" name="name" value="<?= htmlspecialchars($b['name']) ?>"></td>
                <td><input type="text" form="update_brand_<?= $b['id'] ?>" name="logo" value="<?= htmlspecialchars($b['logo']) ?>" style="width:200px"></td>
                <td><input type="text" form="update_brand_<?= $b['id'] ?>" name="link" value="<?= htmlspecialchars($b['link']) ?>"></td>
                <td><input type="text" form="update_brand_<?= $b['id'] ?>" name="sort" value="<?= $b['sort'] ?>" style="width:50px"></td>
                <td><input type="checkbox" form="update_brand_<?= $b['id'] ?>" name="status" <?= $b['status'] ? 'checked' : '' ?>> 显示</td>
                <td>
                    <form id="update_brand_<?= $b['id'] ?>" method="post" style="display:inline">
                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                        <button type="submit" name="update_brand">更新</button>
                    </form>
                    <a href="?delete_brand=<?= $b['id'] ?>" onclick="return confirm('删除？')">删除</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h3>工厂实拍/资质</h3>
        <div class="item-form">
            <form method="post">
                <input type="text" name="title" placeholder="标题">
                <input type="text" name="image" placeholder="图片URL">
                <input type="text" name="sort" placeholder="排序" value="0">
                <button type="submit" name="add_factory_image">添加工厂图</button>
            </form>
        </div>
        <table>
            <tr><th>ID</th><th>标题</th><th>图片</th><th>排序</th><th>状态</th><th>操作</th></tr>
            <?php while($f = $factory_images->fetch_assoc()): ?>
            <tr>
                <td><?= $f['id'] ?></td>
                <td><input type="text" form="update_factory_<?= $f['id'] ?>" name="title" value="<?= htmlspecialchars($f['title']) ?>"></td>
                <td><input type="text" form="update_factory_<?= $f['id'] ?>" name="image" value="<?= htmlspecialchars($f['image']) ?>" style="width:200px"></td>
                <td><input type="text" form="update_factory_<?= $f['id'] ?>" name="sort" value="<?= $f['sort'] ?>" style="width:50px"></td>
                <td><input type="checkbox" form="update_factory_<?= $f['id'] ?>" name="status" <?= $f['status'] ? 'checked' : '' ?>> 显示</td>
                <td>
                    <form id="update_factory_<?= $f['id'] ?>" method="post" style="display:inline">
                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                        <button type="submit" name="update_factory_image">更新</button>
                    </form>
                    <a href="?delete_factory_image=<?= $f['id'] ?>" onclick="return confirm('删除？')">删除</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h3>导航菜单</h3>
        <div class="item-form">
            <form method="post">
                <input type="text" name="name" placeholder="菜单名称">
                <input type="text" name="link" placeholder="链接">
                <select name="parent_id">
                    <option value="0">作为一级菜单</option>
                    <?php
                    $parent_res = $conn->query("SELECT * FROM menus WHERE parent_id=0 ORDER BY sort ASC");
                    while($parent = $parent_res->fetch_assoc()): ?>
                        <option value="<?= $parent['id'] ?>">作为 <?= htmlspecialchars($parent['name']) ?> 的子菜单</option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="sort" placeholder="排序" value="0">
                <button type="submit" name="add_menu">添加菜单</button>
            </form>
        </div>
        <table>
            <tr><th>ID</th><th>名称</th><th>链接</th><th>父级</th><th>排序</th><th>状态</th><th>操作</th></tr>
            <?php while($m = $menus->fetch_assoc()): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><input type="text" form="update_menu_<?= $m['id'] ?>" name="name" value="<?= htmlspecialchars($m['name']) ?>"></td>
                <td><input type="text" form="update_menu_<?= $m['id'] ?>" name="link" value="<?= htmlspecialchars($m['link']) ?>"></td>
                <td>
                    <select form="update_menu_<?= $m['id'] ?>" name="parent_id">
                        <option value="0" <?= $m['parent_id']==0 ? 'selected' : '' ?>>一级菜单</option>
                        <?php
                        $parent_res2 = $conn->query("SELECT * FROM menus WHERE parent_id=0 AND id!={$m['id']} ORDER BY sort ASC");
                        while($parent2 = $parent_res2->fetch_assoc()): ?>
                            <option value="<?= $parent2['id'] ?>" <?= $m['parent_id']==$parent2['id'] ? 'selected' : '' ?>><?= htmlspecialchars($parent2['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </td>
                <td><input type="text" form="update_menu_<?= $m['id'] ?>" name="sort" value="<?= $m['sort'] ?>" style="width:50px"></td>
                <td><input type="checkbox" form="update_menu_<?= $m['id'] ?>" name="status" <?= $m['status'] ? 'checked' : '' ?>> 显示</td>
                <td>
                    <form id="update_menu_<?= $m['id'] ?>" method="post" style="display:inline">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button type="submit" name="update_menu">更新</button>
                    </form>
                    <a href="?delete_menu=<?= $m['id'] ?>" onclick="return confirm('删除？')">删除</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- 评论管理标签页 -->
    <div id="review" class="tab-content">
        <table>
            <tr><th>时间</th><th>商品ID</th><th>昵称</th><th>评分</th><th>评论</th><th>管理员回复</th><th>状态</th><th>操作</th></tr>
            <?php while($rev = $reviews->fetch_assoc()): ?>
            <tr>
                <td><?= $rev['created_at'] ?></td>
                <td><?= $rev['product_id'] ?></td>
                <td><?= htmlspecialchars($rev['name']) ?></td>
                <td><?= $rev['rating'] ?>星</td>
                <td><?= nl2br(htmlspecialchars($rev['content'])) ?></td>
                <td>
                    <?php if($rev['reply']): ?>
                        <?= nl2br(htmlspecialchars($rev['reply'])) ?>
                    <?php endif; ?>
                    <form method="post">
                        <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                        <input type="text" name="reply" placeholder="回复内容">
                        <button type="submit" name="reply_review">回复</button>
                    </form>
                </td>
                <td><?= $rev['status'] ? '显示' : '隐藏' ?></td>
                <td>
                    <?php if($rev['status']): ?>
                        <a href="?hide_review=<?= $rev['id'] ?>">隐藏</a>
                    <?php else: ?>
                        <a href="?show_review=<?= $rev['id'] ?>">显示</a>
                    <?php endif; ?>
                    <a href="?delete_review=<?= $rev['id'] ?>" onclick="return confirm('删除评论？')">删除</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- 客服消息标签页 -->
    <div id="message" class="tab-content">
        <table>
            <tr><th>时间</th><th>姓名</th><th>邮箱</th><th>留言</th><th>回复</th><th>操作</th></tr>
            <?php while($msg = $messages->fetch_assoc()): ?>
            <tr>
                <td><?= $msg['created_at'] ?></td>
                <td><?= htmlspecialchars($msg['name']) ?></td>
                <td><?= htmlspecialchars($msg['email']) ?></td>
                <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                <td><?= htmlspecialchars($msg['reply']) ?></td>
                <td>
                    <?php if(!$msg['reply']): ?>
                    <form method="post">
                        <input type="hidden" name="msg_id" value="<?= $msg['id'] ?>">
                        <input type="text" name="reply" placeholder="回复内容">
                        <button type="submit" name="reply_message">回复</button>
                    </form>
                    <?php else: ?>
                    已回复
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- 退换货标签页 -->
    <div id="return" class="tab-content">
        <table>
            <tr><th>ID</th><th>订单号</th><th>商品</th><th>原因</th><th>状态</th><th>操作</th></tr>
            <?php while($ret = $returns->fetch_assoc()): ?>
            <tr>
                <td><?= $ret['id'] ?></td>
                <td><?= htmlspecialchars($ret['order_no']) ?></td>
                <td><?= htmlspecialchars($ret['product_name']) ?></td>
                <td><?= htmlspecialchars($ret['reason']) ?></td>
                <td><?= $ret['status'] ?></td>
                <td>
                    <?php if($ret['status'] == 'pending'): ?>
                        <a href="?approve_return=<?= $ret['id'] ?>">同意</a> |
                        <a href="?reject_return=<?= $ret['id'] ?>">拒绝</a>
                    <?php else: ?>
                        已处理
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
<script>
    // 标签页切换
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.getAttribute('data-tab');
            tabs.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            contents.forEach(c => c.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        });
    });
    // 全选复选框
    document.getElementById('selectAll')?.addEventListener('click', function(e) {
        document.querySelectorAll('input[name="product_ids[]"]').forEach(cb => cb.checked = e.target.checked);
    });
</script>
</body>
</html>