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

class MessageDao
{
    /**
     * 获取消息列表
     * @param $userID int 用户ID
     * @param $page int 页码
     * @return bool|array
     */
    public function getMessageList(&$userID, &$page)
    {
        $db = getDatabase();
        $result['messageList'] = $db->prepareExecuteAll('SELECT eo_message.msgID,eo_message.msgType,eo_message.msg,eo_message.summary,eo_message.msgSendTime,eo_message.isRead FROM eo_message WHERE eo_message.toUserID = ? ORDER BY eo_message.msgSendTime DESC LIMIT ?,15;', array(
            $userID,
            ($page - 1) * 15
        ));

        $msgCount = $db->prepareExecute('SELECT COUNT(eo_message.msgID) AS msgCount FROM eo_message WHERE eo_message.toUserID = ?', array($userID));

        $result['msgCount'] = $msgCount['msgCount'];

        if (empty($result['messageList'][0]))
            return FALSE;
        else
            return $result;
    }

    /**
     * 已阅消息
     * @param $msgID int 消息ID
     * @return bool
     */
    public function readMessage(&$msgID)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_message SET eo_message.isRead = 1 WHERE eo_message.msgID = ?;', array($msgID));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 删除消息
     * @param $msgID int 消息ID
     * @return bool
     */
    public function delMessage(&$msgID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_message WHERE eo_message.msgID = ?;', array($msgID));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 清空消息
     * @param $userID int 用户ID
     * @return bool
     */
    public function cleanMessage(&$userID)
    {
        //本接口只能清空所有接收到的消息
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_message WHERE eo_message.toUserID = ?;', array($userID));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 发送消息
     * @param $fromUserID int 发送者用户ID，系统消息ID为0
     * @param $toUserID int 接收者用户ID
     * @param $msgType int 消息类型 [0/1]=>[官方消息/团队消息]
     * @param $summary string 消息概要，默认为NULL
     * @param $msg string 消息内容
     * @return bool
     */
    public function sendMessage($fromUserID, $toUserID, $msgType, &$summary, &$msg)
    {
        //fromUserID默认为0也就是官方消息
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_message (eo_message.fromUserID,eo_message.toUserID,eo_message.msgType,eo_message.summary,eo_message.msg) VALUES (?,?,?,?,?);', array(
            $fromUserID,
            $toUserID,
            $msgType,
            $summary,
            $msg
        ));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取未读消息数量
     * @param $userID int 用户ID
     * @return bool|int
     */
    public function getUnreadMessageNum(&$userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT COUNT(eo_message.msgID) AS unreadMsgNum FROM eo_message WHERE eo_message.toUserID = ? AND eo_message.isRead = 0;', array($userID));

        if (empty($result))
            return FALSE;
        else
            return $result['unreadMsgNum'];
    }

}

?>