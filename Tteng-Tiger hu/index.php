<?php
require_once 'config.php';

// 获取网站设置
$settings = [];
$res = $conn->query("SELECT setting_key, setting_value FROM settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// 获取轮播图（首页大轮播）
$carousels = [];
$res = $conn->query("SELECT * FROM carousels WHERE status=1 ORDER BY sort ASC");
while ($row = $res->fetch_assoc()) {
    $carousels[] = $row;
}

// 获取爆款商品
$featured = [];
$res = $conn->query("SELECT * FROM products WHERE is_featured=1 ORDER BY sort_order ASC, id DESC LIMIT 8");
while ($row = $res->fetch_assoc()) {
    // 获取平均评分
    $avg_rating = $conn->query("SELECT AVG(rating) as avg FROM reviews WHERE product_id={$row['id']} AND status=1")->fetch_assoc()['avg'];
    $row['avg_rating'] = round($avg_rating ?: 0, 1);
    $featured[] = $row;
}

// 获取合作品牌
$brands = [];
$res = $conn->query("SELECT * FROM brands WHERE status=1 ORDER BY sort ASC");
while ($row = $res->fetch_assoc()) {
    $brands[] = $row;
}

// 获取工厂实拍/资质
$factory_images = [];
$res = $conn->query("SELECT * FROM factory_images WHERE status=1 ORDER BY sort ASC");
while ($row = $res->fetch_assoc()) {
    $factory_images[] = $row;
}

// 获取精选评价（从评论表取4条）
$testimonials = [];
$res = $conn->query("SELECT * FROM reviews WHERE status=1 ORDER BY id DESC LIMIT 4");
while ($row = $res->fetch_assoc()) {
    $testimonials[] = $row;
}

// 获取公告
$announcements = [];
$res = $conn->query("SELECT * FROM announcements ORDER BY id DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    $announcements[] = $row;
}

// 获取导航菜单（二级）
$menus = [];
$res = $conn->query("SELECT * FROM menus WHERE status=1 ORDER BY sort ASC");
$menu_list = [];
while ($row = $res->fetch_assoc()) {
    if ($row['parent_id'] == 0) {
        $menu_list[$row['id']] = $row;
        $menu_list[$row['id']]['children'] = [];
    } else {
        $menu_list[$row['parent_id']]['children'][] = $row;
    }
}

// 获取分类筛选（用于全部产品区，这里简单传参数）
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// 构建查询条件
$where = "WHERE 1=1";
if ($category_filter && $category_filter != 'all') {
    $where .= " AND category = '" . $conn->real_escape_string($category_filter) . "'";
}
if ($price_range) {
    if ($price_range == '0-500') $where .= " AND price BETWEEN 0 AND 500";
    elseif ($price_range == '500-1000') $where .= " AND price BETWEEN 500 AND 1000";
    elseif ($price_range == '1000-99999') $where .= " AND price >= 1000";
}
if ($keyword) {
    $kw = $conn->real_escape_string($keyword);
    $where .= " AND (name LIKE '%$kw%' OR keywords LIKE '%$kw%')";
}
$order = "ORDER BY id DESC";
if ($sort == 'price_asc') $order = "ORDER BY price ASC";
elseif ($sort == 'price_desc') $order = "ORDER BY price DESC";

$count_sql = "SELECT COUNT(*) as total FROM products $where";
$total_res = $conn->query($count_sql);
$total = $total_res->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$sql = "SELECT * FROM products $where $order LIMIT $offset, $limit";
$products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// 获取当前用户登录状态
$user = null;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $user_res = $conn->query("SELECT * FROM users WHERE id=$uid");
    if ($user_res->num_rows) $user = $user_res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($settings['site_name'] ?? '腾虎运动器材') ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <img src="uploads/logo-placeholder.png" alt="<?= htmlspecialchars($settings['site_name'] ?? '腾虎') ?>" onerror="this.src='https://placehold.co/200x80?text=腾虎'">
            </div>
            <nav>
                <ul>
                    <?php foreach ($menu_list as $menu): ?>
                        <?php if (empty($menu['children'])): ?>
                            <li><a href="<?= htmlspecialchars($menu['link']) ?>"><?= htmlspecialchars($menu['name']) ?></a></li>
                        <?php else: ?>
                            <li class="has-submenu">
                                <a href="<?= htmlspecialchars($menu['link']) ?>"><?= htmlspecialchars($menu['name']) ?></a>
                                <ul class="submenu">
                                    <?php foreach ($menu['children'] as $child): ?>
                                        <li><a href="<?= htmlspecialchars($child['link']) ?>"><?= htmlspecialchars($child['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <div class="nav-buttons">
                    <?php if ($user): ?>
                        <a href="user.php" class="user-avatar">
                            <img src="<?= htmlspecialchars($user['avatar'] ?: 'https://placehold.co/40x40?text=头像') ?>" class="avatar-sm">
                            <span><?= htmlspecialchars($user['nickname'] ?: $user['email']) ?></span>
                        </a>
                        <a href="cart.php" class="cart-icon">🛒</a>
                        <a href="logout.php">退出</a>
                    <?php else: ?>
                        <a href="login.php">登录</a>
                        <a href="register.php">注册</a>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars($settings['douyin_live_url'] ?? '#') ?>" target="_blank" class="btn-live">进入直播间</a>
                </div>
            </nav>
            <div class="menu-toggle">☰</div>
        </div>
    </header>

    <!-- Hero 区（含轮播图） -->
    <div class="hero-slider">
        <?php if (!empty($carousels)): ?>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php foreach ($carousels as $slide): ?>
                <div class="swiper-slide" style="background-image: url('<?= htmlspecialchars($slide['image']) ?>');">
                    <div class="slide-content">
                        <h2><?= htmlspecialchars($slide['title']) ?></h2>
                        <?php if ($slide['link'] && $slide['link'] != '#'): ?>
                        <a href="<?= htmlspecialchars($slide['link']) ?>" class="btn">查看详情</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        <?php else: ?>
        <div class="hero-bg" style="background-image: url('<?= htmlspecialchars($settings['hero_image'] ?? 'https://placehold.co/1920x600?text=轮滑运动特写') ?>');">
            <div class="container">
                <h1><?= htmlspecialchars($settings['hero_title'] ?? '专业轮滑装备制造商') ?></h1>
                <p><?= htmlspecialchars($settings['hero_subtitle'] ?? '腾虎运动器材 · 品质成就未来') ?></p>
                <a href="#products" class="btn">查看产品</a>
                <a href="<?= htmlspecialchars($settings['douyin_live_url'] ?? '#') ?>" target="_blank" class="btn btn-live">进入直播间</a>
            </div>
        </div>
        <?php endif; ?>
        <!-- 公告区融合在Hero右侧（通过CSS定位） -->
        <?php if (!empty($announcements)): ?>
        <div class="hero-announcements">
            <div class="ann-scroll-vertical">
                <?php foreach ($announcements as $ann): ?>
                <div class="ann-item-vertical">
                    <span class="ann-title">📢 <?= htmlspecialchars($ann['title']) ?></span>
                    <span class="ann-content"><?= htmlspecialchars(mb_substr($ann['content'], 0, 40)) ?>...</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- 核心优势区 -->
    <section class="company-strength">
        <div class="container">
            <h2>核心优势</h2>
            <div class="strength-grid">
                <div class="strength-item"><div class="strength-number">6000 m²</div><div class="strength-label">现代化厂房</div></div>
                <div class="strength-item"><div class="strength-number">20+</div><div class="strength-label">专利证书</div></div>
                <div class="strength-item"><div class="strength-number">20+</div><div class="strength-label">出口国家</div></div>
                <div class="strength-item"><div class="strength-number">96%</div><div class="strength-label">客户满意率</div></div>
            </div>
        </div>
    </section>

    <!-- 合作品牌墙 -->
    <?php if (!empty($brands)): ?>
    <section class="brands">
        <div class="container">
            <h2>合作品牌</h2>
            <div class="brands-scroll">
                <?php foreach ($brands as $brand): ?>
                <a href="<?= htmlspecialchars($brand['link']) ?>" target="_blank" class="brand-item">
                    <img src="<?= htmlspecialchars($brand['logo']) ?>" alt="<?= htmlspecialchars($brand['name']) ?>">
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 工厂实拍/资质轮播 -->
    <?php if (!empty($factory_images)): ?>
    <section class="factory-gallery">
        <div class="container">
            <h2>工厂实拍与资质</h2>
            <div class="factory-slider">
                <?php foreach ($factory_images as $img): ?>
                <div class="factory-slide">
                    <img src="<?= htmlspecialchars($img['image']) ?>" alt="<?= htmlspecialchars($img['title']) ?>">
                    <p><?= htmlspecialchars($img['title']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 客户评价 -->
    <?php if (!empty($testimonials)): ?>
    <section class="testimonials">
        <div class="container">
            <h2>客户评价</h2>
            <div class="testimonials-slider">
                <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-item">
                    <div class="testimonial-header">
                        <strong><?= htmlspecialchars($t['name']) ?></strong>
                        <span class="rating"><?= str_repeat('★', $t['rating']) . str_repeat('☆', 5-$t['rating']) ?></span>
                    </div>
                    <p><?= htmlspecialchars($t['content']) ?></p>
                    <div class="testimonial-date"><?= $t['created_at'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 爆款商品轮播（含直播间卡片） -->
    <section class="featured">
        <div class="container">
            <h2>🔥 爆款推荐</h2>
            <div class="scroll-wrapper">
                <button class="scroll-btn left" id="scrollLeft">‹</button>
                <div class="featured-scroll" id="featuredScroll">
                    <!-- 直播间卡片作为第一个 -->
                    <div class="product-card live-card-special" onclick="window.open('<?= htmlspecialchars($settings['douyin_live_url'] ?? '#') ?>', '_blank')">
                        <div class="live-cover-large">
                            <?php if (!empty($settings['live_cover'])): ?>
                                <img src="<?= htmlspecialchars($settings['live_cover']) ?>" alt="直播封面">
                            <?php else: ?>
                                <div class="live-placeholder">直播中</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($settings['live_title'] ?? '正在热播') ?></h3>
                            <p><?= htmlspecialchars($settings['live_host'] ?? '腾虎运动官方') ?></p>
                            <span class="live-badge">直播中</span>
                        </div>
                    </div>
                    <?php foreach ($featured as $p): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($p['main_image']): ?>
                                <img src="<?= htmlspecialchars($p['main_image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            <?php else: ?>
                                <div class="image-placeholder">主图待传</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($p['name']) ?></h3>
                            <p class="price">¥<?= number_format($p['price'], 2) ?></p>
                            <div class="rating">评分: <?= $p['avg_rating'] ?> ★</div>
                            <a href="product.php?id=<?= $p['id'] ?>" class="btn-sm">查看详情</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="scroll-btn right" id="scrollRight">›</button>
            </div>
        </div>
    </section>

    <!-- 全部产品区（带筛选） -->
    <section id="products" class="products">
        <div class="container">
            <h2>全部产品</h2>
            <div class="filter-bar">
                <div class="filter-buttons">
                    <a href="?category=all&price_range=<?= urlencode($price_range) ?>&keyword=<?= urlencode($keyword) ?>&sort=<?= urlencode($sort) ?>" class="filter-btn <?= ($category_filter == '' || $category_filter == 'all') ? 'active' : '' ?>">全部</a>
                    <a href="?category=双排轮滑鞋&price_range=<?= urlencode($price_range) ?>&keyword=<?= urlencode($keyword) ?>&sort=<?= urlencode($sort) ?>" class="filter-btn <?= ($category_filter == '双排轮滑鞋') ? 'active' : '' ?>">双排轮滑鞋</a>
                    <a href="?category=单排轮滑鞋&price_range=<?= urlencode($price_range) ?>&keyword=<?= urlencode($keyword) ?>&sort=<?= urlencode($sort) ?>" class="filter-btn <?= ($category_filter == '单排轮滑鞋') ? 'active' : '' ?>">单排轮滑鞋</a>
                    <a href="?category=配件&price_range=<?= urlencode($price_range) ?>&keyword=<?= urlencode($keyword) ?>&sort=<?= urlencode($sort) ?>" class="filter-btn <?= ($category_filter == '配件') ? 'active' : '' ?>">配件</a>
                </div>
                <div class="search-sort">
                    <form method="get" class="search-form">
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category_filter) ?>">
                        <select name="price_range">
                            <option value="">全部价格</option>
                            <option value="0-500" <?= $price_range == '0-500' ? 'selected' : '' ?>>¥0-500</option>
                            <option value="500-1000" <?= $price_range == '500-1000' ? 'selected' : '' ?>>¥500-1000</option>
                            <option value="1000-99999" <?= $price_range == '1000-99999' ? 'selected' : '' ?>>¥1000以上</option>
                        </select>
                        <input type="text" name="keyword" placeholder="搜索关键词" value="<?= htmlspecialchars($keyword) ?>">
                        <select name="sort">
                            <option value="">默认排序</option>
                            <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>价格升序</option>
                            <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>价格降序</option>
                        </select>
                        <button type="submit">筛选</button>
                    </form>
                </div>
            </div>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($p['main_image']): ?>
                            <img src="<?= htmlspecialchars($p['main_image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        <?php else: ?>
                            <div class="image-placeholder">主图待传</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($p['name']) ?></h3>
                        <p class="price">¥<?= number_format($p['price'], 2) ?></p>
                        <a href="product.php?id=<?= $p['id'] ?>" class="btn-sm">查看详情</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&category=<?= urlencode($category_filter) ?>&price_range=<?= urlencode($price_range) ?>&keyword=<?= urlencode($keyword) ?>&sort=<?= urlencode($sort) ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="about" class="about">
        <div class="container">
            <h2>关于我们</h2>
            <p><?= nl2br(htmlspecialchars($settings['company_intro'] ?? '')) ?></p>
            <p>联系电话：<?= htmlspecialchars($settings['contact_phone'] ?? '') ?><br>邮箱：<?= htmlspecialchars($settings['contact_email'] ?? '') ?></p>
        </div>
    </section>

    <section id="contact" class="contact">
        <div class="container">
            <h2>联系我们</h2>
            <div class="contact-info">
                <p><strong>在线客服：</strong></p>
                <form action="send_message.php" method="post">
                    <input type="text" name="name" placeholder="您的姓名" required>
                    <input type="email" name="email" placeholder="您的邮箱（选填）">
                    <textarea name="message" placeholder="留言内容" rows="4" required></textarea>
                    <button type="submit">发送留言</button>
                </form>
                <div class="auto-reply">
                    <p><strong>自动回复：</strong> <?= htmlspecialchars($settings['auto_reply'] ?? '感谢您的留言！我们会在24小时内回复您。') ?></p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($settings['site_name'] ?? '东莞市腾虎运动器材有限公司') ?> 版权所有</p>
            <p>地址：广东省东莞市厚街镇三屯159号三楼</p>
        </div>
    </footer>

    <div class="scroll-buttons">
        <button id="scrollTop" title="回到顶部">↑</button>
        <button id="scrollBottom" title="去底部">↓</button>
    </div>

    <script src="js/main.js"></script>
    <?php if (!empty($settings['live_chat_code'])): ?>
        <?= $settings['live_chat_code'] ?>
    <?php endif; ?>
</body>
</html>