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

class GroupModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * 获取项目中用户类型
     * @param $groupID int 分组ID
     * @return bool|int
     */
    public function getUserType(&$groupID)
    {
        $groupDao = new GroupDao;
        $projectID = $groupDao->checkGroupPermission($groupID, $_SESSION['userID']);
        if (empty($projectID)) {
            return -1;
        }
        $dao = new AuthorizationDao();
        $result = $dao->getProjectUserType($_SESSION['userID'], $projectID);
        if ($result === FALSE) {
            return -1;
        }
        return $result;
    }

    /**
     * 添加项目分组
     * @param $projectID int 项目ID
     * @param $groupName string 分组名
     * @param $parentGroupID int 父分组ID，默认为0
     * @param $isChild
     * @return int|bool
     */
    public function addGroup(&$projectID, &$groupName, &$parentGroupID, &$isChild)
    {
        $groupDao = new GroupDao;
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            if (is_null($parentGroupID)) {
                $result = $groupDao->addGroup($projectID, $groupName);
                if ($result) {
                    $projectDao->updateProjectUpdateTime($projectID);
                    //将操作写入日志
                    $log_dao = new ProjectLogDao();
                    $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API_GROUP, $result, ProjectLogDao::$OP_TYPE_ADD, "添加接口分组:'{$groupName}'", date("Y-m-d H:i:s", time()));
                    return $result;
                } else {
                    return FALSE;
                }
            } else {
                if ($groupDao->checkGroupPermission($parentGroupID, $_SESSION['userID'])) {
                    $result = $groupDao->addChildGroup($projectID, $groupName, $parentGroupID, $isChild);
                    if ($result) {
                        $projectDao->updateProjectUpdateTime($projectID);
                        $parent_group_name = $groupDao->getGroupName($parentGroupID);
                        //将操作写入日志
                        $log_dao = new ProjectLogDao();
                        $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API_GROUP, $result, ProjectLogDao::$OP_TYPE_ADD, "添加接口子分组:'{$parent_group_name}>>{$groupName}'", date("Y-m-d H:i:s", time()));
                        return $result;
                    } else {
                        return FALSE;
                    }
                } else {
                    return FALSE;
                }
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 删除项目分组
     * @param $groupID int 分组ID
     * @return bool
     */
    public function deleteGroup(&$groupID)
    {
        $groupDao = new GroupDao;
        $projectDao = new ProjectDao;
        if ($projectID = $groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $group_name = $groupDao->getGroupName($groupID);
            $result = $groupDao->deleteGroup($groupID);
            if ($result) {
                $projectDao->updateProjectUpdateTime($projectID);
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API_GROUP, $groupID, ProjectLogDao::$OP_TYPE_DELETE, "删除接口分组:'$group_name'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * 获取项目分组
     * @param $projectID int 项目ID
     * @return bool|array
     */
    public function getGroupList(&$projectID)
    {
        $groupDao = new GroupDao;
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID']))
            return $groupDao->getGroupList($projectID);
        else
            return FALSE;
    }

    /**
     * 修改项目分组
     * @param $groupID int 分组ID
     * @param $groupName string 分组名
     * @param $parentGroupID int 父分组ID
     * @param $isChild
     * @return bool
     */
    public function editGroup(&$groupID, &$groupName, &$parentGroupID, &$isChild)
    {
        $groupDao = new GroupDao;
        $projectDao = new ProjectDao;
        if ($projectID = $groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            if ($parentGroupID && !$groupDao->checkGroupPermission($parentGroupID, $_SESSION['userID'])) {
                return FALSE;
            }
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $groupDao->editGroup($groupID, $groupName, $parentGroupID, $isChild);
            if ($result) {
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API_GROUP, $groupID, ProjectLogDao::$OP_TYPE_UPDATE, "修改接口分组:'{$groupName}'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * 修改分组排序
     * @param $projectID int 项目ID
     * @param $orderList string 排序列表
     * @return bool
     */
    public function sortGroup(&$projectID, &$orderList)
    {
        $groupDao = new GroupDao;
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            if ($groupDao->sortGroup($projectID, $orderList)) {
                $projectDao->updateProjectUpdateTime($projectID);

                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API_GROUP, $projectID, ProjectLogDao::$OP_TYPE_UPDATE, "修改接口分组排序", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * 获取分组排序列表
     * @param $projectID int 项目ID
     * @return bool
     */
    public function getGroupOrderList(&$projectID)
    {
        $groupDao = new GroupDao;
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $groupDao->getGroupOrderList($projectID);
        } else {
            return FALSE;
        }
    }

    /**
     * 导出接口分组
     * @param $group_id
     * @return bool|string
     */
    public function exportGroup(&$group_id)
    {
        $group_dao = new GroupDao();
        if (!($projectID = $group_dao->checkGroupPermission($group_id, $_SESSION['userID']))) {
            return FALSE;
        }
        $data = $group_dao->getGroupData($group_id);
        if ($data) {
            $fileName = 'eoLinker_api_group_export_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, json_encode($data))) {
                $group_name = $group_dao->getGroupName($group_id);
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API_GROUP, $group_id, ProjectLogDao::$OP_TYPE_OTHERS, "导出接口分组：$group_name", date("Y-m-d H:i:s", time()));
                return $fileName;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 导入接口分组
     * @param $project_id
     * @param $data
     * @return bool
     */
    public function importGroup(&$project_id, &$data)
    {
        $group_dao = new GroupDao();
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $result = $group_dao->importGroup($project_id, $_SESSION['userID'], $data);
        if ($result) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API_GROUP, $project_id, ProjectLogDao::$OP_TYPE_OTHERS, "导入接口分组：{$data['groupName']}", date("Y-m-d H:i:s", time()));
            return $result;
        } else {
            return FALSE;
        }
    }
}

?>