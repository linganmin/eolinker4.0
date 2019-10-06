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
class DatabaseController
{
    // return an json object
    //返回Json类型
    private $returnJson = array('type' => 'database');

    /**
     * Checkout login status
     * 检查登录状态
     */
    public function __construct()
    {
        //identity authentication
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * Add database
     * 添加数据库
     */
    public function addDatabase()
    {
        $nameLen = mb_strlen(quickInput('dbName'), 'utf8');
        $dbName = securelyInput('dbName');
        $dbVersion = securelyInput('dbVersion');
        if (!($nameLen >= 1 && $nameLen <= 32)) {
            // illegal database name length
            // 数据库名长度不合法
            $this->returnJson['statusCode'] = '220001';
        } elseif (!(is_float(floatval($dbVersion)) && intval($dbVersion))) {
            // illegal database version
            // 数据库版本不合法
            $this->returnJson['statusCode'] = '220002';
        } else {
            $service = new DatabaseModule;
            $result = $service->addDatabase($dbName, $dbVersion);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['dbID'] = $result;
            } else {
                $this->returnJson['statusCode'] = '220003';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Delete database
     * 删除数据库
     */
    public function deleteDatabase()
    {
        $dbID = securelyInput('dbID');
        $module = new DatabaseModule();
        $userType = $module->getUserType($dbID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        // illegal database ID
        //数据库ID格式非法
        if (!preg_match('/^[0-9]{1,11}$/', $dbID)) {
            $this->returnJson['statusCode'] = '220004';
        } else {
            $service = new DatabaseModule;
            $result = $service->deleteDatabase($dbID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '220005';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get all database list
     * 获取数据库列表
     */
    public function getDatabaseList()
    {
        $service = new DatabaseModule;
        $result = $service->getDatabaseList();

        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['databaseList'] = $result;
        } else {
            $this->returnJson['statusCode'] = '220006';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取数据库详情
     */
    public function getDatabase()
    {
        $dbID = securelyInput('dbID');
        if (!preg_match('/^[0-9]{1,11}$/', $dbID)) {
            $this->returnJson['statusCode'] = '220004';
        } else {
            $service = new DatabaseModule();
            $result = $service->getDatabase($dbID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                $this->returnJson['statusCode'] = '220006';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Edit database
     * 修改数据库
     */
    public function editDatabase()
    {
        $dbID = securelyInput('dbID');
        $module = new DatabaseModule();
        $userType = $module->getUserType($dbID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $nameLen = mb_strlen(quickInput('dbName'), 'utf8');
        $dbName = securelyInput('dbName');
        $dbVersion = securelyInput('dbVersion');
        // illegal database ID
        //数据库ID格式非法
        if (!preg_match('/^[0-9]{1,11}$/', $dbID)) {
            $this->returnJson['statusCode'] = '220004';
        } elseif (!($nameLen >= 1 && $nameLen <= 32)) {
            // illegal database name length
            // 数据库名长度不合法
            $this->returnJson['statusCode'] = '220001';
        } elseif (!(is_float(floatval($dbVersion)) && intval($dbVersion))) {
            // illegal database version
            // 数据库版本不合法
            $this->returnJson['statusCode'] = '220002';
        } else {
            $service = new DatabaseModule;
            $result = $service->editDatabase($dbID, $dbName, $dbVersion);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '220007';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Export database's data
     * 导出数据库
     */
    public function exportDatabase()
    {
        $dbID = quickInput('dbID');
        $module = new DatabaseModule();
        $userType = $module->getUserType($dbID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        // illegal database ID
        //数据库ID格式非法
        if (!preg_match('/^[0-9]{1,11}$/', $dbID)) {
            $this->returnJson['statusCode'] = '220004';
        } else {
            $service = new DatabaseModule;
            $fileName = $service->exportDatabase($dbID);
            if ($fileName) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['fileName'] = $fileName;
            } else {
                //export fail
                //导出失败
                $this->returnJson['statusCode'] = '220000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Import database table which export from mysql
     * 导入SQL格式数据表
     */
    public function importDatabase()
    {
        $fileName = securelyInput("fileName");
        $name_length = mb_strlen(quickInput('fileName'), 'utf8');
        $dumpSql = quickInput('dumpSql');
        // 数据库名称格式非法
        if (!($name_length >= 1 && $name_length <= 32)) {
            $this->returnJson['statusCode'] = '220001';
        } else {
            $dbName = array();
            preg_match("/Source Database.*:[\\s\\S](.*?)[\r\n]+/", $dumpSql, $dbName);
            $db_name = $dbName[1] ? $dbName[1]:$fileName;
            $tables = array();
            //match all statement blocks which create tables using regex
            //正则匹配出所有创建表的语句块
            preg_match_all('/CREATE.*?TABLE[\\s\\S]+?;/', $dumpSql, $sql);
            if (empty($sql)) {
                preg_match_all('/create.*?table[\\s\\S]+?;/', $dumpSql, $sql);
            }
            preg_match_all('/ALTER.*?TABLE[\\s\\S]+?PRIMARY.+?\\)/', $dumpSql, $primaryKeys);
            if (empty($primaryKeys)) {
                preg_match_all('/alter.*?table[\\s\\S]+?primary.+?\\)/', $dumpSql, $primaryKeys);
            }

            foreach ($sql[0] as $tableSql) {
                // get table name from the sql
                //正则提取表名，结果为array，取索引为1
                preg_match('/`(.*?)`/', $tableSql, $tableName);
                preg_match("/COMMENT='(.*?)'/", $tableSql, $tableDesc);
                if (empty($tableDesc)) {
                    preg_match("/comment='(.*?)'/", $tableSql, $tableDesc);
                }
                // get table fields from the sql
                //截取表的字段信息
                $tableField = substr(substr($tableSql, strpos($tableSql, '(') + 1), 0, strlen($tableSql) - strpos($tableSql, '(') - 9);

                if ($primaryKeys[0] != NULL) {
                    $key = '';
                    foreach ($primaryKeys[0] as $primaryKey) {
                        if (strpos($primaryKey, $tableName[1]) !== FALSE) {
                            $key = substr($primaryKey, strpos($primaryKey, '('));
                            break;
                        }
                    }
                }

                $tables[] = array(
                    'tableName' => $tableName[1],
                    'tableDesc' => $tableDesc[1],
                    'tableField' => $tableField,
                    'primaryKey' => $key
                );

            }

            if (count($tables) > 0) {
                $service = new DatabaseModule;
                $result = $service->importDatabase($db_name, $tables);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    //$this -> returnJson['result'] =$result;
                } else {
                    //import fail
                    //导入失败
                    $this->returnJson['statusCode'] = '220008';
                }
            } else {
                //cannot find any create table statement block in the sql data
                //导入的sql文件未匹配到创建表的语句块
                $this->returnJson['statusCode'] = '220009';

            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导入oracle数据库
     */
    public function importOracleDatabase()
    {
        $fileName = securelyInput("fileName");
        $name_length = mb_strlen(quickInput('fileName'), 'utf8');
        $dumpSql = quickInput('dumpSql');
        // 数据库名称格式非法
        if(! ($name_length >= 1 && $name_length <= 32))
        {
            $this->returnJson['statusCode'] = '220001';
        }
        else
        {
            $tables = array ();
            // 正则匹配出所有创建表的语句块
            $sql = array ();
            preg_match_all('/CREATE.*?TABLE[\\s\\S]+?\\n\\)/', $dumpSql, $sql);

            foreach ($sql[0] as $tableSql)
            {
                // 正则提取表名，结果为array，取索引为1
                $tableName = array ();
                preg_match('/".*?"."(.*?)"/', $tableSql, $tableName);
                // 正则提取表注释
                $table_desc = array();
                preg_match('/COMMENT ON TABLE.*?'. $tableName[0].'.*?\'(.*?)\'.*?;/', $dumpSql , $table_desc);
                // 正则匹配出表所有字段注释
                $comment_sql = array();
                preg_match_all('/COMMENT ON COLUMN.*?'. $tableName[0].'.*?;/', $dumpSql, $comment_sql);
                // 正则匹配出表所有主键
                $primary_key_sql = array();
                preg_match_all('/ALTER TABLE.*?'. $tableName[0]. '.*?ADD PRIMARY KEY.*?;/', $dumpSql, $primary_key_sql);
                // 截取表的字段信息
                $tableField = substr(substr($tableSql, strpos($tableSql, '(') + 1), 0, strlen($tableSql) - strpos($tableSql, '(') - 3);
                $tables[] = array (
                    'tableName' => $tableName[1],
                    'tableDesc' => $table_desc[1],
                    'tableField' => $tableField,
                    'commentSql' => implode('',$comment_sql[0]),
                    'primaryKeySql' => implode('',$primary_key_sql[0])
                );
            }

            if(count($tables) > 0)
            {
                $service = new DatabaseModule();
                $result = $service -> importOracleDatabase($fileName, $tables);
                if($result)
                {
                    $this->returnJson['statusCode'] = '000000';
                }
                else
                {
                    // 导入失败
                    $this->returnJson['statusCode'] = '220000';
                }
            }
            else
            {
                // 导入的sql文件未匹配到创建表的语句块
                $this->returnJson['statusCode'] = '220005';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Import database by database's data which export from the api named exportDatabase
     * 导入数据库
     */
    public function importDatabseByJson()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (empty($data)) {
            //empty data
            //数据为空
            $this->returnJson['statusCode'] = '220010';
        } else {
            $service = new DatabaseModule;
            $result = $service->importDatabaseByJson($data);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '220000';
            }
        }
        exitOutput($this->returnJson);
    }

}

?>