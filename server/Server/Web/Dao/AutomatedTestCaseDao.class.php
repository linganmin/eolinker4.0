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
class AutomatedTestCaseDao
{
    /**
     * 新增测试用例
     * @param $project_id
     * @param $user_id
     * @param $case_name
     * @param $case_desc
     * @param $case_type
     * @param $group_id
     * @return bool
     */
    public function addTestCase(&$project_id, &$user_id, &$case_name, &$case_desc, &$case_type, &$group_id)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_project_test_case(eo_project_test_case.projectID,eo_project_test_case.userID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.createTime,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case.groupID)VALUES(?,?,?,?,?,?,?,?);', array(
            $project_id,
            $user_id,
            $case_name,
            $case_desc,
            date('Y-m-d H:i:s', time()),
            date('Y-m-d H:i:s', time()),
            $case_type,
            $group_id
        ));
        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }

    /**
     * 修改测试用例
     * @param $project_id
     * @param $user_id
     * @param $case_id
     * @param $case_name
     * @param $case_desc
     * @param $case_type
     * @param $group_id
     * @return bool
     */
    public function editTestCase(&$project_id, &$user_id, &$case_id, &$case_name, &$case_desc, &$case_type, &$group_id)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_project_test_case SET eo_project_test_case.userID = ?,eo_project_test_case.caseName = ?,eo_project_test_case.caseDesc = ?,eo_project_test_case.updateTime = ?,eo_project_test_case.caseType = ?,eo_project_test_case.groupID = ? WHERE eo_project_test_case.caseID = ? AND eo_project_test_case.projectID = ?;', array(
            $user_id,
            $case_name,
            $case_desc,
            date('Y-m-d H:i:s', time()),
            $case_type,
            $group_id,
            $case_id,
            $project_id
        ));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取测试用例列表
     * @param $group_id
     * @return bool
     */
    public function getTestCaseList(&$group_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT eo_conn_project.partnerNickName,eo_user.userNickName,eo_project_test_case.caseID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case_group.groupID,eo_project_test_case_group.parentGroupID,eo_project_test_case_group.groupName FROM eo_project_test_case LEFT JOIN eo_project_test_case_group ON eo_project_test_case_group.groupID = eo_project_test_case.groupID LEFT JOIN eo_conn_project ON eo_conn_project.userID = eo_project_test_case.userID AND eo_conn_project.projectID = eo_project_test_case.projectID LEFT JOIN eo_user ON eo_project_test_case.userID = eo_user.userID WHERE eo_project_test_case_group.groupID = ? OR eo_project_test_case_group.parentGroupID = ? OR eo_project_test_case.groupID IN (SELECT eo_project_test_case_group.groupID FROM eo_project_test_case_group WHERE eo_project_test_case_group.parentGroupID IN (SELECT eo_project_test_case_group.groupID FROM eo_project_test_case_group WHERE eo_project_test_case_group.parentGroupID = ?)) ORDER BY CONCAT(eo_project_test_case.caseName,eo_project_test_case.updateTime) DESC;", array(
            $group_id,
            $group_id,
            $group_id
        ));
        if ($result) {
            foreach ($result as &$case) {
                $topParentGroupID = $db->prepareExecute('SELECT eo_project_test_case_group.parentGroupID FROM eo_project_test_case_group WHERE eo_project_test_case_group.groupID = ? AND eo_project_test_case_group.isChild = 1;', array(
                    $case['parentGroupID']
                ));
                $case['topParentGroupID'] = $topParentGroupID['parentGroupID'] ? $topParentGroupID['parentGroupID'] : $case['parentGroupID'];
            }
            return $result;
        } else
            return FALSE;
    }

    /**
     * 获取所有测试用例列表
     * @param $project_id
     * @return bool
     */
    public function getAllTestCaseList(&$project_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT eo_conn_project.partnerNickName,eo_user.userNickName,eo_project_test_case.caseID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case_group.groupID,eo_project_test_case_group.parentGroupID,eo_project_test_case_group.groupName FROM eo_project_test_case LEFT JOIN eo_project_test_case_group ON eo_project_test_case_group.groupID = eo_project_test_case.groupID LEFT JOIN eo_conn_project ON eo_project_test_case.userID = eo_conn_project.userID AND eo_conn_project.projectID = eo_project_test_case.projectID LEFT JOIN eo_user ON eo_project_test_case.userID = eo_user.userID WHERE eo_project_test_case.projectID = ? ORDER BY CONCAT(eo_project_test_case.caseName,eo_project_test_case.updateTime) DESC;", array(
            $project_id
        ));
        if ($result) {
            foreach ($result as &$case) {
                $topParentGroupID = $db->prepareExecute('SELECT eo_project_test_case_group.parentGroupID FROM eo_project_test_case_group WHERE eo_project_test_case_group.groupID = ? AND eo_project_test_case_group.isChild = 1;', array(
                    $case['parentGroupID']
                ));
                $case['topParentGroupID'] = $topParentGroupID['parentGroupID'] ? $topParentGroupID['parentGroupID'] : $case['parentGroupID'];
            }
            return $result;
        } else
            return FALSE;
    }

    /**
     * 获取测试用例详情
     * @param $case_id
     * @return bool
     */
    public function getTestCaseInfo(&$case_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT eo_conn_project.partnerNickName,eo_user.userNickName,eo_project_test_case.caseID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case_group.groupID,eo_project_test_case_group.parentGroupID,eo_project_test_case_group.groupName FROM eo_project_test_case LEFT JOIN eo_project_test_case_group ON eo_project_test_case_group.groupID = eo_project_test_case.groupID LEFT JOIN eo_conn_project ON eo_project_test_case.userID = eo_conn_project.userID AND eo_conn_project.projectID = eo_project_test_case.projectID LEFT JOIN eo_user ON eo_project_test_case.userID = eo_user.userID WHERE eo_project_test_case.caseID = ?;", array(
            $case_id
        ));
        if ($result)
            return $result;
        else
            return FALSE;
    }

    /**
     * 删除测试用例
     * @param $project_id
     * @param $case_ids
     * @return bool
     */
    public function deleteTestCases(&$project_id, &$case_ids)
    {
        $db = getDatabase();
        $db->beginTransaction();
        $db->prepareExecuteAll("DELETE FROM eo_project_test_case_single WHERE eo_project_test_case_single.caseID IN ($case_ids);", array());
        $db->prepareExecuteAll("DELETE FROM eo_project_test_case WHERE eo_project_test_case.caseID IN ($case_ids) AND eo_project_test_case.projectID = ?", array($project_id));
        if ($db->getAffectRow() > 0) {
            $db->commit();
            return TRUE;
        } else {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * 根据ID获取用例名称
     * @param $case_ids
     * @return bool
     */
    public function getTestCaseName(&$case_ids)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT GROUP_CONCAT(eo_project_test_case.caseName) FROM eo_project_test_case WHERE eo_project_test_case.caseID IN(" . $case_ids . ");");
        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 搜索测试用例
     * @param $project_id
     * @param $tips
     * @return bool
     */
    public function searchTestCase(&$project_id, &$tips)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT eo_conn_project.partnerNickName,eo_user.userNickName,eo_project_test_case.caseID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case_group.groupID,eo_project_test_case_group.parentGroupID,eo_project_test_case_group.groupName FROM eo_project_test_case LEFT JOIN eo_project_test_case_group ON eo_project_test_case_group.groupID = eo_project_test_case.groupID LEFT JOIN eo_conn_project ON eo_project_test_case.userID = eo_conn_project.userID AND eo_conn_project.projectID = eo_project_test_case.projectID LEFT JOIN eo_user ON eo_project_test_case.userID = eo_user.userID WHERE eo_project_test_case.projectID = ? AND eo_project_test_case.caseName LIKE ? ORDER BY eo_project_test_case.updateTime DESC;", array(
            $project_id,
            '%' . $tips . '%',
        ));
        if ($result)
            return $result;
        else
            return FALSE;
    }

    /**
     * 检查用例权限
     * @param $case_id
     * @param $user_id
     * @return bool
     */
    public function checkTestCasePermission(&$case_id, &$user_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_project_test_case.projectID FROM eo_project_test_case LEFT JOIN eo_conn_project ON eo_project_test_case.projectID = eo_conn_project.projectID WHERE eo_project_test_case.caseID = ? AND eo_conn_project.userID = ?;', array(
            $case_id,
            $user_id
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['projectID'];
        }
    }

    /**
     * 根据分组ID获取用例列表
     * @param $project_id
     * @param $group_id
     * @return bool
     */
    public function getTestCaseDataList(&$project_id, &$group_id)
    {
        $db = getDatabase();
        $case_list = $db->prepareExecuteAll('SELECT eo_project_test_case.caseID,eo_project_test_case.caseName,eo_project_test_case.caseType FROM eo_project_test_case LEFT JOIN eo_project_test_case_group ON eo_project_test_case.groupID = eo_project_test_case_group.groupID WHERE eo_project_test_case.projectID = ? AND (eo_project_test_case.groupID = ? OR eo_project_test_case_group.parentGroupID = ?) ORDER BY CONCAT(eo_project_test_case.caseName,eo_project_test_case.updateTime) ASC;', array(
            $project_id,
            $group_id,
            $group_id
        ));
        if (!empty($case_list)) {
            foreach ($case_list as &$case) {
                $case['singleCaseList'] = $db->prepareExecuteAll('SELECT eo_project_test_case_single.connID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule,eo_project_test_case_single.apiName,eo_project_test_case_single.apiURI,eo_project_test_case_single.apiRequestType FROM eo_project_test_case_single WHERE eo_project_test_case_single.caseID = ?;', array(
                    $case['caseID']
                ));
                if (!empty($case['singleCaseList'])) {
                    foreach ($case['singleCaseList'] as &$single_case) {
                        if ($single_case['matchType'] == 2 && !empty($single_case['matchRule'])) {
                            $single_case['matchRule'] = json_decode($single_case['matchRule'], TRUE);
                        }
                    }
                }
                unset($case['caseID']);
            }
            return $case_list;
        } else {
            return FALSE;
        }
    }

    /**
     * 根据项目ID获取全部用例数据列表
     * @param $project_id
     * @return bool
     */
    public function getAllTestCaseDataList(&$project_id)
    {
        $db = getDatabase();
        $case_list = $db->prepareExecuteAll('SELECT eo_project_test_case.caseID,eo_project_test_case.caseName,eo_project_test_case.caseType FROM eo_project_test_case WHERE eo_project_test_case.projectID = ? ORDER BY CONCAT(eo_project_test_case.caseName,eo_project_test_case.updateTime) DESC;', array(
            $project_id
        ));
        if (!empty($case_list)) {
            foreach ($case_list as &$case) {
                $case['singleCaseList'] = $db->prepareExecuteAll('SELECT eo_project_test_case_single.connID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule,eo_project_test_case_single.apiName,eo_project_test_case_single.apiURI,eo_project_test_case_single.apiRequestType FROM eo_project_test_case_single WHERE eo_project_test_case_single.caseID = ?;', array(
                    $case['caseID']
                ));
                if (!empty($case['singleCaseList'])) {
                    foreach ($case['singleCaseList'] as &$single_case) {
                        if ($single_case['matchType'] == 2 && !empty($single_case['matchRule'])) {
                            $single_case['matchRule'] = json_decode($single_case['matchRule'], TRUE);
                        }
                    }
                }
                unset($case['caseID']);
            }
            return $case_list;
        } else {
            return FALSE;
        }
    }
}