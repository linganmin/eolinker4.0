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

class StatusCodeModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * 获取项目用户类型
     * @param $codeID
     * @return bool|int
     */
    public function getUserType(&$codeID)
    {
        $statusCodeDao = new StatusCodeDao();
        $projectID = $statusCodeDao->checkStatusCodePermission($codeID, $_SESSION['userID']);
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
     * 添加状态码
     * @param $groupID int 分组ID
     * @param $codeDesc string 状态码描述，默认为NULL
     * @param $code string 状态码
     * @return bool|int
     */
    public function addCode(&$groupID, &$codeDesc, &$code)
    {
        $projectDao = new ProjectDao;
        $statusCodeGroupDao = new StatusCodeGroupDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectID = $statusCodeGroupDao->checkStatusCodeGroupPermission($groupID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $statusCodeDao->addCode($groupID, $codeDesc, $code);
            if ($result) {
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE, $result, ProjectLogDao::$OP_TYPE_ADD, "添加状态码:'{$code}'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * 删除状态码
     * @param $codeID int 状态码ID
     * @return bool
     */
    public function deleteCode(&$codeID)
    {
        $projectDao = new ProjectDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectID = $statusCodeDao->checkStatusCodePermission($codeID, $_SESSION['userID'])) {
            $status_codes = $statusCodeDao->getStatusCodes($code_ids);
            $result = $statusCodeDao->deleteCode($codeID);
            if ($result) {
                $projectDao->updateProjectUpdateTime($projectID);
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE, $code_ids, ProjectLogDao::$OP_TYPE_DELETE, "删除状态码:'{$status_codes}'", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * 批量删除状态码
     * @param $code_ids string 状态码ID
     * @return bool
     */
    public function deleteCodes(&$code_ids)
    {
        $status_code_dao = new StatusCodeDao;
        $arr = explode(',', $code_ids);
        for ($i = 0; $i < count($arr); $i++) {
            if (!($projectID = $status_code_dao->checkStatusCodePermission($arr[$i], $_SESSION['userID'])))
                return FALSE;
        }
        $projectDao = new ProjectDao;
        $status_codes = $status_code_dao->getStatusCodes($code_ids);
        if ($status_code_dao->deleteCodes($code_ids)) {
            $projectDao->updateProjectUpdateTime($projectID);
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE, $code_ids, ProjectLogDao::$OP_TYPE_DELETE, "删除状态码:'{$status_codes}'", date("Y-m-d H:i:s", time()));

            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取状态码列表
     * @param $groupID int 分组ID
     * @return array|bool
     */
    public function getCodeList(&$groupID)
    {
        $statusCodeGroupDao = new StatusCodeGroupDao;
        $statusCodeDao = new StatusCodeDao;
        if ($statusCodeGroupDao->checkStatusCodeGroupPermission($groupID, $_SESSION['userID'])) {
            return $statusCodeDao->getCodeList($groupID);
        } else
            return FALSE;
    }

    /**
     * 获取所有状态码列表
     * @param $projectID int 项目ID
     * @return array|bool
     */
    public function getAllCodeList(&$projectID)
    {
        $projectDao = new ProjectDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $statusCodeDao->getAllCodeList($projectID);
        } else
            return FALSE;
    }

    /**
     * 修改状态码
     * @param $groupID int 分组ID
     * @param $codeID int 状态码ID
     * @param $code string 状态码
     * @param $codeDesc string 状态码描述，默认为NULL
     * @return bool
     */
    public function editCode(&$groupID, &$codeID, &$code, &$codeDesc)
    {
        $projectDao = new ProjectDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectID = $statusCodeDao->checkStatusCodePermission($codeID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $statusCodeDao->editCode($groupID, $codeID, $code, $codeDesc);
            if ($result) {
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE, $codeID, ProjectLogDao::$OP_TYPE_UPDATE, "修改状态码:'{$code}'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * 搜索状态码
     * @param $projectID int 项目ID
     * @param $tips string 搜索关键字
     * @return array|bool
     */
    public function searchStatusCode(&$projectID, &$tips)
    {
        $projectDao = new ProjectDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $statusCodeDao->searchStatusCode($projectID, $tips);
        } else
            return FALSE;
    }

    /**
     * 获取状态码数量
     * @param $projectID int 项目ID
     * @return int|bool
     */
    public function getStatusCodeNum(&$projectID)
    {
        $projectDao = new ProjectDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $statusCodeDao->getStatusCodeNum($projectID);
        } else
            return FALSE;
    }

    /**
     * 通过Excel批量添加状态码
     * @param $group_id
     * @param $code_list
     * @return bool
     */
    public function addStatusCodeByExcel(&$group_id, &$code_list)
    {
        $statusCodeGroupDao = new StatusCodeGroupDao;
        $statusCodeDao = new StatusCodeDao;
        if ($projectID = $statusCodeGroupDao->checkStatusCodeGroupPermission($group_id, $_SESSION['userID'])) {
            $result = $statusCodeDao->addStatusCodeByExcel($group_id, $code_list);
            if ($result) {
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_STATUS_CODE, $group_id, ProjectLogDao::$OP_TYPE_ADD, "通过导入Excel添加状态码", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
}

?>