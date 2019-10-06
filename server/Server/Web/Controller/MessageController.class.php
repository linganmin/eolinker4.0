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

class MessageController
{
    // 返回json类型
    private $returnJson = array('type' => 'message');

    /**
     * 检查登录状态
     */
    public function __construct()
    {
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * 获取消息列表
     */
    public function getMessageList()
    {
        $page = securelyInput('page', 1);
        $server = new MessageModule;
        $result = $server->getMessageList($page);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson = array_merge($this->returnJson, $result);
        } else {
            //消息列表为空
            $this->returnJson['statusCode'] = '260001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 已阅消息
     */
    public function readMessage()
    {
        $msgID = securelyInput('msgID');

        // 判断ID格式是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $msgID)) {
            $this->returnJson['statusCode'] = '260004';
        } else {
            $server = new MessageModule;
            if ($server->readMessage($msgID)) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '260002';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除消息
     */
    public function delMessage()
    {
        $msgID = securelyInput('msgID');

        // 判断ID格式是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $msgID)) {
            $this->returnJson['statusCode'] = '260004';
        } else {
            $server = new MessageModule;
            if ($server->delMessage($msgID)) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '260005';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 清空消息
     */
    public function cleanMessage()
    {
        $server = new MessageModule;
        $result = $server->cleanMessage();
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '260001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取未读消息数量
     */
    public function getUnreadMessageNum()
    {
        $server = new MessageModule;
        $result = $server->getUnreadMessageNum();
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['unreadMsgNum'] = $result;
        } else {
            //消息列表为空
            $this->returnJson['statusCode'] = '260001';
        }
        exitOutput($this->returnJson);
    }

}

?>