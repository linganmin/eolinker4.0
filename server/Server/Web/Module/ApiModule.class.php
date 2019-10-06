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
class ApiModule
{

    public function __construct()
    {
        @session_start();
    }

    /**
     * get userType by apiID
     * 根据apiID获取项目用户类型
     *
     * @param $apiID int
     *            接口ID
     * @return bool
     */
    public function getUserType(&$apiID)
    {
        $apiDao = new ApiDao();
        $projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID']);
        if (empty($projectID)) {
            return -1;
        }
        $dao = new AuthorizationDao();
        $result = $dao->getProjectUserType($_SESSION['userID'], $projectID);
        if ($result === FALSE) {
            return -1;
        } else {
            return $result;
        }
    }

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
     *            访问成功结果，默认为NULL(default null)
     * @param $apiFailureMock string
     *            访问失败结果，默认为NULL(default null)
     * @param $apiRequestType int
     *            请求类型 [0/1/2/3/4/5/6]=>[POST/GET/PUT/DELETE/HEAD/OPTIONS/PATCH]
     * @param $apiStatus int
     *            接口状态 [0/1/2]=>[启用(using)/维护(maintain)/弃用(abandon)]
     * @param $groupID int
     *            接口分组ID
     * @param $apiHeader string
     *            请求头(JSON格式) [{"headerName":"","headerValue":""]
     * @param $apiRequestParam string
     *            请求参数(JSON格式) [{"paramName":"","paramKey":"","paramType":"","paramLimit":"","paramValue":"","paramNotNull":"","paramValueList":[]}]
     * @param $apiResultParam string
     *            返回参数(JSON格式) ["paramKey":"","paramName":"","paramNotNull":"","paramValueList":[]]
     * @param $starred int
     *            是否加星标 [0/1]=>[否(false)/是(true)]，默认为0
     * @param $apiNoteType int
     *            备注类型 [0/1]=>[富文本(richText)/markdown]，默认为0(default 0)
     * @param $apiNoteRaw string
     *            备注(markdown)，默认为NULL(default null)
     * @param $apiNote string
     *            备注(富文本)，默认为NULL(default null)
     * @param $apiRequestParamType int
     *            请求参数类型 [0/1]=>[表单类型(form-data)/源数据类型(raw)]，默认为0(default 0)
     * @param $apiRequestRaw string
     *            请求参数源数据，默认为NULL(default null)
     * @param $mockRule array mock规则
     * @param $mockResult string mock结果
     * @param $mockConfig array mock配置
     * @param $success_status_code
     * @param $failure_status_code
     * @param $before_inject
     * @param $after_inject
     * @return int|bool
     */
    public function addApi(&$apiName, &$apiURI, &$apiProtocol, &$apiSuccessMock, &$apiFailureMock, &$apiRequestType, &$apiStatus, &$groupID, &$apiHeader, &$apiRequestParam, &$apiResultParam, &$starred, &$apiNoteType, &$apiNoteRaw, &$apiNote, &$apiRequestParamType, &$apiRequestRaw, &$mockRule, &$mockResult, &$mockConfig, &$success_status_code, &$failure_status_code, &$before_inject, &$after_inject)
    {
        // if the request params were null, then assign an empty string to them
        // 判断部分请求参数是否为空，如果为空值则赋值成为空字符串
        if (empty($apiSuccessMock)) {
            $apiSuccessMock = '';
        }
        if (empty($apiFailureMock)) {
            $apiFailureMock = '';
        }
        if (empty($apiRequestRaw)) {
            $apiRequestRaw = '';
        }
        if (empty($apiNote) || $apiNote == '&lt;p&gt;&lt;br&gt;&lt;/p&gt;') {
            $apiNote = '';
        }
        if (empty($apiNoteRaw)) {
            $apiNoteRaw = '';
        }

        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        $projectDao = new ProjectDao();
        if ($projectID = $groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            // make up a cache json data about the api
            // 生成缓存数据
            $cacheJson['baseInfo']['apiName'] = $apiName;
            $cacheJson['baseInfo']['apiURI'] = $apiURI;
            $cacheJson['baseInfo']['apiProtocol'] = intval($apiProtocol);
            $cacheJson['baseInfo']['apiSuccessMock'] = $apiSuccessMock;
            $cacheJson['baseInfo']['apiFailureMock'] = $apiFailureMock;
            $cacheJson['baseInfo']['apiRequestType'] = intval($apiRequestType);
            $cacheJson['baseInfo']['apiStatus'] = intval($apiStatus);
            $cacheJson['baseInfo']['starred'] = intval($starred);
            $cacheJson['baseInfo']['apiNoteType'] = intval($apiNoteType);
            $cacheJson['baseInfo']['apiNoteRaw'] = $apiNoteRaw;
            $cacheJson['baseInfo']['apiNote'] = $apiNote;
            $cacheJson['baseInfo']['apiRequestParamType'] = intval($apiRequestParamType);
            $cacheJson['baseInfo']['apiRequestRaw'] = $apiRequestRaw;
            $updateTime = date("Y-m-d H:i:s", time());
            $cacheJson['baseInfo']['apiUpdateTime'] = $updateTime;
            $cacheJson['baseInfo']['apiFailureStatusCode'] = $failure_status_code;
            $cacheJson['baseInfo']['apiSuccessStatusCode'] = $success_status_code;
            $cacheJson['baseInfo']['beforeInject'] = $before_inject;
            $cacheJson['baseInfo']['afterInject'] = $after_inject;
            $cacheJson['headerInfo'] = $apiHeader;
            $cacheJson['mockInfo']['mockRule'] = $mockRule;
            $cacheJson['mockInfo']['mockResult'] = $mockResult;
            $cacheJson['mockInfo']['mockConfig'] = json_decode($mockConfig, TRUE);

            // sort the request params
            // 将数组中的数字字符串转换为数字并且进行排序
            // if (is_array($apiRequestParam))
            // {
            // $sortKey = array();
            // foreach ($apiRequestParam as &$param)
            // {
            // $sortKey[] = $param['paramKey'];
            // $param['paramNotNull'] = intval($param['paramNotNull']);
            // $param['paramType'] = intval($param['paramType']);
            // }
            // array_multisort($sortKey, SORT_ASC, $apiRequestParam);
            // }
            $cacheJson['requestInfo'] = $apiRequestParam;
            // sort the result params
            // if (is_array($apiResultParam))
            // {
            // $sortKey = array();
            // foreach ($apiResultParam as &$param)
            // {
            // $sortKey[] = $param['paramKey'];
            // $param['paramNotNull'] = intval($param['paramNotNull']);
            // }
            // array_multisort($sortKey, SORT_ASC, $apiResultParam);
            // }
            $cacheJson['resultInfo'] = $apiResultParam;
            $cacheJson = json_encode($cacheJson);

            $result = $apiDao->addApi($apiName, $apiURI, $apiProtocol, $apiSuccessMock, $apiFailureMock, $apiRequestType, $apiStatus, $groupID, $apiHeader, $apiRequestParam, $apiResultParam, $starred, $apiNoteType, $apiNoteRaw, $apiNote, $projectID, $apiRequestParamType, $apiRequestRaw, $cacheJson, $updateTime, $_SESSION['userID'], $mockRule, $mockResult, $mockConfig, $success_status_code, $failure_status_code, $before_inject, $after_inject);

            if ($result) {
                //添加版本历史
                $apiDao->addApiHistory($projectID, $groupID, $result['apiID'], $cacheJson, '创建API', $_SESSION['userID'], $updateTime);
                // 将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $result['apiID'], ProjectLogDao::$OP_TYPE_ADD, "新增接口:'{$apiName}'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return $result;
            }
        } else
            return FALSE;
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
     * @param $apiNoteType string
     *            备注类型 [0/1]=>[富文本/markdown]，默认为0
     * @param $apiNoteRaw string
     *            备注(markdown)，默认为NULL
     * @param $apiNote string
     *            备注(富文本)，默认为NULL
     * @param $apiRequestParamType int
     *            请求参数类型 [0/1]=>[表单类型/源数据类型]，默认为0
     * @param $apiRequestRaw string
     *            请求参数源数据，默认为NULL
     * @param $update_desc string 更新描述
     * @param $mockRule array mock规则
     * @param $mockResult string mock结果
     * @param $mockConfig string mock配置
     * @param $success_status_code
     * @param $failure_status_code
     * @param $before_inject
     * @param $after_inject
     * @return bool
     */
    public function editApi(&$apiID, &$apiName, &$apiURI, &$apiProtocol, &$apiSuccessMock, &$apiFailureMock, &$apiRequestType, &$apiStatus, &$groupID, &$apiHeader, &$apiRequestParam, &$apiResultParam, &$starred, &$apiNoteType, &$apiNoteRaw, &$apiNote, &$apiRequestParamType, &$apiRequestRaw, &$update_desc = NULL, &$mockRule, &$mockResult, &$mockConfig, &$success_status_code, &$failure_status_code, &$before_inject, &$after_inject)
    {
        // if the request params were null, then assign an empty string to them
        // 判断部分请求参数是否为空，如果为空值则赋值成为空字符串
        if (empty($apiSuccessMock)) {
            $apiSuccessMock = '';
        }
        if (empty($apiFailureMock)) {
            $apiFailureMock = '';
        }
        if (empty($apiRequestRaw)) {
            $apiRequestRaw = '';
        }
        if (empty($apiNote) || $apiNote == '&lt;p&gt;&lt;br&gt;&lt;/p&gt;') {
            $apiNote = '';
        }
        if (empty($apiNoteRaw)) {
            $apiNoteRaw = '';
        }

        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        $projectDao = new ProjectDao();
        if ($apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            if ($projectID = $groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
                $projectDao->updateProjectUpdateTime($projectID);
                // make up a cache json data about the api
                // 生成缓存数据
                $cacheJson['baseInfo']['apiName'] = $apiName;
                $cacheJson['baseInfo']['apiURI'] = $apiURI;
                $cacheJson['baseInfo']['apiProtocol'] = intval($apiProtocol);
                $cacheJson['baseInfo']['apiSuccessMock'] = $apiSuccessMock;
                $cacheJson['baseInfo']['apiFailureMock'] = $apiFailureMock;
                $cacheJson['baseInfo']['apiRequestType'] = intval($apiRequestType);
                $cacheJson['baseInfo']['apiStatus'] = intval($apiStatus);
                $cacheJson['baseInfo']['starred'] = intval($starred);
                $cacheJson['baseInfo']['apiNoteType'] = intval($apiNoteType);
                $cacheJson['baseInfo']['apiNoteRaw'] = $apiNoteRaw;
                $cacheJson['baseInfo']['apiNote'] = $apiNote;
                $cacheJson['baseInfo']['apiRequestParamType'] = intval($apiRequestParamType);
                $cacheJson['baseInfo']['apiRequestRaw'] = $apiRequestRaw;
                $updateTime = date("Y-m-d H:i:s", time());
                $cacheJson['baseInfo']['apiUpdateTime'] = $updateTime;
                $cacheJson['baseInfo']['apiFailureStatusCode'] = $failure_status_code;
                $cacheJson['baseInfo']['apiSuccessStatusCode'] = $success_status_code;
                $cacheJson['baseInfo']['beforeInject'] = $before_inject;
                $cacheJson['baseInfo']['afterInject'] = $after_inject;
                $cacheJson['headerInfo'] = $apiHeader;
                $cacheJson['mockInfo']['mockRule'] = $mockRule;
                $cacheJson['mockInfo']['mockResult'] = $mockResult;
                $cacheJson['mockInfo']['mockConfig'] = json_decode($mockConfig, TRUE);
                // 将数组中的数字字符串转换为数字并且进行排序
                // if (is_array($apiRequestParam))
                // {
                // $sortKey = array();
                // foreach ($apiRequestParam as &$param)
                // {
                // $sortKey[] = $param['paramKey'];
                // $param['paramNotNull'] = intval($param['paramNotNull']);
                // $param['paramType'] = intval($param['paramType']);
                // }
                // array_multisort($sortKey, SORT_ASC, $apiRequestParam);
                // }
                $cacheJson['requestInfo'] = $apiRequestParam;
                // if (is_array($apiResultParam))
                // {
                // $sortKey = array();
                // foreach ($apiResultParam as &$param)
                // {
                // $sortKey[] = $param['paramKey'];
                // $param['paramNotNull'] = intval($param['paramNotNull']);
                // }
                // array_multisort($sortKey, SORT_ASC, $apiResultParam);
                // }
                $cacheJson['resultInfo'] = $apiResultParam;
                $cacheJson = json_encode($cacheJson);

                $result = $apiDao->editApi($apiID, $apiName, $apiURI, $apiProtocol, $apiSuccessMock, $apiFailureMock, $apiRequestType, $apiStatus, $groupID, $apiHeader, $apiRequestParam, $apiResultParam, $starred, $apiNoteType, $apiNoteRaw, $apiNote, $apiRequestParamType, $apiRequestRaw, $cacheJson, $updateTime, $_SESSION['userID'], $mockRule, $mockResult, $mockConfig, $success_status_code, $failure_status_code, $before_inject, $after_inject);

                if ($result) {
                    $desc = $update_desc ? $update_desc : '[快速保存]修改接口';
                    //添加版本历史
                    $apiDao->addApiHistory($projectID, $groupID, $apiID, $cacheJson, $desc, $_SESSION['userID'], $updateTime);
                    $update_desc = $update_desc ? "修改接口:'{$apiName}',更新描述：" . $update_desc : "修改接口:'{$apiName}'";
                    // 将操作写入日志
                    $log_dao = new ProjectLogDao();
                    $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiID, ProjectLogDao::$OP_TYPE_UPDATE, $update_desc, date("Y-m-d H:i:s", time()));
                    return $result;
                } else {
                    return FALSE;
                }
            } else
                return FALSE;
        } else
            return FALSE;
    }

    /**
     * Delete apis in batches and move them into recycling station
     * 删除api,将其移入回收站
     *
     * @param $apiID int
     *            接口ID
     * @return bool
     */
    public function removeApi(&$apiID)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->removeApi($apiID);
            if ($result) {
                $apiName = $apiDao->getApiName($apiID);
                // 将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiID, ProjectLogDao::$OP_TYPE_DELETE, "将接口:'$apiName'移入接口回收站", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
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
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->recoverApi($apiID);
            if ($result) {
                $apiName = $apiDao->getApiName($apiID);
                // 将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiID, ProjectLogDao::$OP_TYPE_OTHERS, "将接口:'$apiName'从回收站恢复", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * delete api
     * 彻底删除api
     *
     * @param $apiID int
     *            接口ID
     * @return bool
     */
    public function deleteApi(&$apiID)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->deleteApi($apiID);
            if ($result) {
                $apiName = $apiDao->getApiName($apiID);
                // 将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiID, ProjectLogDao::$OP_TYPE_DELETE, "彻底删除接口:'$apiName'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
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
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->cleanRecyclingStation($projectID);
            if ($result) {
                // 将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $projectID, ProjectLogDao::$OP_TYPE_DELETE, "清空接口回收站", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
    }

    /**
     * get api list by group and order by apiName
     * 获取api列表并按照名称排序
     *
     * @param $groupID int
     *            接口分组ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getApiListOrderByName(&$groupID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        if ($groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getApiListOrderByName($groupID, $asc);
        } else
            return FALSE;
    }

    /**
     * get api list by group and order by update time
     * 获取api列表并按照时间排序
     *
     * @param $groupID int
     *            接口分组ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getApiListOrderByTime(&$groupID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        if ($groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getApiListOrderByTime($groupID, $asc);
        } else
            return FALSE;
    }

    /**
     * get api list by group and order by starred
     * 获取api列表并按照星标排序
     *
     * @param $groupID int
     *            接口分组ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getApiListOrderByStarred(&$groupID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        if ($groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getApiListOrderByStarred($groupID, $asc);
        } else
            return FALSE;
    }

    /**
     * get api list by group and order by URI
     * 获取api列表并按Uri排序
     *
     * @param $groupID int
     *            接口分组ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getApiListOrderByUri(&$groupID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        if ($groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getApiListOrderByUri($groupID, $asc);
        } else
            return FALSE;
    }

    /**
     * get api list by group and order by create time
     * 获取api列表按创建时间排序
     *
     * @param $groupID int
     *            分组ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getApiListOrderByCreateTime(&$groupID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        if ($groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getApiListOrderByCreateTime($groupID, $asc);
        } else
            return FALSE;
    }

    /**
     * get recycling station api list by project and order by apiName
     * 获取api列表并按照名称排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByName(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getRecyclingStationApiListOrderByName($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get recycling station api list by project and order by remove time
     * 获取api列表并按照移除时间排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByRemoveTime(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getRecyclingStationApiListOrderByRemoveTime($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get recycling station api list by project and order by starred
     * 获取api列表并按照星标排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByStarred(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getRecyclingStationApiListOrderByStarred($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get recycling station api list by project and order by URI
     * 获取api列表并按照Uri排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByUri(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getRecyclingStationApiListOrderByUri($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get recycling station api list by project and order by create time
     * 获取api列表并按照创建时间排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getRecyclingStationApiListOrderByCreateTime(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getRecyclingStationApiListOrderByCreateTime($projectID, $asc);
        } else
            return FALSE;
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
        $apiDao = new ApiDao();
        if ($apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $result = $apiDao->getApi($apiID);
            // 将mock数据转码以适应前端直接显示html代码
            $result['baseInfo']['apiSuccessMock'] = htmlspecialchars($result['baseInfo']['apiSuccessMock']);
            $result['baseInfo']['apiFailureMock'] = htmlspecialchars($result['baseInfo']['apiFailureMock']);

            foreach ($result['testHistory'] as &$history) {
                $history['requestInfo'] = json_decode($history['requestInfo'], TRUE);
                $history['resultInfo'] = json_decode($history['resultInfo'], TRUE);
            }

            return $result;
        } else
            return FALSE;
    }

    /**
     * get all api list by project and order by apiName
     * 获取所有分组的api并按照名称排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getAllApiListOrderByName(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getAllApiListOrderByName($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get all api list by project and order by apiName
     * 获取所有分组的api并按照名称排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getAllApiListOrderByTime(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getAllApiListOrderByTime($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get all api list by project and order by URI
     * 获取所有分组的api并按照URI排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getAllApiListOrderByUri(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getAllApiListOrderByUri($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get all api list by project and order by create time
     * 获取所有分组的api并按照创建时间排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getAllApiListOrderByCreateTime(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getAllApiListOrderByCreateTime($projectID, $asc);
        } else
            return FALSE;
    }

    /**
     * get all api list by project and order by starred
     * 获取所有分组的api并按照星标排序
     *
     * @param $projectID int
     *            项目ID
     * @param $asc int
     *            排序 [0/1]=>[升序/降序]
     * @return bool|array
     */
    public function getAllApiListOrderByStarred(&$projectID, &$asc = 0)
    {
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $asc = $asc == 0 ? 'ASC' : 'DESC';
            return $apiDao->getAllApiListOrderByStarred($projectID, $asc);
        } else
            return FALSE;
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
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            return $apiDao->searchApi($tips, $projectID);
        } else
            return FALSE;
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
        $apiDao = new ApiDao();
        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao = new ProjectDao();
            $projectDao->updateProjectUpdateTime($projectID);
            return $apiDao->addStar($apiID);
        } else
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
        $apiDao = new ApiDao();
        if ($projectID = $apiDao->checkApiPermission($apiID, $_SESSION['userID'])) {
            $projectDao = new ProjectDao();
            $projectDao->updateProjectUpdateTime($projectID);
            return $apiDao->removeStar($apiID);
        } else
            return FALSE;
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
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $apiName = $apiDao->getApiName($apiIDs);
            $result = $apiDao->deleteApis($projectID, $apiIDs);
            if ($result) {
                $projectDao->updateProjectUpdateTime($projectID);
                // 将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiIDs, ProjectLogDao::$OP_TYPE_DELETE, "彻底删除接口:'$apiName'", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
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
        $apiDao = new ApiDao();
        $projectDao = new ProjectDao();
        if ($projectDao->checkProjectPermission($projectID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->removeApis($projectID, $apiIDs);
            if ($result) {
                $apiName = $apiDao->getApiName($apiIDs);
                // 将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiIDs, ProjectLogDao::$OP_TYPE_DELETE, "将接口:'$apiName'移入接口回收站", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else
            return FALSE;
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
        $apiDao = new ApiDao();
        $groupDao = new GroupDao();
        $projectDao = new ProjectDao();
        if ($projectID = $groupDao->checkGroupPermission($groupID, $_SESSION['userID'])) {
            $projectDao->updateProjectUpdateTime($projectID);
            $result = $apiDao->recoverApis($groupID, $apiIDs);
            if ($result) {
                $apiName = $apiDao->getApiName($apiIDs);
                // 将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($projectID, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $apiIDs, ProjectLogDao::$OP_TYPE_OTHERS, "将接口:'$apiName'从回收站恢复", date("Y-m-d H:i:s", time()));
                return $result;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 获取接口历史列表
     * @param $api_id
     * @return array|bool
     */
    public function getApiHistoryList(&$api_id)
    {
        $dao = new ApiDao();
        if ($dao->checkApiPermission($api_id, $_SESSION['userID'])) {
            // 可以获取10条历史记录
            $api_history_list = $dao->getApiHistoryList($api_id, 10);

            $result = array();
            $result['apiHistoryList'] = $api_history_list ? $api_history_list : array();
            $result['apiName'] = $dao->getApiName($api_id);
            return $result;
        } else {
            return FALSE;
        }
    }

    /**
     * 删除历史记录
     * @param $api_id
     * @param $api_history_id
     * @return bool
     */
    public function deleteApiHistory(&$api_id, &$api_history_id)
    {
        $user_id = $_SESSION['userID'];
        $api_dao = new ApiDao();
        if ($project_id = $api_dao->checkApiPermission($api_id, $user_id)) {
            if ($api_dao->deleteApiHistory($api_history_id, $api_id)) {
                $api_name = $api_dao->getApiName($api_id);

                // 将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_API, $api_id, ProjectLogDao::$OP_TYPE_DELETE, "删除了'$api_name'的历史版本", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 切换接口历史版本
     * @param $api_id
     * @param $api_history_id
     * @return bool
     */
    public function toggleApiHistory(&$api_id, &$api_history_id)
    {
        $user_id = $_SESSION['userID'];
        $api_dao = new ApiDao();
        if ($project_id = $api_dao->checkApiPermission($api_id, $user_id)) {
            if ($api_dao->toggleApiHistory($api_id, $api_history_id)) {
                $api_name = $api_dao->getApiName($api_id);
                // 将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_API, $api_id, ProjectLogDao::$OP_TYPE_UPDATE, "切换了'$api_name'的版本", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        } else {
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
        $user_id = $_SESSION['userID'];
        $api_dao = new ApiDao();
        if ($api_dao->checkApiPermission($api_id, $user_id)) {
            return $api_dao->getApiMockData($api_id);
        } else {
            return FALSE;
        }
    }

    /**
     * 修改接口mock数据
     * @param $api_id
     * @param $mock_rule
     * @param $mock_result
     * @param $mock_config
     * @return bool
     */
    public function editApiMockData(&$api_id, &$mock_rule, &$mock_result, &$mock_config)
    {
        $user_id = $_SESSION['userID'];
        $api_dao = new ApiDao();
        if ($project_id = $api_dao->checkApiPermission($api_id, $user_id)) {
            $result = $api_dao->editApiMockData($api_id, $mock_rule, $mock_result, $mock_config);
            if ($result) {
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($project_id, $user_id, ProjectLogDao::$OP_TARGET_API, $api_id, ProjectLogDao::$OP_TYPE_UPDATE, '更新mock数据', date('Y-m-d H:i:s', time()));
                return $result;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 批量修改接口分组
     * @param $api_ids
     * @param $group_id
     * @return bool
     */
    public function changeApiGroup(&$api_ids, &$group_id)
    {
        $group_dao = new GroupDao();
        if (!($project_id = $group_dao->checkGroupPermission($group_id, $_SESSION['userID']))) {
            return FALSE;
        }
        $dao = new ApiDao();
        return $dao->changeApiGroup($api_ids, $project_id, $group_id);
    }

    /**
     * 批量导出接口数据
     * @param $project_id
     * @param $api_ids
     * @return bool|string
     */
    public function exportApi(&$project_id, &$api_ids)
    {
        $dao = new ApiDao();
        $project_dao = new ProjectDao();
        if (!$project_dao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $result = $dao->getApiData($project_id, $api_ids);
        if ($result) {
            $fileName = 'eoLinker_api_export_' . $_SESSION['userName'] . '_' . time() . '.export';
            if (file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $fileName, json_encode($result))) {
                $api_name = $dao->getApiName($api_ids);
                //将操作写入日志
                $log_dao = new ProjectLogDao();
                $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $project_id, ProjectLogDao::$OP_TYPE_OTHERS, "批量导出接口：$api_name", date("Y-m-d H:i:s", time()));
                return $fileName;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 批量导入接口
     * @param $group_id
     * @param $data
     * @return bool
     */
    public function importApi(&$group_id, &$data)
    {
        $group_dao = new GroupDao();
        if (!($project_id = $group_dao->checkGroupPermission($group_id, $_SESSION['userID']))) {
            return FALSE;
        }
        $dao = new ApiDao();
        $result = $dao->importApi($group_id, $project_id, $data, $_SESSION['userID']);
        if ($result) {
            $names = array();
            foreach ($data as $api) {
                $names[] = $api['baseInfo']['apiName'];
            }
            $api_name = implode(",", $names);
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_API, $group_id, ProjectLogDao::$OP_TYPE_OTHERS, "批量导入接口：$api_name", date("Y-m-d H:i:s", time()));
            return $result;
        } else {
            return FALSE;
        }
    }
}

?>