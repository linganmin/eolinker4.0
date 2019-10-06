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
class AutomatedTestCaseModule
{
    /**
     * 获取用户权限类型
     * @param $case_id
     * @return bool|int
     */
    public function getUserType(&$case_id)
    {
        $dao = new AutomatedTestCaseDao();
        if (!($project_id = $dao->checkTestCasePermission($case_id, $_SESSION['userID']))) {
            return -1;
        }
        $auth_dao = new AuthorizationDao();
        $result = $auth_dao->getProjectUserType($_SESSION['userID'], $project_id);
        if ($result === FALSE) {
            return -1;
        }
        return $result;
    }

    /**
     * 新增测试用例
     * @param $user_id
     * @param $case_name
     * @param $case_desc
     * @param $case_type
     * @param $group_id
     * @return bool
     */
    public function addTestCase(&$user_id, &$case_name, &$case_desc, &$case_type, &$group_id)
    {
        $group_dao = new AutomatedTestCaseGroupDao();
        if (!($project_id = $group_dao->checkAutomatedTestCaseGroupPermission($group_id, $user_id))) {
            return FALSE;
        }
        $dao = new AutomatedTestCaseDao();
        $result = $dao->addTestCase($project_id, $user_id, $case_name, $case_desc, $case_type, $group_id);
        if ($result) {
            // 刷新项目更新时间
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);

            // 将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE, $result, ProjectLogDao::$OP_TYPE_ADD, "新增自动化测试用例:'{$case_name}'", date("Y-m-d H:i:s", time()));
            return $result;
        } else
            return FALSE;
    }

    /**
     * 修改测试用例
     * @param $user_id
     * @param $case_id
     * @param $case_name
     * @param $case_desc
     * @param $case_type
     * @param $group_id
     * @return bool
     */
    public function editTestCase(&$user_id, &$case_id, &$case_name, &$case_desc, &$case_type, &$group_id)
    {
        $group_dao = new AutomatedTestCaseGroupDao();
        if (!($project_id = $group_dao->checkAutomatedTestCaseGroupPermission($group_id, $user_id))) {
            return FALSE;
        }
        $dao = new AutomatedTestCaseDao();
        if (!$dao->checkTestCasePermission($case_id, $user_id)) {
            return FALSE;
        }
        $result = $dao->editTestCase($project_id, $user_id, $case_id, $case_name, $case_desc, $case_type, $group_id);
        if ($result) {
            // 刷新项目更新时间
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);

            // 将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE, $case_id, ProjectLogDao::$OP_TYPE_UPDATE, "修改自动化测试用例:'{$case_name}'", date("Y-m-d H:i:s", time()));
            return $result;
        } else
            return FALSE;
    }

    /**
     * 获取测试用例列表
     * @param $project_id
     * @param $group_id
     * @param $user_id
     * @return mixed
     */
    public function getTestCaseList(&$project_id, &$group_id, &$user_id)
    {
        $dao = new AutomatedTestCaseDao();
        if ($group_id) {
            $group_dao = new AutomatedTestCaseGroupDao();
            if (!$group_dao->checkAutomatedTestCaseGroupPermission($group_id, $user_id)) {
                return FALSE;
            }
            return $dao->getTestCaseList($group_id);
        } else {
            $project_dao = new ProjectDao();
            if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
                return FALSE;
            }
            return $dao->getAllTestCaseList($project_id);
        }
    }

    /**
     * 获取测试用例详情
     * @param $case_id
     * @param $user_id
     * @return mixed
     */
    public function getTestCaseInfo(&$case_id, &$user_id)
    {
        $dao = new AutomatedTestCaseDao();
        if ($dao->checkTestCasePermission($case_id, $user_id)) {
            return $dao->getTestCaseInfo($case_id);
        } else {
            return FALSE;
        }
    }

    /**
     * 删除测试用例
     * @param $project_id
     * @param $user_id
     * @param $case_ids
     * @return bool
     */
    public function deleteTestCase(&$project_id, &$user_id, &$case_ids)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $dao = new AutomatedTestCaseDao();
        // 获取接口名称
        $test_case_name = $dao->getTestCaseName($case_ids);
        $result = $dao->deleteTestCases($project_id, $case_ids);
        if ($result) {
            // 刷新项目更新时间
            $project_dao->updateProjectUpdateTime($project_id);

            // 将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE, $case_ids, ProjectLogDao::$OP_TYPE_DELETE, "删除自动化测试用例:'{$test_case_name}'", date("Y-m-d H:i:s", time()));
            return $result;
        } else
            return FALSE;
    }

    /**
     * 搜索用例
     * @param $project_id
     * @param $tips
     * @param $user_id
     * @return mixed
     */
    public function searchTestCase(&$project_id, &$tips, &$user_id)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $api_dao = new AutomatedTestCaseDao();
        return $api_dao->searchTestCase($project_id, $tips);
    }

    /**
     * 获取用例数据列表
     * @param $project_id
     * @param $group_id
     * @param $user_id
     * @return bool
     */
    public function getTestCaseDataList(&$project_id, &$group_id, &$user_id)
    {
        $dao = new AutomatedTestCaseDao();
        if ($group_id) {
            $group_dao = new AutomatedTestCaseGroupDao();
            if (!$group_dao->checkAutomatedTestCaseGroupPermission($group_id, $user_id)) {
                return FALSE;
            }
            return $dao->getTestCaseDataList($project_id, $group_id);
        } else {
            $project_dao = new ProjectDao();
            if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
                return FALSE;
            }
            return $dao->getAllTestCaseDataList($project_id);
        }
    }
}