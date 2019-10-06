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

class DatabaseTableFieldModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * 获取数据字典用户类型
     * @param $fieldID int 字段ID
     * @return bool|int
     */
    public function getUserType(&$fieldID)
    {
        $fieldDao = new DatabaseTableFieldDao();
        $dbID = $fieldDao->checkFieldPermission($fieldID, $_SESSION['userID']);
        if (empty($dbID)) {
            return -1;
        }
        $dao = new AuthorizationDao();
        $result = $dao->getDatabaseUserType($_SESSION['userID'], $dbID);
        if ($result === FALSE) {
            return -1;
        } else {
            return $result;
        }
    }

    /**
     * 添加字段
     * @param $tableID int 数据表ID
     * @param $fieldName string 字段名
     * @param $fieldType string 字段类型
     * @param $fieldLength int 字段长度
     * @param $isNotNull int 是否非空 [0/1]=>[否/是]，默认为0
     * @param $isPrimaryKey int 是否为主键 [0/1]=>[否/是]，默认为0
     * @param $fieldDesc string 字段描述，默认为NULL
     * @return bool|int
     */
    public function addField(&$tableID, &$fieldName, &$fieldType, &$fieldLength, &$isNotNull, &$isPrimaryKey, &$fieldDesc, &$fieldDefaultValue)
    {
        $databaseDao = new DatabaseDao;
        $databaseTableDao = new DatabaseTableDao;
        $databaseTableFieldDao = new DatabaseTableFieldDao;

        if ($dbID = $databaseTableDao->checkTablePermission($tableID, $_SESSION['userID'])) {
            $databaseDao->updateDatabaseUpdateTime($dbID);
            return $databaseTableFieldDao->addField($tableID, $fieldName, $fieldType, $fieldLength, $isNotNull, $isPrimaryKey, $fieldDesc, $fieldDefaultValue);
        } else
            return FALSE;
    }

    /**
     * 删除字段
     * @param $fieldID int 字段ID
     * @return bool
     */
    public function deleteField(&$fieldID)
    {
        $databaseDao = new DatabaseDao;
        $databaseTableFieldDao = new DatabaseTableFieldDao;

        if ($dbID = $databaseTableFieldDao->checkFieldPermission($fieldID, $_SESSION['userID'])) {
            $databaseDao->updateDatabaseUpdateTime($dbID);
            return $databaseTableFieldDao->deleteField($fieldID);
        } else
            return FALSE;
    }

    /**
     * 获取字段列表
     * @param $tableID int 数据表ID
     * @return bool|array
     */
    public function getField(&$tableID)
    {
        $databaseDao = new DatabaseDao;
        $databaseTableDao = new DatabaseTableDao;
        $databaseTableFieldDao = new DatabaseTableFieldDao;

        if ($dbID = $databaseTableDao->checkTablePermission($tableID, $_SESSION['userID'])) {
            $databaseDao->updateDatabaseUpdateTime($dbID);
            return $databaseTableFieldDao->getField($tableID);
        } else
            return FALSE;
    }

    /**
     * 修改字段
     * @param $fieldID int 字段ID
     * @param $fieldName string 字段名
     * @param $fieldType string 字段类型
     * @param $fieldLength int 字段长度
     * @param $isNotNull int 是否非空 [0/1]=>[否/是]，默认为0
     * @param $isPrimaryKey int 是否为主键 [0/1]=>[否/是]，默认为0
     * @param $fieldDesc string 字段描述，默认为NULL
     * @return bool
     */
    public function editField(&$fieldID, &$fieldName, &$fieldType, &$fieldLength, &$isNotNull, &$isPrimaryKey, &$fieldDesc, &$fieldDefaultValue)
    {
        $databaseDao = new DatabaseDao;
        $databaseTableFieldDao = new DatabaseTableFieldDao;

        if ($dbID = $databaseTableFieldDao->checkFieldPermission($fieldID, $_SESSION['userID'])) {
            $databaseDao->updateDatabaseUpdateTime($dbID);
            return $databaseTableFieldDao->editField($fieldID, $fieldName, $fieldType, $fieldLength, $isNotNull, $isPrimaryKey, $fieldDesc, $fieldDefaultValue);
        } else
            return FALSE;
    }

}

?>