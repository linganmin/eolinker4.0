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
class AutomatedTestCaseGroupDao
{

    /**
     * 添加分组
     * @param $project_id
     * @param $group_name
     * @return bool
     */
    public function addGroup(&$project_id, &$group_name)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_test_case_group (eo_project_test_case_group.groupName,eo_project_test_case_group.projectID) VALUES (?,?);', array(
            $group_name,
            $project_id,
        ));

        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }


    /**
     * 添加子分组
     * @param $project_id
     * @param $group_name
     * @param $parent_group_id
     * @param $isChild
     * @return bool
     */
    public function addChildGroup(&$project_id, &$group_name, &$parent_group_id, &$isChild)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_test_case_group (eo_project_test_case_group.groupName,eo_project_test_case_group.projectID,eo_project_test_case_group.parentGroupID,eo_project_test_case_group.isChild) VALUES (?,?,?,?);', array(
            $group_name,
            $project_id,
            $parent_group_id,
            $isChild
        ));

        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }

    /**
     * 删除用例分组
     * @param $project_id
     * @param $group_id
     * @return bool
     */
    public function deleteGroup(&$project_id, &$group_id)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            $db->prepareExecuteAll('DELETE FROM eo_project_test_case_group WHERE eo_project_test_case_group.groupID = ? AND eo_project_test_case_group.projectID = ?;', array($group_id, $project_id));
            if ($db->getAffectRow() < 1)
                throw new \PDOException('delete error');
            $db->prepareExecuteAll('DELETE FROM eo_project_test_case_single WHERE eo_project_test_case_single.caseID IN (SELECT eo_project_test_case.caseID FROM eo_project_test_case WHERE eo_project_test_case.groupID = ?);', array(
                $group_id
            ));
            $db->prepareExecuteAll("DELETE FROM eo_project_test_case WHERE eo_project_test_case.groupID = ?;", array($group_id));
            $db->prepareExecuteAll('DELETE eo_project_test_case FROM eo_project_test_case INNER JOIN eo_project_test_case_group ON eo_project_test_case.groupID = eo_project_test_case_group.groupID  WHERE eo_project_test_case_group.parentGroupID = ?;', array($group_id));
            $db->prepareExecuteAll('DELETE FROM eo_project_test_case_group WHERE eo_project_test_case_group.parentGroupID = ?;', array($group_id));
            $db->commit();
            return TRUE;
        } catch (\PDOException $e) {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * 获取用例分组
     * @param $project_id
     * @return bool|array
     */
    public function getGroupList(&$project_id)
    {
        $db = getDatabase();
        $result = array();
        $group_list = $db->prepareExecuteAll('SELECT eo_project_test_case_group.groupID,eo_project_test_case_group.groupName FROM eo_project_test_case_group WHERE eo_project_test_case_group.projectID = ? AND eo_project_test_case_group.isChild = 0 ORDER BY  eo_project_test_case_group.groupName ASC;', array($project_id));

        //检查是否含有子分组
        if (is_array($group_list)) {
            foreach ($group_list as &$parentGroup) {
                $parentGroup['childGroupList'] = array();
                $childGroup = $db->prepareExecuteAll('SELECT eo_project_test_case_group.groupID,eo_project_test_case_group.groupName,eo_project_test_case_group.parentGroupID FROM eo_project_test_case_group WHERE eo_project_test_case_group.projectID = ? AND eo_project_test_case_group.isChild = 1 AND eo_project_test_case_group.parentGroupID = ? ORDER BY eo_project_test_case_group.groupName ASC;', array(
                    $project_id,
                    $parentGroup['groupID']
                ));

                if ($childGroup) {
                    foreach ($childGroup as &$group) {
                        $child_group_list = $db->prepareExecuteAll('SELECT eo_project_test_case_group.groupID,eo_project_test_case_group.groupName,eo_project_test_case_group.parentGroupID FROM eo_project_test_case_group WHERE eo_project_test_case_group.projectID = ? AND eo_project_test_case_group.isChild = 2 AND eo_project_test_case_group.parentGroupID = ? ORDER BY eo_project_test_case_group.groupName ASC;', array(
                            $project_id,
                            $group['groupID']
                        ));

                        if ($child_group_list) {
                            $group['childGroupList'] = $child_group_list;
                        } else {
                            $group['childGroupList'] = array();
                        }
                    }
                }

                //判断是否有子分组
                if ($childGroup) {
                    $parentGroup['childGroupList'] = $childGroup;
                } else {
                    $parentGroup['childGroupList'] = array();
                }
            }
        }

        $result['groupList'] = $group_list;
        $group_order = $db->prepareExecute('SELECT eo_project_test_case_group_order.orderList FROM eo_project_test_case_group_order WHERE eo_project_test_case_group_order.projectID = ?;', array(
            $project_id
        ));
        $result['groupOrder'] = $group_order['orderList'];

        if (empty($group_list))
            return FALSE;
        else
            return $result;
    }

    /**
     * 修改用例分组
     * @param $project_id
     * @param $group_id
     * @param $group_name
     * @param $parent_group_id
     * @param $isChild
     * @return bool
     */
    public function editGroup(&$project_id, &$group_id, &$group_name, &$parent_group_id, &$isChild)
    {
        $db = getDatabase();

        //如果没有父分组
        if ($parent_group_id <= 0) {
            $db->prepareExecute('UPDATE eo_project_test_case_group SET eo_project_test_case_group.groupName = ?,eo_project_test_case_group.parentGroupID = 0,eo_project_test_case_group.isChild = 0 WHERE eo_project_test_case_group.groupID = ? AND eo_project_test_case_group.projectID = ?;', array(
                $group_name,
                $group_id,
                $project_id
            ));
        } else {
            //有父分组
            $db->prepareExecute('UPDATE eo_project_test_case_group SET eo_project_test_case_group.groupName = ?,eo_project_test_case_group.parentGroupID = ?,eo_project_test_case_group.isChild = ? WHERE eo_project_test_case_group.groupID = ? AND eo_project_test_case_group.projectID = ?;', array(
                $group_name,
                $parent_group_id,
                $isChild,
                $group_id,
                $project_id
            ));
        }

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取分组名称
     * @param $group_id
     * @return bool
     */
    public function getGroupName($group_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT eo_project_test_case_group.groupName FROM eo_project_test_case_group WHERE eo_project_test_case_group.groupID = ?;", array($group_id));

        if (empty($result))
            return FALSE;
        else
            return $result['groupName'];
    }

    /**
     * 更新分组排序
     * @param $project_id
     * @param $order_list
     * @return bool
     */
    public function updateGroupOrder(&$project_id, &$order_list)
    {
        $db = getDatabase();
        $db->prepareExecute("REPLACE INTO eo_project_test_case_group_order(projectID,orderList)VALUES(?,?);", array($project_id, $order_list));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;

    }

    /**
     * 检查分组权限
     * @param $group_id
     * @param $user_id
     * @return bool
     */
    public function checkAutomatedTestCaseGroupPermission(&$group_id, &$user_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_project_test_case_group INNER JOIN eo_conn_project ON eo_project_test_case_group.projectID = eo_conn_project.projectID WHERE eo_project_test_case_group.groupID = ? AND eo_conn_project.userID = ?;', array($group_id, $user_id));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['projectID'];
        }
    }

    /**
     * 获取用例分组数据
     * @param $group_id
     * @return array
     */
    public function getTestCaseGroupData(&$group_id)
    {
        $db = getDatabase();
        $result = array();
        $group_info = $db->prepareExecute('SELECT eo_project_test_case_group.groupName,eo_project_test_case_group.isChild FROM eo_project_test_case_group WHERE eo_project_test_case_group.groupID = ?;', array(
            $group_id
        ));
        $case_list = $db->prepareExecuteAll('SELECT eo_project_test_case.caseID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.caseType,eo_project_test_case.caseCode FROM eo_project_test_case WHERE eo_project_test_case.groupID = ?;', array(
            $group_id
        ));
        $result['groupName'] = $group_info['groupName'];
        if (is_array($case_list)) {
            foreach ($case_list as &$case) {
                $sing_case_list = $db->prepareExecuteAll('SELECT eo_project_test_case_single.connID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule,eo_project_test_case_single.apiName,eo_project_test_case_single.apiURI,eo_project_test_case_single.apiRequestType,eo_project_test_case_single.orderNumber FROM eo_project_test_case_single WHERE eo_project_test_case_single.caseID = ?;', array(
                    $case['caseID']
                ));
                $case['caseSingleList'] = $sing_case_list;
            }
            $result['caseList'] = $case_list;
        } else {
            $result['caseList'] = array();
        }
        $child_group_list = array();
        if ($group_info['isChild'] <= 0) {
            $child_group_list = $db->prepareExecuteAll('SELECT eo_project_test_case_group.groupID,eo_project_test_case_group.groupName FROM eo_project_test_case_group WHERE eo_project_test_case_group.parentGroupID = ?;', array(
                $group_id
            ));
            if (is_array($child_group_list)) {
                foreach ($child_group_list as &$child_group_info) {
                    $child_case_list = $db->prepareExecuteAll('SELECT eo_project_test_case.caseID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.caseType,eo_project_test_case.caseCode FROM eo_project_test_case WHERE eo_project_test_case.groupID = ?;', array(
                        $child_group_info['groupID']
                    ));
                    if (is_array($child_case_list)) {
                        foreach ($child_case_list as &$child_case) {
                            $child_single_case_list = $db->prepareExecuteAll('SELECT eo_project_test_case_single.connID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule,eo_project_test_case_single.apiName,eo_project_test_case_single.apiURI,eo_project_test_case_single.apiRequestType,eo_project_test_case_single.orderNumber FROM eo_project_test_case_single WHERE eo_project_test_case_single.caseID = ?;', array(
                                $child_case['caseID']
                            ));
                            $child_case['caseSingleList'] = $child_single_case_list ? $child_single_case_list : array();
                        }
                        $child_group_info['caseList'] = $child_case_list;
                    } else {
                        $child_group_info['caseList'] = array();
                    }
                    $group_list = $db->prepareExecuteAll('SELECT eo_project_test_case_group.groupID,eo_project_test_case_group.groupName FROM eo_project_test_case_group WHERE eo_project_test_case_group.parentGroupID = ?;', array(
                        $child_group_info['groupID']
                    ));
                    if (is_array($group_list)) {
                        foreach ($group_list as $group) {
                            $second_child_case_list = $db->prepareExecuteAll('SELECT eo_project_test_case.caseID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.caseType,eo_project_test_case.caseCode FROM eo_project_test_case WHERE eo_project_test_case.groupID = ?;', array(
                                $group['groupID']
                            ));
                            if (is_array($second_child_case_list)) {
                                foreach ($second_child_case_list as &$second_child_case) {
                                    $second_child_single_case_list = $db->prepareExecuteAll('SELECT eo_project_test_case_single.connID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule,eo_project_test_case_single.apiName,eo_project_test_case_single.apiURI,eo_project_test_case_single.apiRequestType,eo_project_test_case_single.orderNumber FROM eo_project_test_case_single WHERE eo_project_test_case_single.caseID = ?;', array(
                                        $second_child_case['caseID']
                                    ));
                                    $second_child_case['caseSingleList'] = $second_child_single_case_list ? $second_child_single_case_list : array();
                                }
                            }
                        }
                        $child_group_info['childGroupList'] = $group_list;
                    } else {
                        $child_group_info['childGroupList'] = array();
                    }
                }
            } else {
                $child_group_list = array();
            }
        }
        $result['childGroupList'] = $child_group_list;
        return $result;
    }

    /**
     * 导入测试用例分组数据
     * @param $project_id
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function importTestCaseGroup(&$project_id, &$user_id, &$data)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            $db->prepareExecute('INSERT INTO eo_project_test_case_group (groupName,projectID) VALUES (?,?);', array(
                $data['groupName'],
                $project_id
            ));
            if ($db->getAffectRow() < 1) {
                throw new \PDOException('insert group error');
            }
            $group_id = $db->getLastInsertID();
            if ($data['caseList']) {
                foreach ($data['caseList'] as $case) {
                    $db->prepareExecute('INSERT INTO eo_project_test_case(eo_project_test_case.projectID,eo_project_test_case.userID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.createTime,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case.groupID,eo_project_test_case.caseCode)VALUES(?,?,?,?,?,?,?,?,?);', array(
                        $project_id,
                        $user_id,
                        $case['caseName'],
                        $case['caseDesc'],
                        date('Y-m-d H:i:s', time()),
                        date('Y-m-d H:i:s', time()),
                        $case['caseType'],
                        $group_id,
                        $case['caseCode']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("insert test case error");
                    $case_id = $db->getLastInsertID();
                    if ($case['caseSingleList']) {
                        foreach ($case['caseSingleList'] as $single_case) {
                            $match = array();
                            // 匹配<response[]>，当没有匹配结果的时候跳过
                            if (preg_match_all('#<response\[(\d+)\]#', $single_case['caseData'], $match) > 0) {
                                // 遍历匹配结果，对原字符串进行多次替换
                                foreach ($match[1] as $response_id) {
                                    for ($i = 0; $i < count($case['caseSingleList']); $i++) {
                                        if ($case['caseSingleList'][$i]['connID'] == $response_id) {
                                            $result = $db->prepareExecute("SELECT eo_project_test_case_single.connID FROM eo_project_test_case_single WHERE eo_project_test_case_single.apiName = ? AND eo_project_test_case_single.apiURI = ? AND eo_project_test_case_single.caseID = ?;", array(
                                                $case['caseSingleList'][$i]['apiName'],
                                                $case['caseSingleList'][$i]['apiURI'],
                                                $case_id
                                            ));
                                            $single_case['caseData'] = str_replace("<response[" . $response_id, "<response[" . $result['connID'], $single_case['caseData']);
                                        }
                                    }
                                }
                            }
                            $db->prepareExecute('INSERT INTO eo_project_test_case_single(eo_project_test_case_single.caseID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule, eo_project_test_case_single.apiName, eo_project_test_case_single.apiURI, eo_project_test_case_single.apiRequestType,eo_project_test_case_single.orderNumber) VALUES (?,?,?,?,?,?,?,?,?,?);', array(
                                $case_id,
                                $single_case['caseData'],
                                $single_case['caseCode'],
                                $single_case['statusCode'],
                                $single_case['matchType'],
                                $single_case['matchRule'],
                                $single_case['apiName'],
                                $single_case['apiURI'],
                                $single_case['apiRequestType'],
                                $single_case['orderNumber']
                            ));
                            if ($db->getAffectRow() < 1)
                                throw new \PDOException('insert single test case error');
                        }
                    }
                }
            }
            if ($data['childGroupList']) {
                $group_id_parent = $group_id;
                foreach ($data['childGroupList'] as $child_group) {
                    // 插入分组
                    $db->prepareExecute('INSERT INTO eo_project_test_case_group (eo_project_test_case_group.projectID,eo_project_test_case_group.groupName,eo_project_test_case_group.parentGroupID,eo_project_test_case_group.isChild) VALUES (?,?,?,?);', array(
                        $project_id,
                        $child_group['groupName'],
                        $group_id_parent,
                        1
                    ));
                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("inset child group error");
                    }

                    $group_id = $db->getLastInsertID();
                    if ($child_group['caseList']) {
                        // 插入状态码
                        foreach ($child_group['caseList'] as $case) {
                            $db->prepareExecute('INSERT INTO eo_project_test_case(eo_project_test_case.projectID,eo_project_test_case.userID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.createTime,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case.groupID,eo_project_test_case.caseCode)VALUES(?,?,?,?,?,?,?,?,?);', array(
                                $project_id,
                                $user_id,
                                $case['caseName'],
                                $case['caseDesc'],
                                date('Y-m-d H:i:s', time()),
                                date('Y-m-d H:i:s', time()),
                                $case['caseType'],
                                $group_id,
                                $case['caseCode']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("insert child test case error");
                            $case_id = $db->getLastInsertID();
                            if ($case['caseSingleList']) {
                                foreach ($case['caseSingleList'] as $single_case) {
                                    $match = array();
                                    // 匹配<response[]>，当没有匹配结果的时候跳过
                                    if (preg_match_all('#<response\[(\d+)\]#', $single_case['caseData'], $match) > 0) {
                                        // 遍历匹配结果，对原字符串进行多次替换
                                        foreach ($match[1] as $response_id) {
                                            for ($i = 0; $i < count($case['caseSingleList']); $i++) {
                                                if ($case['caseSingleList'][$i]['connID'] == $response_id) {
                                                    $result = $db->prepareExecute("SELECT eo_project_test_case_single.connID FROM eo_project_test_case_single WHERE eo_project_test_case_single.apiName = ? AND eo_project_test_case_single.apiURI = ? AND eo_project_test_case_single.caseID = ?;", array(
                                                        $case['caseSingleList'][$i]['apiName'],
                                                        $case['caseSingleList'][$i]['apiURI'],
                                                        $case_id
                                                    ));
                                                    $single_case['caseData'] = str_replace("<response[" . $response_id, "<response[" . $result['connID'], $single_case['caseData']);
                                                }
                                            }
                                        }
                                    }

                                    $db->prepareExecute('INSERT INTO eo_project_test_case_single(eo_project_test_case_single.caseID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule, eo_project_test_case_single.apiName, eo_project_test_case_single.apiURI, eo_project_test_case_single.apiRequestType,eo_project_test_case_single.orderNumber) VALUES (?,?,?,?,?,?,?,?,?,?);', array(
                                        $case_id,
                                        $single_case['caseData'],
                                        $single_case['caseCode'],
                                        $single_case['statusCode'],
                                        $single_case['matchType'],
                                        $single_case['matchRule'],
                                        $single_case['apiName'],
                                        $single_case['apiURI'],
                                        $single_case['apiRequestType'],
                                        $single_case['orderNumber']
                                    ));
                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException('insert child single test case error');
                                }
                            }
                        }
                    }
                    if ($child_group['childGroupList']) {
                        $parent_id = $group_id;
                        foreach ($child_group['childGroupList'] as $group) {
                            // 插入分组
                            $db->prepareExecute('INSERT INTO eo_project_test_case_group (eo_project_test_case_group.projectID,eo_project_test_case_group.groupName,eo_project_test_case_group.parentGroupID,eo_project_test_case_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $group['groupName'],
                                $parent_id,
                                2
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("inset child group error");
                            }

                            $group_id = $db->getLastInsertID();
                            if ($group['caseList']) {
                                // 插入状态码
                                foreach ($group['caseList'] as $case) {
                                    $db->prepareExecute('INSERT INTO eo_project_test_case(eo_project_test_case.projectID,eo_project_test_case.userID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.createTime,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case.groupID,eo_project_test_case.caseCode)VALUES(?,?,?,?,?,?,?,?,?);', array(
                                        $project_id,
                                        $user_id,
                                        $case['caseName'],
                                        $case['caseDesc'],
                                        date('Y-m-d H:i:s', time()),
                                        date('Y-m-d H:i:s', time()),
                                        $case['caseType'],
                                        $group_id,
                                        $case['caseCode']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("insert child test case error");
                                    $case_id = $db->getLastInsertID();
                                    if ($case['caseSingleList']) {
                                        foreach ($case['caseSingleList'] as $single_case) {
                                            $match = array();
                                            // 匹配<response[]>，当没有匹配结果的时候跳过
                                            if (preg_match_all('#<response\[(\d+)\]#', $single_case['caseData'], $match) > 0) {
                                                // 遍历匹配结果，对原字符串进行多次替换
                                                foreach ($match[1] as $response_id) {
                                                    for ($i = 0; $i < count($case['caseSingleList']); $i++) {
                                                        if ($case['caseSingleList'][$i]['connID'] == $response_id) {
                                                            $result = $db->prepareExecute("SELECT eo_project_test_case_single.connID FROM eo_project_test_case_single WHERE eo_project_test_case_single.apiName = ? AND eo_project_test_case_single.apiURI = ? AND eo_project_test_case_single.caseID = ?;", array(
                                                                $case['caseSingleList'][$i]['apiName'],
                                                                $case['caseSingleList'][$i]['apiURI'],
                                                                $case_id
                                                            ));
                                                            $single_case['caseData'] = str_replace("<response[" . $response_id, "<response[" . $result['connID'], $single_case['caseData']);
                                                        }
                                                    }
                                                }
                                            }

                                            $db->prepareExecute('INSERT INTO eo_project_test_case_single(eo_project_test_case_single.caseID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule, eo_project_test_case_single.apiName, eo_project_test_case_single.apiURI, eo_project_test_case_single.apiRequestType,eo_project_test_case_single.orderNumber) VALUES (?,?,?,?,?,?,?,?,?,?);', array(
                                                $case_id,
                                                $single_case['caseData'],
                                                $single_case['caseCode'],
                                                $single_case['statusCode'],
                                                $single_case['matchType'],
                                                $single_case['matchRule'],
                                                $single_case['apiName'],
                                                $single_case['apiURI'],
                                                $single_case['apiRequestType'],
                                                $single_case['orderNumber']
                                            ));
                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException('insert child single test case error');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $db->commit();
            return TRUE;
        } catch (\PDOException $e) {
            $db->rollback();
            return FALSE;
        }
    }
}