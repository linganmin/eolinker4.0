<?php

/**
 * @name eolinker ams open source，eolinker开源版本
 * @link https://www.eolinker.com/
 * @package eolinker
 * @author www.eolinker.com 广州银云信息科技有限公司 2015-2017
 * eoLinker是目前全球领先、国内最大的在线API接口管理平台，提供自动生成API文档、API自动化测试、Mock测试、团队协作等功能，旨在解决由于前后端分离导致的开发效率低下问题。
 * 如在使用的过程中有任何问题，欢迎加入用户讨论群进行反馈，我们将会以最快的速度，最好的服务态度为您解决问题。
 *
 * eoLinker AMS开源版的开源协议遵循Apache License 2.0，如需获取最新的eolinker开源版以及相关资讯，请访问:https://www.eolinker.com/#/os/download
 *
 * 官方网站：https://www.eolinker.com/
 * 官方博客以及社区：http://blog.eolinker.com/
 * 使用教程以及帮助：http://help.eolinker.com/
 * 商务合作邮箱：market@eolinker.com
 * 用户讨论QQ群：284421832
 */
class DatabaseTableDao
{
    /**
     * 添加表
     * @param $dbID int 数据库ID
     * @param $tableName string 表名称
     * @param $tableDesc string 表描述
     * @return bool
     */
    public function addTable(&$dbID, &$tableName, &$tableDesc)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_database_table (eo_database_table.dbID,eo_database_table.tableName,eo_database_table.tableDescription) VALUES (?,?,?);', array(
            $dbID,
            $tableName,
            $tableDesc
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else
            return $db->getLastInsertID();
    }

    /**
     * 检查数据表权限
     * @param $tableID int 表ID
     * @param $userID int 用户ID
     * @return bool
     */
    public function checkTablePermission(&$tableID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_database.dbID FROM eo_database_table INNER JOIN eo_database ON eo_database_table.dbID = eo_database.dbID INNER JOIN eo_conn_database ON eo_database.dbID = eo_conn_database.dbID WHERE eo_database_table.tableID =? AND eo_conn_database.userID =?;', array(
            $tableID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['dbID'];
    }

    /**
     * 删除表
     * @param $tableID int 表ID
     * @return bool
     */
    public function deleteTable(&$tableID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_database_table WHERE eo_database_table.tableID = ?;', array($tableID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取表列表
     * @param $dbID int 数据库ID
     * @return bool
     */
    public function getTable(&$dbID)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_database_table.dbID,eo_database_table.tableID,eo_database_table.tableName,eo_database_table.tableDescription FROM eo_database_table WHERE eo_database_table.dbID =?;', array($dbID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 编辑表
     * @param $tableID int 表ID
     * @param $tableName string 表名称
     * @param $tableDesc string 表描述
     * @return bool
     */
    public function editTable(&$tableID, &$tableName, &$tableDesc)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_database_table SET eo_database_table.tableName = ?,eo_database_table.tableDescription = ? WHERE eo_database_table.tableID = ?;', array(
            $tableName,
            $tableDesc,
            $tableID
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

}

?>