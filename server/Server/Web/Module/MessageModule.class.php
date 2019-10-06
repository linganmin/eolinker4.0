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

class MessageModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * 获取消息列表
     * @param $page int 页码
     * @return bool|array
     */
    public function getMessageList(&$page)
    {
        $dao = new MessageDao;
        $result = $dao->getMessageList($_SESSION['userID'], $page);
        if ($result) {
            $result['pageCount'] = ceil($result['msgCount'] / 15);
            $result['pageNow'] = $page;
            return $result;
        } else
            return FALSE;
    }

    /**
     * 已阅消息
     * @param $msgID int 消息ID
     * @return bool
     */
    public function readMessage(&$msgID)
    {
        $dao = new MessageDao;
        return $dao->readMessage($msgID);
    }

    /**
     * 删除消息
     * @param $msgID int 消息ID
     * @return bool
     */
    public function delMessage(&$msgID)
    {
        $dao = new MessageDao;
        return $dao->delMessage($msgID);
    }

    /**
     * 清空消息
     */
    public function cleanMessage()
    {
        $dao = new MessageDao;
        return $dao->cleanMessage($_SESSION['userID']);
    }

    /**
     * 获取消息列表
     */
    public function getUnreadMessageNum()
    {
        $dao = new MessageDao;
        return $dao->getUnreadMessageNum($_SESSION['userID']);
    }

}

?>