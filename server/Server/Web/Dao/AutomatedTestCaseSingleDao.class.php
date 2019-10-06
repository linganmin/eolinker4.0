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
class AutomatedTestCaseSingleDao
{
    /**
     * 新增用例单例
     * @param $case_id
     * @param $case_data
     * @param $case_code
     * @param $status_code
     * @param $match_type
     * @param $match_rule
     * @param $api_name
     * @param $api_uri
     * @param $api_request_type
     * @param $order_number
     * @return bool
     */
    public function addSingleTestCase(&$case_id, &$case_data, &$case_code, &$status_code, &$match_type, &$match_rule, &$api_name, &$api_uri, &$api_request_type, &$order_number)
    {
        $db = getDatabase();
        if ($order_number) {
            $db->prepareExecuteAll('UPDATE eo_project_test_case_single SET orderNumber = orderNumber + 1 WHERE eo_project_test_case_single.caseID = ? AND eo_project_test_case_single.orderNumber >= ?;', array(
                $case_id,
                $order_number
            ));
        }
        if ($order_number === 0) {
            $max_order_number = $db->prepareExecute('SELECT MAX(eo_project_test_case_single.orderNumber) AS number FROM eo_project_test_case_single WHERE eo_project_test_case_single.caseID = ?;', array(
                $case_id
            ));
            $order_number = ($max_order_number['number'] ? $max_order_number['number'] : 0) + 1;
        }
        $db->prepareExecute('INSERT INTO eo_project_test_case_single(eo_project_test_case_single.caseID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule, eo_project_test_case_single.apiName, eo_project_test_case_single.apiURI, eo_project_test_case_single.apiRequestType,eo_project_test_case_single.orderNumber) VALUES (?,?,?,?,?,?,?,?,?,?);', array(
            $case_id,
            $case_data,
            $case_code,
            $status_code,
            $match_type,
            $match_rule,
            $api_name,
            $api_uri,
            $api_request_type,
            $order_number
        ));
        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }

    /**
     * 修改用例单例
     * @param $case_id
     * @param $conn_id
     * @param $case_data
     * @param $case_code
     * @param $status_code
     * @param $match_type
     * @param $match_rule
     * @param $api_name
     * @param $api_uri
     * @param $api_request_type
     * @return bool
     */
    public function editSingleTestCase(&$case_id, &$conn_id, &$case_data, &$case_code, &$status_code, &$match_type, &$match_rule, &$api_name, &$api_uri, &$api_request_type)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_project_test_case_single SET eo_project_test_case_single.caseData = ?,eo_project_test_case_single.caseCode = ?, eo_project_test_case_single.statusCode = ?, eo_project_test_case_single.matchType = ?, eo_project_test_case_single.matchRule = ?, eo_project_test_case_single.apiName = ?, eo_project_test_case_single.apiURI = ?,eo_project_test_case_single.apiRequestType = ? WHERE eo_project_test_case_single.caseID = ? AND eo_project_test_case_single.connID = ?;', array(
            $case_data,
            $case_code,
            $status_code,
            $match_type,
            $match_rule,
            $api_name,
            $api_uri,
            $api_request_type,
            $case_id,
            $conn_id
        ));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取单例列表
     * @param $case_id
     * @return bool
     */
    public function getSingleTestCaseList(&$case_id)
    {
        $db = getDatabase();
        $db->beginTransaction();
        $result = $db->prepareExecuteAll('SELECT * FROM eo_project_test_case_single WHERE eo_project_test_case_single.caseID = ? ORDER BY eo_project_test_case_single.orderNumber ASC;', array($case_id));
        $i = 0;
        $index = 1;
        if (is_array($result)) {
            foreach ($result as &$single_case) {
                if (($single_case['orderNumber'] === NULL && $index == 1) || $index > 1) {
                    if (preg_match_all('#<response\[(\d+)\]#', $single_case['caseData'], $match) > 0) {
                        foreach ($match[1] as $response_id) {
                            $single_case['caseData'] = str_replace("<response[" . $response_id, "<response[" . $result[$i]['connID'], $single_case['caseData']);
                        }
                    }
                    $db->prepareExecute('UPDATE eo_project_test_case_single SET eo_project_test_case_single.orderNumber = ?,eo_project_test_case_single.caseData = ? WHERE eo_project_test_case_single.connID = ?;', array(
                        $index,
                        $single_case['caseData'],
                        $single_case['connID']
                    ));
                    if ($db->getAffectRow() < 1) {
                        $db->rollback();
                        return FALSE;
                    }
                    $single_case['orderNumber'] = $index;
                    $index++;
                }
                if ($single_case['matchType'] == 2 && !empty($single_case['matchRule'])) {
                    $single_case['matchRule'] = json_decode($single_case['matchRule'], TRUE);
                }

            }
        }
        if ($result) {
            $db->commit();
            return $result;
        } else {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * 获取单例详情
     * @param $project_id
     * @param $conn_id
     * @return bool
     */
    public function getSingleTestCaseInfo(&$project_id, &$conn_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT * FROM eo_project_test_case_single INNER JOIN eo_project_test_case ON eo_project_test_case.caseID = eo_project_test_case_single.caseID WHERE eo_project_test_case_single.connID = ? AND eo_project_test_case.projectID = ?;', array($conn_id, $project_id));

        if ($result['matchType'] == 2 && !empty($result['matchRule'])) {
            $result['matchRule'] = json_decode($result['matchRule'], TRUE);
        }
        if ($result)
            return $result;
        else
            return FALSE;
    }

    /**
     * 删除测试用例
     * @param $conn_ids
     * @param $project_id
     * @return bool
     */
    public function deleteSingleTestCase(&$conn_ids, &$project_id)
    {
        $db = getDatabase();
        $db->prepareExecute("DELETE FROM eo_project_test_case_single WHERE eo_project_test_case_single.connID IN ($conn_ids) AND eo_project_test_case_single.caseID IN (SELECT eo_project_test_case.caseID FROM eo_project_test_case WHERE eo_project_test_case.projectID = ?);", array(
            $project_id
        ));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取单例列表
     * @param $project_id
     * @return bool
     */
    public function getAllSingleTestCaseList(&$project_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_project_test_case_single.* FROM eo_project_test_case INNER JOIN eo_project_test_case_single ON eo_project_test_case_single.caseID = eo_project_test_case.caseID WHERE eo_project_test_case.projectID = ? ORDER BY eo_project_test_case_single.connID ASC;', array($project_id));
        foreach ($result as &$single_case) {
            if ($single_case['matchType'] == 2 && !empty($single_case['matchRule'])) {
                $single_case['matchRule'] = json_decode($single_case['matchRule'], TRUE);
            }
        }
        if ($result)
            return $result;
        else
            return FALSE;
    }

    /**
     * 检查单例权限
     * @param $conn_id
     * @param $user_id
     * @return bool
     */
    public function checkSingleTestCasePermission(&$conn_id, &$user_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_project_test_case.projectID FROM eo_project_test_case_single LEFT JOIN eo_project_test_case ON eo_project_test_case_single.caseID = eo_project_test_case.caseID LEFT JOIN eo_conn_project ON eo_project_test_case.projectID = eo_conn_project.projectID WHERE eo_project_test_case_single.connID = ? AND eo_conn_project.userID = ?;', array(
            $conn_id,
            $user_id
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['projectID'];
        }
    }

    /**
     * 获取用例名称
     * @param $conn_id
     * @return bool
     */
    public function getTestCastName(&$conn_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT GROUP_CONCAT(DISTINCT(eo_project_test_case.caseName)) AS caseName FROM eo_project_test_case_single LEFT JOIN eo_project_test_case ON eo_project_test_case_single.caseID = eo_project_test_case.caseID WHERE eo_project_test_case_single.connID IN ($conn_id);", array());
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['caseName'];
        }
    }

    /**
     * 根据connID获取用例ID
     * @param $conn_id
     * @return bool
     */
    public function getCaseIDByConnID(&$conn_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT eo_project_test_case_single.caseID FROM eo_project_test_case_single WHERE eo_project_test_case_single.connID IN ($conn_id);", array());
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['caseID'];
        }
    }

    /**
     * 获取所有api列表
     * @param $project_id
     * @return bool
     */
    public function getApiList(&$project_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_api_cache.apiJson FROM eo_api INNER JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID INNER JOIN eo_api_cache ON eo_api.apiID = eo_api_cache.apiID WHERE eo_api_group.projectID = ? AND eo_api.removed = 0  ORDER BY eo_api.apiName DESC;", array(
            $project_id
        ));
        foreach ($result as &$api) {
            $api_json = json_decode($api['apiJson'], TRUE);
            $api['headerInfo'] = $api_json['headerInfo'];
            $api['requestInfo'] = $api_json['requestInfo'];
            $api['resultInfo'] = $api_json['resultInfo'];
            unset($api['apiJson']);
        }
        if (empty($result))
            return FALSE;
        else
            return $result;
    }
}