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


namespace RTP;

use RTP\Module;

//是否初次部署，设定为TRUE将在所有用户自行创建的用户目录下新建空白的index.html文件防止部分服务器开启的目录查看功能，上线前设为false提高性能
defined('FIRST_DEPLOYMENT') or define('FIRST_DEPLOYMENT', FALSE);

//定义请求方式(AJAX-Type)，GET/POST/AUTO,默认为POST
defined('AT') or define('AT', 'POST');

defined('DEBUG') or define('DEBUG', TRUE);

//数据库类型，用于PDO数据库连接
defined('DB_TYPE') or define('DB_TYPE', 'mysql');

//数据库是否需要保持长期连接（长连接）,多线程高并发环境下请开启,默认关闭
defined('DB_PERSISTENT_CONNECTION') or define('DB_PERSISTENT_CONNECTION', FALSE);

//框架模块目录名称
defined('PATH_MODULE') or define('PATH_MODULE', '/Module/');

//框架函数目录名称
defined('PATH_COMMON') or define('PATH_COMMON', '/Common/');

//框架特性(Traits)目录名称
defined('PATH_TRAITS') or define('PATH_TRAITS', '/Traits/');

//框架拓展(extend)目录名称
defined('PATH_EXTEND') or define('PATH_EXTEND', './RTP/extend/');

//框架异常(Exception)目录名称
defined('PATH_EXCEPTION') or define('PATH_EXCEPTION', '/Module/Exception/');

//用户控制器目录名称
defined('DIR_CONTROLLER') or define('DIR_CONTROLLER', 'Controller');

//用户模块目录名称
defined('DIR_MODULE') or define('DIR_MODULE', 'Module');

//用户Dao目录名称
defined('DIR_DAO') or define('DIR_DAO', 'Dao');

//用户数据模型目录名称
defined('DIR_MODEL') or define('DIR_MODEL', 'Model');

//框架存放的相对路径（相对于入口文件而言）,默认是'./RTP'
defined('PATH_FW') or define('PATH_FW', './RTP');

//项目代码存放的相对路径（相对于入口文件而言）
defined('PATH_APP') or define('PATH_APP', './Server');

//设置时区
date_default_timezone_set('Asia/Shanghai');

//判断DEBUG模式操作
DEBUG ? error_reporting(E_ALL ^ E_NOTICE) : error_reporting(0);

//引入必要文件文件
require PATH_FW . PATH_COMMON . 'EasyFunction.php';
require PATH_FW . PATH_MODULE . 'AutomaticallyModule.class.php';

//捕获全局信息
try {
    //启动自动化模块
    Module\AutomaticallyModule::start();

    //如果是首次部署项目，则在所有的项目下面新建空白的安全文件
    if (FIRST_DEPLOYMENT)
        Module\FileModule::createSecurityIndex();
} catch (Module\ExceptionModule $e) {
    //传参为True时，遇到异常后即停止程序运行
    $e->printError(FALSE);
} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}
?>