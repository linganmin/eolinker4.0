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
class BackupController
{
    // 返回json类型
    private $returnJson = array('type' => 'backup');

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
     * 备份项目
     */
    public function backupProject()
    {
        $user_call = securelyInput('userCall');
        $user_password = securelyInput('userPassword');
        $project_id = securelyInput('projectID');
        $verify_code = securelyInput('verifyCode');
        if (empty($user_call)) {
            $this->returnJson['statusCode'] = '310001';
        } elseif (!preg_match('/^[0-9a-zA-Z]{32}$/', $user_password)) {
            $this->returnJson['statusCode'] = '310002';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '310003';
        } elseif (!preg_match('/^[0-9a-zA-Z]{32}$/', $verify_code)) {
            $this->returnJson['statusCode'] = '310004';
        } else {
            $project_module = new ProjectModule();
            $user_type = $project_module->getUserType($project_id);
            if ($user_type < 0 || $user_type > 1) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $module = new BackupModule();
                $result = $module->backupProject($user_call, $user_password, $project_id, $verify_code);
                if ($result === TRUE) {
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    switch ($result) {
                        case -1:
                            $this->returnJson['msg'] = '用户没有写权限';
                            $this->returnJson['statusCode'] = '310005';
                            break;
                        case -2:
                            $this->returnJson['msg'] = '发送登录请求失败';
                            $this->returnJson['statusCode'] = '310006';
                            break;
                        case -3:
                            $this->returnJson['msg'] = '登录账号非法';
                            $this->returnJson['statusCode'] = '310007';
                            break;
                        case -4:
                            $this->returnJson['msg'] = '账号不存在或密码错误';
                            $this->returnJson['statusCode'] = '310008';
                            break;
                        case -5:
                            $this->returnJson['msg'] = '未知登录错误';
                            $this->returnJson['statusCode'] = '310009';
                            break;
                        case -6:
                            $this->returnJson['msg'] = '备份项目失败';
                            $this->returnJson['statusCode'] = '310010';
                            break;
                        default:
                            $this->returnJson['statusCode'] = '310000';
                            $this->returnJson['statusCode'] = '备份项目失败';
                    }
                }
            }
        }
        exitOutput($this->returnJson);
    }
}