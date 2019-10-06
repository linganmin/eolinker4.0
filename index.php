<?php
if (file_exists(realpath('./server/RTP/config/eo_config.php')))
    include_once('./server/RTP/config/eo_config.php');
defined('ALLOW_REGISTER') or define('ALLOW_REGISTER', TRUE);
defined('ALLOW_UPDATE') or define('ALLOW_UPDATE', TRUE);
defined('WEBSITE_NAME') or define('WEBSITE_NAME', 'eoLinker接口管理工具开源版本');
?>
<!DOCTYPE html>
<html data-ng-app="eolinker">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="baidu-site-verification" content="QB4v9rHypp">
    <meta name="viewport" content="width=device-width, initial-scale=0.1,maximum-scale=1.0,user-scalable=0">
    <meta name="product-type" content="0" />
    <meta name="version" content="400">
    <meta name="author" content="广州银云信息科技有限公司">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title data-ng-init='title="<?php echo defined('WEBSITE_NAME') ? WEBSITE_NAME : ' '; ?>"'></title>
    <link href="assets/images/favicon.ico" rel="shortcut icon">
    <link rel="stylesheet" href="styles/app-bfd94e21a7.css">
    <script>
        var allowRegister =<?php echo ALLOW_REGISTER ? 'true' : 'false'; ?>;
        var allowUpdate =<?php echo ALLOW_UPDATE ? 'true' : 'false'; ?>;
        var language = "<?php echo defined('LANGUAGE') ? LANGUAGE : 'zh-cn'; ?>";
    </script>
</head>
</head>
<!--[if lt IE 8]>
<style>html, body {
    overflow: hidden;
    height: 100%
}</style>
<div class="tb-ie-updater-layer"></div>
<div class="tb-ie-updater-box" data-spm="20161112">
    <a href="https://www.google.cn/intl/zh-CN/chrome/browser/desktop/" class="tb-ie-updater-google" target="_blank"
       data-spm-click="gostr=/tbieupdate;locaid=d1;name=google">谷歌 Chrome</a>
    <a href="http://www.uc.cn/ucbrowser/download/" class="tb-ie-updater-uc" target="_blank"
       data-spm-click="gostr=/tbieupdate20161112;locaid=d2;name=uc">UC 浏览器</a>"
</div>
<![endif]-->

<body>
<div ui-view=""></div>
<eo-modal></eo-modal>
</body>
<script id="plug-inner-script"></script>
<script src="scripts/app-f957a3e843.js"></script>
</html>