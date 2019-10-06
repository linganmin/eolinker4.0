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
class ApiDao
{

    /**
     * add api
     * 添加api
     *
     * @param $apiName string
     *            接口名称
     * @param $apiURI string
     *            接口地址
     * @param $apiProtocol int
     *            请求协议 [0/1]=>[HTTP/HTTPS]
     * @param $apiSuccessMock string
     *            访问成功结果，默认为NULL
     * @param $apiFailureMock string
     *            访问失败结果，默认为NULL
     * @param $apiRequestType int
     *            请求类型 [0/1/2/3/4/5/6]=>[POST/GET/PUT/DELETE/HEAD/OPTIONS/PATCH]
     * @param $apiStatus int
     *            接口状态 [0/1/2]=>[启用/维护/弃用]
     * @param $groupID int
     *            接口分组ID
     * @param $apiHeader string
     *            请求头(JSON格式) [{"headerName":"","headerValue":""]
     * @param $apiRequestParam string
     *            请求参数(JSON格式) [{"paramName":"","paramKey":"","paramType":"","paramLimit":"","paramValue":"","paramNotNull":"","paramValueList":[]}]
     * @param $apiResultParam string
     *            返回参数(JSON格式) ["paramKey":"","paramName":"","paramNotNull":"","paramValueList":[]]
     * @param $starred int
     *            是否加星标 [0/1]=>[否/是]，默认为0
     * @param $apiNoteType int
     *            备注类型 [0/1]=>[富文本/markdown]，默认为0
     * @param $apiNoteRaw string
     *            备注(markdown)，默认为NULL
     * @param $apiNote string
     *            备注(富文本)，默认为NULL
     * @param $apiRequestParamType int
     *            请求参数类型 [0/1]=>[表单类型/源数据类型]，默认为0
     * @param $apiRequestRaw string
     *            请求参数源数据，默认为NULL
     * @param $cacheJson string
     *            接口缓存数据
     * @param $updateTime string
     *            更新时间
     * @param $updateUserID int 更新者用户ID
     * @param $mockRule array mock规则
     * @param $mockResult string mock结果
     * @param $mockConfig array mock配置
     * @param $success_status_code
     * @param $failure_status_code
     * @param $before_inject
     * @param $after_inject
     * @return bool|array
     */
    public function addApi(&$apiName, &$apiURI, &$apiProtocol, &$apiSuccessMock = '', &$apiFailureMock = '', &$apiRequestType, &$apiStatus, &$groupID, &$apiHeader, &$apiRequestParam, &$apiResultParam, &$starred, &$apiNoteType, &$apiNoteRaw, &$apiNote, &$projectID, &$apiRequestParamType, &$apiRequestRaw, &$cacheJson, &$updateTime, &$updateUserID, &$mockRule, &$mockResult, &$mockConfig, &$success_status_code, &$failure_status_code, &$before_inject, &$after_inject)
    {
        $db = getDatabase();
        try {
            // begin transaction
            // 开始事务
            $db->beginTransaction();
            // insert api base info
            // 插入api基本信息
            $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID,eo_api.mockRule,eo_api.mockResult,eo_api.mockConfig,apiSuccessStatusCode,apiFailureStatusCode,beforeInject,afterInject) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                $apiName,
                $apiURI,
                $apiProtocol,
                $apiSuccessMock,
                $apiFailureMock,
                $apiRequestType,
                $apiStatus,
                $groupID,
                $projectID,
                $starred,
                $apiNoteType,
                $apiNoteRaw,
                $apiNote,
                $apiRequestParamType,
                $apiRequestRaw,
                $updateTime,
                $updateUserID,
                json_encode($mockRule),
                $mockResult,
                $mockConfig,
                $success_status_code,
                $failure_status_code,
                $before_inject,
                $after_inject
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addApi error");

            if ($db->getAffectRow() > 0) {
                $apiID = $db->getLastInsertID();
                // insert api header info
                // 插入header信息
                foreach ($apiHeader as $param) {
                    $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                        $param['headerName'],
                        $param['headerValue'],
                        $apiID
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addHeader error");
                }
                // insert api request param info
                // 插入api请求值信息
                foreach ($apiRequestParam as $param) {
                    $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                        $apiID,
                        $param['paramName'],
                        $param['paramKey'],
                        $param['paramValue'],
                        $param['paramLimit'],
                        $param['paramNotNull'],
                        $param['paramType']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addRequestParam error");

                    $paramID = $db->getLastInsertID();

                    foreach ($param['paramValueList'] as $value) {
                        $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                            $paramID,
                            $value['value'],
                            $value['valueDescription']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addApi error");
                    };
                };
                // insert api result param info
                // 插入api返回值信息
                foreach ($apiResultParam as $param) {
                    $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                        $apiID,
                        $param['paramName'],
                        $param['paramKey'],
                        $param['paramNotNull']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addResultParam error");

                    $paramID = $db->getLastInsertID();

                    foreach ($param['paramValueList'] as $value) {
                        $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);', array(
                            $paramID,
                            $value['value'],
                            $value['valueDescription']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addApi error");
                    };
                };
                // insert api cache json which used for exportation
                // 插入api缓存数据用于导出
                $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred,eo_api_cache.updateUserID) VALUES (?,?,?,?,?,?);", array(
                    $projectID,
                    $groupID,
                    $apiID,
                    $cacheJson,
                    $starred,
                    $updateUserID
                ));

                if ($db->getAffectRow() < 1) {
                    throw new \PDOException("addApiCache error");
                }

                $db->commit();
                $result['apiID'] = $apiID;
                $result['groupID'] = $groupID;
                return $result;
            } else {
                throw new \PDOException("addApi error");
            }
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            $db->rollBack();
            return FALSE;
        }
    }

    /**
     * edit api
     * 修改api
     *
     * @param $apiID int
     *            接口ID
     * @param $apiName string
     *            接口名称
     * @param $apiURI string
     *            接口地址
     * @param $apiProtocol int
     *            请求协议 [0/1]=>[HTTP/HTTPS]
     * @param $apiSuccessMock string
     *            访问成功结果，默认为NULL
     * @param $apiFailureMock string
     *            访问失败结果，默认为NULL
     * @param $apiRequestType int
     *            请求类型 [0/1/2/3/4/5/6]=>[POST/GET/PUT/DELETE/HEAD/OPTIONS/PATCH]
     * @param $apiStatus int
     *            接口状态 [0/1/2]=>[启用/维护/弃用]
     * @param $groupID int
     *            接口分组ID
     * @param $apiHeader string
     *            请求头(JSON格式) [{"headerName":"","headerValue":""]
     * @param $apiRequestParam string
     *            请求参数(JSON格式) [{"paramName":"","paramKey":"","paramType":"","paramLimit":"","paramValue":"","paramNotNull":"","paramValueList":[]}]
     * @param $apiResultParam string
     *            返回参数(JSON格式) ["paramKey":"","paramName":"","paramNotNull":"","paramValueList":[]]
     * @param $starred int
     *            是否加星标 [0/1]=>[否/是]，默认为0
     * @param $apiNoteType int
     *            备注类型 [0/1]=>[富文本/markdown]，默认为0
     * @param $apiNoteRaw string
     *            备注(markdown)，默认为NULL
     * @param $apiNote string
     *            备注(富文本)，默认为NULL
     * @param $apiRequestParamType int
     *            请求参数类型 [0/1]=>[表单类型/源数据类型]，默认为0
     * @param $apiRequestRaw string
     *            请求参数源数据，默认为NULL
     * @param $cacheJson string
     *            接口缓存数据
     * @param $updateTime string
     *            更新时间
     * @param $updateUserID int 更新者用户ID
     * @param $mockRule array mock规则
     * @param $mockResult string mock结果
     * @param $mockConfig array mock配置
     * @param $success_status_code
     * @param $failure_status_code
     * @param $before_inject
     * @param $after_inject
     * @return bool
     */
    public function editApi(&$apiID, &$apiName, &$apiURI, &$apiProtocol, &$apiSuccessMock, &$apiFailureMock, &$apiRequestType, &$apiStatus, &$groupID, &$apiHeader, &$apiRequestParam, &$apiResultParam, &$starred, &$apiNoteType, &$apiNoteRaw, &$apiNote, &$apiRequestParamType, &$apiRequestRaw, &$cacheJson, &$updateTime, &$updateUserID, &$mockRule, &$mockResult, &$mockConfig, &$success_status_code, &$failure_status_code, &$before_inject, &$after_inject)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            $db->prepareExecute('UPDATE eo_api SET eo_api.apiName = ?,eo_api.apiURI = ?,eo_api.apiProtocol = ?,eo_api.apiSuccessMock = ?,eo_api.apiFailureMock = ?,eo_api.apiRequestType = ?,eo_api.apiStatus = ?,eo_api.starred = ?,eo_api.groupID = ?,eo_api.apiNoteType = ?,eo_api.apiNoteRaw = ?,eo_api.apiNote = ?,eo_api.apiUpdateTime = ?,eo_api.apiRequestParamType = ?,eo_api.apiRequestRaw = ?,eo_api.updateUserID = ?,eo_api.mockRule = ?,eo_api.mockResult = ?,eo_api.mockConfig = ?,eo_api.apiSuccessStatusCode = ?,eo_api.apiFailureStatusCode = ?,eo_api.beforeInject = ?,eo_api.afterInject = ? WHERE eo_api.apiID = ?;', array(
                $apiName,
                $apiURI,
                $apiProtocol,
                $apiSuccessMock,
                $apiFailureMock,
                $apiRequestType,
                $apiStatus,
                $starred,
                $groupID,
                $apiNoteType,
                $apiNoteRaw,
                $apiNote,
                $updateTime,
                $apiRequestParamType,
                $apiRequestRaw,
                $updateUserID,
                json_encode($mockRule),
                $mockResult,
                $mockConfig,
                $success_status_code,
                $failure_status_code,
                $before_inject,
                $after_inject,
                $apiID
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("edit Api error");

            $db->prepareExecute('DELETE FROM eo_api_header WHERE eo_api_header.apiID = ?;', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_api_request_param WHERE eo_api_request_param.apiID = ?;', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_api_result_param WHERE eo_api_result_param.apiID = ?;', array(
                $apiID
            ));
            // insert api header info
            // 插入header信息
            foreach ($apiHeader as $param) {
                $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                    $param['headerName'],
                    $param['headerValue'],
                    $apiID
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("addApi error");
            };
            // insert api request param info
            // 插入api请求值信息
            foreach ($apiRequestParam as $param) {
                $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                    $apiID,
                    $param['paramName'],
                    $param['paramKey'],
                    $param['paramValue'],
                    $param['paramLimit'],
                    $param['paramNotNull'],
                    $param['paramType']
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("addApi error");

                $paramID = $db->getLastInsertID();
                if (is_array($param['paramValueList'])) {
                    foreach ($param['paramValueList'] as $value) {
                        $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                            $paramID,
                            $value['value'],
                            $value['valueDescription']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addApi error");
                    };
                }
            };
            // insert api result param info
            // 插入api返回值信息
            foreach ($apiResultParam as $param) {
                $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                    $apiID,
                    $param['paramName'],
                    $param['paramKey'],
                    $param['paramNotNull']
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("addApi error");

                $paramID = $db->getLastInsertID();
                if (is_array($param['paramValueList'])) {
                    foreach ($param['paramValueList'] as $value) {
                        $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);', array(
                            $paramID,
                            $value['value'],
                            $value['valueDescription']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addApi error");
                    };
                }
            };
            // update api cache json
            // 更新api缓存
            $db->prepareExecute("UPDATE eo_api_cache SET eo_api_cache.apiJson = ?,eo_api_cache.groupID = ?,eo_api_cache.starred = ?,eo_api_cache.updateUserID = ? WHERE eo_api_cache.apiID = ?;", array(
                $cacheJson,
                $groupID,
                $starred,
                $updateUserID,
                $apiID
            ));

            if ($db->getAffectRow() < 1) {
                throw new \PDOException("updateApiCache error");
            }

            $db->commit();
            $result['apiID'] = $apiID;
            $result['groupID'] = $groupID;
            return $result;
        } catch (\PDOException $e) {
            $db->rollBack();
            return FALSE;
        }
    }

    /**
     * delete api and move the api into recycling station
     * 删除api,将其移入回收站
     *
     * @param $apiID int
     *            接口ID
     * @return bool
     */
    public function removeApi(&$apiID)
    {
        $db = getDatabase();
        $db->beginTransaction();

        $db->prepareExecute('UPDATE eo_api SET eo_api.removed = 1 ,eo_api.removeTime = ? WHERE eo_api.apiID = ?;', array(
            date("Y-m-d H:i:s", time()),
            $apiID
        ));

        if ($db->getAffectRow() > 0) {
            $db->commit();
            return TRUE;
        } else {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * recover api
     * 恢复api
     *
     * @param $apiID int
     *            接口ID
     * @return bool
     */
    public function recoverApi(&$apiID)
    {
        $db = getDatabase();
        $db->beginTransaction();

        $db->prepareExecute('UPDATE eo_api SET eo_api.removed = 0 WHERE eo_api.apiID = ?;', array(
            $apiID
        ));

        if ($db->getAffectRow() > 0) {
            $db->commit();
            return TRUE;
        } else {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * remove api
     * 彻底删除api
     *
     * @param $apiID int
     *            接口ID
     * @return bool
     */
    public function deleteApi(&$apiID)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();

            $db->prepareExecute('DELETE FROM eo_api WHERE eo_api.apiID = ? AND eo_api.removed = 1;', array(
                $apiID
            ));
            if ($db->getAffectRow() < 1)
                throw new \PDOException("deleteApi error");

            $db->prepareExecute('DELETE FROM eo_api_cache WHERE eo_api_cache.apiID = ?;', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_api_header WHERE eo_api_header.apiID = ?;', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_api_request_value WHERE eo_api_request_value.paramID IN (SELECT eo_api_request_param.paramID FROM eo_api_request_param WHERE eo_api_request_param.apiID = ?);', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_api_request_param WHERE eo_api_request_param.apiID = ?;', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_api_result_value WHERE eo_api_result_value.paramID IN (SELECT eo_api_result_param.paramID FROM eo_api_result_param WHERE eo_api_result_param.apiID = ?);', array(
                $apiID
            ));
            $db->prepareExecute('DELETE FROM eo_api_result_param WHERE eo_api_result_param.apiID = ?;', array(
                $apiID
            ));

            $db->commit();
            return TRUE;
        } catch (\PDOException $e) {
            $db->rollBack();
            return FALSE;
        }
    }

    /**
     * clean up recycling station
     * 清空回收站
     *
     * @param $projectID int
     *            项目ID
     * @return bool
     */
    public function cleanRecyclingStation(&$projectID)
    {
        $db = getDatabase();
        $db->prepareExecute('DELETE FROM eo_api WHERE eo_api.projectID= ? AND eo_api.removed = 1;', array(
            $projectID
        ));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * get api list by group and order by apiName
     * 获取api列表并按照名称排序
     *
     * @param $groupID int
     *            接口分组ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getApiListOrderByName(&$groupID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.updateUserID,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE (eo_api_group.groupID = ? OR eo_api_group.parentGroupID = ? OR eo_api.groupID IN (SELECT eo_api_group.groupID FROM eo_api_group WHERE eo_api_group.parentGroupID IN (SELECT eo_api_group.groupID FROM eo_api_group WHERE eo_api_group.parentGroupID = ?))) AND eo_api.removed = 0 ORDER BY eo_api.apiName $asc;", array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list by group and order by upodate time
     * 获取api列表并按照更新时间排序
     *
     * @param $groupID int
     *            接口分组ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getApiListOrderByTime(&$groupID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.updateUserID,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE (eo_api_group.groupID = ? OR eo_api_group.parentGroupID = ? OR eo_api.groupID IN (SELECT eo_api_group.groupID FROM eo_api_group WHERE eo_api_group.parentGroupID IN (SELECT eo_api_group.groupID FROM eo_api_group WHERE eo_api_group.parentGroupID = ?))) AND eo_api.removed = 0 ORDER BY eo_api.apiUpdateTime $asc;", array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list by group and order by starred
     * 获取api列表并按照星标排序
     *
     * @param $groupID int
     *            接口分组ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getApiListOrderByStarred(&$groupID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.updateUserID,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE (eo_api_group.groupID = ? OR eo_api_group.parentGroupID = ? OR eo_api.groupID IN (SELECT eo_api_group.groupID FROM eo_api_group WHERE eo_api_group.parentGroupID IN (SELECT eo_api_group.groupID FROM eo_api_group WHERE eo_api_group.parentGroupID = ?))) AND eo_api.removed = 0 ORDER BY eo_api.starred $asc;", array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list by group and order by starred
     * 获取api列表并按照星标排序
     *
     * @param $groupID int
     *            接口分组ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getApiListOrderByUri(&$groupID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.updateUserID,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE (eo_api_group.groupID = ? OR eo_api_group.parentGroupID = ? OR eo_api.groupID IN (SELECT eo_api_group.groupID FROM eo_api_group WHERE eo_api_group.parentGroupID IN (SELECT eo_api_group.groupID FROM eo_api_group WHERE eo_api_group.parentGroupID = ?))) AND eo_api.removed = 0 ORDER BY eo_api.apiURI $asc;", array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list by group and order by create time
     * 获取api列表并按照创建时间排序
     *
     * @param $groupID int
     *            接口分组ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getApiListOrderByCreateTime(&$groupID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.updateUserID,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE (eo_api_group.groupID = ? OR eo_api_group.parentGroupID = ? OR eo_api.groupID IN (SELECT eo_api_group.groupID FROM eo_api_group WHERE eo_api_group.parentGroupID IN (SELECT eo_api_group.groupID FROM eo_api_group WHERE eo_api_group.parentGroupID = ?))) AND eo_api.removed = 0 ORDER BY eo_api.apiID $asc;", array(
            $groupID,
            $groupID,
            $groupID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api detail
     * 获取api详情
     *
     * @param $apiID int
     *            接口ID
     * @return array|bool
     */
    public function getApi(&$apiID)
    {
        $db = getDatabase();
        $apiInfo = $db->prepareExecute('SELECT eo_api_cache.*,eo_api_group.parentGroupID FROM eo_api_cache LEFT JOIN eo_api_group ON eo_api_cache.groupID = eo_api_group.groupID WHERE eo_api_cache.apiID = ?;', array(
            $apiID
        ));

        $apiJson = json_decode($apiInfo['apiJson'], TRUE);
        $apiJson['baseInfo']['mockCode'] = "&projectID={$apiInfo['projectID']}&uri={$apiJson['baseInfo']['apiURI']}";
        $apiJson['baseInfo']['successMockURL'] = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?g=Web&c=Mock&o=simple' . $apiJson['baseInfo']['mockCode'];
        $apiJson['baseInfo']['failureMockURL'] = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?g=Web&c=Mock&o=simple&resultType=failure' . $apiJson['baseInfo']['mockCode'];
        $apiJson['baseInfo']['starred'] = $apiInfo['starred'];
        $apiJson['baseInfo']['groupID'] = $apiInfo['groupID'];
        $apiJson['baseInfo']['parentGroupID'] = $apiInfo['parentGroupID'];
        $apiJson['baseInfo']['projectID'] = $apiInfo['projectID'];
        $apiJson['baseInfo']['apiID'] = $apiInfo['apiID'];
        $topParentGroupID = $db->prepareExecute('SELECT eo_api_group.parentGroupID FROM eo_api_group WHERE eo_api_group.groupID = ? AND eo_api_group.isChild = 1;', array(
            $apiInfo['parentGroupID']
        ));
        $apiJson['baseInfo']['topParentGroupID'] = $topParentGroupID['parentGroupID'] ? $topParentGroupID['parentGroupID'] : $apiInfo['parentGroupID'];
        $apiJson['mockInfo']['mockURL'] = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?g=Web&c=Mock&o=mock' . $apiJson['baseInfo']['mockCode'];

        $test_history = $db->prepareExecuteAll('SELECT eo_api_test_history.testID,eo_api_test_history.requestInfo,eo_api_test_history.resultInfo,eo_api_test_history.testTime FROM eo_api_test_history WHERE eo_api_test_history.apiID = ? ORDER BY eo_api_test_history.testTime DESC LIMIT 10;', array(
            $apiID
        ));
        $apiJson['testHistory'] = $test_history;

        return $apiJson;
    }

    /**
     * get all api list by project and order by apiName
     * 获取所有api列表
     *
     * @param $projectID int
     *            项目ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getAllApiListOrderByName(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE eo_api_group.projectID = ? AND eo_api.removed = 0 ORDER BY eo_api.apiName $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get all api list by project and order by URI
     * 获取所有api列表
     *
     * @param $projectID int
     *            项目ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getAllApiListOrderByUri(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE eo_api_group.projectID = ? AND eo_api.removed = 0 ORDER BY eo_api.apiURI $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get all api list by project and order by create time
     * 获取所有api列表
     *
     * @param $projectID int
     *            项目ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getAllApiListOrderByCreateTime(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE eo_api_group.projectID = ? AND eo_api.removed = 0 ORDER BY eo_api.apiID $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get all api list by project and order by update time
     * 获取所有api列表
     *
     * @param $projectID int
     *            项目ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getAllApiListOrderByTime(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE eo_api_group.projectID = ? AND eo_api.removed = 0 ORDER BY eo_api.apiUpdateTime $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get all api list by project and order by starred
     * 获取所有api列表
     *
     * @param $projectID int
     *            项目ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getAllApiListOrderByStarred(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.starred,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE eo_api_group.projectID = ? AND eo_api.removed = 0 ORDER BY eo_api.starred $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list from recycling station and order by apiName
     * 获取回收站中所有api列表按名称排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByName(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.removeTime,eo_api.starred,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE eo_api.projectID = ? AND eo_api.removed = 1 ORDER BY eo_api.apiName $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list from recycling station and order by URI
     * 获取回收站中所有api列表按名称排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByUri(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.removeTime,eo_api.starred,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE eo_api.projectID = ? AND eo_api.removed = 1 ORDER BY eo_api.apiURI $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list from recycling station and order by create time
     * 获取回收站中所有api列表按创建时间排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByCreateTime(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.removeTime,eo_api.starred,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE eo_api.projectID = ? AND eo_api.removed = 1 ORDER BY eo_api.apiID $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list from recycling station and order by remove time
     * 获取回收站中所有api列表按移除时间排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByRemoveTime(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.removeTime,eo_api.starred,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE eo_api.projectID = ? AND eo_api.removed = 1 ORDER BY eo_api.removeTime $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * get api list from recycling station and order by starree
     * 获取回收站中所有api列表按星标排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc string
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByStarred(&$projectID, &$asc = 'ASC')
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime,eo_api.removeTime,eo_api.starred,eo_conn_project.partnerNickName,eo_user.userNickName,eo_user.userName FROM eo_api LEFT JOIN eo_conn_project ON eo_api.updateUserID = eo_conn_project.userID AND eo_api.projectID = eo_conn_project.projectID LEFT JOIN eo_user ON eo_api.updateUserID = eo_user.userID WHERE eo_api.projectID = ? AND eo_api.removed = 1 ORDER BY eo_api.starred $asc;", array(
            $projectID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * search api
     * 搜索api
     *
     * @param $tips string
     *            搜索关键字
     * @param $projectID int
     *            项目ID
     * @return bool|array
     */
    public function searchApi(&$tips, &$projectID)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT DISTINCT eo_api.apiID,eo_api.apiName,eo_api.apiURI,eo_api_group.groupID,eo_api_group.parentGroupID,eo_api_group.groupName,eo_api.apiStatus,eo_api.apiRequestType,eo_api.apiUpdateTime FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID WHERE eo_api_group.projectID = ? AND eo_api.removed = 0 AND (eo_api.apiName LIKE ? OR eo_api.apiURI LIKE ?)ORDER BY eo_api.apiName;', array(
            $projectID,
            '%' . $tips . '%',
            '%' . $tips . '%'
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * check api permission
     * 判断api与用户是否匹配
     *
     * @param $apiID int
     *            接口ID
     * @param $userID int
     *            用户ID
     * @return bool|int
     */
    public function checkApiPermission(&$apiID, &$userID)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_conn_project.projectID FROM eo_api LEFT JOIN eo_api_group ON eo_api.groupID = eo_api_group.groupID LEFT JOIN eo_conn_project ON eo_conn_project.projectID = eo_api.projectID WHERE eo_conn_project.userID = ? AND eo_api.apiID = ?;', array(
            $userID,
            $apiID
        ));

        if (empty($result))
            return FALSE;
        else
            return $result['projectID'];
    }

    /**
     * add star
     * 添加星标
     *
     * @param $apiID int
     *            接口ID
     * @return bool
     */
    public function addStar(&$apiID)
    {
        $db = getDatabase();
        $db->prepareExecute("UPDATE eo_api SET eo_api.starred = 1 WHERE eo_api.apiID = ?", array($apiID));
        $db->prepareExecute("UPDATE eo_api_cache SET eo_api_cache.starred = 1 WHERE eo_api_cache.apiID = ?;", array($apiID));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * remove star
     * 去除星标
     *
     * @param $apiID int
     *            接口ID
     * @return bool
     */
    public function removeStar(&$apiID)
    {
        $db = getDatabase();
        $db->prepareExecute("UPDATE eo_api SET eo_api.starred = 0 WHERE eo_api.apiID = ?", array($apiID));
        $db->prepareExecute("UPDATE eo_api_cache SET eo_api_cache.starred = 0 WHERE eo_api_cache.apiID = ?", array($apiID));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Delete apis in batches and move them into recycling station
     * 批量将api移入回收站
     *
     * @param $projectID int
     *            项目ID
     * @param $apiIDs string
     *            接口ID列表
     * @return bool
     */
    public function removeApis(&$projectID, &$apiIDs)
    {
        $db = getDatabase();
        $db->prepareExecuteAll("UPDATE eo_api SET eo_api.removed = 1, eo_api.removeTime = ? WHERE eo_api.apiID IN ($apiIDs) AND projectID = ?;", array(
            date("Y-m-d H:i:s", time()),
            $projectID
        ));
        if ($db->getAffectRow() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Remove apis in batches from recycling station
     * 批量删除api
     *
     * @param $projectID int
     *            项目ID
     * @param $apiIDs string
     *            接口ID列表
     * @return bool
     */
    public function deleteApis(&$projectID, &$apiIDs)
    {
        $db = getDatabase();
        $db->beginTransaction();
        $db->prepareExecuteAll("DELETE FROM eo_api WHERE apiID IN ($apiIDs) AND projectID = ?;", array(
            $projectID
        ));
        if ($db->getAffectRow() > 0) {
            $db->prepareExecute("DELETE FROM eo_api_cache WHERE eo_api_cache.apiID IN ($apiIDs);", array());
            $db->prepareExecute("DELETE FROM eo_api_header WHERE eo_api_header.apiID IN ($apiIDs);", array());
            $db->prepareExecute("DELETE FROM eo_api_request_value WHERE eo_api_request_value.paramID IN (SELECT eo_api_request_param.paramID FROM eo_api_request_param WHERE eo_api_request_param.apiID IN ($apiIDs));", array());
            $db->prepareExecute("DELETE FROM eo_api_request_param WHERE eo_api_request_param.apiID IN ($apiIDs);", array());
            $db->prepareExecute("DELETE FROM eo_api_result_value WHERE eo_api_result_value.paramID IN (SELECT eo_api_result_param.paramID FROM eo_api_result_param WHERE eo_api_result_param.apiID IN ($apiIDs));", array());
            $db->prepareExecute("DELETE FROM eo_api_result_param WHERE eo_api_result_param.apiID IN ($apiIDs);", array());
            $db->commit();
            return TRUE;
        } else {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * Recover api in batches
     * 批量恢复api
     *
     * @param $groupID int
     *            分组ID
     * @param $apiIDs string
     *            接口ID列表
     * @return bool
     */
    public function recoverApis(&$groupID, &$apiIDs)
    {
        $db = getDatabase();
        $db->prepareExecuteAll("UPDATE eo_api SET eo_api.removed = 0, eo_api.groupID = ? WHERE eo_api.apiID IN ($apiIDs);", array(
            $groupID
        ));
        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 获取接口名称
     *
     * @param string $apiIDs
     * @return boolean|mixed
     */
    public function getApiName(&$apiIDs)
    {
        $db = getDatabase();
        $result = $db->prepareExecute("SELECT GROUP_CONCAT(DISTINCT eo_api.apiName) AS apiName FROM eo_api WHERE eo_api.apiID IN ($apiIDs);", array());
        if (empty($result)) {
            return FALSE;
        } else {
            return $result['apiName'];
        }
    }

    /**
     * 添加历史记录
     * @param $project_id
     * @param $group_id
     * @param $api_id
     * @param $history_json
     * @param $update_desc
     * @param $update_user_id
     * @param $update_time
     * @return bool
     */
    public function addApiHistory(&$project_id, &$group_id, &$api_id, &$history_json, $update_desc, &$update_user_id, &$update_time)
    {
        $db = getDatabase();
        $db->beginTransaction();
        //直接插入缓存数据
        $db->prepareExecute("UPDATE eo_api_history SET eo_api_history.isNow = 0 WHERE eo_api_history.apiID = ?;", array($api_id));
        $db->prepareExecute("INSERT INTO eo_api_history (eo_api_history.projectID,eo_api_history.groupID,eo_api_history.apiID,eo_api_history.historyJson,eo_api_history.updateDesc,eo_api_history.updateUserID,eo_api_history.updateTime,eo_api_history.isNow) VALUES (?,?,?,?,?,?,?,1);", array(
            $project_id,
            $group_id,
            $api_id,
            $history_json,
            $update_desc,
            $update_user_id,
            $update_time
        ));
        if ($db->getAffectRow() > 0) {
            $db->commit();
            return TRUE;
        } else {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * 删除历史记录
     * @param $api_history_id
     * @param $api_id
     * @return bool
     */
    public function deleteApiHistory(&$api_history_id, &$api_id)
    {
        $db = getDatabase();
        $db->prepareExecute("DELETE FROM eo_api_history WHERE eo_api_history.historyID = ? AND eo_api_history.isNow = 0 AND eo_api_history.apiID = ?;", array($api_history_id, $api_id));

        if ($db->getAffectRow() > 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 获取接口修改历史列表
     * @param $api_id
     * @param $num_limit
     * @return bool
     */
    public function getApiHistoryList(&$api_id, $num_limit)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll('SELECT eo_api_history.historyID,eo_api_history.apiID,eo_api_history.groupID,eo_api_history.projectID,eo_api_history.updateDesc,eo_user.userNickName,eo_api_history.updateTime,eo_api_history.isNow FROM eo_api_history INNER JOIN eo_user ON eo_api_history.updateUserID = eo_user.userID WHERE eo_api_history.apiID = ? ORDER BY eo_api_history.updateTime DESC LIMIT ?;', array(
            $api_id,
            $num_limit
        ));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

    /**
     * 切换接口历史版本
     * @param $api_id
     * @param $api_history_id
     * @return bool
     */
    public function toggleApiHistory(&$api_id, &$api_history_id)
    {
        $db = getDatabase();
        try {
            //开始事务
            $db->beginTransaction();
            $result = $db->prepareExecute('SELECT eo_api_history.projectID FROM eo_api_history WHERE eo_api_history.apiID = ? AND eo_api_history.historyID = ?;', array(
                $api_id,
                $api_history_id
            ));
            if (empty($result)) {
                $db->rollback();
                return FALSE;
            }

            $db->prepareExecute("UPDATE eo_api_history SET eo_api_history.isNow = 0 WHERE eo_api_history.apiID = ?;", array($api_id));
            $db->prepareExecute("UPDATE eo_api_history SET eo_api_history.isNow = 1 WHERE eo_api_history.historyID = ?;", array($api_history_id));

            //更新接口的缓存数据
            $api_info = $db->prepareExecute('SELECT eo_api_history.historyJson,eo_api_history.groupID,eo_api_history.updateUserID FROM eo_api_history WHERE eo_api_history.historyID = ?;', array($api_history_id));

            $group_id = $api_info['groupID'];
            $update_user_id = $api_info['updateUserID'];

            $db->prepareExecute('UPDATE eo_api_cache SET eo_api_cache.groupID = ?, eo_api_cache.apiJson = ?,eo_api_cache.updateUserID = ? WHERE eo_api_cache.apiID = ?;', array(
                $group_id,
                $api_info['historyJson'],
                $update_user_id,
                $api_id
            ));

            $api_info = json_decode($api_info['historyJson'], TRUE);

            //删除旧的接口参数信息
            $db->prepareExecute('DELETE FROM eo_api_header WHERE eo_api_header.apiID = ?;', array($api_id));
            $db->prepareExecute('DELETE FROM eo_api_request_param WHERE eo_api_request_param.apiID = ?;', array($api_id));
            $db->prepareExecute('DELETE FROM eo_api_result_param WHERE eo_api_result_param.apiID = ?;', array($api_id));

            $db->prepareExecute('UPDATE eo_api SET eo_api.apiName = ?,eo_api.apiURI = ?,eo_api.apiProtocol = ?,eo_api.apiSuccessMock = ?,eo_api.apiFailureMock = ?,eo_api.apiRequestType = ?,eo_api.apiStatus = ?,eo_api.starred = ?,eo_api.groupID = ?,eo_api.apiNoteType = ?,eo_api.apiNoteRaw = ?,eo_api.apiNote = ?,eo_api.apiUpdateTime = ?,eo_api.apiRequestParamType = ?,eo_api.apiRequestRaw = ?,eo_api.updateUserID = ? WHERE eo_api.apiID = ?;', array(
                $api_info['baseInfo']['apiName'],
                $api_info['baseInfo']['apiURI'],
                $api_info['baseInfo']['apiProtocol'],
                $api_info['baseInfo']['apiSuccessMock'],
                $api_info['baseInfo']['apiFailureMock'],
                $api_info['baseInfo']['apiRequestType'],
                $api_info['baseInfo']['apiStatus'],
                $api_info['baseInfo']['starred'],
                $group_id,
                $api_info['baseInfo']['apiNoteType'],
                $api_info['baseInfo']['apiNoteRaw'],
                $api_info['baseInfo']['apiNote'],
                $api_info['baseInfo']['apiUpdateTime'],
                $api_info['baseInfo']['apiRequestParamType'],
                $api_info['baseInfo']['apiRequestRaw'],
                $update_user_id,
                $api_id
            ));

            //插入header信息
            foreach ($api_info['headerInfo'] as $param) {
                $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                    $param['headerName'],
                    $param['headerValue'],
                    $api_id
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("toggleApiHistory error");
            };

            //插入api请求值信息
            foreach ($api_info['requestInfo'] as $param) {
                $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                    $api_id,
                    $param['paramName'],
                    $param['paramKey'],
                    $param['paramValue'],
                    $param['paramLimit'],
                    $param['paramNotNull'],
                    $param['paramType']
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("toggleApiHistory error");

                $param_id = $db->getLastInsertID();

                foreach ($param['paramValueList'] as $value) {
                    $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                        $param_id,
                        $value['value'],
                        $value['valueDescription']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("toggleApiHistory error");
                };
            };

            //插入api返回值信息
            foreach ($api_info['resultInfo'] as $param) {
                $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                    $api_id,
                    $param['paramName'],
                    $param['paramKey'],
                    $param['paramNotNull']
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("toggleApiHistory error");

                $param_id = $db->getLastInsertID();

                foreach ($param['paramValueList'] as $value) {
                    $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);', array(
                        $param_id,
                        $value['value'],
                        $value['valueDescription']
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("toggleApiHistory error");
                };
            }

            $db->commit();
            return TRUE;
        } catch (\PDOException $e) {
            $db->rollBack();
            return FALSE;
        }
    }

    /**
     * 获取接口mock数据
     * @param $api_id
     * @return bool
     */
    public function getApiMockData(&$api_id)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_api.apiName,eo_api.projectID,eo_api.apiID,eo_api.apiURI,eo_api.mockRule,eo_api.mockResult,eo_api.mockConfig FROM eo_api WHERE eo_api.apiID = ?;', array(
            $api_id
        ));
        if (empty($result)) {
            return FALSE;
        } else {
            $result['mockRule'] = json_decode($result['mockRule']);
            $result['mockConfig'] = json_decode($result['mockConfig']);
            $mockCode = "&projectID={$result['projectID']}&uri={$result['apiURI']}";
            $result['mockURL'] = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?g=Web&c=Mock&o=mock' . $mockCode;

            return $result;
        }
    }

    /**
     * 编辑接口mock数据
     * @param $api_id
     * @param $mock_rule
     * @param $mock_result
     * @param $mock_config
     * @return bool
     */
    public function editApiMockData(&$api_id, &$mock_rule, &$mock_result, &$mock_config)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_api SET eo_api.mockRule = ?,eo_api.mockResult = ?,eo_api.mockConfig = ? WHERE eo_api.apiID = ?;', array(
            $mock_rule,
            $mock_result,
            $mock_config,
            $api_id
        ));
        if ($db->getAffectRow() < 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 批量修改接口分组
     * @param $api_ids
     * @param $project_id
     * @param $group_id
     * @return bool
     */
    public function changeApiGroup(&$api_ids, &$project_id, &$group_id)
    {
        $db = getDatabase();
        $db->beginTransaction();
        $db->prepareExecuteAll("UPDATE eo_api_cache SET eo_api_cache.groupID = ? WHERE eo_api_cache.apiID IN ($api_ids) AND eo_api_cache.projectID = ?;", array(
            $group_id,
            $project_id
        ));
        if ($db->getAffectRow() < 1) {
            $db->rollback();
            return FALSE;
        }
        $db->prepareExecuteAll("UPDATE eo_api SET eo_api.groupID = ? WHERE eo_api.apiID IN ($api_ids) AND eo_api.projectID = ?;", array(
            $group_id,
            $project_id
        ));
        if ($db->getAffectRow() < 1) {
            $db->rollback();
            return FALSE;
        }
        $db->commit();
        return TRUE;
    }

    /**
     * 批量获取接口数据
     * @param $project_id
     * @param $api_ids
     * @return array|bool
     */
    public function getApiData(&$project_id, &$api_ids)
    {
        $db = getDatabase();
        $result = $db->prepareExecuteAll("SELECT eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred FROM eo_api_cache WHERE eo_api_cache.projectID = ? AND eo_api_cache.apiID in ($api_ids);", array(
            $project_id
        ));
        $api_list = array();
        $i = 0;
        foreach ($result as $api) {
            $api_list[$i] = json_decode($api['apiJson'], TRUE);
            $api_list[$i]['baseInfo']['starred'] = $api['starred'];
            ++$i;
        }
        if ($api_list)
            return $api_list;
        else
            return FALSE;
    }

    /**
     * 批量导入接口
     * @param $group_id
     * @param $project_id
     * @param $data
     * @param $user_id
     * @return bool
     */
    public function importApi(&$group_id, &$project_id, &$data, &$user_id)
    {
        $db = getDatabase();
        try {
            $db->beginTransaction();
            if (is_array($data)) {
                foreach ($data as $api) {
                    // 插入api基本信息
                    $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                        $api['baseInfo']['apiName'],
                        $api['baseInfo']['apiURI'],
                        $api['baseInfo']['apiProtocol'],
                        $api['baseInfo']['apiSuccessMock'],
                        $api['baseInfo']['apiFailureMock'],
                        $api['baseInfo']['apiRequestType'],
                        $api['baseInfo']['apiStatus'],
                        $group_id,
                        $project_id,
                        $api['baseInfo']['starred'],
                        $api['baseInfo']['apiNoteType'],
                        $api['baseInfo']['apiNoteRaw'],
                        $api['baseInfo']['apiNote'],
                        $api['baseInfo']['apiRequestParamType'],
                        $api['baseInfo']['apiRequestRaw'],
                        $api['baseInfo']['apiUpdateTime'],
                        $user_id
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addApi error");

                    $api_id = $db->getLastInsertID();

                    // 插入header信息
                    foreach ($api['headerInfo'] as $header) {
                        $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                            $header['headerName'],
                            $header['headerValue'],
                            $api_id
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addHeader error");
                    }

                    // 插入api请求值信息
                    foreach ($api['requestInfo'] as $request) {
                        $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                            $api_id,
                            $request['paramName'],
                            $request['paramKey'],
                            $request['paramValue'],
                            $request['paramLimit'],
                            $request['paramNotNull'],
                            $request['paramType']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addRequestParam error");

                        $param_id = $db->getLastInsertID();

                        foreach ($request['paramValueList'] as $value) {
                            $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                $param_id,
                                $value['value'],
                                $value['valueDescription']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");
                        };
                    };

                    // 插入api返回值信息
                    foreach ($api['resultInfo'] as $result) {
                        $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                            $api_id,
                            $result['paramName'],
                            $result['paramKey'],
                            $result['paramNotNull']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addResultParam error");

                        $param_id = $db->getLastInsertID();

                        foreach ($result['paramValueList'] as $value) {
                            $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                $param_id,
                                $value['value'],
                                $value['valueDescription']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");
                        };
                    };

                    // 插入api缓存数据用于导出
                    $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                        $project_id,
                        $group_id,
                        $api_id,
                        json_encode($api),
                        $api['baseInfo']['starred']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("addApiCache error");
                    }
                }
            }
            $db->commit();
            return TRUE;
        } catch (\Exception $e) {
            $db->rollback();
            return FALSE;
        }
    }
}

?>