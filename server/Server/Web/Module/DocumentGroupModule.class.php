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

class DocumentGroupModule
{
    /**
     * 获取用户类型
     * @param $group_id
     * @return bool|int
     */
    public function getUserType(&$group_id)
    {
        $dao = new DocumentGroupDao();
        if (!($project_id = $dao->checkGroupPermission($group_id, $_SESSION['userID']))) {
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
     * 添加文档分组
     * @param $project_id int 项目ID
     * @param $user_id int 用户ID
     * @param $group_name string 分组名称
     * @param $parent_group_id int 父分组ID
     * @param $isChild
     * @return bool|int
     */
    public function addGroup(&$project_id, &$user_id, &$group_name, &$parent_group_id, &$isChild)
    {
        $group_dao = new DocumentGroupDao();
        $project_dao = new ProjectDao();
        if (!($project_id = $project_dao->checkProjectPermission($project_id, $user_id))) {
            return FALSE;
        }

        //判断是否有父分组
        if (is_null($parent_group_id)) {
            //没有父分组
            $group_id = $group_dao->addGroup($project_id, $group_name);
            if ($group_id) {
                //更新项目的更新时间
                $project_dao->updateProjectUpdateTime($project_id);

                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT_GROUP, $group_id, ProjectLogDao::$OP_TYPE_ADD, "添加项目文档分组:'{$group_name}'", date("Y-m-d H:i:s", time()));

                //返回分组的groupID
                return $group_id;
            } else {
                return FALSE;
            }
        } else {
            //有父分组
            $group_id = $group_dao->addChildGroup($project_id, $group_name, $parent_group_id, $isChild);
            if ($group_id) {
                if (!$group_dao->checkGroupPermission($parent_group_id, $user_id)) {
                    return FALSE;
                }
                //更新项目的更新时间
                $project_dao->updateProjectUpdateTime($project_id);
                $parent_group_name = $group_dao->getGroupName($parent_group_id);

                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT_GROUP, $group_id, ProjectLogDao::$OP_TYPE_ADD, "添加项目文档子分组:'{$parent_group_name}>>{$group_name}'", date("Y-m-d H:i:s", time()));

                //返回分组ID
                return $group_id;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * 删除文档分组
     * @param $user_id int 用户ID
     * @param $group_id int 分组ID
     * @return bool
     */
    public function deleteGroup(&$user_id, &$group_id)
    {
        $group_dao = new DocumentGroupDao();
        if (!($project_id = $group_dao->checkGroupPermission($group_id, $user_id))) {
            return FALSE;
        }
        $group_name = $group_dao->getGroupName($group_id);
        if ($group_dao->deleteGroup($group_id)) {
            //更新项目的更新时间
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);

            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT_GROUP, $group_id, ProjectLogDao::$OP_TYPE_DELETE, "删除项目文档分组:'$group_name'", date("Y-m-d H:i:s", time()));

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取项目分组
     * @param $project_id int 项目ID
     * @param $user_id int 用户ID
     * @return bool|mixed
     */
    public function getGroupList(&$project_id, &$user_id)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $group_dao = new DocumentGroupDao();
        return $group_dao->getGroupList($project_id);
    }

    /**
     * 修改文档分组
     * @param $user_id int 用户ID
     * @param $group_id int 分组ID
     * @param $group_name string 分组名称
     * @param $parent_group_id int 父分组ID
     * @param $isChild
     * @return bool
     */
    public function editGroup(&$user_id, &$group_id, &$group_name, &$parent_group_id, &$isChild)
    {
        $group_dao = new DocumentGroupDao();
        if (!($project_id = $group_dao->checkGroupPermission($group_id, $user_id))) {
            return FALSE;
        }
        if ($parent_group_id && !$group_dao->checkGroupPermission($parent_group_id, $user_id)) {
            return FALSE;
        }
        if ($group_dao->editGroup($group_id, $group_name, $parent_group_id, $isChild)) {
            //更新项目的更新时间
            $project_dao = new ProjectDao();
            $project_dao->updateProjectUpdateTime($project_id);

            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT_GROUP, $group_id, ProjectLogDao::$OP_TYPE_UPDATE, "修改项目文档分组:'{$group_name}'", date("Y-m-d H:i:s", time()));

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 更新分组排序
     * @param $project_id int 项目ID
     * @param $order_list string 排序列表
     * @param $user_id int 用户ID
     * @return bool
     */
    public function updateGroupOrder(&$project_id, &$order_list, &$user_id)
    {
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $user_id)) {
            return FALSE;
        }
        $group_dao = new DocumentGroupDao();
        $result = $group_dao->updateGroupOrder($project_id, $order_list);
        if ($result) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT_GROUP, $project_id, ProjectLogDao::$OP_TYPE_UPDATE, "修改项目文档分组排序", date('Y-m-d H:i:s', time()));
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 导出分组
     * @param $group_id
     * @return bool|string
     */
    public function exportGroup(&$group_id)
    {
        $group_dao = new DocumentGroupDao();
        if (!($projectID = $group_dao->checkGroupPermission($group_id, $_SESSION['userID']))) {
            return FALSE;
        }
        $data = $group_dao->getGroupData($projectID, $group_id);
        if ($data) {
            $fileName = 'eoLinker_document_group_export_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, json_encode($data))) {
                $group_name = $group_dao->getGroupName($group_id);
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT_GROUP, $group_id, ProjectLogDao::$OP_TYPE_OTHERS, "导出文档分组：$group_name", date("Y-m-d H:i:s", time()));
                return $fileName;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 导入分组
     * @param $project_id
     * @param $data
     * @return bool
     */
    public function importGroup(&$project_id, &$data)
    {
        $group_dao = new DocumentGroupDao();
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $result = $group_dao->importGroup($project_id, $_SESSION['userID'], $data);
        if ($result) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PROJECT_DOCUMENT_GROUP, $project_id, ProjectLogDao::$OP_TYPE_OTHERS, "导入文档分组：{$data['groupName']}", date("Y-m-d H:i:s", time()));
            return $result;
        } else {
            return FALSE;
        }
    }
}