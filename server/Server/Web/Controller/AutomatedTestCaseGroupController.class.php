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
class AutomatedTestCaseGroupController
{
    // 返回json类型
    private $returnJson = array(
        'type' => 'automated_test_case_group'
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
     * 添加用例分组
     */
    public function addGroup()
    {
        $project_id = securelyInput('projectID');
        // 分组组名
        $name_length = mb_strlen(quickInput('groupName'), 'utf8');
        $group_name = securelyInput('groupName');
        // 父分组ID
        $parent_group_id = securelyInput('parentGroupID', NULL);
        $isChild = securelyInput('isChild', 0);

        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '850005';
        } elseif ($name_length < 1 || $name_length > 32) {
            // 分组名称格式非法
            $this->returnJson['statusCode'] = '850001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $parent_group_id) && $parent_group_id != NULL) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '850002';
        } else {
            // 检查权限
            $project_module = new ProjectModule();
            $user_type = $project_module->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $service = new AutomatedTestCaseGroupModule();
                $result = $service->addGroup($project_id, $this->user_id, $group_name, $parent_group_id, $isChild);
                // 验证结果
                if ($result) {
                    // 添加项目用例分组成功
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['groupID'] = $result;
                } else {
                    // 添加项目用例分组失败
                    $this->returnJson['statusCode'] = '850000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除用例分组
     */
    public function deleteGroup()
    {
        // 分组ID
        $group_id = securelyInput('groupID');
        // 验证分组ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '850003';
        } else {
            // 分组ID格式合法
            $service = new AutomatedTestCaseGroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->deleteGroup($this->user_id, $group_id);
                // 验证结果
                if ($result) {
                    // 删除项目用例分组成功
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    // 删除用例分组失败
                    $this->returnJson['statusCode'] = '850000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取用例分组列表
     */
    public function getGroupList()
    {
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '850005';
        } else {
            $service = new AutomatedTestCaseGroupModule();
            // 获取分组列表
            $result = $service->getGroupList($project_id, $this->user_id);
            // 验证结果
            if ($result) {
                // 获取用例分组成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                // 获取用例分组失败
                $this->returnJson['statusCode'] = '850000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改用例分组
     */
    public function editGroup()
    {
        // 分组组名长度
        $name_length = mb_strlen(quickInput('groupName'), 'utf8');
        // 分组ID
        $group_id = securelyInput('groupID');
        // 分组组名
        $group_name = securelyInput('groupName');
        // 父分组ID
        $parent_group_id = securelyInput('parentGroupID', NULL);
        $isChild = securelyInput('isChild');

        // 判断分组ID和组名格式是否合法
        if ($name_length < 1 && $name_length > 32) {
            $this->returnJson['statusCode'] = '850001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $parent_group_id) && $parent_group_id != NULL) {
            // 父分组ID格式不合法
            $this->returnJson['statusCode'] = '850002';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '850003';
        } else {
            // 检查权限
            $service = new AutomatedTestCaseGroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->editGroup($this->user_id, $group_id, $group_name, $parent_group_id, $isChild);
                if ($result) {
                    // 修改用例分组成功
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    // 修改用例分组失败
                    $this->returnJson['statusCode'] = '850000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 分组排序
     */
    public function sortGroup()
    {
        // 排序json字符串
        $order_list = quickInput('orderList');
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '850005';
        } // 判断排序格式是否合法
        elseif (empty($order_list)) {
            // 排序格式非法
            $this->returnJson['statusCode'] = '850004';
        } else {
            // 检查权限
            $project_module = new ProjectModule();
            $user_type = $project_module->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $service = new AutomatedTestCaseGroupModule();
                $result = $service->sortGroup($project_id, $order_list, $this->user_id);
                // 验证结果
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    $this->returnJson['statusCode'] = '850000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导出测试用例分组数据
     */
    public function exportGroup()
    {
        // 分组ID
        $group_id = securelyInput('groupID');
        if (!preg_match('/^[1-9][0-9]{0,10}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '850003';
        } else {
            $server = new AutomatedTestCaseGroupModule();
            $user_type = $server->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $server->exportTestCaseGroup($group_id, $this->user_id);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['fileName'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '850000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    public function importGroup()
    {
        $json = quickInput('data');
        $project_id = securelyInput('projectID');
        $data = json_decode($json, TRUE);
        if (!preg_match('/^[1-9][0-9]{0,10}$/', $project_id)) {
            $this->returnJson['statusCode'] = '850005';
        } //判断导入数据是否为空
        elseif (empty($data)) {
            $this->returnJson['statusCode'] = '850005';
        } else {
            // 检查权限
            $project_module = new ProjectModule();
            $user_type = $project_module->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $server = new AutomatedTestCaseGroupModule();
                $result = $server->importTestCaseGroup($project_id, $this->user_id, $data);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    $this->returnJson['statusCode'] = '850000';
                }
            }
        }
        exitOutput($this->returnJson);
    }
}