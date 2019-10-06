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

class ProjectLogDao
{

    public static $OP_TYPE_ADD = 0;
    public static $OP_TYPE_UPDATE = 1;
    public static $OP_TYPE_DELETE = 2;
    public static $OP_TYPE_OTHERS = 3;
    public static $OP_TARGET_PROJECT = 0;
    public static $OP_TARGET_API = 1;
    public static $OP_TARGET_API_GROUP = 2;
    public static $OP_TARGET_STATUS_CODE = 3;
    public static $OP_TARGET_STATUS_CODE_GROUP = 4;
    public static $OP_TARGET_ENVIRONMENT = 5;
    public static $OP_TARGET_PARTNER = 6;
    public static $OP_TARGET_PROJECT_DOCUMENT_GROUP = 7;
    public static $OP_TARGET_PROJECT_DOCUMENT = 8;
    public static $OP_TARGET_AUTOMATED_TEST_CASE_GROUP = 9;
    public static $OP_TARGET_AUTOMATED_TEST_CASE = 10;

    /**
     * 记录操作日志
     * @param $project_id
     * @param $user_id
     * @param $op_target
     * @param $op_targetID
     * @param $op_type
     * @param $op_desc
     * @param $op_time
     * @return bool
     */
    public function addOperationLog(&$project_id, &$user_id, $op_target, &$op_targetID, $op_type, $op_desc, $op_time)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_log_project_operation (eo_log_project_operation.opType,eo_log_project_operation.opUserID,eo_log_project_operation.opDesc,eo_log_project_operation.opTime,eo_log_project_operation.opProjectID,eo_log_project_operation.opTarget,eo_log_project_operation.opTargetID) VALUES (?,?,?,?,?,?,?);', array(
            $op_type,
            $user_id,
            $op_desc,
            $op_time,
            $project_id,
            $op_target,
            $op_targetID
        ));

        if ($db->getAffectRow() > 0)
            return $db->getLastInsertID();
        else
            return FALSE;
    }

    /**
     * 获取操作日志
     * @param $project_id
     * @param $page
     * @param $page_size
     * @param $dayOffset
     * @return array|bool
     */
    public function getOperationLogList(&$project_id, &$page, &$page_size, $dayOffset)
    {
        $db = getDatabase();
        $result = array();
        $result['logList'] = $db->prepareExecuteAll('SELECT eo_log_project_operation.opTime,eo_log_project_operation.opType,eo_conn_project.partnerNickName,eo_user.userNickName,eo_log_project_operation.opTarget,eo_log_project_operation.opDesc
			FROM eo_log_project_operation LEFT JOIN eo_conn_project ON eo_log_project_operation.opUserID = eo_conn_project.userID AND eo_log_project_operation.opProjectID = eo_conn_project.projectID
			INNER JOIN eo_user ON eo_log_project_operation.opUserID = eo_user.userID
			WHERE eo_log_project_operation.opProjectID = ? AND eo_log_project_operation.opTime > DATE_SUB(NOW(),INTERVAL ? DAY) ORDER BY eo_log_project_operation.opTime DESC LIMIT ?,?;', array(
            $project_id,
            $dayOffset,
            ($page - 1) * $page_size,
            $page_size
        ));

        $log_count = $db->prepareExecute('SELECT COUNT(eo_log_project_operation.opID) AS logCount FROM eo_log_project_operation WHERE eo_log_project_operation.opProjectID = ? AND eo_log_project_operation.opTime > DATE_SUB(NOW(),INTERVAL ? DAY)', array(
            $project_id,
            $dayOffset
        ));

        $result = array_merge($result, $log_count);

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 获取24小时之内操作日志以及数量
     * @param $project_id
     * @return array|bool
     */
    public function getLogInADay(&$project_id)
    {
        $db = getDatabase();
        $result = array();
        $result['logList'] = $db->prepareExecuteAll('SELECT eo_log_project_operation.opTime,eo_conn_project.partnerNickName,eo_user.userNickName,eo_log_project_operation.opDesc FROM eo_log_project_operation LEFT JOIN eo_conn_project ON eo_log_project_operation.opUserID = eo_conn_project.userID AND eo_log_project_operation.opProjectID = eo_conn_project.projectID INNER JOIN eo_user ON eo_log_project_operation.opUserID = eo_user.userID WHERE eo_log_project_operation.opProjectID = ? AND eo_log_project_operation.opTime > DATE_SUB(NOW(),INTERVAL 1 DAY) ORDER BY eo_log_project_operation.opTime DESC LIMIT 0,10;', array(
            $project_id
        ));

        $log_count = $db->prepareExecute('SELECT COUNT(eo_log_project_operation.opID) AS logCount FROM eo_log_project_operation WHERE eo_log_project_operation.opProjectID = ? AND eo_log_project_operation.opTime > DATE_SUB(NOW(),INTERVAL 1 DAY) ', array(
            $project_id
        ));

        $result = array_merge($result, $log_count);

        if (empty($result))
            return FALSE;
        else
            return $result;
    }
}
