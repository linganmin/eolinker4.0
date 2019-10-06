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
class DatabaseModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * get userType from database
     * 获取数据字典用户类型
     * @param $dbID int 数据库ID
     * @return bool|int
     */
    public function getUserType(&$dbID)
    {
        $dao = new AuthorizationDao();
        $result = $dao->getDatabaseUserType($_SESSION['userID'], $dbID);
        if ($result === FALSE) {
            return -1;
        } else {
            return $result;
        }
    }

    /**
     * add database
     * 添加数据库
     * @param $dbName string 数据库名
     * @param $dbVersion string 数据库版本，默认1.0
     * @return bool|int
     */
    public function addDatabase(&$dbName, &$dbVersion = "1.0")
    {
        $databaseDao = new DatabaseDao;
        return $databaseDao->addDatabase($dbName, $dbVersion, $_SESSION['userID']);
    }

    /**
     * delete database
     * 删除数据库
     * @param $dbID int 数据库ID
     * @return bool
     */
    public function deleteDatabase(&$dbID)
    {
        $databaseDao = new DatabaseDao;
        if ($dbID = $databaseDao->checkDatabasePermission($dbID, $_SESSION['userID'])) {
            return $databaseDao->deleteDatabase($dbID);
        } else
            return FALSE;
    }

    /**
     * get all database list
     * 获取数据库列表
     * @return bool|array
     */
    public function getDatabaseList()
    {
        $databaseDao = new DatabaseDao;
        return $databaseDao->getDatabaseList($_SESSION['userID']);
    }

    /**
     * 获取数据库详情
     * @param $dbID
     * @return array|bool
     */
    public function getDatabase(&$dbID)
    {
        $dbDao = new DatabaseDao();
        return $dbDao->getDatabase($dbID, $_SESSION['userID']);
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
        $databaseDao = new DatabaseDao;
        if ($dbID = $databaseDao->checkDatabasePermission($dbID, $_SESSION['userID'])) {
            return $databaseDao->editDatabase($dbID, $dbName, $dbVersion);
        } else
            return FALSE;
    }

    /**
     * Import database table which export from mysql
     * 导入数据表
     * @param $dbName
     * @param $tables array 数据库表
     * @return bool
     */
    public function importDatabase(&$dbName, &$tables)
    {
        $userID = $_SESSION['userID'];
        $databaseDao = new DatabaseDao;
        $tableList = array();
        foreach ($tables as $table) {
            $fieldList = array();
            //将各字段信息分割成一行一个
            preg_match_all('/.+?[\r\n]+/s', $table['tableField'], $fields);

            $primaryKeys = '';
            foreach ($fields[0] as $field) {
                $field = trim($field);
                //以'`'开头的是字段
                if (strpos($field, '`') === 0) {
                    $fieldName = substr($field, 1, strpos(substr($field, 1), '`'));
                    //将字段类型和长度的混合提取出来
                    preg_match('/`\\s(.+?)\\s/', $field, $type);
                    preg_match("/COMMENT.*'(.*?)'/", $field, $fieldDesc);
                    if (empty($fieldDesc)) {
                        preg_match("/comment.*'(.*?)'/", $field, $fieldDesc);
                    }
                    if (!$type[1]) {
                        $type[1] = substr($field, strlen($fieldName) + 3, strpos(substr($field, strlen($fieldName) + 3), ','));
                    }
                    if (!$type[1]) {
                        $type[1] = substr($field, strlen($fieldName) + 3);
                    }
                    if (strpos($type[1], '(')) {
                        $fieldType = substr($type[1], 0, strpos($type[1], '('));
                        if (preg_match('/\([0-9]{1,10}/', $type[1], $match)) {
                            //长度用左括号右边第一个10位内数字表示
                            $fieldLength = substr($match[0], 1);
                        } else {
                            $fieldLength = '0';
                        }
                    } else {
                        $fieldType = $type[1];
                        //未注明长度，默认为0
                        $fieldLength = '0';
                    }

                    if (strpos($field, 'NOT NULL') !== FALSE) {
                        $isNotNull = 1;
                    } else
                        $isNotNull = 0;

                    $fieldList[] = array(
                        'fieldName' => $fieldName,
                        'fieldDesc' => $fieldDesc[1],
                        'fieldType' => $fieldType,
                        'fieldLength' => $fieldLength,
                        'isNotNull' => $isNotNull
                    );
                }

                //以PRIMARY KEY开头的是整个表中主键的集合
                if (strpos($field, 'PRIMARY') !== FALSE) {
                    $table['primaryKey'] = $table['primaryKey'] . substr($field, strpos($field, '('));
                }
            }

            //判断各字段是否为主键
            foreach ($fieldList as &$tableField) {
                if (strpos($table['primaryKey'], $tableField['fieldName']) !== FALSE) {
                    $tableField['isPrimaryKey'] = 1;
                } else {
                    $tableField['isPrimaryKey'] = 0;
                }
            }
            $tableList[] = array(
                'tableName' => $table['tableName'],
                'tableDesc' => $table['tableDesc'],
                'fieldList' => $fieldList
            );
            unset($fieldList);
        }

        if (isset($tableList[0])) {
            $databaseType = 0;
            return $databaseDao->importDatabase($dbName, $tableList, $databaseType, $userID);
        } else {
            return FALSE;
        }
    }

    /**
     * Import database by database's data which export from the api named exportDatabase
     * 导入数据字典界面数据库
     * @param $data string 数据库相关数据
     * @return bool
     */
    public function importDatabaseByJson(&$data)
    {
        $user_id = $_SESSION['userID'];

        $service = new DatabaseDao;
        $result = $service->importDatabaseByJson($user_id, $data);
        if ($result)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Export database's data
     * 数据表导出成为json格式
     * @param $dbID int 数据库ID
     * @return bool|string
     */
    public function exportDatabase(&$dbID)
    {
        $dao = new DatabaseDao;
        if ($dao->checkDatabasePermission($dbID, $_SESSION['userID'])) {
            $dumpJson = json_encode($dao->getDatabaseInfo($dbID));
            $fileName = 'eoLinker_export_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, $dumpJson)) {
                return $fileName;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    public function importOracleDatabase(&$database_name, &$tables)
    {
        $user_id = $_SESSION['userID'];

        $database_dao = new DatabaseDao();

        $table_list = array();
        foreach ($tables as $table) {
            $field_list = array();
            $fields = array();
            // 将各字段信息分割成一行一个
            preg_match_all('/.+?[\r\n]+/s', $table ['tableField'], $fields);

            foreach ($fields [0] as $field) {
                $field = trim($field);
                // 以'`'开头的是字段
                if (strpos($field, '"') === 0) {
                    $field_name = substr($field, 1, strpos(substr($field, 1), '"'));
                    // 将字段类型和长度的混合提取出来
                    $type = array();
                    preg_match('/`\\s(.+?)\\s/', $field, $type);
                    if (!$type [1]) {
                        $type [1] = substr($field, strlen($field_name) + 3, strpos(substr($field, strlen($field_name) + 3), ','));
                    }
                    if (!$type [1]) {
                        $type [1] = substr($field, strlen($field_name) + 3);
                    }
                    if (strpos($type [1], '(')) {
                        $field_type = substr($type [1], 0, strpos($type [1], '('));
                        $match = array();
                        if (preg_match('/\([0-9]{1,10}/', $type [1], $match)) {
                            // 长度用左括号右边第一个10位内数字表示
                            $field_length = substr($match [0], 1);
                        } else {
                            $field_length = '0';
                        }
                    } else {
                        $field_type = $type [1];
                        // 未注明长度，默认为0
                        $field_length = '0';
                    }

                    if (strpos($field, 'NOT NULL') !== FALSE) {
                        $is_not_null = 1;
                    } else
                        $is_not_null = 0;

                    if (strpos($table ['primaryKeySql'], $field_name) !== FALSE) {
                        $is_primary_key = 1;
                    } else {
                        $is_primary_key = 0;
                    }
                    $field_desc = array();
                    if (strpos($table ['commentSql'], $field_name) !== FALSE) {
                        preg_match('/COMMENT ON COLUMN.*?' . $field_name . '.*?\'(.*?)\'.*?;/', $table ['commentSql'], $field_desc);
                    }

                    $field_list [] = array(
                        'fieldName' => $field_name,
                        'fieldType' => $field_type,
                        'fieldLength' => $field_length,
                        'isNotNull' => $is_not_null,
                        'isPrimaryKey' => $is_primary_key,
                        'fieldDesc' => $field_desc [1]
                    );
                }
            }
            $table_list [] = array(
                'tableName' => $table ['tableName'],
                'tableDesc' => $table ['tableDesc'],
                'fieldList' => $field_list
            );
            unset($field_list);
        }

        if (isset($table_list [0])) {
            $database_type = 1;
            return $database_dao->importDatabase($database_name, $table_list, $database_type, $user_id);
        } else
            return FALSE;
    }
}

?>