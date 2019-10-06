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

class PartnerDao
{
    /**
     * 邀请协作人员
     * @param $projectID int 项目ID
     * @param $inviteUserID int 邀请人ID
     * @return bool|int
     */
    public function invitePartner(&$projectID, &$userID, &$inviteUserID)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_conn_project (eo_conn_project.projectID,eo_conn_project.userID,eo_conn_project.userType,eo_conn_project.inviteUserID) VALUES (?,?,2,?);', array(
            $projectID,
            $userID,
            $inviteUserID
        ));

        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }

    /**
     * 移除协作人员
     * @param $projectID int 项目ID
     * @param $connID int 用户与项目联系ID
     * @return bool
     */
    public function removePartner(&$projectID, &$connID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_conn_project WHERE eo_conn_project.projectID = ? AND eo_conn_project.connID = ? AND eo_conn_project.userType != 0;', array(
            $projectID,
            $connID
        ));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取协作人员列表
     * @param $projectID int 项目ID
     * @return bool|array
     */
    public function getPartnerList(&$projectID)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_conn_project.userID,eo_conn_project.connID,eo_conn_project.userType,eo_user.userName,eo_user.userNickName,eo_conn_project.partnerNickName FROM eo_conn_project INNER JOIN eo_user ON eo_conn_project.userID = eo_user.userID WHERE eo_conn_project.projectID = ? ORDER BY eo_conn_project.userType ASC;', array($projectID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 退出协作项目
     * @param $projectID int 项目ID
     * @param $userID int 用户ID
     * @return bool
     */
    public function quitPartner(&$projectID, &$userID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_conn_project WHERE eo_conn_project.projectID = ? AND eo_conn_project.userID = ? AND eo_conn_project.userType != 0;', array(
            $projectID,
            $userID
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 查询是否已经加入过项目
     * @param $projectID int 项目ID
     * @param $userName string 用户名
     * @return bool
     */
    public function checkIsInvited(&$projectID, &$userName)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_conn_project.connID FROM eo_conn_project INNER JOIN eo_user ON eo_user.userID = eo_conn_project.userID WHERE eo_conn_project.projectID = ? AND eo_user.userName = ?;', array(
            $projectID,
            $userName
        ));
        if (empty($result))
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取用户ID
     * @param $connID int 用户与项目联系ID
     * @return bool|int
     */
    public function getUserID(&$connID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_conn_project.userID FROM eo_conn_project WHERE eo_conn_project.connID = ?;', array($connID));
        if (empty($result))
            return FALSE;
        else
            return $result['userID'];
    }

    /**
     * 修改协作成员的昵称
     * @param $project_id int 项目ID
     * @param $conn_id int 连接ID
     * @param $nick_name string 昵称
     * @return bool
     */
    public function editPartnerNickName(&$project_id, &$conn_id, &$nick_name)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_conn_project SET eo_conn_project.partnerNickName = ? WHERE eo_conn_project.connID = ? AND eo_conn_project.projectID = ?;', array(
            $nick_name,
            $conn_id,
            $project_id
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 修改协作成员的类型
     * @param $project_id int 项目ID
     * @param $conn_id int 连接ID
     * @param $user_type int 用户类型
     * @return bool
     */
    public function editPartnerType(&$project_id, &$conn_id, &$user_type)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_conn_project SET eo_conn_project.userType = ? WHERE eo_conn_project.connID = ? AND eo_conn_project.projectID = ?;', array(
            $user_type,
            $conn_id,
            $project_id
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 获取协作成员账号名
     * @param $user_id
     * @return bool
     */
    public function getPartnerUserCall(&$user_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_user.userName FROM eo_user WHERE eo_user.userID = ?;', array(
            $user_id
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['userName'];
        }
    }

    public function getProjectInviteCode(&$project_id)
    {
        $db = getDatabase();
        // 尝试次数,超过3次则认为是服务器出错
        $count = 0;
        do {
            $count++;
            // 获取随机6位字符串
            $invite_code = '';
            $strPool = 'NMqlzxcvdfghjQXCER67ty5HuasJKLZYTWmPASDFGk12iBpn34UIb9werV8';
            for ($i = 0; $i <= 5; $i++) {
                $invite_code .= $strPool[rand(0, 58)];
            }

            // 查重
            $result = $db->prepareExecute('SELECT eo_project_invite.projectID FROM eo_project_invite WHERE eo_project_invite.projectInviteCode = ?;', array(
                $invite_code
            ));
        } while (!empty($result) && $count < 3);
        if (!empty($result)) {
            return FALSE;
        }
    }
}

?>