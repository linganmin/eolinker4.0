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
class AutomatedTestCaseSingleModule
{

    public function getUserType(&$conn_id)
    {
        $dao = new AutomatedTestCaseSingleDao();
        if (!($project_id = $dao->checkSingleTestCasePermission($conn_id, $_SESSION['userID']))) {
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
     * 新增用例单例
     * @param $user_id
     * @param $case_id
     * @param $case_data
     * @param $case_code
     * @param $status_code
     * @param $match_type
     * @param $match_rule
     * @param $api_name
     * @param $api_uri
     * @param $api_request_type
     * @param $order_number
     * @return bool
     */
    public function addSingleTestCase(&$user_id, &$case_id, &$case_data, &$case_code, &$status_code, &$match_type, &$match_rule, &$api_name, &$api_uri, &$api_request_type, &$order_number)
    {
        $test_case_dao = new AutomatedTestCaseDao();
        if (!($project_id = $test_case_dao->checkTestCasePermission($case_id, $user_id))) {
            return FALSE;
        }
        $dao = new AutomatedTestCaseSingleDao();
        $result = $dao->addSingleTestCase($case_id, $case_data, $case_code, $status_code, $match_type, $match_rule, $api_name, $api_uri, $api_request_type, $order_number);
        if ($result) {
            // 刷新项目更新时间
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);
            //获取用例信息
            $case_dao = new AutomatedTestCaseDao();
            $case_name = $case_dao->getTestCaseName($case_id);
            // 将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE, $case_id, ProjectLogDao::$OP_TYPE_UPDATE, "修改自动化测试用例:'{$case_name}'", date("Y-m-d H:i:s", time()));
            return $result;
        } else
            return FALSE;
    }

    /**
     * 修改用例单例
     * @param $user_id
     * @param $case_id
     * @param $conn_id
     * @param $case_data
     * @param $case_code
     * @param $status_code
     * @param $match_type
     * @param $match_rule
     * @param $api_name
     * @param $api_uri
     * @param $api_request_type
     * @return bool
     */
    public function editSingleTestCase(&$user_id, &$case_id, &$conn_id, &$case_data, &$case_code, &$status_code, &$match_type, &$match_rule, &$api_name, &$api_uri, &$api_request_type)
    {
        $dao = new AutomatedTestCaseSingleDao();
        if (!($project_id = $dao->checkSingleTestCasePermission($conn_id, $user_id))) {
            return FALSE;
        }
        $result = $dao->editSingleTestCase($case_id, $conn_id, $case_data, $case_code, $status_code, $match_type, $match_rule, $api_name, $api_uri, $api_request_type);
        if ($result) {
            // 刷新项目更新时间
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);
            //获取用例信息
            $case_dao = new AutomatedTestCaseDao();
            $case_name = $case_dao->getTestCaseName($case_id);
            // 将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE, $case_id, ProjectLogDao::$OP_TYPE_UPDATE, "修改自动化测试用例:'{$case_name}'", date("Y-m-d H:i:s", time()));
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 获取用例单例列表
     * @param $project_id
     * @param $case_id
     * @param $user_id
     * @return mixed
     */
    public function getSingleTestCaseList(&$project_id, &$case_id, &$user_id)
    {
        $dao = new AutomatedTestCaseSingleDao();
        if ($case_id) {
            $test_case_dao = new AutomatedTestCaseDao();
            if (!$test_case_dao->checkTestCasePermission($case_id, $user_id)) {
                return FALSE;
            }
            return $dao->getSingleTestCaseList($case_id);
        } else {
            $project_dao = new ProjectDao();
            if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
                return FALSE;
            }
            return $dao->getAllSingleTestCaseList($project_id);
        }
    }

    /**
     * 获取用例单例详情
     * @param $conn_id
     * @param $user_id
     * @return mixed
     */
    public function getSingleTestCaseInfo(&$conn_id, &$user_id)
    {
        $dao = new AutomatedTestCaseSingleDao();
        if (!($project_id = $dao->checkSingleTestCasePermission($conn_id, $user_id))) {
            return FALSE;
        }
        return $dao->getSingleTestCaseInfo($project_id, $conn_id);
    }

    /**
     * 删除测试用例
     * @param $project_id
     * @param $user_id
     * @param $conn_ids
     * @return bool
     */
    public function deleteSingleTestCase(&$project_id, &$user_id, &$conn_ids)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $dao = new AutomatedTestCaseSingleDao();
        $case_name = $dao->getTestCastName($conn_ids);
        $case_id = $dao->getCaseIDByConnID($conn_ids);
        $result = $dao->deleteSingleTestCase($conn_ids, $project_id);
        if ($result) {
            // 刷新项目更新时间
            $project_dao->updateProjectUpdateTime($project_id);

            // 将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE, $case_id, ProjectLogDao::$OP_TYPE_UPDATE, "修改自动化测试用例:'{$case_name}'", date("Y-m-d H:i:s", time()));
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 获取所有接口列表
     * @param $project_id
     * @param $user_id
     * @return bool
     */
    public function getApiList(&$project_id, &$user_id)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $dao = new AutomatedTestCaseSingleDao();
        return $dao->getApiList($project_id);
    }
}