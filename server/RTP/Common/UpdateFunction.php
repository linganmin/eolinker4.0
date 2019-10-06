<?php
/**
 * @name eolinker open source，eolinker开源版本
 * @link https://www.eolinker.com
 * @package eolinker
 * @author www.eolinker.com 广州银云信息科技有限公司 2015-2018

 * eolinker，业内领先的Api接口管理及测试平台，为您提供最专业便捷的在线接口管理、测试、维护以及各类性能测试方案，帮助您高效开发、安全协作。
 * 如在使用的过程中有任何问题，可通过http://help.eolinker.com寻求帮助
 *
 * 注意！eolinker开源版本遵循GPL V3开源协议，仅供用户下载试用，禁止“一切公开使用于商业用途”或者“以eoLinker开源版本为基础而开发的二次版本”在互联网上流通。
 * 注意！一经发现，我们将立刻启用法律程序进行维权。
 * 再次感谢您的使用，希望我们能够共同维护国内的互联网开源文明和正常商业秩序。
 *
 */

$db_url = DB_URL;
$db_port = DB_PORT;
$db_user = DB_USER;
$db_password = DB_PASSWORD;
$db_name = DB_NAME;
$websiteName = defined('WEBSITE_NAME') ? WEBSITE_NAME : 'eolinker开源版';
$prefixion = defined('DB_TABLE_PREFIXION') ? DB_TABLE_PREFIXION : 'eo';
$language = defined('LANGUAGE') ? LANGUAGE : 'zh-cn';

$config = "<?php
//主机地址
defined('DB_URL') or define('DB_URL', '{$db_url}');

//主机端口,默认mysql为3306
defined('DB_PORT') or define('DB_PORT', '{$db_port}');

//连接数据库的用户名
defined('DB_USER') or define('DB_USER', '{$db_user}');

//连接数据库的密码，推荐使用随机生成的字符串
defined('DB_PASSWORD') or define('DB_PASSWORD', '{$db_password}');

//数据库名
defined('DB_NAME') or define('DB_NAME', '{$db_name}');

//是否允许新用户注册
defined('ALLOW_REGISTER') or define('ALLOW_REGISTER', TRUE);

//是否允许更新项目，如果设置为FALSE，那么自动更新和手动更新都将失效
defined('ALLOW_UPDATE') or define('ALLOW_UPDATE', TRUE);

//网站名称
defined('WEBSITE_NAME') or define('WEBSITE_NAME', '{$websiteName}');

//数据表前缀
defined('DB_TABLE_PREFIXION') or define('DB_TABLE_PRIFIXION', '{$prefixion}');

//语言
defined('LANGUAGE') or define('LANGUAGE', '{$language}')
?>";

$configFile = file_put_contents(PATH_FW . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'eo_config.php', $config);
?>