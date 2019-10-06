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

class StatusCodeGroupDao
{
    /**
     * 添加状态码分组
     * @param $projectID int 项目ID
     * @param $groupName string 分组名
     * @return int|bool
     */
    public function addGroup(&$projectID, &$groupName)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName) VALUES (?,?);', array(
            $projectID,
            $groupName
        ));

        $groupID = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $groupID;

    }

    /**
     * 添加子分组
     * @param $projectID int 项目ID
     * @param $groupName string 分组名
     * @param $parentGroupID int 父分组ID
     * @param $isChild
     * @return bool|int
     */
    public function addChildGroup(&$projectID, &$groupName, &$parentGroupID, &$isChild)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
            $projectID,
            $groupName,
            $parentGroupID,
            $isChild
        ));

        $groupID = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $groupID;
    }

    /**
     * 判断用户和分组是否匹配
     * @param $groupID int 分组ID
     * @param $userID int 用户ID
     * @return bool|int
     */
    public function checkStatusCodeGroupPermission(&$groupID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_conn_project INNER JOIN eo_project_status_code_group ON eo_conn_project.projectID = eo_project_status_code_group.projectID WHERE groupID = ? AND userID = ?;', array(
            $groupID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * 删除分组
     * @param $groupID int 分组ID
     * @return bool
     */
    public function deleteGroup(&$groupID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_project_status_code_group WHERE eo_project_status_code_group.groupID = ?;', array($groupID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取分组列表
     * @param $projectID int 项目ID
     * @return bool|array
     */
    public function getGroupList(&$projectID)
    {
        $db = getDatabase();

        $groupList = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.groupName FROM eo_project_status_code_group WHERE projectID = ? AND isChild = 0 ORDER BY eo_project_status_code_group.groupID DESC;', array($projectID));

        if (is_array($groupList))
            foreach ($groupList as &$parentGroup) {
                $parentGroup['childGroupList'] = array();
                $childGroup = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID FROM eo_project_status_code_group WHERE projectID = ? AND isChild = 1 AND parentGroupID = ? ORDER BY eo_project_status_code_group.groupID DESC;', array(
                    $projectID,
                    $parentGroup['groupID']
                ));

                if ($childGroup) {
                    foreach ($childGroup as &$group) {
                        $secondChildGroup = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID FROM eo_project_status_code_group WHERE projectID = ? AND isChild = 2 AND parentGroupID = ? ORDER BY eo_project_status_code_group.groupID DESC;', array(
                            $projectID,
                            $group['groupID']
                        ));
                        if ($secondChildGroup) {
                            $group['childGroupList'] = $secondChildGroup;
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

        $result = array();
        $result['groupList'] = $groupList;
        $groupOrder = $db->prepareExecute('SELECT eo_api_status_code_group_order.orderList FROM eo_api_status_code_group_order WHERE projectID = ?;', array(
            $projectID
        ));
        $result['groupOrder'] = $groupOrder['orderList'];

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 修改分组
     * @param $groupID int 分组ID
     * @param $groupName string 分组名
     * @param $parentGroupID int 父分组ID
     * @param $isChild
     * @return bool
     */
    public function editGroup(&$groupID, &$groupName, $parentGroupID, &$isChild)
    {
        $db = getDatabase();

        if (!$parentGroupID) {
            $db->prepareExecute('UPDATE eo_project_status_code_group SET eo_project_status_code_group.groupName = ?,isChild = 0,parentGroupID = NULL WHERE eo_project_status_code_group.groupID = ?;', array(
                $groupName,
                $groupID
            ));
        } else {
            $db->prepareExecute('UPDATE eo_project_status_code_group SET eo_project_status_code_group.groupName = ?,isChild = ?,parentGroupID = ? WHERE eo_project_status_code_group.groupID = ?;', array(
                $groupName,
                $isChild,
                $parentGroupID,
                $groupID
            ));
        }


        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 更新分组排序
     * @param $projectID int 项目ID
     * @param $orderList string 排序列表
     * @return bool
     */
    public function sortGroup(&$projectID, &$orderList)
    {
        $db = getDatabase();
        $db->prepareExecute('REPLACE INTO eo_api_status_code_group_order(projectID, orderList) VALUES (?,?);', array(
            $projectID,
            $orderList
        ));
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
    public function getGroupName(&$group_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_project_status_code_group.groupName FROM eo_project_status_code_group WHERE eo_project_status_code_group.groupID = ?;', array($group_id));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['groupName'];
        }
    }

    /**
     * 获取分组数据
     * @param $project_id
     * @param $group_id
     * @return array|bool
     */
    public function getGroupData(&$project_id, &$group_id)
    {
        $db = getDatabase();
        $result = array();
        $group = $db->prepareExecute('SELECT eo_project_status_code_group.groupName,eo_project_status_code_group.isChild FROM eo_project_status_code_group WHERE eo_project_status_code_group.projectID = ? AND eo_project_status_code_group.groupID = ?;', array(
            $project_id,
            $group_id
        ));
        $result['statusCodeList'] = $db->prepareExecuteAll("SELECT eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription FROM eo_project_status_code WHERE eo_project_status_code.groupID = ?", array(
            $group_id
        ));
        $result['groupName'] = $group['groupName'];
        if ($group['isChild'] <= 1) {
            $child_group_list = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.groupName FROM eo_project_status_code_group WHERE eo_project_status_code_group.parentGroupID = ? AND eo_project_status_code_group.projectID = ?', array(
                $group_id,
                $project_id
            ));
            if ($child_group_list) {
                $i = 0;
                foreach ($child_group_list as $group) {
                    $result['childGroupList'][$i]['groupID'] = $group['groupID'];
                    $result['childGroupList'][$i]['groupName'] = $group['groupName'];
                    $result['childGroupList'][$i]['statusCodeList'] = $db->prepareExecuteAll("SELECT eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription FROM eo_project_status_code WHERE eo_project_status_code.groupID = ?", array(
                        $group['groupID']
                    ));
                    $group_list = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.groupName FROM eo_project_status_code_group WHERE eo_project_status_code_group.parentGroupID = ? AND eo_project_status_code_group.projectID = ?', array(
                        $group['groupID'],
                        $project_id
                    ));
                    if ($group_list) {
                        $j = 0;
                        foreach ($group_list as $child_group) {
                            $result['childGroupList'][$i]['childGroupList'][$j]['groupID'] = $child_group['groupID'];
                            $result['childGroupList'][$i]['childGroupList'][$j]['groupName'] = $child_group['groupName'];
                            $result['childGroupList'][$i]['childGroupList'][$j]['statusCodeList'] = $db->prepareExecuteAll("SELECT eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription FROM eo_project_status_code WHERE eo_project_status_code.groupID = ?", array(
                                $child_group['groupID']
                            ));
                            $j++;
                        }
                    }
                    $i++;
                }
            }
        }
        if ($result)
            return $result;
        else
            return FALSE;
    }

    /**
     * 导入状态码分组
     * @param $project_id
     * @param $data
     * @return bool
     */
    public function importGroup(&$project_id, &$data)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            // 插入分组
            $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName) VALUES (?,?);', array(
                $project_id,
                $data['groupName']
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("add statusCodeGroup error");
            $group_id = $db->getLastInsertID();
            if ($data['statusCodeList']) {
                // 插入状态码
                foreach ($data['statusCodeList'] as $status_code) {
                    $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array(
                        $group_id,
                        $status_code['code'],
                        $status_code['codeDescription']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("add statusCode error");
                }
            }
            if ($data['childGroupList']) {
                $group_id_parent = $group_id;
                foreach ($data['childGroupList'] as $child_group) {
                    // 插入分组
                    $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
                        $project_id,
                        $child_group['groupName'],
                        $group_id_parent,
                        1
                    ));
                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("add statusCodeGroup error");
                    }

                    $group_id = $db->getLastInsertID();
                    if ($child_group['statusCodeList']) {
                        // 插入状态码
                        foreach ($child_group['statusCodeList'] as $status_code) {
                            $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array(
                                $group_id,
                                $status_code['code'],
                                $status_code['codeDescription']
                            ));

                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("add statusCode error");
                            }
                        }
                    }

                    if ($child_group['childGroupList']) {
                        $parent_id = $group_id;
                        foreach ($child_group['childGroupList'] as $group) {
                            // 插入分组
                            $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $group['groupName'],
                                $parent_id,
                                2
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("add statusCodeGroup error");
                            }

                            $group_id = $db->getLastInsertID();
                            if ($group['statusCodeList']) {
                                // 插入状态码
                                foreach ($child_group['statusCodeList'] as $status_code) {
                                    $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array(
                                        $group_id,
                                        $status_code['code'],
                                        $status_code['codeDescription']
                                    ));

                                    if ($db->getAffectRow() < 1) {
                                        throw new \PDOException("add statusCode error");
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

?>