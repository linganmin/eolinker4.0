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

class UserController
{
    // 返回json类型
    private $returnJson = array('type' => 'user');

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
     * 退出登录
     */
    public function logout()
    {
        @session_start();
        @session_destroy();
        $this->returnJson['statusCode'] = '000000';
        exitOutput(json_encode($this->returnJson));
    }

    /**
     * 修改密码
     */
    public function changePassword()
    {
        $oldPassword = securelyInput('oldPassword');
        $newPassword = securelyInput('newPassword');

        if (!preg_match('/^[0-9a-zA-Z]{32}$/', $newPassword) || !preg_match('/^[0-9a-zA-Z]{32}$/', $oldPassword)) {
            //密码非法
            $this->returnJson['statusCode'] = '130002';
        } elseif ($oldPassword == $newPassword) {
            //密码相同
            $this->returnJson['statusCode'] = '000000';
        } else {
            $server = new UserModule;
            $result = $server->changePassword($oldPassword, $newPassword);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '130006';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改昵称
     */
    public function changeNickName()
    {
        $nickNameLength = mb_strlen(quickInput('nickName'), 'utf8');
        $nickName = securelyInput('nickName');

        if ($nickNameLength > 20) {
            //昵称格式非法
            $this->returnJson['statusCode'] = '130008';
        } else {
            $server = new UserModule;
            $result = $server->changeNickName($nickName);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '130009';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 确认用户名
     */
    public function confirmUserName()
    {
        $userName = securelyInput('userName');

        //验证用户名,4~16位非纯数字，英文数字下划线组合，只能以英文开头
        if (!preg_match('/^[a-zA-Z][0-9a-zA-Z_]{3,59}$/', $userName)) {
            //用户名非法
            $this->returnJson['statusCode'] = '130001';
        } else {
            $server = new UserModule;
            $result = $server->confirmUserName($userName);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '130010';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo()
    {
        $server = new UserModule;
        $result = $server->getUserInfo();
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['userInfo'] = $result;
        } else {
            $this->returnJson['statusCode'] = '130013';
        }
        exitOutput($this->returnJson);
    }

}

?>