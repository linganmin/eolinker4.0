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

class PartnerController
{
    // 返回json类型
    private $returnJson = array('type' => 'partner');

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
     * 获取人员信息
     */
    public function getPartnerInfo()
    {
        $userName = securelyInput('userName');
        $projectID = securelyInput('projectID');

        if (!preg_match('/^([a-zA-Z][0-9a-zA-Z_]{3,59})$/', $userName)) {
            //userName格式非法
            $this->returnJson['statusCode'] = '250001';
        } else {
            $userServer = new UserModule;
            $userInfo = $userServer->checkUserExist($userName);
            if ($userInfo) {
                $partnerServer = new PartnerModule;
                if ($partnerServer->checkIsInvited($projectID, $userName)) {
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
                //用户不存在
                $this->returnJson['statusCode'] = '250002';
            }

        }
        exitOutput($this->returnJson);
    }

    /**
     * 邀请协作人员
     */
    public function invitePartner()
    {
        $userName = securelyInput('userName');
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }

        if (!preg_match('/^([a-zA-Z][0-9a-zA-Z_]{3,59})$/', $userName)) {
            //userName格式非法
            $this->returnJson['statusCode'] = '250001';
        } else {
            $userServer = new UserModule;
            $userInfo = $userServer->checkUserExist($userName);
            if ($userInfo) {
                $partnerServer = new PartnerModule;
                //检查是否已经被邀请过
                if ($partnerServer->checkIsInvited($projectID, $userName)) {
                    //已被邀请
                    $this->returnJson['statusCode'] = '250007';
                } else {
                    if ($connID = $partnerServer->invitePartner($projectID, $userInfo['userID'])) {
                        $this->returnJson['statusCode'] = '000000';
                        $this->returnJson['connID'] = $connID;
                    } else {
                        //添加协作成员失败，成员已经添加
                        $this->returnJson['statusCode'] = '250003';
                    }
                }
            } else {
                //用户不存在
                $this->returnJson['statusCode'] = '250002';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 移除协作人员
     */
    public function removePartner()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $connID = securelyInput('connID');

        $server = new PartnerModule;
        if ($server->removePartner($projectID, $connID)) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            //移除成员失败，成员已经被移出
            $this->returnJson['statusCode'] = '250004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取协作人员列表
     */
    public function getPartnerList()
    {
        $projectID = securelyInput('projectID');

        $server = new PartnerModule;
        $result = $server->getPartnerList($projectID);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['partnerList'] = $result;
        } else {
            //协作人员列表为空
            $this->returnJson['statusCode'] = '250005';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 退出协作项目
     */
    public function quitPartner()
    {
        $projectID = securelyInput('projectID');

        $server = new PartnerModule;
        $result = $server->quitPartner($projectID);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            //退出协作项目失败，已退出协作项目
            $this->returnJson['statusCode'] = '250006';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改协作成员的昵称
     */
    public function editPartnerNickName()
    {
        $projectID = securelyInput('projectID');
        $conn_id = securelyInput('connID');
        $nick_name = securelyInput('nickName');
        $name_length = mb_strlen(quickInput('nickName'), 'utf8');
        //判断关联ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $conn_id)) {
            //关联ID格式非法
            $this->returnJson['statusCode'] = '250003';
        } elseif ($name_length < 1 || $name_length > 32) {
            //昵称格式非法
            $this->returnJson['statusCode'] = '250004';
        } else {
            $module = new PartnerModule();
            $result = $module->editPartnerNickName($projectID, $conn_id, $nick_name);
            if ($result) {
                //成功
                $this->returnJson['statusCode'] = '000000';
            } else {
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
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 1) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $conn_id = securelyInput('connID');
        $user_type = securelyInput('userType');

        if (!preg_match('/^[0-9]{1,11}$/', $conn_id)) {
            //关联ID格式非法
            $this->returnJson['statusCode'] = '250003';
        } elseif (!preg_match('/^[1-3]{1}$/', $user_type)) {
            //用户类型格式非法
            $this->returnJson['statusCode'] = '250005';
        } else {
            $module = new PartnerModule();
            $result = $module->editPartnerType($projectID, $conn_id, $user_type);
            if ($result) {
                //成功
                $this->returnJson['statusCode'] = '000000';
            } else {
                //失败
                $this->returnJson['statusCode'] = '250000';
            }
        }
        exitOutput($this->returnJson);
    }

    public function getProjectInviteCode()
    {
        $projectID = securelyInput('projectID');
    }

    public function joinProjectByInviteCode()
    {
        $projectID = securelyInput('projectID');
    }
}

?>