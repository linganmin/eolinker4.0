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
class AutomatedTestCaseSingleController
{
    private $returnJson = array(
        'type' => 'automated_test_case_single'
    );

    // 用户ID
    private $user_id;

    /**
     * Checkout login status
     * 验证登录状态
     */
    public function __construct()
    {
        // identity authentication
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        } else {
            $this->user_id = $_SESSION['userID'];
        }
    }

    /**
     * 新增用例单例
     */
    public function addSingleTestCase()
    {
        $case_id = securelyInput('caseID');
        $case_data = quickInput('caseData', '');
        $case_code = quickInput('caseCode', '');
        $status_code = securelyInput('statusCode', '');
        $match_type = securelyInput('matchType');
        $match_rule = quickInput('matchRule', '');
        $api_name = securelyInput('apiName');
        $api_uri = quickInput('apiURI');
        $api_request_type = securelyInput('apiRequestType');
        $order_number = securelyInput('orderNumber', 0);
        // 验证分组ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $case_id)) {
            // 用例ID格式不合法
            $this->returnJson['statusCode'] = '870001';
        } else {
            // 检查权限
            $service = new AutomatedTestCaseModule();
            $user_type = $service->getUserType($case_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $service = new AutomatedTestCaseSingleModule();
                $result = $service->addSingleTestCase($this->user_id, $case_id, $case_data, $case_code, $status_code, $match_type, $match_rule, $api_name, $api_uri, $api_request_type, $order_number);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['connID'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '870000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改用例单例
     */
    public function editSingleTestCase()
    {
        $conn_id = securelyInput('connID');
        $case_data = quickInput('caseData', '');
        $case_code = quickInput('caseCode', '');
        $case_id = securelyInput('caseID');
        $status_code = securelyInput('statusCode', '');
        $match_type = securelyInput('matchType');
        $match_rule = quickInput('matchRule', '');
        $api_name = securelyInput('apiName');
        $api_uri = quickInput('apiURI');
        $api_request_type = securelyInput('apiRequestType');
        // 验证分组ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $case_id)) {
            // 用例ID格式不合法
            $this->returnJson['statusCode'] = '870001';
        } // 验证单例ID是否合法
        elseif (!preg_match('/^[0-9]{1,11}$/', $conn_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '870002';
        } else {
            $service = new AutomatedTestCaseSingleModule();
            $user_type = $service->getUserType($conn_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->editSingleTestCase($this->user_id, $case_id, $conn_id, $case_data, $case_code, $status_code, $match_type, $match_rule, $api_name, $api_uri, $api_request_type);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    $this->returnJson['statusCode'] = '870000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取用例单例列表
     */
    public function getSingleTestCaseList()
    {
        $case_id = securelyInput('caseID');
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id) && $project_id) {
            $this->returnJson['statusCode'] = '870004';
        } // 验证分组ID是否合法
        elseif (!preg_match('/^[0-9]{1,11}$/', $case_id) && !empty($case_id)) {
            // 用例ID格式不合法
            $this->returnJson['statusCode'] = '870001';
        } else {
            $service = new AutomatedTestCaseSingleModule();
            $result = $service->getSingleTestCaseList($project_id, $case_id, $this->user_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['singCaseList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '870000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取单例详情
     */
    public function getSingleTestCaseInfo()
    {
        $conn_id = securelyInput('connID');
        if (!preg_match('/^[0-9]{1,11}$/', $conn_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '870002';
        } else {
            $service = new AutomatedTestCaseSingleModule();
            $result = $service->getSingleTestCaseInfo($conn_id, $this->user_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['singleCaseInfo'] = $result;
            } else {
                $this->returnJson['statusCode'] = '870000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除单例
     */
    public function deleteSingleTestCase()
    {
        $ids = quickInput('connID');
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr); // 去掉数组中不是数字的ID
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '870004';
        } elseif (empty($arr)) {
            // 测试用例ID格式不合法
            $this->returnJson['statusCode'] = '870003';
        } else {
            // 检查权限
            $project_module = new ProjectModule();
            $user_type = $project_module->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $conn_ids = implode(',', $arr);
                $service = new AutomatedTestCaseSingleModule();
                $result = $service->deleteSingleTestCase($project_id, $this->user_id, $conn_ids);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    $this->returnJson['statusCode'] = '870000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取所有接口列表
     */
    public function getApiList()
    {
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '870004';
        } else {
            $service = new AutomatedTestCaseSingleModule();
            $result = $service->getApiList($project_id, $this->user_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '870000';
            }
        }

        exitOutput($this->returnJson);
    }
}