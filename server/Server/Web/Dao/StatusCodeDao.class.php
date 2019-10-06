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

class StatusCodeDao
{
    /**
     * 添加状态码
     * @param $groupID int 分组ID
     * @param $codeDesc string 状态码描述，默认为NULL
     * @param $code string 状态码
     * @return bool|int
     */
    public function addCode(&$groupID, &$codeDesc, &$code)
    {
        $db = getDatabase();

        $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array($groupID, $code, $codeDesc));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return $db->getLastInsertID();

    }

    /**
     * 删除状态码
     * @param $codeID int 状态码ID
     * @return bool
     */
    public function deleteCode(&$codeID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_project_status_code WHERE eo_project_status_code.codeID = ?;', array($codeID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取状态码列表
     * @param $groupID int 分组ID
     * @return bool|array
     */
    public function getCodeList(&$groupID)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription,eo_project_status_code_group.groupName,eo_project_status_code_group.groupID,eo_project_status_code_group.parentGroupID FROM eo_project_status_code INNER JOIN eo_project_status_code_group ON eo_project_status_code.groupID = eo_project_status_code_group.groupID WHERE (eo_project_status_code_group.groupID = ? OR eo_project_status_code_group.parentGroupID = ? OR eo_project_status_code.groupID IN (SELECT eo_project_status_code_group.groupID FROM eo_project_status_code_group WHERE eo_project_status_code_group.parentGroupID IN (SELECT eo_project_status_code_group.groupID FROM eo_project_status_code_group WHERE eo_project_status_code_group.parentGroupID = ?))) ORDER BY eo_project_status_code.code ASC;', array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else {
            foreach ($result as &$code) {
                $topParentGroupID = $db->prepareExecute('SELECT eo_project_status_code_group.parentGroupID FROM eo_project_status_code_group WHERE eo_project_status_code_group.groupID = ? AND eo_project_status_code_group.isChild = 1;', array(
                    $code['parentGroupID']
                ));
                $code['topParentGroupID'] = $topParentGroupID['parentGroupID'] ? $topParentGroupID['parentGroupID'] : $code['parentGroupID'];
            }
            return $result;
        }

    }

    /**
     * 获取所有状态码列表
     * @param $projectID int 项目ID
     * @return bool|array
     */
    public function getAllCodeList(&$projectID)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.groupName,eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription FROM eo_project_status_code INNER JOIN eo_project_status_code_group ON eo_project_status_code.groupID = eo_project_status_code_group.groupID WHERE projectID = ? ORDER BY eo_project_status_code.code ASC;', array($projectID));

        if (empty($result))
            return FALSE;
        else {
            foreach ($result as &$code) {
                $topParentGroupID = $db->prepareExecute('SELECT eo_project_status_code_group.parentGroupID FROM eo_project_status_code_group WHERE eo_project_status_code_group.groupID = ? AND eo_project_status_code_group.isChild = 1;', array(
                    $code['parentGroupID']
                ));
                $code['topParentGroupID'] = $topParentGroupID['parentGroupID'] ? $topParentGroupID['parentGroupID'] : $code['parentGroupID'];
            }

            return $result;
        }
    }

    /**
     * 修改状态码
     * @param $groupID int 分组ID
     * @param $codeID int 状态码ID
     * @param $code string 状态码
     * @param $codeDesc string 状态码描述，默认为NULL
     * @return bool
     */
    public function editCode(&$groupID, &$codeID, &$code, &$codeDesc)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_project_status_code SET eo_project_status_code.groupID = ?, eo_project_status_code.code = ? ,eo_project_status_code.codeDescription = ? WHERE codeID = ?;', array($groupID, $code, $codeDesc, $codeID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 检查状态码与用户的联系
     * @param $codeID int 状态码ID
     * @param $userID int 用户ID
     * @return bool|int
     */
    public function checkStatusCodePermission(&$codeID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_project_status_code INNER JOIN eo_conn_project INNER JOIN eo_project_status_code_group ON eo_conn_project.projectID = eo_project_status_code_group.projectID AND eo_project_status_code_group.groupID = eo_project_status_code.groupID WHERE codeID = ? AND userID = ?;', array($codeID, $userID));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * 搜索状态码
     * @param $projectID int 项目ID
     * @param $tips string 搜索关键字
     * @return bool|array
     */
    public function searchStatusCode(&$projectID, &$tips)
    {
        $db = getDatabase();

        $result = $db->prepareExecuteAll('SELECT eo_project_status_code_group.groupID,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.groupName,eo_project_status_code.codeID,eo_project_status_code.code,eo_project_status_code.codeDescription FROM eo_project_status_code INNER JOIN eo_project_status_code_group ON eo_project_status_code.groupID = eo_project_status_code_group.groupID WHERE projectID = ? AND (eo_project_status_code.code LIKE ? OR eo_project_status_code.codeDescription LIKE ?);', array($projectID, '%' . $tips . '%', '%' . $tips . '%'));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 获取状态码数量
     * @param $projectID int 项目ID
     * @return bool|int
     */
    public function getStatusCodeNum(&$projectID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT COUNT(*) AS num FROM eo_project_status_code LEFT JOIN eo_project_status_code_group ON eo_project_status_code.groupID = eo_project_status_code_group.groupID WHERE eo_project_status_code_group.projectID = ?;', array($projectID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 批量删除状态码
     * @param $code_ids string 状态码列表
     * @return bool
     */
    public function deleteCodes(&$code_ids)
    {
        $db = getDatabase();
        $db->prepareExecuteAll("DELETE FROM eo_project_status_code WHERE codeID IN ($code_ids)", array());
        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 获取状态码名称
     * @param $code_ids
     * @return bool
     */
    public function getStatusCodes(&$code_ids)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT GROUP_CONCAT(DISTINCT eo_project_status_code.code) AS statusCodes FROM eo_project_status_code WHERE eo_project_status_code.codeID IN ($code_ids)", array());
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['statusCodes'];
        }
    }

    /**
     * 通过Excel批量添加状态码
     * @param $group_id
     * @param $code_list
     * @return bool
     */
    public function addStatusCodeByExcel(&$group_id, &$code_list)
    {
        $db = getDatabase();
        $db->beginTransaction();
        foreach ($code_list as $code) {
            $db->prepareExecute('INSERT INTO eo_project_status_code (code,codeDescription,groupID) VALUES (?,?,?);', array(
                $code['code'],
                $code['codeDesc'],
                $group_id
            ));
            if ($db->getAffectRow() < 1) {
                $db->rollback();
                return FALSE;
            }
        }
        $db->commit();
        return TRUE;
    }
}

?>