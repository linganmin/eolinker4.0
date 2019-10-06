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

class GuestController
{
    //返回json类型
    private $returnJson = array('type' => 'guest');

    /**
     * 登录
     */
    public function login()
    {
        $loginName = securelyInput('loginName');
        $loginPassword = securelyInput('loginPassword');
        $server = new GuestModule;
        if (preg_match('/^[0-9a-zA-Z]{32}$/', $loginPassword)) {
            if (preg_match('/^[0-9a-zA-Z][0-9a-zA-Z_]{3,63}$/', $loginName)) {
                $result = $server->login($loginName, $loginPassword);

                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['userID'] = $_SESSION['userID'];
                } else
                    $this->returnJson['statusCode'] = '120004';
            } else {
                $this->returnJson['statusCode'] = '120001';
                exitOutput(json_encode($this->returnJson));
            }
        } else {
            //密码非法
            $this->returnJson['statusCode'] = '120002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 用户名注册
     */
    public function register()
    {
        if (!ALLOW_REGISTER) {
            //不允许新用户注册
            $this->returnJson['statusCode'] = '130005';
        } else {
            $userName = securelyInput('userName');
            $loginPassword = securelyInput('userPassword');
            $nickNameLen = mb_strlen(quickInput('userNickName'), 'utf8');
            $userNickName = securelyInput('userNickName');

            //验证用户名,4~16位非纯数字，英文数字下划线组合，只能以英文开头
            if (!preg_match('/^[0-9a-zA-Z][0-9a-zA-Z_]{3,63}$/', $userName)) {
                //用户名非法
                $this->returnJson['statusCode'] = '130001';
            } elseif (!preg_match('/^[0-9a-zA-Z]{32}$/', $loginPassword)) {
                //密码非法
                $this->returnJson['statusCode'] = '130002';
            } elseif (!($nickNameLen == 0 || $nickNameLen <= 16)) {
                //用户名非法
                $this->returnJson['statusCode'] = '130014';
            } else {
                $server = new GuestModule;
                $result = $server->register($userName, $loginPassword, $userNickName);

                if ($result)
                    //注册成功
                    $this->returnJson['statusCode'] = '000000';
                else
                    //注册失败
                    $this->returnJson['statusCode'] = '130005';
            }
        }

        exitOutput($this->returnJson);
    }

    /**
     * 用户查重
     */
    public function checkUserNameExist()
    {
        $userName = securelyInput('userName');
        $server = new GuestModule;
        if (preg_match('/^[0-9a-zA-Z][0-9a-zA-Z_]{3,63}$/', $userName)) {
            $result = $server->checkUserNameExist($userName);
            if ($result)
                //用户名可注册
                $this->returnJson['statusCode'] = '000000';
            else
                //用户名重复
                $this->returnJson['statusCode'] = '130005';
        } else
            //userName格式非法
            $this->returnJson['statusCode'] = '130001';

        exitOutput($this->returnJson);
    }

    /**
     * 检查登录状态
     */
    public function checkLogin()
    {
        $server = new GuestModule;
        $result = $server->checkLogin();

        if ($result) {
            //已登录
            $this->returnJson['statusCode'] = '000000';
        } else {
            //未登录
            $this->returnJson['statusCode'] = '120005';
        }
        exitOutput($this->returnJson);
    }

}

?>