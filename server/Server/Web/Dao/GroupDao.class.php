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

class GroupDao
{
    /**
     * 添加项目api分组
     * @param $projectID int 项目ID
     * @param $groupName string 分组名称
     * @return bool
     */
    public function addGroup(&$projectID, &$groupName)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID) VALUES (?,?);', array(
            $groupName,
            $projectID
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
     * @param $groupName string 分组名称
     * @param $parentGroupID int 父分组ID
     * @param $isChild
     * @return bool
     */
    public function addChildGroup(&$projectID, &$groupName, &$parentGroupID, &$isChild)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID,eo_api_group.isChild) VALUES (?,?,?,?);', array(
            $groupName,
            $projectID,
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
     * 删除项目api分组
     * @param $groupID int 项目ID
     * @return bool
     */
    public function deleteGroup(&$groupID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT GROUP_CONCAT(GroupID) AS groups FROM eo_api_group WHERE groupID = ? OR parentGroupID = ? OR parentGroupID IN (SELECT groupID FROM eo_api_group WHERE parentGroupID = ?)', array(
            $groupID,
            $groupID,
            $groupID
        ));
        $groups = $result['groups'];
        $db->prepareExecuteAll("DELETE FROM eo_api_group WHERE eo_api_group.groupID IN ({$groups});", array());
        $result = $db->getAffectRow();
        if ($result > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取项目api分组
     * @param $projectID int 项目ID
     * @return bool
     */
    public function getGroupList(&$projectID)
    {
        $db = getDatabase();
        $groupList = $db->prepareExecuteAll('SELECT eo_api_group.groupID,eo_api_group.groupName FROM eo_api_group WHERE projectID = ? AND isChild = 0 ORDER BY eo_api_group.groupID DESC;', array($projectID));

        if (is_array($groupList))
            foreach ($groupList as &$parentGroup) {
                $parentGroup['childGroupList'] = array();
                $childGroupList = $db->prepareExecuteAll('SELECT eo_api_group.groupID,eo_api_group.groupName,eo_api_group.parentGroupID FROM eo_api_group WHERE projectID = ? AND isChild = 1 AND parentGroupID = ? ORDER BY eo_api_group.groupID DESC;', array(
                    $projectID,
                    $parentGroup['groupID']
                ));

                foreach ($childGroupList as &$childGroup) {
                    $secondChildGroupList = $db->prepareExecuteAll('SELECT eo_api_group.groupID,eo_api_group.groupName,eo_api_group.parentGroupID FROM eo_api_group WHERE projectID = ? AND isChild = 2 AND parentGroupID = ? ORDER BY eo_api_group.groupID DESC;', array(
                        $projectID,
                        $childGroup['groupID']
                    ));
                    if (!empty($secondChildGroupList)) {
                        $childGroup['childGroupList'] = $secondChildGroupList;
                    } else {
                        $childGroup['childGroupList'] = array();
                    }
                }
                //判断是否有子分组
                if (!empty($childGroupList)) {
                    $parentGroup['childGroupList'] = $childGroupList;
                } else {
                    $parentGroup['childGroupList'] = array();
                }
            }

        if (empty($groupList))
            return FALSE;
        else
            return $groupList;
    }

    /**
     * 修改项目api分组
     * @param $groupID int 分组ID
     * @param $groupName string 分组名称
     * @param $parentGroupID int 父分组ID
     * @param $isChild
     * @return bool
     */
    public function editGroup(&$groupID, &$groupName, &$parentGroupID, &$isChild)
    {
        $db = getDatabase();

        if (!$parentGroupID) {
            $db->prepareExecute('UPDATE eo_api_group SET eo_api_group.groupName = ?,eo_api_group.isChild = 0 WHERE eo_api_group.groupID = ?;', array(
                $groupName,
                $groupID
            ));
        } else {
            $db->prepareExecute('UPDATE eo_api_group SET eo_api_group.groupName = ?,eo_api_group.parentGroupID = ?,eo_api_group.isChild = ? WHERE eo_api_group.groupID = ?;', array(
                $groupName,
                $parentGroupID,
                $isChild,
                $groupID
            ));
        }

        if ($db->getAffectRow() > 0)
            return TRUE;
        else

            return FALSE;

    }

    /**
     * 判断分组和用户是否匹配
     * @param $groupID int 分组ID
     * @param $userID int 用户ID
     * @return bool
     */
    public function checkGroupPermission(&$groupID, &$userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_conn_project INNER JOIN eo_api_group ON eo_api_group.projectID = eo_conn_project.projectID WHERE userID = ? AND groupID = ?;', array(
            $userID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
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
        $db->prepareExecute('REPLACE INTO eo_api_group_order(projectID, orderList) VALUES (?,?);', array(
            $projectID,
            $orderList
        ));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取分组排序列表
     * @param $projectID int 项目ID
     * @return bool
     */
    public function getGroupOrderList(&$projectID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_api_group_order.orderList FROM eo_api_group_order WHERE eo_api_group_order.projectID = ?;', array(
            $projectID
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['orderList'];
        }
    }

    /**
     * 获取分组名称
     * @param $group_id
     * @return bool
     */
    public function getGroupName(&$group_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_api_group.groupName FROM eo_api_group WHERE eo_api_group.groupID = ?;', array($group_id));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['groupName'];
        }
    }

    /**
     * 获取分组相关数据
     * @param $group_id
     * @return array|bool
     */
    public function getGroupData(&$group_id)
    {
        $db = getDatabase();
        $result = array();
        $group_info = $db->prepareExecute('SELECT eo_api_group.groupName,eo_api_group.isChild FROM eo_api_group WHERE eo_api_group.groupID = ?;', array(
            $group_id
        ));
        $api_list = $db->prepareExecuteAll("SELECT eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred FROM eo_api_cache INNER JOIN eo_api ON eo_api.apiID = eo_api_cache.apiID WHERE eo_api_cache.groupID = ? AND eo_api.removed = 0;", array(
            $group_id
        ));
        $result['groupName'] = $group_info['groupName'];
        if (is_array($api_list)) {
            $j = 0;
            foreach ($api_list as $api) {
                $result['apiList'][$j] = json_decode($api['apiJson'], TRUE);
                $result['apiList'][$j]['baseInfo']['starred'] = $api['starred'];
                ++$j;
            }
        }
        if ($group_info['isChild'] <= 1) {
            $child_group_list = $db->prepareExecuteAll('SELECT eo_api_group.groupID,eo_api_group.groupName FROM eo_api_group WHERE eo_api_group.parentGroupID = ?', array(
                $group_id
            ));
            if ($child_group_list) {
                $i = 0;
                foreach ($child_group_list as $group) {
                    $result['childGroupList'][$i]['groupName'] = $group['groupName'];
                    $api_list = $db->prepareExecuteAll("SELECT eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred FROM eo_api_cache INNER JOIN eo_api ON eo_api.apiID = eo_api_cache.apiID WHERE eo_api_cache.groupID = ? AND eo_api.removed = 0;", array(
                        $group['groupID']
                    ));
                    if (is_array($api_list)) {
                        $j = 0;
                        foreach ($api_list as $api) {
                            $result['childGroupList'][$i]['apiList'][$j] = json_decode($api['apiJson'], TRUE);
                            $result['childGroupList'][$i]['apiList'][$j]['baseInfo']['starred'] = $api['starred'];
                            ++$j;
                        }
                    }
                    $group_list = $db->prepareExecuteAll('SELECT eo_api_group.groupID,eo_api_group.groupName FROM eo_api_group WHERE eo_api_group.parentGroupID = ?;', array(
                        $group['groupID']
                    ));
                    if ($group_list) {
                        $k = 0;
                        foreach ($group_list as $data) {
                            $result['childGroupList'][$i]['childGroupList'][$k]['groupName'] = $data['groupName'];
                            $api_list = $db->prepareExecuteAll('SELECT eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred FROM eo_api_cache INNER JOIN eo_api ON eo_api.apiID = eo_api_cache.apiID WHERE eo_api_cache.groupID = ? AND eo_api.removed = 0;', array(
                                $data['groupID']
                            ));
                            if (is_array($api_list)) {
                                $l = 0;
                                foreach ($api_list as $api) {
                                    $result['childGroupList'][$i]['childGroupList'][$k]['apiList'][$l] = json_decode($api['apiJson'], TRUE);
                                    $result['childGroupList'][$i]['childGroupList'][$k]['apiList'][$l]['baseInfo']['starred'] = $api['starred'];
                                    $l++;
                                }
                            }
                            $k++;
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
     * 导入接口分组
     * @param $project_id
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function importGroup(&$project_id, &$user_id, &$data)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID) VALUES (?,?);', array(
                $data['groupName'],
                $project_id
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addGroup error");

            $group_id = $db->getLastInsertID();
            if ($data['apiList']) {
                foreach ($data['apiList'] as $api) {
                    // 插入api基本信息
                    $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                        $api['baseInfo']['apiName'],
                        $api['baseInfo']['apiURI'],
                        $api['baseInfo']['apiProtocol'],
                        $api['baseInfo']['apiSuccessMock'],
                        $api['baseInfo']['apiFailureMock'],
                        $api['baseInfo']['apiRequestType'],
                        $api['baseInfo']['apiStatus'],
                        $group_id,
                        $project_id,
                        $api['baseInfo']['starred'],
                        $api['baseInfo']['apiNoteType'],
                        $api['baseInfo']['apiNoteRaw'],
                        $api['baseInfo']['apiNote'],
                        $api['baseInfo']['apiRequestParamType'],
                        $api['baseInfo']['apiRequestRaw'],
                        $api['baseInfo']['apiUpdateTime'],
                        $user_id
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addApi error");

                    $api_id = $db->getLastInsertID();

                    // 插入header信息
                    foreach ($api['headerInfo'] as $header) {
                        $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                            $header['headerName'],
                            $header['headerValue'],
                            $api_id
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addHeader error");
                    }

                    // 插入api请求值信息
                    foreach ($api['requestInfo'] as $request) {
                        $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                            $api_id,
                            $request['paramName'],
                            $request['paramKey'],
                            $request['paramValue'],
                            $request['paramLimit'],
                            $request['paramNotNull'],
                            $request['paramType']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addRequestParam error");

                        $param_id = $db->getLastInsertID();

                        foreach ($request['paramValueList'] as $value) {
                            $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                $param_id,
                                $value['value'],
                                $value['valueDescription']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");
                        };
                    };

                    // 插入api返回值信息
                    foreach ($api['resultInfo'] as $result) {
                        $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                            $api_id,
                            $result['paramName'],
                            $result['paramKey'],
                            $result['paramNotNull']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addResultParam error");

                        $param_id = $db->getLastInsertID();

                        foreach ($result['paramValueList'] as $value) {
                            $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                $param_id,
                                $value['value'],
                                $value['valueDescription']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");
                        };
                    };

                    // 插入api缓存数据用于导出
                    $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                        $project_id,
                        $group_id,
                        $api_id,
                        json_encode($api),
                        $api['baseInfo']['starred']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("addApiCache error");
                    }
                }
            }
            // 二级分组代码
            if ($data['childGroupList']) {
                $group_parent_id = $group_id;
                foreach ($data['childGroupList'] as $api_group_child) {
                    $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID, eo_api_group.isChild) VALUES (?,?,?,?);', array(
                        $api_group_child['groupName'],
                        $project_id,
                        $group_parent_id,
                        1
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addChildGroup error");

                    $group_id = $db->getLastInsertID();

                    // 如果当前分组没有接口，则跳过到下一分组
                    if (empty($api_group_child['apiList']))
                        continue;

                    foreach ($api_group_child['apiList'] as $api) {
                        // 插入api基本信息
                        $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                            $api['baseInfo']['apiName'],
                            $api['baseInfo']['apiURI'],
                            $api['baseInfo']['apiProtocol'],
                            $api['baseInfo']['apiSuccessMock'],
                            $api['baseInfo']['apiFailureMock'],
                            $api['baseInfo']['apiRequestType'],
                            $api['baseInfo']['apiStatus'],
                            $group_id,
                            $project_id,
                            $api['baseInfo']['starred'],
                            $api['baseInfo']['apiNoteType'],
                            $api['baseInfo']['apiNoteRaw'],
                            $api['baseInfo']['apiNote'],
                            $api['baseInfo']['apiRequestParamType'],
                            $api['baseInfo']['apiRequestRaw'],
                            $api['baseInfo']['apiUpdateTime'],
                            $user_id
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addChildApi error");

                        $api_id = $db->getLastInsertID();

                        // 插入header信息
                        foreach ($api['headerInfo'] as $header) {
                            $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                $header['headerName'],
                                $header['headerValue'],
                                $api_id
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildHeader error");
                        }

                        // 插入api请求值信息
                        foreach ($api['requestInfo'] as $request) {
                            $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                $api_id,
                                $request['paramName'],
                                $request['paramKey'],
                                $request['paramValue'],
                                $request['paramLimit'],
                                $request['paramNotNull'],
                                $request['paramType']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildRequestParam error");

                            $param_id = $db->getLastInsertID();
                            if ($request['paramValueList']) {
                                foreach ($request['paramValueList'] as $value) {
                                    $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                        $param_id,
                                        $value['value'],
                                        $value['valueDescription']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildApi error");
                                };
                            }
                        };

                        // 插入api返回值信息
                        foreach ($api['resultInfo'] as $result) {
                            $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                $api_id,
                                $result['paramName'],
                                $result['paramKey'],
                                $result['paramNotNull']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildResultParam error");

                            $param_id = $db->getLastInsertID();
                            if ($result['paramValueList']) {
                                foreach ($result['paramValueList'] as $value) {
                                    $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                        $param_id,
                                        $value['value'],
                                        $value['valueDescription']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildParamValue error");
                                };
                            }
                        };

                        // 插入api缓存数据用于导出
                        $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                            $project_id,
                            $group_id,
                            $api_id,
                            json_encode($api),
                            $api['baseInfo']['starred']
                        ));

                        if ($db->getAffectRow() < 1) {
                            throw new \PDOException("addChildApiCache error");
                        }
                    }
                    if ($api_group_child['childGroupList']) {
                        $parent_id = $group_id;
                        foreach ($api_group_child['childGroupList'] as $group) {
                            $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID, eo_api_group.isChild) VALUES (?,?,?,?);', array(
                                $group['groupName'],
                                $project_id,
                                $parent_id,
                                2
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildGroup error");

                            $group_id = $db->getLastInsertID();

                            // 如果当前分组没有接口，则跳过到下一分组
                            if (empty($group['apiList']))
                                continue;

                            foreach ($group['apiList'] as $api) {
                                // 插入api基本信息
                                $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                                    $api['baseInfo']['apiName'],
                                    $api['baseInfo']['apiURI'],
                                    $api['baseInfo']['apiProtocol'],
                                    $api['baseInfo']['apiSuccessMock'],
                                    $api['baseInfo']['apiFailureMock'],
                                    $api['baseInfo']['apiRequestType'],
                                    $api['baseInfo']['apiStatus'],
                                    $group_id,
                                    $project_id,
                                    $api['baseInfo']['starred'],
                                    $api['baseInfo']['apiNoteType'],
                                    $api['baseInfo']['apiNoteRaw'],
                                    $api['baseInfo']['apiNote'],
                                    $api['baseInfo']['apiRequestParamType'],
                                    $api['baseInfo']['apiRequestRaw'],
                                    $api['baseInfo']['apiUpdateTime'],
                                    $user_id
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addChildApi error");

                                $api_id = $db->getLastInsertID();

                                // 插入header信息
                                foreach ($api['headerInfo'] as $header) {
                                    $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                        $header['headerName'],
                                        $header['headerValue'],
                                        $api_id
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildHeader error");
                                }

                                // 插入api请求值信息
                                foreach ($api['requestInfo'] as $request) {
                                    $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                        $api_id,
                                        $request['paramName'],
                                        $request['paramKey'],
                                        $request['paramValue'],
                                        $request['paramLimit'],
                                        $request['paramNotNull'],
                                        $request['paramType']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildRequestParam error");

                                    $param_id = $db->getLastInsertID();
                                    if ($request['paramValueList']) {
                                        foreach ($request['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildApi error");
                                        };
                                    }
                                };

                                // 插入api返回值信息
                                foreach ($api['resultInfo'] as $result) {
                                    $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                        $api_id,
                                        $result['paramName'],
                                        $result['paramKey'],
                                        $result['paramNotNull']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildResultParam error");

                                    $param_id = $db->getLastInsertID();
                                    if ($result['paramValueList']) {
                                        foreach ($result['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildParamValue error");
                                        };
                                    }
                                };

                                // 插入api缓存数据用于导出
                                $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                                    $project_id,
                                    $group_id,
                                    $api_id,
                                    json_encode($api),
                                    $api['baseInfo']['starred']
                                ));

                                if ($db->getAffectRow() < 1) {
                                    throw new \PDOException("addChildApiCache error");
                                }
                            }
                        }
                    }
                }
            }
            $db->commit();
            return TRUE;
        } catch (\Exception $e) {
            $db->rollback();
            return FALSE;
        }
    }
}

?>