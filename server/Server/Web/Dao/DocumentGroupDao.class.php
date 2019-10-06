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

class DocumentGroupDao
{
    /**
     * 添加文档分组
     * @param $project_id int 项目ID
     * @param $group_name string 分组名称
     * @return bool|int
     */
    public function addGroup(&$project_id, &$group_name)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_document_group (eo_project_document_group.groupName,eo_project_document_group.projectID) VALUES (?,?);', array(
            $group_name,
            $project_id,
        ));

        $group_id = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $group_id;
    }

    /**
     * 添加文档子分组
     * @param $project_id int 项目ID
     * @param $parent_group_id int 父分组ID
     * @param $group_name string 分组名称
     * @param $isChild
     * @return bool|int
     */
    public function addChildGroup(&$project_id, &$group_name, &$parent_group_id, &$isChild)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_document_group (eo_project_document_group.groupName,eo_project_document_group.projectID,eo_project_document_group.parentGroupID,eo_project_document_group.isChild) VALUES (?,?,?,?);', array(
            $group_name,
            $project_id,
            $parent_group_id,
            $isChild
        ));

        $group_id = $db->getLastInsertID();

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $group_id;
    }

    /**
     * 删除文档分组
     * @param $group_id int 分组ID
     * @return bool
     */
    public function deleteGroup(&$group_id)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_project_document_group WHERE eo_project_document_group.groupID = ?;', array($group_id));
        $result = $db->getAffectRow();
        $db->prepareExecute('DELETE FROM eo_project_document_group WHERE eo_project_document_group.parentGroupID = ?;', array($group_id));
        $db->prepareExecute('DELETE FROM eo_project_document WHERE eo_project_document.groupID = ?;', array($group_id));

        if ($result > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取文档分组列表
     * @param $project_id int 项目ID
     * @return bool|array
     */
    public function getGroupList(&$project_id)
    {
        $db = getDatabase();
        $result = array();
        $group_list = $db->prepareExecuteAll('SELECT eo_project_document_group.groupID,eo_project_document_group.groupName FROM eo_project_document_group WHERE projectID = ? AND isChild = 0 ORDER BY groupID  ASC;', array($project_id));

        //检查是否含有子分组
        if (is_array($group_list)) {
            foreach ($group_list as &$parentGroup) {
                $parentGroup['childGroupList'] = array();
                $childGroup = $db->prepareExecuteAll('SELECT eo_project_document_group.groupID,eo_project_document_group.groupName,eo_project_document_group.parentGroupID FROM eo_project_document_group WHERE projectID = ? AND isChild = 1 AND parentGroupID = ? ORDER BY groupID ASC;', array(
                    $project_id,
                    $parentGroup['groupID']
                ));

                if (!empty($childGroup)) {
                    foreach ($childGroup as &$group) {
                        $child_group_list = $db->prepareExecuteAll('SELECT eo_project_document_group.groupID,eo_project_document_group.groupName,eo_project_document_group.parentGroupID FROM eo_project_document_group WHERE projectID = ? AND isChild = 2 AND parentGroupID = ? ORDER BY groupID ASC;', array(
                            $project_id,
                            $group['groupID']
                        ));
                        if (!empty($child_group_list)) {
                            $group['childGroupList'] = $child_group_list;
                        } else {
                            $group['childGroupList'] = array();
                        }
                    }
                }


                //判断是否有子分组
                if (!empty($childGroup)) {
                    $parentGroup['childGroupList'] = $childGroup;
                } else {
                    $parentGroup['childGroupList'] = array();
                }
            }
        }
        if (empty($group_list)) {
            return FALSE;
        }
        $result['groupList'] = $group_list;
        $order_list = $db->prepareExecute('SELECT eo_project_document_group_order.orderList FROM eo_project_document_group_order WHERE eo_project_document_group_order.projectID = ?;', array(
            $project_id
        ));
        $result['groupOrder'] = $order_list['orderList'];
        return $result;
    }

    /**
     * 修改文档分组信息
     * @param $group_id int 分组ID
     * @param $group_name string 分组名称
     * @param $parent_group_id int 父分组ID
     * @param $isChild
     * @return bool
     */
    public function editGroup(&$group_id, &$group_name, &$parent_group_id, &$isChild)
    {
        $db = getDatabase();

        //如果没有父分组
        if ($parent_group_id <= 0) {
            $db->prepareExecute('UPDATE eo_project_document_group SET eo_project_document_group.groupName = ?,eo_project_document_group.parentGroupID = 0,eo_project_document_group.isChild = 0 WHERE eo_project_document_group.groupID = ?;', array(
                $group_name,
                $group_id
            ));
        } else {
            //有父分组
            $db->prepareExecute('UPDATE eo_project_document_group SET eo_project_document_group.groupName = ?,eo_project_document_group.parentGroupID = ?,eo_project_document_group.isChild = ? WHERE eo_project_document_group.groupID = ?;', array(
                $group_name,
                $parent_group_id,
                $isChild,
                $group_id
            ));
        }

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 判断文档分组和用户是否匹配
     * @param $group_id int 分组ID
     * @param $user_id int 用户ID
     * @return bool|int
     */
    public function checkGroupPermission(&$group_id, &$user_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_conn_project INNER JOIN eo_project_document_group ON eo_project_document_group.projectID = eo_conn_project.projectID WHERE userID = ? AND groupID = ?;', array(
            $user_id,
            $group_id
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * 获取文档分组名称
     * @param $group_id int 分组ID
     * @return bool|string
     */
    public function getGroupName($group_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT eo_project_document_group.groupName FROM eo_project_document_group WHERE eo_project_document_group.groupID = ?;", array($group_id));

        if (empty($result))
            return FALSE;
        else
            return $result['groupName'];
    }

    /**
     * 更新文档分组排序
     * @param $project_id
     * @param $order_list
     * @return bool
     */
    public function updateGroupOrder(&$project_id, &$order_list)
    {
        $db = getDatabase();
        $db->prepareExecute("REPLACE INTO eo_project_document_group_order(projectID,orderList)VALUES(?,?);", array($project_id, $order_list));
        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
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
        $group = $db->prepareExecute('SELECT eo_project_document_group.groupName,eo_project_document_group.isChild FROM eo_project_document_group WHERE eo_project_document_group.projectID = ? AND eo_project_document_group.groupID = ?;', array(
            $project_id,
            $group_id
        ));
        $result['pageList'] = $db->prepareExecuteAll("SELECT eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title FROM eo_project_document WHERE eo_project_document.groupID = ? AND eo_project_document.projectID = ?", array(
            $group_id,
            $project_id
        ));
        $result['groupName'] = $group['groupName'];
        if ($group['isChild'] <= 1) {
            $child_group_list = $db->prepareExecuteAll('SELECT eo_project_document_group.groupID,eo_project_document_group.groupName FROM eo_project_document_group WHERE eo_project_document_group.parentGroupID = ? AND eo_project_document_group.projectID = ?', array(
                $group_id,
                $project_id
            ));
            if ($child_group_list) {
                $i = 0;
                foreach ($child_group_list as $group) {
                    $result['childGroupList'][$i]['groupID'] = $group['groupID'];
                    $result['childGroupList'][$i]['groupName'] = $group['groupName'];
                    $result['childGroupList'][$i]['pageList'] = $db->prepareExecuteAll("SELECT eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title FROM eo_project_document WHERE eo_project_document.groupID = ? AND eo_project_document.projectID = ?", array(
                        $group['groupID'],
                        $project_id
                    ));
                    $group_list = $db->prepareExecuteAll('SELECT eo_project_document_group.groupID,eo_project_document_group.groupName FROM eo_project_document_group WHERE eo_project_document_group.parentGroupID = ? AND eo_project_document_group.projectID = ?', array(
                        $group['groupID'],
                        $project_id
                    ));
                    if ($group_list) {
                        $j = 0;
                        foreach ($group_list as $child_group) {
                            $result['childGroupList'][$i]['childGroupList'][$j]['groupID'] = $child_group['groupID'];
                            $result['childGroupList'][$i]['childGroupList'][$j]['groupName'] = $child_group['groupName'];
                            $result['childGroupList'][$i]['childGroupList'][$j]['pageList'] = $db->prepareExecuteAll("SELECT eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title FROM eo_project_document WHERE eo_project_document.groupID = ? AND eo_project_document.projectID = ?", array(
                                $child_group['groupID'],
                                $project_id
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
     * 导入文档分组
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
            // 插入分组
            $db->prepareExecute('INSERT INTO eo_project_document_group (eo_project_document_group.projectID,eo_project_document_group.groupName) VALUES (?,?);', array(
                $project_id,
                $data['groupName']
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addPageGroup error");
            $group_id = $db->getLastInsertID();
            if ($data['pageList']) {
                // 插入状态码
                foreach ($data['pageList'] as $page) {
                    $db->prepareExecute('INSERT INTO eo_project_document (eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
                        $group_id,
                        $project_id,
                        $page['contentType'],
                        $page['contentRaw'],
                        $page['content'],
                        $page['title'],
                        date('Y-m-d H:i:s'),
                        $user_id
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addPage error");
                }
            }
            if ($data['childGroupList']) {
                $group_id_parent = $group_id;
                foreach ($data['childGroupList'] as $child_group) {
                    // 插入分组
                    $db->prepareExecute('INSERT INTO eo_project_document_group (eo_project_document_group.projectID,eo_project_document_group.groupName,eo_project_document_group.parentGroupID,eo_project_document_group.isChild) VALUES (?,?,?,?);', array(
                        $project_id,
                        $child_group['groupName'],
                        $group_id_parent,
                        1
                    ));
                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("addPageGroup error");
                    }

                    $group_id = $db->getLastInsertID();
                    if ($child_group['pageList']) {
                        // 插入状态码
                        foreach ($child_group['pageList'] as $page) {
                            $db->prepareExecute('INSERT INTO eo_project_document (eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
                                $group_id,
                                $project_id,
                                $page['contentType'],
                                $page['contentRaw'],
                                $page['content'],
                                $page['title'],
                                date('Y-m-d H:i:s'),
                                $user_id
                            ));

                            if ($db->getAffectRow() < 1)

                                throw new \PDOException("addPage error");
                        }
                    }

                    if ($child_group['childGroupList']) {
                        $parent_id = $group_id;
                        foreach ($child_group['childGroupList'] as $group) {
                            // 插入分组
                            $db->prepareExecute('INSERT INTO eo_project_document_group (eo_project_document_group.projectID,eo_project_document_group.groupName,eo_project_document_group.parentGroupID,eo_project_document_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $group['groupName'],
                                $parent_id,
                                2
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("addPageGroup error");
                            }

                            $group_id = $db->getLastInsertID();
                            if ($group['pageList']) {
                                // 插入状态码
                                foreach ($group['pageList'] as $page) {
                                    $db->prepareExecute('INSERT INTO eo_project_document (eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
                                        $group_id,
                                        $project_id,
                                        $page['contentType'],
                                        $page['contentRaw'],
                                        $page['content'],
                                        $page['title'],
                                        date('Y-m-d H:i:s'),
                                        $user_id
                                    ));

                                    if ($db->getAffectRow() < 1)

                                        throw new \PDOException("addPage error");
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