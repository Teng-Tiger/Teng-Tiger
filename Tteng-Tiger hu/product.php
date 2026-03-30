<?php
require_once 'config.php';
session_start();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM products WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) die('商品不存在');
$product = $result->fetch_assoc();
$detail_images = $product['detail_images'] ? explode(',', $product['detail_images']) : [];

// 获取平均评分
$avg_rating = $conn->query("SELECT AVG(rating) as avg FROM reviews WHERE product_id = $id AND status=1")->fetch_assoc()['avg'];
$avg_rating = round($avg_rating ?: 0, 1);

// 获取评论列表
$reviews = $conn->query("SELECT * FROM reviews WHERE product_id = $id AND status=1 ORDER BY id DESC");

// 获取用户收藏状态（如果已登录）
$is_favorited = false;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $fav_check = $conn->query("SELECT id FROM favorites WHERE user_id=$uid AND product_id=$id");
    $is_favorited = $fav_check->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - 腾虎运动器材</title>
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="user.php" class="user-avatar">个人中心</a>
                        <a href="cart.php" class="cart-icon">🛒</a>
                        <a href="logout.php">退出</a>
                    <?php else: ?>
                        <a href="login.php">登录</a>
                        <a href="register.php">注册</a>
                    <?php endif; ?>
                </div>
            </nav>
            <div class="menu-toggle">☰</div>
        </div>
    </header>

    <div class="container product-detail">
        <div class="detail-left">
            <!-- 主图轮播区（将所有主图+详情图合并轮播） -->
            <div class="main-slider" id="mainSlider">
                <?php
                $all_images = [];
                if ($product['main_image']) $all_images[] = $product['main_image'];
                $all_images = array_merge($all_images, $detail_images);
                if (empty($all_images)) $all_images[] = 'https://placehold.co/600x400?text=暂无图片';
                ?>
                <div class="slider-container">
                    <div class="slider-wrapper" id="sliderWrapper">
                        <?php foreach ($all_images as $idx => $img): ?>
                        <div class="slider-item"><img src="<?= htmlspecialchars($img) ?>" alt="产品图片"></div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($all_images) > 1): ?>
                    <button class="slider-prev" id="sliderPrev">‹</button>
                    <button class="slider-next" id="sliderNext">›</button>
                    <div class="slider-dots" id="sliderDots">
                        <?php for ($i = 0; $i < count($all_images); $i++): ?>
                        <span class="dot <?= $i==0 ? 'active' : '' ?>" data-index="<?= $i ?>"></span>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- 详情图网格（可选，如果希望下面也显示所有详情图，可保留） -->
            <?php if (!empty($detail_images)): ?>
            <div class="detail-thumbs">
                <div class="thumb-scroll">
                    <?php foreach ($detail_images as $img): ?>
                    <div class="thumb-item">
                        <img src="<?= htmlspecialchars($img) ?>" alt="详情图">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="detail-right">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <div class="product-rating">评分：<?= $avg_rating ?> ★</div>
            <p class="price">¥<?= number_format($product['price'], 2) ?></p>
            <!-- 规格参数表格 -->
            <div class="spec">
                <h3>规格参数</h3>
                <table class="spec-table">
                    <?php
                    $spec_lines = explode("\n", $product['spec']);
                    foreach ($spec_lines as $line):
                        if (trim($line) == '') continue;
                        if (strpos($line, ':') !== false):
                            list($key, $val) = explode(':', $line, 2);
                            $key = trim($key);
                            $val = trim($val);
                        else:
                            $key = '参数';
                            $val = trim($line);
                        endif;
                    ?>
                    <tr><th><?= htmlspecialchars($key) ?></th><td><?= htmlspecialchars($val) ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="buy-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="cart.php?add=<?= $id ?>" class="btn">加入购物车</a>
                    <a href="order.php?product_id=<?= $id ?>" class="btn">立即购买</a>
                    <a href="favorite.php?action=toggle&product_id=<?= $id ?>" class="btn btn-fav <?= $is_favorited ? 'favorited' : '' ?>" id="favBtn">
                        <?= $is_favorited ? '❤️ 已收藏' : '🤍 收藏' ?>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn">登录后购买</a>
                <?php endif; ?>
                <a href="tel:13509203585" class="btn btn-call">电话咨询</a>
            </div>
        </div>
    </div>

    <!-- 评论区域 -->
    <div class="container reviews">
        <h3>用户评论</h3>
        <div class="review-list">
            <?php while($rev = $reviews->fetch_assoc()): ?>
            <div class="review-item">
                <div class="review-header">
                    <strong><?= htmlspecialchars($rev['name']) ?></strong>
                    <span class="rating"><?= str_repeat('★', $rev['rating']) . str_repeat('☆', 5-$rev['rating']) ?></span>
                </div>
                <div class="review-content"><?= nl2br(htmlspecialchars($rev['content'])) ?></div>
                <?php if ($rev['reply']): ?>
                <div class="review-reply"><strong>管理员回复：</strong> <?= nl2br(htmlspecialchars($rev['reply'])) ?></div>
                <?php endif; ?>
                <div class="review-time"><?= $rev['created_at'] ?></div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
        <form method="post" action="submit_review.php" class="review-form" id="reviewForm">
            <input type="hidden" name="product_id" value="<?= $id ?>">
            <input type="text" name="name" placeholder="您的昵称" required>
            <select name="rating" required>
                <option value="">评分</option>
                <option value="5">★★★★★ 5星</option>
                <option value="4">★★★★☆ 4星</option>
                <option value="3">★★★☆☆ 3星</option>
                <option value="2">★★☆☆☆ 2星</option>
                <option value="1">★☆☆☆☆ 1星</option>
            </select>
            <textarea name="content" placeholder="评论内容（最多50字）" maxlength="50" rows="3" required></textarea>
            <button type="submit">提交评论</button>
            <p class="hint">如果您想要看到官方回复，请填写联系方式（选填）</p>
        </form>
        <?php else: ?>
        <p><a href="login.php">登录后</a> 即可发表评论。</p>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> 东莞市腾虎运动器材有限公司</p>
        </div>
    </footer>

    <script>
        // 主图轮播（自动 + 手动）
        const wrapper = document.getElementById('sliderWrapper');
        const prev = document.getElementById('sliderPrev');
        const next = document.getElementById('sliderNext');
        const dots = document.querySelectorAll('.dot');
        if (wrapper && prev && next && dots.length) {
            let current = 0;
            const total = dots.length;
            const updateSlider = () => {
                wrapper.style.transform = `translateX(-${current * 100}%)`;
                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === current);
                });
            };
            const nextSlide = () => {
                current = (current + 1) % total;
                updateSlider();
            };
            const prevSlide = () => {
                current = (current - 1 + total) % total;
                updateSlider();
            };
            next.addEventListener('click', prevSlide);
            prev.addEventListener('click', nextSlide);
            dots.forEach(dot => {
                dot.addEventListener('click', () => {
                    current = parseInt(dot.getAttribute('data-index'));
                    updateSlider();
                });
            });
            let autoInterval = setInterval(nextSlide, 3000);
            wrapper.addEventListener('mouseenter', () => clearInterval(autoInterval));
            wrapper.addEventListener('mouseleave', () => autoInterval = setInterval(nextSlide, 3000));
        }
    </script>
</body>
</html>