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
class AutoGenerateController
{
    /**
     * 导入接口
     */
    public function importApi()
    {
        $user_name = securelyInput('userName');
        $user_password = securelyInput('password');
        $project_id = securelyInput('projectKey');
        $data = json_decode(quickInput('data'), TRUE);
        //验证用户名,4~16位非纯数字，英文数字下划线组合，只能以英文开头
        if (!preg_match('/^[a-zA-Z][0-9a-zA-Z_]{3,59}$/', $user_name)) {
            //用户名非法
            exit('用户名非法');
        } elseif (!preg_match('/^[0-9a-zA-Z]{32}$/', $user_password)) {
            //密码非法
            exit('密码格式非法');
        } elseif (!preg_match('/^[1-9][0-9]{0,10}$/', $project_id)) {
            //projectID非法
            exit('projectKey非法');
        } else {
            $module = new AutoGenerateModule();
            if ($user_info = $module->checkProjectPermission($user_name, $user_password, $project_id)) {
                $result = $module->importApi($data, $project_id, $user_info['userID']);
                if ($result) {
                    exit('导入成功');
                } else {
                    exit('导入失败');
                }
            } else {
                exit('无权限');
            }
        }
    }
}