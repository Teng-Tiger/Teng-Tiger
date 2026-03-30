<?php
// 数据库连接配置（Railway 测试环境）
$db_host = 'hopper.proxy.rlwy.net';
$db_port = 39953;
$db_user = 'root';
$db_pass = 'GNeSNyMoEckYYPzzAcnxNwChWOMLGKeh';
$db_name = 'railway';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// 网站基础 URL（自动识别当前域名）
$site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]";
define('SITE_URL', $site_url);
define('ADMIN_PASS', 'th123456'); // 后台登录密码
?>