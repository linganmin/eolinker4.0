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
class AutomatedTestCaseGroupModule
{
    /**
     * 获取用户权限类型
     * @param $group_id
     * @return bool|int
     */
    public function getUserType(&$group_id)
    {
        $group_dao = new AutomatedTestCaseGroupDao();
        if (!($project_id = $group_dao->checkAutomatedTestCaseGroupPermission($group_id, $_SESSION['userID']))) {
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
     * 添加用例分组
     * @param $project_id
     * @param $user_id
     * @param $group_name
     * @param $parent_group_id
     * @param $isChild
     * @return bool
     */
    public function addGroup(&$project_id, &$user_id, &$group_name, &$parent_group_id, &$isChild)
    {
        $project_dao = new ProjectDao();
        $group_dao = new AutomatedTestCaseGroupDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        // 判断是否有父分组
        if (!$parent_group_id) {
            // 没有父分组
            $group_id = $group_dao->addGroup($project_id, $group_name);
            $desc = "添加用例分组:'{$group_name}'";
        } else {
            if (!$group_dao->checkAutomatedTestCaseGroupPermission($parent_group_id, $user_id)) {
                return FALSE;
            }
            // 有父分组
            $group_id = $group_dao->addChildGroup($project_id, $group_name, $parent_group_id, $isChild);
            $parent_group_name = $group_dao->getGroupName($parent_group_id);
            $desc = "添加用例子分组:'{$parent_group_name}>>{$group_name}'";
        }

        if ($group_id) {
            // 更新项目的更新时间
            $project_dao->updateProjectUpdateTime($project_id);
            // 将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE_GROUP, $group_id, ProjectLogDao::$OP_TYPE_ADD, $desc, date("Y-m-d H:i:s", time()));
            // 返回分组的groupID
            return $group_id;
        } else {
            return FALSE;
        }
    }

    /**
     * 删除用例分组
     * @param $user_id
     * @param $group_id
     * @return bool
     */
    public function deleteGroup(&$user_id, &$group_id)
    {
        $group_dao = new AutomatedTestCaseGroupDao();
        if (!($project_id = $group_dao->checkAutomatedTestCaseGroupPermission($group_id, $user_id))) {
            return FALSE;
        }
        $group_name = $group_dao->getGroupName($group_id);
        if ($group_dao->deleteGroup($project_id, $group_id)) {
            // 更新项目的更新时间
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);

            // 将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE_GROUP, $group_id, ProjectLogDao::$OP_TYPE_DELETE, "删除用例分组:'$group_name'", date("Y-m-d H:i:s", time()));
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取用例分组
     * @param $project_id
     * @param $user_id
     * @return bool
     */
    public function getGroupList(&$project_id, &$user_id)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $group_dao = new AutomatedTestCaseGroupDao();
        return $group_dao->getGroupList($project_id);
    }

    /**
     * 修改用例分组
     * @param $user_id
     * @param $group_id
     * @param $group_name
     * @param $parent_group_id
     * @param $isChild
     * @return bool
     */
    public function editGroup(&$user_id, &$group_id, &$group_name, &$parent_group_id, &$isChild)
    {
        $group_dao = new AutomatedTestCaseGroupDao();
        if (!($project_id = $group_dao->checkAutomatedTestCaseGroupPermission($group_id, $user_id))) {
            return FALSE;
        }
        if ($parent_group_id && !$group_dao->checkAutomatedTestCaseGroupPermission($parent_group_id, $user_id)) {
            return FALSE;
        }
        if ($group_dao->editGroup($project_id, $group_id, $group_name, $parent_group_id, $isChild)) {
            // 更新项目的更新时间
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);

            // 将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE_GROUP, $group_id, ProjectLogDao::$OP_TYPE_UPDATE, "修改用例分组:'{$group_name}'", date("Y-m-d H:i:s", time()));
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 分组排序
     * @param $project_id
     * @param $order_list
     * @param $user_id
     * @return bool
     */
    public function sortGroup(&$project_id, &$order_list, &$user_id)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $group_dao = new AutomatedTestCaseGroupDao();
        $result = $group_dao->updateGroupOrder($project_id, $order_list);
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取用例分组数据
     * @param $group_id
     * @param $user_id
     * @return array|bool
     */
    public function exportTestCaseGroup(&$group_id, &$user_id)
    {
        $group_dao = new AutomatedTestCaseGroupDao();
        if (!($projectID = $group_dao->checkAutomatedTestCaseGroupPermission($group_id, $user_id))) {
            return FALSE;
        } else {
            $data = json_encode($group_dao->getTestCaseGroupData($group_id));
            $fileName = 'eoLinker_test_case_group_dump_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, $data)) {
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE_GROUP, $group_id, ProjectLogDao::$OP_TYPE_OTHERS, "导出自动化测试用例分组", date("Y-m-d H:i:s", time()));
                return $fileName;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * 导出用例分组数据
     * @param $project_id
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function importTestCaseGroup(&$project_id, &$user_id, &$data)
    {
        $project_dao = new ProjectDao();
        if (!($projectID = $project_dao->checkProjectPermission($project_id, $user_id))) {
            return FALSE;
        }
        $group_dao = new AutomatedTestCaseGroupDao();
        if ($group_dao->importTestCaseGroup($project_id, $user_id, $data)) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_AUTOMATED_TEST_CASE_GROUP, $project_id, ProjectLogDao::$OP_TYPE_ADD, "导入自动化测试用例分组:" . $data['groupName'], date("Y-m-d H:i:s", time()));
            return TRUE;
        } else {
            return FALSE;
        }
    }
}