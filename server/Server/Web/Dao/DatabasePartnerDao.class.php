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
class DatabasePartnerDao
{
    /**
     * 邀请协作人员
     * @param $dbID int 数据库ID
     * @param $userID int 被邀请人ID
     * @param $inviteUserID int 邀请人ID
     * @return bool|int
     */
    public function invitePartner(&$dbID, &$userID, &$inviteUserID)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_conn_database (eo_conn_database.dbID,eo_conn_database.userID,eo_conn_database.userType,eo_conn_database.inviteUserID) VALUES (?,?,2,?);', array(
            $dbID,
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
     * @param $dbID int 项目ID
     * @param $connID int 用户与数据库联系ID
     * @return bool
     */
    public function removePartner(&$dbID, &$connID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_conn_database WHERE eo_conn_database.dbID = ? AND eo_conn_database.connID = ? AND eo_conn_database.userType != 0;', array(
            $dbID,
            $connID
        ));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取协作人员列表
     * @param $dbID int 数据库ID
     * @return bool|array
     */
    public function getPartnerList(&$dbID)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_conn_database.userID,eo_conn_database.connID,eo_conn_database.userType,eo_user.userName,eo_user.userNickName,eo_conn_database.partnerNickName FROM eo_conn_database INNER JOIN eo_user ON eo_conn_database.userID = eo_user.userID WHERE eo_conn_database.dbID = ? ORDER BY eo_conn_database.userType ASC;', array($dbID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 退出协作项目
     * @param $dbID int 数据库ID
     * @param $userID int 用户ID
     * @return bool
     */
    public function quitPartner(&$dbID, &$userID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_conn_database WHERE eo_conn_database.dbID = ? AND eo_conn_database.userID = ? AND eo_conn_database.userType != 0;', array(
            $dbID,
            $userID
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 查询是否已经加入过项目
     * @param $dbID int 数据库ID
     * @param $userName string 用户名
     * @return bool
     */
    public function checkIsInvited(&$dbID, &$userName)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_conn_database.connID FROM eo_conn_database INNER JOIN eo_user ON eo_user.userID = eo_conn_database.userID WHERE eo_conn_database.dbID = ? AND eo_user.userName = ?;', array(
            $dbID,
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
        $result = $db->prepareExecute('SELECT eo_conn_database.userID FROM eo_conn_database WHERE eo_conn_database.connID = ?;', array($connID));
        if (empty($result))
            return FALSE;
        else
            return $result['userID'];
    }

    /**
     * 修改协作成员的昵称
     * @param $dbID int 数据库ID
     * @param $conn_id int 连接ID
     * @param $nick_name string 昵称
     * @return bool
     */
    public function editPartnerNickName(&$dbID, &$conn_id, &$nick_name)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_conn_database SET eo_conn_database.partnerNickName = ? WHERE eo_conn_database.connID = ? AND eo_conn_database.dbID = ?;', array(
            $nick_name,
            $conn_id,
            $dbID
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 修改协作成员的类型
     * @param $dbID int 数据库ID
     * @param $conn_id int 连接ID
     * @param $user_type int 用户类型
     * @return bool
     */
    public function editPartnerType(&$dbID, &$conn_id, &$user_type)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_conn_database SET eo_conn_database.userType = ? WHERE eo_conn_database.connID = ? AND eo_conn_database.dbID = ?;', array(
            $user_type,
            $conn_id,
            $dbID
        ));

        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else
            return FALSE;
    }
}

?>