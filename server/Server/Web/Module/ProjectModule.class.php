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

class ProjectModule
{

    public function __construct()
    {
        @session_start();
    }

    /**
     * 获取项目用户类型
     *
     * @param $projectID int
     *            项目ID
     * @return bool|int
     */
    public function getUserType(&$projectID)
    {
        $dao = new AuthorizationDao();
        $result = $dao->getProjectUserType($_SESSION['userID'], $projectID);
        if ($result === FALSE) {
            return -1;
        }
        return $result;
    }

    /**
     * 创建项目
     *
     * @param $projectName string
     *            项目名
     * @param $projectType int
     *            项目类型 [0/1/2/3]=>[Web/App/PC/其他]
     * @param $projectVersion float
     *            项目版本，默认为1.0
     * @return bool|int
     */
    public function addProject(&$projectName, &$projectType = 0, &$projectVersion = 1.0)
    {
        $projectDao = new ProjectDao();
        $projectInfo = $projectDao->addProject($projectName, $projectType, $projectVersion, $_SESSION['userID']);
        if ($projectInfo) {
            $groupDao = new GroupDao();
            $groupName = '默认分组';
            $groupDao->addGroup($projectInfo['projectID'], $groupName);
            $status_code_group_dao = new StatusCodeGroupDao();
            $status_code_group_dao->addGroup($projectInfo['projectID'], $groupName);

            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($projectInfo['projectID'], $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PROJECT, $projectInfo['projectID'], ProjectLogDao::$OP_TYPE_UPDATE, "创建项目", date("Y-m-d H:i:s", time()));

            return $projectInfo;
        } else {
            return FALSE;
        }

    }

    /**
     * 删除项目
     *
     * @param $projectID int
     *            项目ID
     * @return bool
     */
    public function deleteProject(&$projectID)
    {
        $dao = new ProjectDao();
        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $dao->deleteProject($projectID);
        } else
            return FALSE;
    }

    /**
     * 获取项目列表
     *
     * @param $projectType int
     *            项目类型 [-1/0/1/2/3]=>[全部/Web/App/PC/其他]
     * @return bool|array
     */
    public function getProjectList(&$projectType = -1)
    {
        $dao = new ProjectDao();
        return $dao->getProjectList($_SESSION['userID'], $projectType);
    }

    /**
     * 更改项目
     *
     * @param $projectID int
     *            项目ID
     * @param $projectName string
     *            项目名
     * @param $projectType int
     *            项目类型 [0/1/2/3]=>[Web/App/PC/其他]
     * @param $projectVersion float
     *            项目版本，默认为1.0
     * @return bool
     */
    public function editProject(&$projectID, &$projectName, &$projectType, &$projectVersion = 1.0)
    {
        $dao = new ProjectDao();
        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $result = $dao->editProject($projectID, $projectName, $projectType, $projectVersion);
            if ($result) {
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PROJECT, $projectID, ProjectLogDao::$OP_TYPE_UPDATE, "修改项目信息:{$projectName}", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * 获取项目信息
     *
     * @param $projectID int
     *            项目ID
     * @return bool|array
     */
    public function getProject(&$projectID)
    {
        $dao = new ProjectDao();
        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $info = $dao->getProject($projectID, $_SESSION['userID']);
            // 获取当天项目动态
            $log_dao = new ProjectLogDao();
            $log_info = $log_dao->getLogInADay($projectID);
            $info = array_merge($info, $log_info);
            return $info;
        } else
            return FALSE;
    }

    /**
     * 更新项目更新时间
     *
     * @param $projectID int
     *            项目ID
     * @return bool
     */
    public function updateProjectUpdateTime(&$projectID)
    {
        $dao = new ProjectDao();
        if ($dao->updateProjectUpdateTime($projectID))
            return TRUE;
        else
            return FALSE;
    }

//    /**
//     * 获取环境列表
//     *
//     * @param $projectID int
//     *            项目ID
//     * @return bool|array
//     */
//    public function getEnvList(&$projectID)
//    {
//        $dao = new ProjectDao();
//        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
//            return $dao->getEnvList($projectID);
//        } else
//            return FALSE;
//    }
//
//    /**
//     * 添加环境
//     *
//     * @param $projectID int
//     *            项目ID
//     * @param $envName string
//     *            环境名
//     * @param $envURI string
//     *            环境地址
//     * @return bool|int
//     */
//    public function addEnv(&$projectID, &$envName, &$envURI)
//    {
//        $dao = new ProjectDao();
//        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
//            return $dao->addEnv($projectID, $envName, $envURI);
//        } else
//            return FALSE;
//    }
//
//    /**
//     * 删除环境
//     *
//     * @param $projectID int
//     *            项目ID
//     * @param $envID int
//     *            环境ID
//     * @return bool
//     */
//    public function deleteEnv(&$projectID, &$envID)
//    {
//        $dao = new ProjectDao();
//        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
//            return $dao->deleteEnv($projectID, $envID);
//        } else
//            return FALSE;
//    }
//
//    /**
//     * 修改环境
//     *
//     * @param $projectID int
//     *            项目ID
//     * @param $envID int
//     *            环境ID
//     * @param $envName string
//     *            环境名
//     * @param $envURI string
//     *            环境地址
//     * @return bool
//     */
//    public function editEnv(&$projectID, &$envID, &$envName, &$envURI)
//    {
//        $dao = new ProjectDao();
//        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
//            return $dao->editEnv($envID, $envName, $envURI);
//        } else
//            return FALSE;
//    }

    /**
     * 导出项目
     *
     * @param $projectID int
     *            项目ID
     * @return bool|string
     */
    public function dumpProject(&$projectID)
    {
        $dao = new ProjectDao();
        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $dumpJson = json_encode($dao->dumpProject($projectID));
            $fileName = 'eoLinker_dump_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, $dumpJson)) {
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_PROJECT, $projectID, ProjectLogDao::$OP_TYPE_ADD, "导出项目", date("Y-m-d H:i:s", time()));
                return $fileName;
            }

        } else
            return FALSE;
    }

    /**
     * 获取api数量
     *
     * @param $projectID int
     *            项目ID
     * @return bool|int
     */
    public function getApiNum(&$projectID)
    {
        $dao = new ProjectDao();
        if ($dao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $dao->getApiNum($projectID);
        } else
            return FALSE;
    }

    /**
     * 获取日志列表
     * @param $project_id int 项目的ID
     * @param $page int 页码
     * @param $page_size int 每页条目数量
     * @return bool|array
     */
    public function getProjectLogList(&$project_id, &$page, &$page_size)
    {
        $user_id = $_SESSION['userID'];

        $dao = new ProjectDao();

        if ($dao->checkProjectPermission($project_id, $user_id)) {
            $log_dao = new ProjectLogDao();

            //7天之内的日志
            $log_list = $log_dao->getOperationLogList($project_id, $page, $page_size, 7);
            return $log_list;
        } else
            return FALSE;
    }
}

?>