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

class UpdateDao
{
    /**
     * 获取所有项目表
     */
    public function getAllTable()
    {
        $db = getDatabase();
        $result = $db->queryAll('SHOW TABLES');
        return $result;
    }

    /**
     * 获取相应表的字段名
     * @param $tableName string 数据表名
     * @return bool|array
     */
    public function getTableColumns(&$tableName)
    {
        $db = getDatabase();
        $result = $db->queryAll('SHOW COLUMNS FROM ' . $tableName);
        return $result;
    }

    /**
     * 修改数据库表名称
     * @param $oldTableName string 旧数据表名
     * @param $newTableName string 新数据表名
     * @return bool
     */
    public function changeTableName(&$oldTableName, $newTableName)
    {
        $db = getDatabase();
        $db->execute('RENAME TABLE ' . $oldTableName . ' TO ' . $newTableName);
        return TRUE;
    }

    /**
     * 删除数据库表
     * @param $oldTables array 旧数据表
     * @return bool
     */
    public function dropTable(&$oldTables)
    {
        $db = getDatabase();
        $db->beginTransaction();
        foreach ($oldTables as &$oldTable) {
            $db->execute("DROP TABLE IF EXISTS {$oldTable['tableName']}");
        }
        $db->commit();
        return TRUE;
    }

    /**
     * 转移数据
     * @param $oldTables array 旧数据表
     * @param $newTables array 新数据表
     * @return bool
     */
    public function dumpData(&$oldTables, &$newTables)
    {
        $db = getDatabase();
        $db->beginTransaction();
        $oldTablesCount = count($oldTables) - 1;
        $newTablesCount = count($newTables) - 1;
        for ($i = 0, $j = 0; $i <= $oldTablesCount; $i++, $j = 0) {
            //检查是否有相同的表
            for (; $j <= $newTablesCount; $j++) {
                //如果有相同的表
                if ($oldTables[$i]['tableName'] == 'old_' . $newTables[$j]['tableName']) {
                    $columnSQL = '';
                    foreach ($oldTables[$i]['columns'] as $column) {
                        $columnSQL .= "`{$column}`,";
                    }

                    //过滤空参数
                    if (empty($columnSQL))
                        continue;
                    $columnSQL = substr($columnSQL, 0, -1);
                    $db->execute("INSERT INTO {$newTables[$j]['tableName']} ($columnSQL) SELECT $columnSQL FROM {$oldTables[$i]['tableName']}");

                    if ($db->getAffectRow() < 1) {
                        $db->rollback();
                        return FALSE;
                    }
                } else
                    continue;
            }
        }

        $db->commit();
        return TRUE;
    }

    /**
     * 更新数据库
     * @return bool
     * @throws Exception
     */
    public function updateDatabase()
    {
        $db = getDatabase();
        $db->beginTransaction();

        try {
            //查询所有表
            $oldTablesCache = $db->queryAll('SHOW TABLES');
            $oldTables = array();

            $i = 0;
            defined('DB_TABLE_PREFIXION') or define('DB_TABLE_PREFIXION', 'eo');
            foreach ($oldTablesCache as $oldTable) {
                if (!(strpos($oldTable['Tables_in_' . DB_NAME], DB_TABLE_PREFIXION) === 0))
                    continue;
                //获取表之后，遍历新建数组以存放表字段
                $oldTables[$i]['tableName'] = $oldTable['Tables_in_' . DB_NAME];

                //遍历获取所有表的字段名
                $columnFields = $db->queryAll('SHOW COLUMNS FROM ' . $oldTables[$i]['tableName']);
                foreach ($columnFields as $field) {
                    $oldTables[$i]['columns'][] = $field['Field'];
                }
                ++$i;
            }
            unset($i);
            //开始重命名所有旧数据表
            foreach ($oldTables as &$oldTable) {
                $db->execute('RENAME TABLE ' . $oldTable['tableName'] . ' TO ' . 'old_' . $oldTable['tableName']);
                $oldTable['tableName'] = 'old_' . $oldTable['tableName'];
            }

            //开始创建新的数据表
            //读取数据库文件
            $sql = file_get_contents(PATH_FW . DIRECTORY_SEPARATOR . 'db/eolinker_os_mysql.sql');
            $sqlArray = array_filter(explode(';', $sql));

            foreach ($sqlArray as $query) {
                $db->query($query);
                if ($db->getError()) {
                    $db->rollback();
                    return FALSE;
                }
            }

            //获取新的数据表
            $newTablesCache = $db->queryAll('SHOW TABLES');
            $newTables = array();
            $i = 0;
            foreach ($newTablesCache as $newTable) {
                //获取表之后，遍历新建数组以存放表字段
                //先判断是否含有old关键字，有则跳过
                if (!strstr($newTable['Tables_in_' . DB_NAME], 'old_'))
                    $newTables[$i]['tableName'] = $newTable['Tables_in_' . DB_NAME];
                else
                    continue;

                //遍历获取所有表的字段名
                $columnFields = $db->queryAll('SHOW COLUMNS FROM ' . $newTables[$i]['tableName']);
                foreach ($columnFields as $field) {
                    $newTables[$i]['columns'][] = $field['Field'];
                }
                ++$i;
            }
            unset($i);

            //开始转移数据
            $oldTablesCount = count($oldTables) - 1;
            $newTablesCount = count($newTables) - 1;
            for ($i = 0, $j = 0; $i <= $oldTablesCount; $i++, $j = 0) {
                //检查是否有相同的表
                for (; $j <= $newTablesCount; $j++) {
                    //如果有相同的表
                    if ($oldTables[$i]['tableName'] == 'old_' . $newTables[$j]['tableName']) {
                        $columnSQL = '';
                        foreach ($oldTables[$i]['columns'] as $column) {
                            $columnSQL .= "`{$column}`,";
                        }

                        //过滤空参数
                        if (empty($columnSQL))
                            continue;
                        $columnSQL = substr($columnSQL, 0, -1);
                        $db->execute("INSERT INTO {$newTables[$j]['tableName']} ($columnSQL) SELECT $columnSQL FROM {$oldTables[$i]['tableName']}");

                        if ($db->getAffectRow() < 1) {
                            $db->rollback();
                            return FALSE;
                        }
                    } else
                        continue;
                }
            }

            //删除旧表格
            foreach ($oldTables as &$oldTable) {
                $db->execute("DROP TABLE IF EXISTS {$oldTable['tableName']}");
            }

            $db->commit();
            return TRUE;
        } catch (\Exception $e) {
            $db->rollback();
            throw new Exception($e->getMessage(), 100001);
        }
    }

}

?>