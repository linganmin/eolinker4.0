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
class DatabaseDao
{

    /**
     * add database
     * 添加数据库
     * @param $dbName string 数据库名
     * @param $dbVersion string 数据库版本，默认1.0
     * @param $userID int 用户ID
     * @return int|bool
     */
    public function addDatabase(&$dbName, &$dbVersion, &$userID)
    {
        $db = getDatabase();

        $db->beginTransaction();

        $db->prepareExecute('INSERT INTO eo_database (eo_database.dbName,eo_database.dbVersion,eo_database.dbUpdateTime) VALUE (?,?,?);', array(
            $dbName,
            $dbVersion,
            date('Y-m-d H:i:s', time())
        ));

        if ($db->getAffectRow() < 1) {
            $db->rollback();
            return FALSE;
        }
        $dbID = $db->getLastInsertID();

        // 生成数据库与用户的联系
        $db->prepareExecute('INSERT INTO eo_conn_database (eo_conn_database.dbID,eo_conn_database.userID) VALUES (?,?);', array(
            $dbID,
            $userID
        ));

        if ($db->getAffectRow() < 1) {
            $db->rollback();
            return FALSE;
        } else {
            $db->commit();
            return $dbID;
        }
    }

    /**
     * check database permission
     * 检查数据库跟用户是否匹配
     * @param $dbID int 数据库ID
     * @param $userID int 用户ID
     * @return bool|int
     */
    public function checkDatabasePermission(&$dbID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_conn_database.dbID FROM eo_conn_database WHERE eo_conn_database.dbID = ? AND eo_conn_database.userID = ?;', array(
            $dbID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['dbID'];
    }

    /**
     * delete database
     * 删除数据库
     * @param $dbID int 数据库ID
     * @return bool
     */
    public function deleteDatabase(&$dbID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_database WHERE eo_database.dbID = ?;', array(
            $dbID
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else
            return TRUE;
    }

    /**
     * get database list
     * 获取数据库列表
     * @param $userID int 用户ID
     * @return array|bool
     */
    public function getDatabaseList(&$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_database.dbID,eo_database.dbName,eo_database.dbVersion,eo_database.dbUpdateTime,eo_conn_database.userType FROM eo_database INNER JOIN eo_conn_database ON eo_database.dbID = eo_conn_database.dbID WHERE eo_conn_database.userID = ?;', array(
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 获取数据库详情
     * @param $dbID
     * @param $userID
     * @return array|bool
     */
    public function getDatabase(&$dbID, &$userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_database.dbID,eo_database.dbName,eo_database.dbVersion,eo_database.dbUpdateTime,eo_conn_database.userType,eo_database.databaseType FROM eo_database LEFT JOIN eo_conn_database ON eo_database.dbID = eo_conn_database.dbID WHERE eo_conn_database.userID = ? AND eo_database.dbID =?;', array(
            $userID,
            $dbID
        ));

        if (empty($result)) {
            return FALSE;
        } else {
            $res = array('databaseInfo' => $result);
            $table_count = $db->prepareExecute('SELECT COUNT(0) AS count FROM eo_database_table WHERE eo_database_table.dbID = ?;', array(
                $dbID
            ));
            $res['tableCount'] = $table_count['count'] ? $table_count['count'] : 0;
            $field_count = $db->prepareExecute('SELECT COUNT(0) AS count FROM eo_database_table_field WHERE eo_database_table_field.tableID IN (SELECT eo_database_table.tableID FROM eo_database_table WHERE eo_database_table.dbID = ?);', array(
                $dbID
            ));
            $res['fieldCount'] = $field_count['count'] ? $field_count['count'] : 0;
            return $res;
        }
    }

    /**
     * edit database
     * 修改数据库
     * @param $dbID int 数据库ID
     * @param $dbName string 数据库名
     * @param $dbVersion string 数据库版本
     * @return bool
     */
    public function editDatabase(&$dbID, &$dbName, &$dbVersion)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_database SET eo_database.dbName = ?,eo_database.dbVersion =?,eo_database.dbUpdateTime =? WHERE eo_database.dbID =?;', array(
            $dbName,
            $dbVersion,
            date('Y-m-d H:i:s', time()),
            $dbID
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else
            return TRUE;
    }

    /**
     * Import database table which export from mysql
     * 导入数据表
     * @param $dbName
     * @param $tableList array 数据库表
     * @param $databaseType
     * @param $userID
     * @return bool
     */
    public function importDatabase(&$dbName, &$tableList, &$databaseType, &$userID)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();

            $db -> prepareExecute('INSERT INTO eo_database (dbName, dbVersion, dbUpdateTime, databaseType) VALUES (?,?,?,?);', array(
                $dbName,
                '1.0',
                date('Y-m-d H:i:s', time()),
                $databaseType
            ));
            if($db->getAffectRow() <1){
                throw new \PDOException('insert database error.');
            }
            $dbID = $db -> getLastInsertID();

            // 生成数据库与用户的联系
            $db->prepareExecute('INSERT INTO eo_conn_database (eo_conn_database.dbID,eo_conn_database.userID) VALUES (?,?);', array(
                $dbID,
                $userID
            ));
            if($db->getAffectRow()<1) {
                throw new \PDOException('insert database conn error.');
            }

            foreach ($tableList as $table) {
                $db->prepareExecute('INSERT INTO eo_database_table (eo_database_table.dbID,eo_database_table.tableName,eo_database_table.tableDescription) VALUES (?,?,?);', array(
                    $dbID,
                    $table['tableName'],
                    $table['tableDesc']
                ));

                if ($db->getAffectRow() < 1) {
                    throw new \PDOException("add databaseTable error");
                }

                $tableID = $db->getLastInsertID();

                foreach ($table['fieldList'] as $field) {
                    $db->prepareExecute('INSERT INTO eo_database_table_field (eo_database_table_field.tableID,eo_database_table_field.fieldName,eo_database_table_field.fieldType,eo_database_table_field.fieldLength,eo_database_table_field.isNotNull,eo_database_table_field.isPrimaryKey,eo_database_table_field.fieldDescription) VALUES (?,?,?,?,?,?,?);', array(
                        $tableID,
                        $field['fieldName'],
                        $field['fieldType'],
                        $field['fieldLength'],
                        $field['isNotNull'],
                        $field['isPrimaryKey'],
                        $field['fieldDesc']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("add databaseTableField error");
                    }
                }
            }
        } catch (\PDOException $e) {
            $db->rollBack();
            return FALSE;
        }
        $db->commit();
        return TRUE;
    }

    /**
     * get database info
     * 获取数据库信息
     * @param $dbID int 数据库ID
     * @return array|bool
     */
    public function getDatabaseInfo(&$dbID)
    {
        $db = getDatabase();
        $dumpJson = array();
        // 获取数据库信息
        $dumpJson['databaseInfo'] = $db->prepareExecute("SELECT eo_database.dbName AS databaseName,eo_database.dbVersion AS databaseVersion FROM eo_database WHERE eo_database.dbID = ?;", array(
            $dbID
        ));
        // 获取数据库表信息
        $table_list = $db->prepareExecuteAll("SELECT tableID,tableName AS tableName, tableDescription AS tableDesc FROM eo_database_table WHERE eo_database_table.dbID = ?;", array(
            $dbID
        ));
        $dumpJson['tableList'] = array();
        $i = 0;
        foreach ($table_list as $table) {
            $dumpJson['tableList'][$i] = $table;
            // 获取字段信息
            $field_list = $db->prepareExecuteAll("SELECT fieldName,fieldType,fieldLength,isNotNull,isPrimaryKey,fieldDescription AS fieldDesc,defaultValue FROM eo_database_table_field WHERE eo_database_table_field.tableID = ?;", array(
                $table['tableID']
            ));

            $dumpJson['tableList'][$i]['fieldList'] = array();
            $j = 0;
            foreach ($field_list as $field_list) {
                $dumpJson['tableList'][$i]['fieldList'][$j] = $field_list;
                ++$j;
            }
            ++$i;
        }
        if (!empty($dumpJson))
            return $dumpJson;
        else
            return FALSE;
    }

    /**
     * update database update time
     * 更新数据库更新时间
     * @param $dbID int 数据库ID
     */
    public function updateDatabaseUpdateTime(&$dbID)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_database SET eo_database.dbUpdateTime =? WHERE eo_database.dbID =?;', array(
            date('Y-m-d H:i:s', time()),
            $dbID
        ));
    }

    /**
     * Import database by database's data which export from the api named exportDatabase
     * 导入数据字典界面数据库
     * @param $userID int 用户ID
     * @param $data string 数据库相关数据
     * @return bool
     */
    public function importDatabaseByJson(&$userID, &$data)
    {
        $db = getDatabase();

        try {
            // 开始事务
            $db->beginTransaction();
            // 生成数据库
            $db->prepareExecute("INSERT INTO eo_database(eo_database.dbName,eo_database.dbVersion,eo_database.dbUpdateTime)VALUES(?,?,?);", array(
                $data['databaseInfo']['databaseName'],
                $data['databaseInfo']['databaseVersion'],
                date('Y-m-d H:i:s', time())
            ));
            if ($db->getAffectRow() < 1) {
                throw new \PDOException('insert database error');
            }
            $dbID = $db->getLastInsertID();
            // 生成数据库与用户的联系
            $db->prepareExecute('INSERT INTO eo_conn_database (eo_conn_database.dbID,eo_conn_database.userID) VALUES (?,?);', array(
                $dbID,
                $userID
            ));
            if ($db->getAffectRow() < 1) {
                throw new \PDOException('insert conn error');
            }
            foreach ($data['tableList'] as $table) {
                // 生成数据库表
                $db->prepareExecute("INSERT INTO eo_database_table(eo_database_table.dbID,eo_database_table.tableName,eo_database_table.tableDescription)VALUES(?,?,?);", array(
                    $dbID,
                    $table['tableName'],
                    $table['tableDesc']
                ));
                if ($db->getAffectRow() < 1) {
                    throw new \PDOException('insert table error');
                }

                $table_id = $db->getLastInsertID();
                foreach ($table['fieldList'] as $field) {
                    // 生成字段表
                    $db->prepareExecute("INSERT INTO eo_database_table_field(eo_database_table_field.fieldName,eo_database_table_field.fieldType,eo_database_table_field.fieldLength,eo_database_table_field.isNotNull,eo_database_table_field.isPrimaryKey,eo_database_table_field.fieldDescription,eo_database_table_field.tableID,eo_database_table_field.defaultValue)VALUES(?,?,?,?,?,?,?,?);", array(
                        $field['fieldName'],
                        $field['fieldType'],
                        $field['fieldLength'],
                        $field['isNotNull'],
                        $field['isPrimaryKey'],
                        $field['fieldDesc'],
                        $table_id,
                        $field['defaultValue']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException('insert field error');
                    }
                }
            }
        } catch (\PDOException $e) {
            // 出错回滚
            $db->rollBack();
            return FALSE;
        }
        // 提交更改
        $db->commit();
        return TRUE;
    }
}

?>