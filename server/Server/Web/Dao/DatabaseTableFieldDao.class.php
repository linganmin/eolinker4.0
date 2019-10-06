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

class DatabaseTableFieldDao
{
    /**
     * 添加字段
     * @param $tableID int 数据表ID
     * @param $fieldName string 字段名
     * @param $fieldType string  字段类型
     * @param $fieldLength int 字段长度
     * @param $isNotNull int 是否非空 [0/1]=>[否/是]，默认为0
     * @param $isPrimaryKey int 是否为主键 [0/1]=>[否/是]，默认为0
     * @param $fieldDesc string 字段描述，默认为NULL
     * @return bool|int
     */
    public function addField(&$tableID, &$fieldName, &$fieldType, &$fieldLength, &$isNotNull, &$isPrimaryKey, &$fieldDesc, &$defaultValue)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_database_table_field (eo_database_table_field.tableID,eo_database_table_field.fieldName,eo_database_table_field.fieldType,eo_database_table_field.fieldLength,eo_database_table_field.isNotNull,eo_database_table_field.isPrimaryKey,eo_database_table_field.fieldDescription,eo_database_table_field.defaultValue) VALUES (?,?,?,?,?,?,?,?);', array(
            $tableID,
            $fieldName,
            $fieldType,
            $fieldLength,
            $isNotNull,
            $isPrimaryKey,
            $fieldDesc,
            $defaultValue
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $db->getLastInsertID();
    }

    /**
     * 检查字段与用户是否匹配
     * @param $fieldID int 字段ID
     * @param $userID int 用户ID
     * @return bool|int
     */
    public function checkFieldPermission($fieldID, $userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_database_table.dbID FROM eo_database_table INNER JOIN eo_database_table_field ON eo_database_table.tableID = eo_database_table_field.tableID INNER JOIN eo_conn_database ON eo_database_table.dbID = eo_conn_database.dbID WHERE eo_database_table_field.fieldID = ? AND eo_conn_database.userID =?;', array(
            $fieldID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['dbID'];
    }

    /**
     * 删除字段
     * @param $fieldID int 字段ID
     * @return bool
     */
    public function deleteField(&$fieldID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_database_table_field WHERE eo_database_table_field.fieldID =?;', array($fieldID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取字段列表
     * @param $tableID int 数据表ID
     * @return bool|array
     */
    public function getField(&$tableID)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_database_table_field.tableID,eo_database_table_field.fieldID,eo_database_table_field.fieldName,eo_database_table_field.fieldType,eo_database_table_field.fieldLength,eo_database_table_field.isNotNull,eo_database_table_field.isPrimaryKey,eo_database_table_field.fieldDescription,eo_database_table_field.defaultValue FROM eo_database_table_field WHERE eo_database_table_field.tableID = ?;', array($tableID));

        if (empty($result))
            return FALSE;
        else
            return $result;
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
     * @param $defaultValue string 默认值
     * @return bool
     */
    public function editField(&$fieldID, &$fieldName, &$fieldType, &$fieldLength, &$isNotNull, &$isPrimaryKey, &$fieldDesc, $defaultValue)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_database_table_field SET eo_database_table_field.fieldName =?,eo_database_table_field.fieldType=?,eo_database_table_field.fieldLength =?,eo_database_table_field.isNotNull =?,eo_database_table_field.isPrimaryKey =?,eo_database_table_field.fieldDescription =?,eo_database_table_field.defaultValue =? WHERE eo_database_table_field.fieldID =?;', array(
            $fieldName,
            $fieldType,
            $fieldLength,
            $isNotNull,
            $isPrimaryKey,
            $fieldDesc,
            $defaultValue,
            $fieldID
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

}

?>