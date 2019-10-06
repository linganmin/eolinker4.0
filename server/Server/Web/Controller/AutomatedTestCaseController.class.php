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
class AutomatedTestCaseController
{
    private $returnJson = array(
        'type' => 'automated_test_case'
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
     * 添加用例
     */
    public function addTestCase()
    {
        $case_name = securelyInput('caseName');
        $name_length = mb_strlen(quickInput('caseName'), 'utf8');
        $case_desc = securelyInput('caseDesc', '');
        $case_type = securelyInput('caseType', 0);
        // 分组ID
        $group_id = securelyInput('groupID');
        // 验证分组ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '860001';
        } elseif ($name_length < 1 || $name_length > 32) {
            // 用例名称格式非法
            $this->returnJson['statusCode'] = '860002';
        } else {
            //检查权限
            $group_module = new AutomatedTestCaseGroupModule();
            $user_type = $group_module->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $service = new AutomatedTestCaseModule();
                $result = $service->addTestCase($this->user_id, $case_name, $case_desc, $case_type, $group_id);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['caseID'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '860000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改用例
     */
    public function editTestCase()
    {
        $case_name = securelyInput('caseName');
        $name_length = mb_strlen(quickInput('caseName'), 'utf8');
        $case_desc = securelyInput('caseDesc', '');
        $case_type = securelyInput('caseType', 0);
        $case_id = securelyInput('caseID');
        // 分组ID
        $group_id = securelyInput('groupID');
        // 验证分组ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '860001';
        } elseif ($name_length < 1 || $name_length > 32) {
            // 用例名称格式非法
            $this->returnJson['statusCode'] = '860002';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $case_id)) {
            // 用例ID格式非法
            $this->returnJson['statusCode'] = '860005';
        } else {
            $service = new AutomatedTestCaseModule();
            $user_type = $service->getUserType($case_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->editTestCase($this->user_id, $case_id, $case_name, $case_desc, $case_type, $group_id);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    $this->returnJson['statusCode'] = '860000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取测试用例列表
     */
    public function getTestCaseList()
    {
        $group_id = securelyInput('groupID');
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $group_id) && !empty($group_id)) {
            // 测试用例ID格式不合法
            $this->returnJson['statusCode'] = '860001';
        } elseif (!empty($project_id) && !preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '860006';
        } else {
            $service = new AutomatedTestCaseModule();
            $result = $service->getTestCaseList($project_id, $group_id, $this->user_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['caseList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '860000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取测试用例详情
     */
    public function getTestCaseInfo()
    {
        $case_id = securelyInput('caseID');
        if (!preg_match('/^[0-9]{1,11}$/', $case_id)) {
            // 测试用例ID格式不合法
            $this->returnJson['statusCode'] = '860005';
        } else {
            $service = new AutomatedTestCaseModule();
            $result = $service->getTestCaseInfo($case_id, $this->user_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['caseInfo'] = $result;
            } else {
                $this->returnJson['statusCode'] = '860000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除测试用例
     */
    public function deleteTestCase()
    {
        $ids = quickInput('caseID');
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr); // 去掉数组中不是数字的ID
        $project_id = securelyInput('projectID');
        if (!empty($project_id) && !preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '860006';
        } elseif (empty($arr)) {
            // 测试用例ID格式不合法
            $this->returnJson['statusCode'] = '860005';
        } else {
            $case_ids = implode(',', $arr);
            $service = new AutomatedTestCaseModule();
            $result = $service->deleteTestCase($project_id, $this->user_id, $case_ids);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '860000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 搜索api
     */
    public function searchTestCase()
    {
        //搜索关键字的长度
        $tips_length = mb_strlen(quickInput('tips'), 'utf8');
        //搜索的关键字
        $tips = securelyInput('tips');
        $project_id = securelyInput('projectID');
        if (!empty($project_id) && !preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '860006';
        } elseif ($tips_length > 255 || $tips_length == 0) {
            $this->returnJson['statusCode'] = '860003';
        } else {
            $api_module = new AutomatedTestCaseModule();
            $result = $api_module->searchTestCase($project_id, $tips, $this->user_id);
            //判断是否成功
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['caseList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '860000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取用例数据列表
     */
    public function getTestCaseDataList()
    {
        $group_id = securelyInput('groupID');
        $project_id = securelyInput('projectID');
        if (!empty($project_id) && !preg_match('/^[1-9][0-9]{0,10}$/', $project_id)) {
            $this->returnJson['statusCode'] = '860006';
        } elseif (!empty($group_id) && !preg_match('/^[1-9][0-9]{0,10}$/', $group_id)) {
            $this->returnJson['statusCode'] = '860001';
        } else {
            $server = new AutomatedTestCaseModule();
            $result = $server->getTestCaseDataList($project_id, $group_id, $this->user_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['caseList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '860000';
            }
        }
        exitOutput($this->returnJson);
    }
}