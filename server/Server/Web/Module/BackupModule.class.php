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
class BackupModule
{
    /**
     * 备份项目
     * @param $user_name
     * @param $user_password
     * @param $project_id
     * @param $verify_code
     * @return bool
     */
    public function backupProject(&$user_name, &$user_password, &$project_id, &$verify_code)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return -1;
        }
        $login_url = 'https://api.eolinker.com/common/Guest/login';
        $backup_url = 'https://api.eolinker.com/apiManagement/Import/importEoapi';
        $referer_url = 'https://api.eolinker.com/openSource';
        $proxy = new ProxyModule();
        $headers = array("Referer: $referer_url");
        $params = array('loginCall' => $user_name, 'loginPassword' => $user_password, 'verifyCode' => $verify_code);
        $response = $proxy->proxyToDesURL('POST', $login_url, $headers, $params);
        $body = json_decode($response['testResult']['body'], TRUE);
        if ($body && $body['statusCode']) {
            if ($body['statusCode'] == '000000') {
                $cookie = "verifyCode=$verify_code; ";
                $headers = $response['testResult']['headers'];
                foreach ($headers as $header) {
                    if ($header['key'] == 'Set-Cookie') {
                        $cookie = $cookie . $header['value'] . ';';
                    }
                }
                $headers = array("Cookie: $cookie", "Referer: $referer_url");
                $dao = new BackupDao();
                $data = $dao->getProjectBackupData($project_id);
                $params = array('data' => json_encode($data));
                $response = $proxy->proxyToDesURL('POST', $backup_url, $headers, $params);
                $body = json_decode($response['testResult']['body'], TRUE);
                if ($body && $body['statusCode'] == '000000') {
                    return TRUE;
                } else {
                    return -6;
                }
            } elseif ($body['statusCode'] == '120001') {
                return -3;
            } elseif ($body['statusCode'] == '120003') {
                return -4;
            } else {
                return -5;
            }
        } else {
            return -2;
        }


    }
}