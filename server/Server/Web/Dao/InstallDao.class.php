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

class InstallDao
{
    /**
     * 检查数据库是否可以连接
     */
    public function checkDBConnect()
    {
        $conInfo = DB_TYPE . ':host=' . DB_URL . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8';
        $option = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
            \PDO::ATTR_EMULATE_PREPARES => FALSE
        );
        $db_con = new \PDO($conInfo, DB_USER, DB_PASSWORD, $option);
        return $db_con;
    }

    /**
     * 安装数据库
     * @param $sqlArray array 创建数据库的语句
     * @return bool
     */
    public function installDatabase(&$sqlArray)
    {
        $db = getDatabase();
        $db->beginTransaction();
        try {
            foreach ($sqlArray as $query) {
                $db->query($query);
                if ($db->getError()) {
                    $db->rollback();
                    return FALSE;
                }
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            $db->rollback();
            return FALSE;
        }

        $db->commit();
        return TRUE;
    }

}

?>