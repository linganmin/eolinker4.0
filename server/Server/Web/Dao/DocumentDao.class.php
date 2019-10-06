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

class DocumentDao
{
    /**
     * 添加文档
     * @param $group_id
     * @param $project_id
     * @param $content_type
     * @param $content
     * @param $content_raw
     * @param $title
     * @param $user_id
     * @return bool
     */
    public function addDocument(&$group_id, &$project_id, &$content_type, &$content, &$content_raw, &$title, &$user_id)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_document (eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.userID) VALUES (?,?,?,?,?,?,?);', array(
            $group_id,
            $project_id,
            $content_type,
            $content_raw,
            $content,
            $title,
            $user_id
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return $db->getLastInsertID();
        }
    }

    /**
     * 删除文档
     * @param $document_id
     * @return bool
     */
    public function deleteDocument(&$document_id)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_project_document WHERE eo_project_document.documentID = ?;', array(
            $document_id
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 根据分组获取文档列表
     * @param $group_id
     * @return bool
     */
    public function getDocumentList(&$group_id)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll("SELECT eo_project_document.groupID,eo_project_document.projectID,eo_project_document.documentID,eo_project_document_group.groupName,eo_project_document.updateTime,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.userID,eo_user.userNickName FROM eo_project_document LEFT JOIN eo_user ON eo_project_document.userID = eo_user.userID LEFT JOIN eo_project_document_group ON eo_project_document.groupID = eo_project_document_group.groupID WHERE eo_project_document.groupID = ? OR eo_project_document_group.parentGroupID = ? OR eo_project_document.groupID IN (SELECT eo_project_document_group.groupID FROM eo_project_document_group WHERE eo_project_document_group.parentGroupID IN (SELECT eo_project_document_group.groupID FROM eo_project_document_group WHERE eo_project_document_group.parentGroupID = ?)) ORDER BY eo_project_document.updateTime DESC;", array(
            $group_id,
            $group_id,
            $group_id
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 获取所有文档列表
     * @param $project_id
     * @return bool
     */
    public function getAllDocumentList(&$project_id)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_project_document.groupID,eo_project_document.projectID,eo_project_document.documentID,eo_project_document_group.groupName,eo_project_document.updateTime,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.userID,eo_user.userNickName FROM eo_project_document LEFT JOIN eo_user ON eo_project_document.userID = eo_user.userID LEFT JOIN eo_project_document_group ON eo_project_document_group.groupID = eo_project_document.groupID WHERE eo_project_document_group.projectID = ?;', array($project_id));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 修改文档
     * @param $document_id
     * @param $group_id
     * @param $content_type
     * @param $content
     * @param $content_raw
     * @param $title
     * @param $user_id
     * @return bool
     */
    public function editDocument(&$document_id, &$group_id, &$content_type, &$content, &$content_raw, &$title, &$user_id)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_project_document SET eo_project_document.groupID = ?,eo_project_document.contentType = ?,eo_project_document.contentRaw = ?,eo_project_document.content = ?,eo_project_document.title = ?,eo_project_document.userID = ? WHERE eo_project_document.documentID = ?;', array(
            $group_id,
            $content_type,
            $content_raw,
            $content,
            $title,
            $user_id,
            $document_id
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 搜索文档
     * @param $project_id
     * @param $tips
     * @return bool
     */
    public function searchDocument(&$project_id, &$tips)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_project_document.groupID,eo_project_document.projectID,eo_project_document.documentID,eo_project_document_group.groupName,eo_project_document.updateTime,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.userID,eo_user.userNickName FROM eo_project_document LEFT JOIN eo_user ON eo_project_document.userID = eo_user.userID LEFT JOIN eo_project_document_group ON eo_project_document_group.groupID = eo_project_document.groupID WHERE eo_project_document_group.projectID = ? AND eo_project_document.title LIKE ?;', array(
            $project_id,
            '%' . $tips . '%'
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 获取文档详情
     * @param $document_id
     * @return array|bool
     */
    public function getDocument(&$document_id)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.userID,eo_user.userNickName,eo_project_document.updateTime,eo_project_document_group.parentGroupID FROM eo_project_document LEFT JOIN eo_user ON eo_project_document.userID = eo_user.userID LEFT JOIN eo_project_document_group ON eo_project_document_group.groupID = eo_project_document.groupID WHERE eo_project_document.documentID = ?;', array($document_id));
        $parentGroupInfo = $db->prepareExecute('SELECT eo_project_document_group.groupName AS parentGroupName FROM eo_project_document_group WHERE eo_project_document_group.groupID = ?;', array($result['parentGroupID']));
        if ($parentGroupInfo) {
            $result = array_merge($result, $parentGroupInfo);
        } else {
            $result['parentGroupID'] = 0;
        }
        $topParentGroupID = $db->prepareExecute('SELECT eo_project_document_group.parentGroupID FROM eo_project_document_group WHERE eo_project_document_group.groupID = ? AND eo_project_document_group.isChild = 1;', array(
            $result['parentGroupID']
        ));
        $result['topParentGroupID'] = $topParentGroupID['parentGroupID'] ? $topParentGroupID['parentGroupID'] : $result['parentGroupID'];

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 批量删除文档
     * @param $document_ids
     * @param $project_id
     * @return bool
     */
    public function deleteDocuments(&$document_ids, $project_id)
    {
        $db = getDatabase();
        $db->prepareExecute("DELETE FROM eo_project_document WHERE eo_project_document.documentID in ($document_ids) AND eo_project_document.projectID = ?;", array(
            $project_id
        ));

        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 检查文档权限
     * @param $document_id
     * @param $user_id
     * @return bool
     */
    public function checkDocumentPermission(&$document_id, &$user_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_project_document LEFT JOIN eo_conn_project ON eo_project_document.projectID = eo_conn_project.projectID WHERE eo_conn_project.userID = ? AND eo_project_document.documentID = ?;', array(
            $user_id,
            $document_id
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['projectID'];
        }
    }

    /**
     * 获取文档标题
     * @param $document_ids
     * @return bool
     */
    public function getDocumentTitle(&$document_ids)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT GROUP_CONCAT(eo_project_document.title) AS title FROM eo_project_document WHERE eo_project_document.documentID IN ($document_ids);", array());
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['title'];
        }
    }
}