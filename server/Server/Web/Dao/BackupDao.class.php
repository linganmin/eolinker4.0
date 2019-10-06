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
class BackupDao
{
    /**
     * 获取备份项目的相关信息
     * @param $project_id
     * @return array|bool
     */
    public function getProjectBackupData(&$project_id)
    {
        $db = getDatabase();

        $dumpJson = array();

        // 获取项目信息
        $dumpJson['projectInfo'] = $db->prepareExecute("SELECT * FROM eo_project WHERE eo_project.projectID = ?;", array(
            $project_id
        ));
        $backup_time = date('Y/m/d H:i', time());
        $dumpJson['projectInfo']['projectName'] = "开源备份-{$dumpJson['projectInfo']['projectName']}-{$backup_time}";

        $dumpJson['apiGroupList'] = array();
        // 获取接口父分组信息
        $apiGroupList = $db->prepareExecuteAll("SELECT * FROM eo_api_group WHERE eo_api_group.projectID = ? AND eo_api_group.isChild = 0;", array(
            $project_id
        ));

        $i = 0;
        foreach ($apiGroupList as $apiGroup) {
            $dumpJson['apiGroupList'][$i] = $apiGroup;
            // 获取接口信息
            $apiList = $db->prepareExecuteAll("SELECT eo_api_cache.apiJson FROM eo_api_cache WHERE eo_api_cache.projectID = ? AND eo_api_cache.groupID = ?;", array(
                $project_id,
                $apiGroup['groupID']
            ));
            $dumpJson['apiGroupList'][$i]['apiList'] = array();
            $j = 0;
            foreach ($apiList as $api) {
                $dumpJson['apiGroupList'][$i]['apiList'][$j] = json_decode($api['apiJson'], TRUE);
                // $dumpJson['apiGroupList'][$i]['apiList'][$j]['baseInfo']['starred'] = $api['starred'];
                ++$j;
            }

            $dumpJson['apiGroupList'][$i]['apiGroupChildList'] = array();
            $apiGroupChildList = $db->prepareExecuteAll('SELECT * FROM eo_api_group WHERE eo_api_group.projectID = ? AND eo_api_group.parentGroupID = ?', array(
                $project_id,
                $apiGroup['groupID']
            ));
            $k = 0;
            if ($apiGroupChildList) {
                foreach ($apiGroupChildList as $apiChildGroup) {
                    $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k] = $apiChildGroup;
                    // 获取接口信息
                    $apiList = $db->prepareExecuteAll("SELECT * FROM eo_api_cache WHERE eo_api_cache.projectID = ? AND eo_api_cache.groupID = ?;", array(
                        $project_id,
                        $apiChildGroup['groupID']
                    ));
                    $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiList'] = array();
                    $l = 0;
                    foreach ($apiList as $api) {
                        $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiList'][$l] = json_decode($api['apiJson'], TRUE);
                        // $dumpJson['apiGroupList'][$i]['apiGroupChildList'][$k]['apiList'][$l]['baseInfo']['starred'] = $api['starred'];
                        ++$l;
                    }
                    ++$k;
                }
            }
            ++$i;
        }

        $dumpJson['statusCodeGroupList'] = array();
        // 获取状态码分组信息
        $statusCodeGroupList = $db->prepareExecuteAll("SELECT * FROM eo_project_status_code_group WHERE eo_project_status_code_group.projectID = ? AND isChild = 0;", array(
            $project_id
        ));

        $i = 0;
        foreach ($statusCodeGroupList as $statusCodeGroup) {
            $dumpJson['statusCodeGroupList'][$i] = $statusCodeGroup;

            // 获取状态码信息
            $statusCodeList = $db->prepareExecuteAll("SELECT * FROM eo_project_status_code WHERE eo_project_status_code.groupID = ?;", array(
                $statusCodeGroup['groupID']
            ));

            $dumpJson['statusCodeGroupList'][$i]['statusCodeList'] = array();
            $j = 0;
            foreach ($statusCodeList as $statusCode) {
                $dumpJson['statusCodeGroupList'][$i]['statusCodeList'][$j] = $statusCode;
                ++$j;
            }
            $statusCodeGroupChildList = $db->prepareExecuteAll("SELECT * FROM eo_project_status_code_group WHERE eo_project_status_code_group.projectID = ? AND parentGroupID = ?;", array(
                $project_id,
                $statusCodeGroup['groupID']
            ));
            $k = 0;
            $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'] = array();
            if ($statusCodeGroupChildList) {
                foreach ($statusCodeGroupChildList as $statusCodeChildGroup) {
                    $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k] = $statusCodeChildGroup;

                    // 获取状态码信息
                    $statusCodeList = $db->prepareExecuteAll("SELECT * FROM eo_project_status_code WHERE eo_project_status_code.groupID = ?;", array(
                        $statusCodeChildGroup['groupID']
                    ));

                    $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k]['statusCodeList'] = array();
                    $l = 0;
                    foreach ($statusCodeList as $statusCode) {
                        $dumpJson['statusCodeGroupList'][$i]['statusCodeGroupChildList'][$k]['statusCodeList'][$l] = $statusCode;
                        ++$l;
                    }
                    ++$k;
                }
            }
            ++$i;
        }

        $dumpJson['env'] = array();
        // 获取环境管理相关信息
        $envList = $db->prepareExecuteAll('SELECT eo_api_env.envID,eo_api_env.envName FROM eo_api_env WHERE eo_api_env.projectID = ?;', array($project_id));
        if ($envList) {
            foreach ($envList as &$env) {
                $front_uri = $db->prepareExecute('SELECT eo_api_env_front_uri.applyProtocol,eo_api_env_front_uri.uri,eo_api_env_front_uri.uriID FROM eo_api_env_front_uri WHERE eo_api_env_front_uri.envID = ?;', array(
                    $env['envID']
                ));
                $headers = $db->prepareExecuteAll('SELECT eo_api_env_header.applyProtocol,eo_api_env_header.headerName,eo_api_env_header.headerValue,eo_api_env_header.headerID FROM eo_api_env_header WHERE eo_api_env_header.envID = ?;', array(
                    $env['envID']
                ));
                $params = $db->prepareExecuteAll('SELECT eo_api_env_param.paramKey,eo_api_env_param.paramValue,eo_api_env_param.paramID FROM eo_api_env_param WHERE eo_api_env_param.envID = ?;', array(
                    $env['envID']
                ));
                $env['frontURI'] = $front_uri ? $front_uri : array();
                $env['headerList'] = $headers ? $headers : array();
                $env['paramList'] = $params ? $params : array();
            }
        }
        $dumpJson['env'] = $envList ? $envList : array();

        $dumpJson['pageGroupList'] = array();
        //获取项目文档分组信息
        $documentGroupList = $db->prepareExecuteAll('SELECT eo_project_document_group.* FROM eo_project_document_group WHERE eo_project_document_group.projectID = ? AND eo_project_document_group.isChild = 0;', array(
            $project_id
        ));
        $i = 0;
        foreach ($documentGroupList as $documentGroup) {
            $dumpJson['pageGroupList'][$i] = $documentGroup;
            $dumpJson['pageGroupList'][$i]['pageList'] = array();
            //获取文档信息
            $documentList = $db->prepareExecuteAll('SELECT eo_project_document.documentID AS pageID,eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID AS authorID FROM eo_project_document WHERE eo_project_document.groupID = ?;', array($documentGroup['groupID']));

            $j = 0;
            foreach ($documentList as $document) {
                $dumpJson['pageGroupList'][$i]['pageList'][$j] = $document;
                $dumpJson['pageGroupList'][$i]['pageList'][$j]['groupName'] = $documentGroup['groupName'];
                $j++;
            }

            $documentGroupChildList = $db->prepareExecuteAll('SELECT eo_project_document_group.* FROM eo_project_document_group WHERE eo_project_document_group.projectID = ? AND eo_project_document_group.parentGroupID = ? AND eo_project_document_group.isChild = 1;', array(
                $project_id,
                $documentGroup['groupID']
            ));

            $k = 0;
            $dumpJson['pageGroupList'][$i]['pageGroupChildList'] = array();
            if ($documentGroupChildList) {
                foreach ($documentGroupChildList as $documentChildGroup) {
                    $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k] = $documentChildGroup;
                    $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k]['pageList'] = array();
                    //获取文档信息
                    $documentList = $db->prepareExecuteAll('SELECT eo_project_document.documentID AS pageID,eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID AS authorID FROM eo_project_document WHERE eo_project_document.groupID = ?;', array($documentChildGroup['groupID']));
                    $l = 0;
                    foreach ($documentList as $document) {
                        $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k]['pageList'][$l] = $document;
                        $dumpJson['pageGroupList'][$i]['pageGroupChildList'][$k]['pageList'][$l]['groupName'] = $documentChildGroup['groupName'];
                        $l++;
                    }
                    $k++;
                }
            }
            $i++;
        }

        if (empty($dumpJson))
            return FALSE;
        else
            return $dumpJson;
    }

    private $eol = PHP_EOL;
    private $sqlEnd = ';';

    /**
     * 数据库备份基本信息
     * @return string
     */
    private function retrieve()
    {
        $value = '';
        $value .= '/*' . $this->eol;
        $value .= 'eoLinker MySQL Data Transfer' . $this->eol;
        $value .= $this->eol;
        $value .= 'Source Host     :' . DB_URL . ':' . DB_PORT . $this->eol;
        $value .= 'Source Database :' . DB_NAME . $this->eol;
        $value .= 'Date            :' . date('Y-m-d H:i:s', time()) . $this->eol;
        $value .= $this->eol;
        $value .= '*/';
        $value .= $this->eol . $this->eol;
        return $value;
    }

    /**
     * 插入表结构
     * @param $table
     * @param $show_table
     * @return string
     */
    private function create_table_structure($table, $show_table)
    {
        $sql = '';
        $sql .= "-- ----------------------------" . $this->eol;
        $sql .= "-- Table structure for " . $table . $this->eol;
        $sql .= "-- ----------------------------" . $this->eol;

        // 如果存在则删除表
        $sql .= "DROP TABLE IF EXISTS `" . $table . '`' . $this->sqlEnd . $this->eol;
        // 获取详细表信息
        $sql .= $show_table['Create Table'];
        $sql .= $this->sqlEnd . $this->eol;
        // 加上
        $sql .= $this->eol;
        $sql .= "-- ----------------------------" . $this->eol;
        $sql .= "-- Records of " . $table . $this->eol;
        $sql .= "-- ----------------------------" . $this->eol;
        return $sql;
    }

    /**
     * 插入单条记录
     *
     * @param $table_name
     * @param $fields
     * @return string
     */
    private function insert_record($table_name, $fields)
    {
        // sql字段逗号分割
        $insert = '';
        $comma = "";
        $insert .= "INSERT INTO `$table_name` VALUES (";
        // 循环每个子段下面的内容
        foreach ($fields as $key => $value) {
            $insert .= $comma . "'" . addslashes($value) . "'";
            $comma = ',';
        }
        $insert .= ");" . $this->eol;
        return $insert;
    }

    /**
     * 获取数据库备份sql脚本
     * @return string
     */
    public function getDatabaseBackupSql()
    {
        $db = getDatabase();

        $sql = '';
        //插入dump信息
        $sql .= $this->retrieve();
        //查询所有表
        $tables = $db->queryAll('SHOW TABLES');

        defined('DB_TABLE_PREFIXION') or define('DB_TABLE_PREFIXION', 'eo');
        foreach ($tables as $table) {
            $table_name = $table['Tables_in_' . DB_NAME];
            if (!(strpos($table_name, DB_TABLE_PREFIXION) === 0))
                continue;

            //获取表结构
            $show_table = $db->query('SHOW CREATE TABLE `' . $table_name . '`');
            $sql .= $this->create_table_structure($table_name, $show_table);

            $columns = $db->queryAll('SELECT * FROM ' . $table_name);
            if (!empty($columns)) {
                foreach ($columns as $column) {
                    $sql .= $this->insert_record($table_name, $column);
                }
            }

            $sql .= $this->eol;
        }

        return $sql;
    }
}