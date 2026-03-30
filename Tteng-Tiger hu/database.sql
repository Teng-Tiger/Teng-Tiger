-- ============================================================
-- 腾虎运动器材官网 - 完整数据库脚本
-- 执行前请确认数据库已选择，此脚本会删除并重建所有表
-- ============================================================

-- 1. 删除现有表（按依赖顺序）
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `returns`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `admin`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `addresses`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `carousels`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `factory_images`;
DROP TABLE IF EXISTS `products`;

-- 2. 商品表
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `spec` text,
  `keywords` varchar(255) DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `detail_images` text,
  `category` varchar(100) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. 用户表
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. 收货地址表
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `consignee` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `province` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `detail` text NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. 购物车表
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `spec_selected` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. 收藏表
CREATE TABLE `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. 订单表（扩展）
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(32) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `spec_selected` varchar(255) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text,
  `remark` text,
  `shipping_fee` decimal(10,2) DEFAULT 0,
  `tracking_no` varchar(100) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT 'pending',
  `pay_time` timestamp NULL DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. 管理员表
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. 客服消息表
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `reply` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. 公告表
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. 退换货申请表
CREATE TABLE `returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `reason` text,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. 网站设置表
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. 评论表
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `content` varchar(200) NOT NULL,
  `reply` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. 轮播图表
CREATE TABLE `carousels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. 导航菜单表（支持二级）
CREATE TABLE `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT 0,
  `sort` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. 合作品牌表
CREATE TABLE `brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. 工厂实拍/资质表
CREATE TABLE `factory_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `sort` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 18. 初始数据
-- ============================================================

-- 管理员（密码 th123456）
INSERT INTO `admin` (`username`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 网站默认设置
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', '腾虎运动器材'),
('hero_title', '专业轮滑装备制造商'),
('hero_subtitle', '腾虎运动器材 · 品质成就未来'),
('hero_image', 'https://placehold.co/1920x600?text=轮滑运动特写'),
('company_intro', '东莞市腾虎运动器材有限公司，位于广东省东莞市厚街镇三屯159号三楼。专业生产轮滑鞋及相关配件，产品远销国内外。我们拥有6000㎡现代化厂房、20+项专利认证、产品出口20+国家、客户满意率96%。'),
('contact_phone', '13509203585 / 13049796516'),
('contact_email', '2814924932@qq.com'),
('auto_reply', '感谢您的留言！我们会在24小时内回复您。快递一般第二天发货，优惠活动请关注官网公告栏。'),
('douyin_live_url', 'https://v.douyin.com/5r8NwstpPwc/'),
('live_cover', ''),
('live_title', '腾虎轮滑 春季新品直播'),
('live_host', '腾虎运动官方'),
('live_chat_code', ''),
('default_shipping', '圆通快递，3-5天送达，包邮。如需顺丰加急，请在备注中注明“顺丰陆运+30元”或“顺丰航空+50元”。');

-- 示例公告
INSERT INTO `announcements` (`title`, `content`) VALUES
('【优惠活动】满299减30', '即日起至月底，全场商品满299元减30元，自动抵扣，无需领券。'),
('【发货公告】每天16点前付款当天发货', '当天16:00前付款的订单当天发出，16:00后次日发货。快递默认中通/圆通。'),
('【售后无忧】7天无理由退换货', '支持7天无理由退换货，质量问题包邮退，非质量问题买家承担寄回运费。');

-- 商品数据（共46条，部分已设为爆款）
INSERT INTO `products` (`name`, `price`, `spec`, `keywords`, `category`, `is_featured`, `sort_order`) VALUES
('专业双排溜冰鞋旱冰鞋男轮滑鞋外卖成人代步四轮', 2980.00, '品牌:TENGHU/腾虎, 货号：TH-轮舞鞋, 颜色：黑色 白色, 材质：航空铝合金一体刀架, 特点：舞蹈、透气、轻便', '双排,花样,跳舞', '双排轮滑鞋', 1, 1),
('专业双排溜冰鞋旱冰鞋男轮滑鞋外卖成人代步四轮', 788.00, '品牌:TENGHU/腾虎, 货号：T006, 颜色：黑色 红色 绿色 紫色, 材质：超纤皮, 轮子：PU花式轮', '双排', '双排轮滑鞋', 0, 0),
('溜冰鞋双排轮滑鞋旱冰鞋成人男四轮外卖成人神器', 1280.00, '品牌:TENGHU/腾虎, 货号：T008', '双排', '双排轮滑鞋', 0, 0),
('溜冰鞋时尚四轮轮滑鞋成人腾虎轮滑鞋成人代步神器', 518.00, '品牌:TENGHU/腾虎, 货号：T006A, 材质：超纤皮, 刹车：橡胶刹车, 轮子：PU花式轮, 支架：加厚铝合金, 颜色：粉色 白色 红色 黑色, 产品尺码：27~48', '双排', '双排轮滑鞋', 0, 0),
('轮滑鞋四轮专用溜冰鞋旱冰鞋腾虎轮滑鞋成人神器', 518.00, '品牌:TENGHU/腾虎, 货号：T006A, 材质：超纤皮, 刹车：橡胶刹车, 轮子：PU花式轮, 支架：加厚铝合金, 颜色：粉色 白色 红色 黑色, 尺码：27~48', '双排', '双排轮滑鞋', 0, 0),
('黑色滑轮鞋专用极速旱冰鞋成人外卖轮滑滑冰鞋冰刀', 518.00, '品牌：TENGTIGER/腾虎, 货号：2023121503, 鞋身：超纤皮, 支架：航空铝合金, 轮子：耐磨PU, 刹车：橡胶刹车, 颜色：黑色, 尺码：31-48码', '单排,冰刀', '单排轮滑鞋', 0, 0),
('专业轮滑鞋双排溜冰鞋旱冰鞋户外溜冰鞋成人四轮鞋', 688.00, '品牌：TENGHU/腾店, 货号：202301, 内胆：超纤皮, 轮子：PU轮, 刹车：橡胶刹车, 颜色：炫彩蓝, 尺码：27-48码', '双排', '双排轮滑鞋', 0, 0),
('四轮溜冰鞋轮滑溜冰鞋旱冰鞋成人专用腾虎溜冰鞋', 688.00, '品牌:TENGHU/腾虎', '双排', '双排轮滑鞋', 0, 0),
('专业轮滑鞋双排溜冰鞋旱冰鞋户外溜冰鞋成人四轮鞋', 528.00, '品牌：TENGTIGER/腾虎, 材质：超纤皮, 品名：腾虎双排轮滑鞋, 底座：加厚铝合金, 产地：广东, 货号：2024318, 颜色：绿色黄色蓝色粉色炫彩红炫彩蓝, 尺码：27~48码', '双排', '双排轮滑鞋', 0, 0),
('复古溜冰鞋轮滑鞋高端旱冰鞋高档四轮成人代步神器', 588.00, '品牌：腾虎/TENGTIGER, 型号：TH1125, 颜色：黄色粉色白色绿色黑色, 可选尺码：31#-48, 鞋面材质：超纤皮, 刹车：橡胶刹车, 鞋子内单：超纤皮', '双排', '双排轮滑鞋', 0, 0),
('溜冰鞋滑冰鞋四轮滑鞋溜冰场专用旱冰鞋外卖成人', 298.00, '品牌：腾虎/TENGTIGER', '双排', '双排轮滑鞋', 0, 0),
('专业旱冰鞋四轮滑鞋成人代步神器溜冰鞋成人女款', 398.00, '品牌：TENGHU/腾虎, 货号：TH0001, 底座：尼龙, 内胆：超纤皮, 轮子：PU闪光轮, 刹车：橡胶刹车, 颜色：粉色, 尺码：27-48码', '双排', '双排轮滑鞋', 0, 0),
('溜冰鞋滑冰鞋旱冰鞋户外四轮轮滑鞋成人代步神器', 688.00, '品牌：腾虎/TENGTIGER, 材质：超纤皮, 颜色：红色, 尺码：31-48码', '双排', '双排轮滑鞋', 0, 0),
('溜冰鞋专业花样轮滑CNC旱冰鞋成人溜冰鞋四轮双排', 1080.00, '品牌：腾虎/TENGTIGER, T006S', '双排,花样', '双排轮滑鞋', 0, 0),
('时尚超纤轮滑鞋成人溜冰鞋双排溜溜冰鞋轮滑溜冰鞋', 238.00, '品牌：腾虎/TENGTIGER, 货号：T003', '双排', '双排轮滑鞋', 0, 0),
('旱冰鞋铝合金双排鞋成人代步轮滑鞋推荐滑冰鞋溜冰', 398.00, '名称:双排轮滑鞋, 材质:超纤皮, 刹车:橡胶刹车, 颜色:黑色白色粉色银色橙色, 底座:铝合金, 货号:BO01, 尺码:31-48码', '双排', '双排轮滑鞋', 0, 0),
('溜冰鞋CNC底座花样双排溜冰鞋轮滑鞋成人耐磨时尚', 1399.00, '品名:腾虎动力飞跃轮漫装, 材质:耐磨极速轮+航空铝合金底座+超纤材质, 尺码:28-49, 支架:全球头支架, 颜色:炫酷黑，星际蓝，活力粉、极地白', '双排,花样', '双排轮滑鞋', 0, 0),
('轮滑鞋旱冰鞋成人专用双排外卖轮滑花样滑冰滑冰鞋', 298.00, '品牌：TENGHU/腾成, 货号：998, 内胆：超纤皮, 轮子：透明PU闪光轮, 刹车：橡胶刹车, 颜色：白色, 尺码：27-48码', '双排', '双排轮滑鞋', 0, 0),
('专用旱冰鞋防滑耐磨透气轮滑鞋轮滑滑冰鞋溜冰', 368.00, '货号：30240801', '双排', '双排轮滑鞋', 0, 0),
('花样轮滑溜冰鞋旱冰鞋专用溜冰鞋轮滑鞋成人滑冰鞋', 1080.00, '产品品牌：TENGHU/腾成, 产品名称：双排花样轮滑鞋, 产品颜色：黑色白色紫色绿色粉色, 鞋面材质：超纤皮, 特点：轮滑鞋/透气/轻便, 鞋头刹车：橡胶刹车, 鞋底材质：橡胶底', '双排,花样', '双排轮滑鞋', 0, 0),
('溜冰鞋轮滑鞋外卖骑手专用溜冰鞋成人四轮鞋旱冰鞋', 1280.00, 'TH德比鞋', '双排', '双排轮滑鞋', 0, 0),
('双排轮滑鞋溜冰鞋极速轮旱冰鞋溜冰鞋成人四轮鞋', 688.00, '品牌：腾虎/TENGTIGER, 内里材质：超纤皮, 货号：202309, 刹车：橡胶刹车, 颜色：黄色, 轮子：耐磨PU, 鞋身材质：耐磨超纤, 尺码：31-48码', '双排', '双排轮滑鞋', 0, 0),
('旱冰鞋成人专用双排溜冰鞋四轮滑冰鞋花样轮滑鞋', 1280.00, '品牌：TENGHU/腾店, 货号：2023121506, 支架：航空铝合金, 轮子：PU, 刹车：橡胶刹车, 颜色：红棕色, 尺码：31-48码', '双排', '双排轮滑鞋', 0, 0),
('旱冰鞋双排轮滑鞋代步神器溜冰鞋成人四轮鞋户外', NULL, '品牌：TENGHU/腾虎, 货号：202304, 内胆：超纤皮, 轮子：PU轮, 刹车：橡胶刹车, 颜色：金色, 尺码：27-48码', '双排', '双排轮滑鞋', 0, 0),
('溜冰鞋代步旱冰鞋神器溜冰鞋四轮鞋轮滑鞋外卖成人', 518.00, '颜色：白色黑色粉色绿色, 货号：TH11, 鞋面材质：耐磨超纤, 鞋子刹车：橡胶刹车, 鞋子内里：超纤', '双排', '双排轮滑鞋', 0, 0),
('溜冰鞋成人四轮鞋双排轮滑鞋外卖男轮滑鞋旱冰鞋', 688.00, '品牌：TENGHU/腾虎, 货号：202310, 鞋身：耐磨超纤, 内胆：超纤皮, 轮子：PU, 刹车：橡胶刹车, 颜色：黑色, 尺码：31-48码', '双排', '双排轮滑鞋', 0, 0),
('专业成人四轮双排溜冰鞋旱冰鞋轮滑鞋外卖代步神器', 799.00, '货号：TH006刷街鞋', '双排', '双排轮滑鞋', 0, 0),
('双排轮滑鞋男旱冰鞋成人溜冰鞋四轮外卖轮滑滑冰鞋', 368.00, '品牌:TENGHU/腾虎, 货号：20240717, 颜色：黑色 红色 绿色 白色 粉色 浅蓝色, 鞋身材质：耐磨超纤, 尺码：31-48码', '双排', '双排轮滑鞋', 0, 0),
('溜冰鞋成人四轮鞋轮滑鞋旱冰鞋速滑外卖专用轮滑鞋', 498.00, '', '双排', '双排轮滑鞋', 0, 0),
('专业花样轮滑鞋溜冰鞋成人四轮鞋CNC底座旱冰鞋', 1680.00, 'TH1680', '双排,花样', '双排轮滑鞋', 0, 0),
('专业成人四轮双排溜冰鞋旱冰鞋轮滑鞋拼接款', 688.00, '货号：TH-PJ006', '双排', '双排轮滑鞋', 0, 0),
('花样直排跳舞鞋旱冰队列滑专用轮滑男滑轮花式专业', 1280.00, '货号：T001', '单排,花样,跳舞', '单排轮滑鞋', 0, 0),
('双排溜冰鞋专业轮滑鞋旱冰鞋溜冰鞋成人四轮鞋', 688.00, '系列：双排溜冰鞋系列, 鞋面材质：耐磨超纤, 内里材质：超纤皮, 尺码：31-48, 颜色：白色', '双排', '双排轮滑鞋', 0, 0),
('双排专用轮滑鞋溜冰鞋成人四轮鞋旱冰鞋腾虎溜冰鞋', 298.00, '品牌：TENGHU/腾虎, 货号：20230303, 内胆：超纤皮, 轮子：PU轮, 刹车：橡胶刹车, 颜色：彩色, 尺码：27-48码', '双排', '双排轮滑鞋', 0, 0),
('旱冰鞋成人专用双排轮滑鞋溜冰鞋成人四轮鞋滑轮鞋', 1280.00, '品牌：TENGHU/腾虎, 货号：202304010, 内胆：超纤皮, 轮子：PU轮, 刹车：橡胶刹车, 颜色：彩色, 尺码：27-48码', '双排', '双排轮滑鞋', 0, 0),
('旱冰鞋成人双排轮滑鞋溜冰鞋成人四轮鞋腾虎溜冰鞋', 398.00, '品牌：TENGHU/腾虎, 货号：TH0002, 鞋身：耐磨超纤, 内胆：超纤皮, 轮子：PU轮, 刹车：橡胶刹车, 颜色：绿色, 尺码：27-48码', '双排', '双排轮滑鞋', 0, 0),
('双排轮滑鞋成人代步神器溜冰鞋成人四轮鞋旱冰鞋', 688.00, '品牌：TENGHU/腾虎, 货号：202306, 鞋身：超纤炫彩工艺, 内胆：超纤皮, 轮子：PU轮, 刹车：橡胶刹车, 颜色：蓝色, 尺码：27-48码', '双排', '双排轮滑鞋', 0, 0),
('双排轮滑鞋炫彩溜冰鞋旱冰鞋成人专用轮滑鞋成人', 688.00, '品牌：TENGHU/腾虎, 货号：202308, 内胆：超纤皮, 轮子：PU轮, 刹车：橡胶刹车, 颜色：炫彩粉, 尺码：27-48码', '双排', '双排轮滑鞋', 0, 0),
('轮滑鞋旱冰鞋成人代步神器双排轮滑鞋腾虎溜冰鞋', 688.00, '品牌：TENGHU/腾虎, 货号：TH11, 内胆：超纤皮, 轮子：PU轮, 刹车：橡胶刹车, 颜色：银色, 尺码：31-48码', '双排', '双排轮滑鞋', 0, 0),
('溜冰鞋轴承高速静音ABEC-11高转速双排专用耐高温', 90.00, '', '配件', '配件', 0, 0),
('旱冰鞋发光轮滑鞋闪光滑轮83A轮子溜冰鞋专用配件', 100.00, '', '配件', '配件', 0, 0),
('溜冰鞋六灯变色龙闪光轮85A 腾虎轮滑鞋专业轴承', 15.00, '', '配件', '配件', 0, 0),
('旱冰鞋双排轮滑鞋红色粉色白色耐磨PU闪光轮85A', 15.00, '', '配件', '配件', 0, 0),
('双排轮滑鞋83A橙色静音滑冰鞋配件58MM腾虎溜冰鞋', 15.00, '', '配件', '配件', 0, 0),
('溜冰鞋避震品牌轮滑鞋减震器滑轮鞋轮子92APU防震', 75.00, '', '配件', '配件', 0, 0),
('轮滑鞋六灯渐变闪光轮88A轮子双排溜冰鞋轮滑配件', 17.00, '', '配件', '配件', 0, 0),
('品牌滚轮滑轮双排溜冰鞋刷街62*38彩色腾虎轮滑鞋', 238.00, '', '配件', '配件', 0, 0),
('双排轮滑鞋成人轮舞旱冰鞋刹车头溜冰鞋点兵器防滑', 50.00, '', '配件', '配件', 0, 0),
('双排轮滑鞋点冰器溜冰鞋旱冰鞋成人可双排溜冰鞋', 45.00, '', '配件', '配件', 0, 0),
('加厚四轮框架双排轮滑鞋旱冰鞋原装CNC航空铝材轮', 680.00, '', '配件', '配件', 0, 0),
('溜冰鞋双排轮滑鞋品牌鞋头防撞鞋头套旱冰鞋耐磨', 40.00, '', '配件', '配件', 0, 0),
('双排轮滑鞋铝合金三角支架尼龙炫彩底座腾虎溜冰鞋', 398.00, '', '配件', '配件', 0, 0),
('双排轮滑鞋T型工具专用组合内T型扳手可拆装高能感', 8.80, '', '配件', '配件', 0, 0),
('溜冰专用袜子加厚纯棉透气防臭吸汗腾虎双排溜冰鞋', 18.80, '', '配件', '配件', 0, 0),
('多功能扳手T型套筒通用专业腾虎双排轮滑鞋工具', 18.00, '', '配件', '配件', 0, 0),
('双排轮滑鞋鞋垫软加厚足弓支撑减震吸汗腾虎溜冰鞋', 15.00, '', '配件', '配件', 0, 0);

-- 示例轮播图（4个占位）
INSERT INTO `carousels` (`title`, `image`, `link`, `sort`) VALUES
('轮滑运动1', 'https://placehold.co/1920x600?text=轮滑运动1', '#', 1),
('轮滑运动2', 'https://placehold.co/1920x600?text=轮滑运动2', '#', 2),
('轮滑运动3', 'https://placehold.co/1920x600?text=轮滑运动3', '#', 3),
('轮滑运动4', 'https://placehold.co/1920x600?text=轮滑运动4', '#', 4);

-- 示例合作品牌（从原官网提取的占位，上线后替换图片）
INSERT INTO `brands` (`name`, `logo`, `link`, `sort`) VALUES
('迪士尼', 'https://placehold.co/120x60?text=Disney', 'https://www.disney.com', 1),
('米高', 'https://placehold.co/120x60?text=MIGO', 'https://www.migo.com', 2),
('沃尔玛', 'https://placehold.co/120x60?text=Walmart', 'https://www.walmart.com', 3),
('迪卡侬', 'https://placehold.co/120x60?text=Decathlon', 'https://www.decathlon.com', 4);

-- 示例工厂实拍/资质（占位）
INSERT INTO `factory_images` (`title`, `image`, `sort`) VALUES
('现代化工厂', 'https://placehold.co/800x500?text=Factory', 1),
('先进生产线', 'https://placehold.co/800x500?text=Production', 2),
('专利证书', 'https://placehold.co/800x500?text=Patent', 3),
('出口实拍', 'https://placehold.co/800x500?text=Export', 4);

-- 示例评论（4条精选）
INSERT INTO `reviews` (`product_id`, `name`, `rating`, `content`, `status`) VALUES
(1, '王小明', 5, '腾虎的溜冰鞋质量非常好，孩子穿了半年一点没坏，脚感舒适，强烈推荐！', 1),
(2, '李教练', 5, '作为专业教练，我用过很多品牌，腾虎的轮滑鞋耐磨性和抓地力都非常出色。', 1),
(3, '张女士', 5, '客服态度很好，发货快，鞋子设计很时尚，孩子很喜欢。', 1),
(4, '刘选手', 5, '买来参加比赛，碳纤鞋身轻便，动力传输直接，成绩提高了不少！', 1);

-- 默认导航菜单（一级+二级）
INSERT INTO `menus` (`name`, `link`, `parent_id`, `sort`) VALUES
('首页', 'index.php', 0, 1),
('产品中心', '#', 0, 2),
('双排轮滑鞋', 'index.php?category=双排轮滑鞋', 2, 1),
('单排轮滑鞋', 'index.php?category=单排轮滑鞋', 2, 2),
('儿童轮滑鞋', 'index.php?category=儿童轮滑鞋', 2, 3),
('配件', 'index.php?category=配件', 2, 4),
('关于我们', 'index.php#about', 0, 3),
('联系我们', 'index.php#contact', 0, 4);