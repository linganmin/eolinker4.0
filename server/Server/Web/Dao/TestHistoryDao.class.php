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

class TestHistoryDao
{
    /**
     * 添加测试记录
     * @param $projectID int 项目ID
     * @param $apiID int 接口ID
     * @param $requestInfo string 测试请求信息
     * @param $resultInfo string 测试结果信息
     * @param $testTime string 测试时间
     * @return bool|int
     */
    public function addTestHistory(&$projectID, &$apiID, &$requestInfo, &$resultInfo, &$testTime)
    {
        $db = getDatabase();
        $db->prepareExecute('INSERT INTO eo_api_test_history (eo_api_test_history.projectID,eo_api_test_history.apiID,eo_api_test_history.requestInfo,eo_api_test_history.resultInfo,eo_api_test_history.testTime) VALUES (?,?,?,?,?);', array(
            $projectID,
            $apiID,
            $requestInfo,
            $resultInfo,
            $testTime
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else {
            return $db->getLastInsertID();
        }
    }

    /**
     * 删除测试记录
     * @param $testID int 测试记录ID
     * @return bool
     */
    public function deleteTestHistory(&$testID)
    {
        $db = getDatabase();

        $db->prepareExecute('DELETE FROM eo_api_test_history WHERE eo_api_test_history.testID =?;', array($testID));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 获取测试记录信息
     * @param $testID int 测试记录ID
     * @return bool|array
     */
    public function getTestHistory(&$testID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_api_test_history.projectID,eo_api_test_history.apiID,eo_api_test_history.testID,eo_api_test_history.requestInfo,eo_api_test_history.resultInfo,eo_api_test_history.testTime FROM eo_api_test_history WHERE testID =?;', array($testID));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 检查测试记录与用户的联系
     * @param $testID int 测试记录ID
     * @param $userID int 用户ID
     * @return bool|int
     */
    public function checkTestHistoryPermission(&$testID, &$userID)
    {
        $db = getDatabase();

        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_api_test_history INNER JOIN eo_api INNER JOIN eo_conn_project ON eo_api.projectID = eo_conn_project.projectID AND eo_api.apiID = eo_api_test_history.apiID WHERE eo_api_test_history.testID = ? AND eo_conn_project.userID = ?;', array(
            $testID,
            $userID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * 删除所有测试记录
     * @param $apiID int 接口ID
     * @return bool
     */
    public function deleteAllTestHistory(&$apiID)
    {
        $db = getDatabase();
        $db->prepareExecuteAll('DELETE FROM eo_api_test_history WHERE apiID = ?;', array($apiID));
        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}

?>