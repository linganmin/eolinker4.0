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

class DatabasePartnerController
{
    // return json object
    // 返回json类型
    private $returnJson = array('type' => 'partner');

    /**
     * checkout login status
     * 检查登录状态
     */
    public function __construct()
    {
        // identity authentication
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * get partner's personal information by userName
     * 获取人员信息
     */
    public function getPartnerInfo()
    {
        $userName = securelyInput('userName');
        $dbID = securelyInput('dbID');

        if (!preg_match('/^([a-zA-Z][0-9a-zA-Z_]{3,59})$/', $userName)) {
            // illegal userName length
            //userName格式非法
            $this->returnJson['statusCode'] = '250001';
        } else {
            $userServer = new UserModule;
            $userInfo = $userServer->checkUserExist($userName);
            if ($userInfo) {
                $partnerServer = new DatabasePartnerModule;
                if ($partnerServer->checkIsInvited($dbID, $userName)) {
                    $this->returnJson['statusCode'] = '250007';
                    $this->returnJson['userInfo']['userName'] = $userName;
                    $this->returnJson['userInfo']['userNickName'] = $userInfo['userNickName'];
                    $this->returnJson['userInfo']['isInvited'] = 1;
                } else {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['userInfo']['userName'] = $userName;
                    $this->returnJson['userInfo']['userNickName'] = $userInfo['userNickName'];
                    $this->returnJson['userInfo']['isInvited'] = 0;
                }
            } else {
                //the user doesn't exist
                //用户不存在
                $this->returnJson['statusCode'] = '250002';
            }

        }
        exitOutput($this->returnJson);
    }

    /**
     * invite a user to join the database
     * 邀请协作人员
     */
    public function invitePartner()
    {
        $userName = securelyInput('userName');
        $dbID = securelyInput('dbID');
        $module = new DatabaseModule();
        $userType = $module->getUserType($dbID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }

        if (!preg_match('/^([a-zA-Z][0-9a-zA-Z_]{3,59})$/', $userName)) {
            // illegal userName
            //userName格式非法
            $this->returnJson['statusCode'] = '250001';
        } else {
            $userServer = new UserModule;
            $userInfo = $userServer->checkUserExist($userName);
            if ($userInfo) {
                $partnerServer = new DatabasePartnerModule;
                //check if the user have been invited
                //检查是否已经被邀请过
                if ($partnerServer->checkIsInvited($dbID, $userName)) {
                    // Has been invited
                    //已被邀请
                    $this->returnJson['statusCode'] = '250007';
                } else {
                    if ($connID = $partnerServer->invitePartner($dbID, $userInfo['userID'])) {
                        $this->returnJson['statusCode'] = '000000';
                        $this->returnJson['connID'] = $connID;
                    } else {
                        //invite fail, the user has been invited
                        //添加协作成员失败，成员已经添加
                        $this->returnJson['statusCode'] = '250003';
                    }
                }
            } else {
                // userName doesn't exist
                //用户不存在
                $this->returnJson['statusCode'] = '250002';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * remove user from database
     * 移除协作人员
     */
    public function removePartner()
    {
        $dbID = securelyInput('dbID');
        $module = new DatabaseModule();
        $userType = $module->getUserType($dbID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $connID = securelyInput('connID');

        $server = new DatabasePartnerModule;
        if ($server->removePartner($dbID, $connID)) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            // remove user fail, the user has been removed
            //移除成员失败，成员已经被移出
            $this->returnJson['statusCode'] = '250004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * get database partner list
     * 获取协作人员列表
     */
    public function getPartnerList()
    {
        $dbID = securelyInput('dbID');

        $server = new DatabasePartnerModule;
        $result = $server->getPartnerList($dbID);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['partnerList'] = $result;
        } else {
            //the partner list is empty
            //协作人员列表为空
            $this->returnJson['statusCode'] = '250005';
        }
        exitOutput($this->returnJson);
    }

    /**
     * quit the database
     * 退出协作数据字典
     */
    public function quitPartner()
    {
        $dbID = securelyInput('dbID');

        $server = new DatabasePartnerModule;
        $result = $server->quitPartner($dbID);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            //quit fail, the user has quited
            //退出协作项目失败，已退出协作项目
            $this->returnJson['statusCode'] = '250006';
        }
        exitOutput($this->returnJson);
    }

    /**
     * edit partner's nickName
     * 修改协作成员的昵称
     */
    public function editPartnerNickName()
    {
        $dbID = securelyInput('dbID');
        $conn_id = securelyInput('connID');
        $nick_name = securelyInput('nickName');
        $name_length = mb_strlen(quickInput('nickName'), 'utf8');
        //verify connID
        //判断关联ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $conn_id)) {
            //illegal connID
            //关联ID格式非法
            $this->returnJson['statusCode'] = '250003';
        } elseif ($name_length < 1 || $name_length > 32) {
            //illegal nickName
            //昵称格式非法
            $this->returnJson['statusCode'] = '250004';
        } else {
            $module = new DatabasePartnerModule();
            $result = $module->editPartnerNickName($dbID, $conn_id, $nick_name);
            if ($result) {
                //edit success
                //成功
                $this->returnJson['statusCode'] = '000000';
            } else {
                //edit fail
                //失败
                $this->returnJson['statusCode'] = '250000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改协作成员的类型
     */
    public function editPartnerType()
    {
        $dbID = securelyInput('dbID');
        $module = new DatabaseModule();
        $userType = $module->getUserType($dbID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $conn_id = securelyInput('connID');
        $user_type = securelyInput('userType');

        if (!preg_match('/^[0-9]{1,11}$/', $conn_id)) {
            //illegal connID
            //关联ID格式非法
            $this->returnJson['statusCode'] = '250003';
        } elseif (!preg_match('/^[1-3]{1}$/', $user_type)) {
            //illegal userType
            //用户类型格式非法
            $this->returnJson['statusCode'] = '250005';
        } else {
            $module = new DatabasePartnerModule();
            $result = $module->editPartnerType($dbID, $conn_id, $user_type);
            if ($result) {
                //edit success
                //成功
                $this->returnJson['statusCode'] = '000000';
            } else {
                //edit fail
                //失败
                $this->returnJson['statusCode'] = '250000';
            }
        }
        exitOutput($this->returnJson);
    }
}

?>