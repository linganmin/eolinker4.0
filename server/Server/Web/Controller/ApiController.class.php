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
class ApiController
{
    // return an json object
    // 返回json类型
    private $returnJson = array('type' => 'api');

    /**
     * Checkout login status
     * 验证登录状态
     */
    public function __construct()
    {
        // identity authentication
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * Add api
     * 添加api
     */
    public function addApi()
    {
        $groupID = securelyInput('groupID');
        //检查操作权限
        $module = new GroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $apiName = securelyInput('apiName');
        $apiURI = securelyInput('apiURI');
        $apiProtocol = securelyInput('apiProtocol');
        $apiRequestType = securelyInput('apiRequestType');
        $apiSuccessMock = quickInput('apiSuccessMock');
        $apiFailureMock = quickInput('apiFailureMock');
        $apiStatus = securelyInput('apiStatus');
        $starred = securelyInput('starred');
        $apiNoteType = securelyInput('apiNoteType');
        $apiNoteRaw = securelyInput('apiNoteRaw');
        $apiNote = securelyInput('apiNote');
        $apiRequestParamType = securelyInput('apiRequestParamType');
        $apiRequestRaw = securelyInput('apiRequestRaw');
        $apiHeader = json_decode($_POST['apiHeader'], TRUE);
        $apiRequestParam = json_decode($_POST['apiRequestParam'], TRUE);
        $apiResultParam = json_decode($_POST['apiResultParam'], TRUE);
        $mockRule = json_decode(quickInput('mockRule'), TRUE);
        $mockResult = securelyInput('mockResult');
        $mockConfig = quickInput('mockConfig');
        $failure_status_code = securelyInput('apiFailureStatusCode', '200');
        $success_status_code = securelyInput('apiSuccessStatusCode', '200');
        $before_inject = quickInput("beforeInject");
        $after_inject = quickInput("afterInject");
        $service = new ApiModule;
        $result = $service->addApi($apiName, $apiURI, $apiProtocol, $apiSuccessMock, $apiFailureMock, $apiRequestType, $apiStatus, $groupID, $apiHeader, $apiRequestParam, $apiResultParam, $starred, $apiNoteType, $apiNoteRaw, $apiNote, $apiRequestParamType, $apiRequestRaw, $mockRule, $mockResult, $mockConfig, $success_status_code, $failure_status_code, $before_inject, $after_inject);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['apiID'] = $result['apiID'];
            $this->returnJson['groupID'] = $result['groupID'];
        } else {
            $this->returnJson['statusCode'] = '160000';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Edit api
     * 编辑api
     */
    public function editApi()
    {
        $apiID = securelyInput('apiID');
        $module = new ApiModule();
        //检查操作权限
        $userType = $module->getUserType($apiID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $apiName = securelyInput('apiName');
        $apiURI = securelyInput('apiURI');
        $apiProtocol = securelyInput('apiProtocol');
        $apiRequestType = securelyInput('apiRequestType');
        $apiSuccessMock = quickInput('apiSuccessMock');
        $apiFailureMock = quickInput('apiFailureMock');
        $apiStatus = securelyInput('apiStatus');
        $starred = securelyInput('starred');
        $apiNoteType = securelyInput('apiNoteType');
        $apiNoteRaw = securelyInput('apiNoteRaw');
        $apiNote = securelyInput('apiNote');
        $apiRequestParamType = securelyInput('apiRequestParamType');
        $apiRequestRaw = securelyInput('apiRequestRaw');
        $groupID = securelyInput('groupID');
        $apiHeader = json_decode($_POST['apiHeader'], TRUE);
        $apiRequestParam = json_decode($_POST['apiRequestParam'], TRUE);
        $apiResultParam = json_decode($_POST['apiResultParam'], TRUE);
        $update_desc = securelyInput('updateDesc');
        $mockRule = json_decode(quickInput('mockRule'), TRUE);
        $mockResult = securelyInput('mockResult');
        $mockConfig = quickInput('mockConfig');
        $failure_status_code = securelyInput('apiFailureStatusCode', '200');
        $success_status_code = securelyInput('apiSuccessStatusCode', '200');
        $before_inject = quickInput("beforeInject");
        $after_inject = quickInput("afterInject");
        $service = new ApiModule;
        $result = $service->editApi($apiID, $apiName, $apiURI, $apiProtocol, $apiSuccessMock, $apiFailureMock, $apiRequestType, $apiStatus, $groupID, $apiHeader, $apiRequestParam, $apiResultParam, $starred, $apiNoteType, $apiNoteRaw, $apiNote, $apiRequestParamType, $apiRequestRaw, $update_desc, $mockRule, $mockResult, $mockConfig, $success_status_code, $failure_status_code, $before_inject, $after_inject);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
            $this->returnJson['apiID'] = $result['apiID'];
            $this->returnJson['groupID'] = $result['groupID'];
        } else {
            $this->returnJson['statusCode'] = '160000';
        }
        exitOutput($this->returnJson);
    }

//	/**
//	 * 删除api,将其移入回收站
//	 */
//	public function removeApi() {
//		$apiID = securelyInput('apiID');
//		//判断apiID格式是否合法
//		if (preg_match('/^[0-9]{1,11}$/', $apiID)) {
//			//apiID格式合法
//			$service = new ApiModule;
//			$result = $service -> removeApi($apiID);
//			//判断删除api是否成功
//			if ($result) {
//				//删除api成功
//				$this -> returnJson['statusCode'] = '000000';
//			} else {
//				//删除api失败
//				$this -> returnJson['statusCode'] = '160008';
//			}
//		} else {
//			//apiID格式不合法
//			$this -> returnJson['statusCode'] = '160001';
//		}
//		exitOutput($this -> returnJson);
//	}

    /**
     * Delete apis in batches and move them into recycling station
     * 批量删除api,将其移入回收站
     */
    public function removeApi()
    {
        //接口ID
        $ids = quickInput('apiID');
        $projectID = securelyInput('projectID');
        //检查操作权限
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);//去掉数组中不是数字的ID
        //判断接口ID是否为空
        if (empty($arr)) {
            $this->returnJson['statusCode'] = '160001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '160002';
        } else {
            $api_ids = implode(',', $arr);
            $api_module = new ApiModule;
            $result = $api_module->removeApis($projectID, $api_ids);
            //验证结果是否成功
            if ($result) {
                //删除api成功
                $this->returnJson['statusCode'] = '000000';
            } else {
                //删除api失败
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Recover api
     * 恢复api
     */
    public function recoverApi()
    {
        //接口ID
        $ids = securelyInput('apiID');
        $groupID = securelyInput('groupID');
        //检查操作权限
        $module = new GroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);//去掉数组中不是数字的ID
        //判断接口ID是否为空
        if (empty($arr)) {
            $this->returnJson['statusCode'] = '160001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $groupID)) {
            $this->returnJson['statusCode'] = '160002';
        } else {
            $api_ids = implode(',', $arr);
            $api_module = new ApiModule;
            $result = $api_module->recoverApis($groupID, $api_ids);
            //验证结果是否成功
            if ($result) {
                //恢复api成功
                $this->returnJson['statusCode'] = '000000';
            } else {
                //恢复api失败
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Remove apis in batches from recycling station
     * 批量彻底删除api
     */
    public function deleteApi()
    {
        //接口ID
        $ids = securelyInput('apiID');
        $projectID = securelyInput('projectID');
        //检查操作权限
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);//去掉数组中不是数字的ID
        //判断接口ID是否为空
        if (empty($arr)) {
            $this->returnJson['statusCode'] = '160001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '160002';
        } else {
            $api_ids = implode(',', $arr);
            $api_module = new ApiModule;
            $result = $api_module->deleteApis($projectID, $api_ids);
            //验证结果是否成功
            if ($result) {
                //删除api成功
                $this->returnJson['statusCode'] = '000000';
            } else {
                //删除api失败
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Clean up the recycling station
     * 清空回收站
     */
    public function cleanRecyclingStation()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '160002';
        } else {
            $service = new ApiModule;
            $result = $service->cleanRecyclingStation($projectID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160011';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get api list from recycling station
     * 获取回收站api列表
     */
    public function getRecyclingStationApiList()
    {
        $projectID = securelyInput('projectID');
        $orderBy = securelyInput('orderBy', 0);
        $asc = securelyInput('asc', 0);
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $service = new ApiModule;

            //判断排序方式
            switch ($orderBy) {
                //名称排序
                case 0 :
                    {
                        $result = $service->getRecyclingStationApiListOrderByName($projectID, $asc);
                        break;
                    }
                //时间排序
                case 1 :
                    {
                        $result = $service->getRecyclingStationApiListOrderByRemoveTime($projectID, $asc);
                        break;

                    }
                //星标排序
                case 2 :
                    {
                        $result = $service->getRecyclingStationApiListOrderByStarred($projectID, $asc);
                        break;
                    }
                //创建时间排序
                case 3 :
                    {
                        $result = $service->getRecyclingStationApiListOrderByCreateTime($projectID, $asc);
                    }
            }

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '160007';
            }
        } else {
            $this->returnJson['statusCode'] = '160002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get api list by group
     * 获取api列表
     */
    public function getApiList()
    {
        $groupID = securelyInput('groupID');
        $orderBy = securelyInput('orderBy', 0);
        $asc = securelyInput('asc', 0);
        if (preg_match('/^[0-9]{1,11}$/', $groupID)) {
            $service = new ApiModule;

            //判断排序方式
            switch ($orderBy) {
                //名称排序
                case 0 :
                    {
                        $result = $service->getApiListOrderByName($groupID, $asc);
                        break;
                    }
                //时间排序
                case 1 :
                    {
                        $result = $service->getApiListOrderByTime($groupID, $asc);
                        break;
                    }
                //星标排序
                case 2 :
                    {
                        $asc = 1;
                        $result = $service->getApiListOrderByStarred($groupID, $asc);
                        break;
                    }
                //创建时间排序
                case 3 :
                    {
                        $result = $service->getApiListOrderByCreateTime($groupID, $asc);
                        break;
                    }
            }

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        } else {
            $this->returnJson['statusCode'] = '160002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get api detail
     * 获取api详情
     */
    public function getApi()
    {
        $apiID = securelyInput('apiID');
        if (preg_match('/^[0-9]{1,11}$/', $apiID)) {
            $service = new ApiModule;
            $result = $service->getApi($apiID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiInfo'] = $result;
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        } else {
            $this->returnJson['statusCode'] = '160001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * Get all api list by project
     * 获取所有分组的api
     */
    public function getAllApiList()
    {
        $projectID = securelyInput('projectID');
        $orderBy = securelyInput('orderBy', 0);
        $asc = securelyInput('asc', 0);
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $service = new ApiModule;

            switch ($orderBy) {
                //名称排序
                case 0 :
                    {
                        $result = $service->getAllApiListOrderByName($projectID, $asc);
                        break;
                    }
                //时间排序
                case 1 :
                    {
                        $result = $service->getAllApiListOrderByTime($projectID, $asc);
                        break;
                    }
                //星标排序
                case 2 :
                    {
                        $asc = 1;
                        $result = $service->getAllApiListOrderByStarred($projectID, $asc);
                        break;
                    }
                //创建时间
                case 3 :
                    {
                        $result = $service->getAllApiListOrderByCreateTime($projectID, $asc);
                    }
            }

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        } else {
            $this->returnJson['statusCode'] = '160003';
        }
        exitOutput($this->returnJson);
    }

    /**
     * search api
     * 搜索api
     */
    public function searchApi()
    {
        $tipsLen = mb_strlen(quickInput('tips'), 'utf8');
        $tips = securelyInput('tips');
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '160003';
        } else if ($tipsLen > 255 || $tipsLen == 0) {
            $this->returnJson['statusCode'] = '160004';
        } else {
            $service = new ApiModule;
            $result = $service->searchApi($tips, $projectID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['apiList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * add star to an api
     * 添加星标
     */
    public function addStar()
    {
        $apiID = securelyInput('apiID');
        if (preg_match('/^[0-9]{1,11}$/', $apiID)) {
            $service = new ApiModule;
            $result = $service->addStar($apiID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        } else {
            $this->returnJson['statusCode'] = '160001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * remove star from an api
     * 添加星标
     */
    public function removeStar()
    {
        $apiID = securelyInput('apiID');
        if (preg_match('/^[0-9]{1,11}$/', $apiID)) {
            $service = new ApiModule;
            $result = $service->removeStar($apiID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        } else {
            $this->returnJson['statusCode'] = '160001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取接口修改历史
     */
    public function getApiHistoryList()
    {
        //接口ID
        $api_id = securelyInput('apiID');
        //判断接口ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $api_id)) {
            $this->returnJson['statusCode'] = '160001';
        } else {
            $api_module = new ApiModule();
            $result = $api_module->getApiHistoryList($api_id);
            //验证结果是否成功
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除历史记录
     */
    public function deleteApiHistory()
    {
        //接口历史记录ID
        $api_history_id = securelyInput('apiHistoryID');
        //接口ID
        $api_id = securelyInput('apiID');
        //检查操作权限
        $api_module = new ApiModule();
        $userType = $api_module->getUserType($api_id);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        //判断接口ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $api_id)) {
            $this->returnJson['statusCode'] = '160001';
        } //判断接口历史记录ID是否合法
        elseif (!preg_match('/^[0-9]{1,11}$/', $api_history_id)) {
            $this->returnJson['statusCode'] = '160004';
        } else {
            $result = $api_module->deleteApiHistory($api_id, $api_history_id);
            //验证结果是否成功
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 切换接口历史版本
     */
    public function toggleApiHistory()
    {
        //接口历史记录ID
        $api_history_id = securelyInput('apiHistoryID');
        //接口ID
        $api_id = securelyInput('apiID');
        //检查操作权限
        $api_module = new ApiModule();
        $userType = $api_module->getUserType($api_id);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        //验证接口ID是否为空
        if (!preg_match('/^[0-9]{1,11}$/', $api_id)) {
            $this->returnJson['statusCode'] = '160001';
        } //验证接口历史记录ID是否合法
        elseif (!preg_match('/^[0-9]{1,11}$/', $api_history_id)) {
            $this->returnJson['statusCode'] = '160004';
        } else {
            $result = $api_module->toggleApiHistory($api_id, $api_history_id);
            //验证结果是否成功
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取接口mock数据
     */
    public function getApiMockData()
    {
        $api_id = securelyInput('apiID');
        //验证接口ID是否为空
        if (!preg_match('/^[0-9]{1,11}$/', $api_id)) {
            $this->returnJson['statusCode'] = '160001';
        } else {
            $module = new ApiModule();
            $result = $module->getApiMockData($api_id);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson = array_merge($this->returnJson, $result);
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 编辑接口mock数据
     */
    public function editApiMockData()
    {
        $api_id = securelyInput('apiID');
        $module = new ApiModule();
        //检查操作权限
        $userType = $module->getUserType($api_id);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $mock_rule = quickInput('mockRule', '');
        $mock_result = securelyInput('mockResult', '');
        $mock_config = quickInput('mockConfig');
        //验证接口ID是否为空
        if (!preg_match('/^[0-9]{1,11}$/', $api_id)) {
            $this->returnJson['statusCode'] = '160001';
        } else {
            $result = $module->editApiMockData($api_id, $mock_rule, $mock_result, $mock_config);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 批量修改接口分组
     */
    public function changeApiGroup()
    {
        //接口ID
        $ids = securelyInput('apiID');
        $group_id = securelyInput('groupID');
        //检查操作权限
        $module = new GroupModule();
        $userType = $module->getUserType($group_id);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);//去掉数组中不是数字的ID
        //判断接口ID是否为空
        if (empty($arr)) {
            $this->returnJson['statusCode'] = '160001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            $this->returnJson['statusCode'] = '160002';
        } else {
            $api_ids = implode(',', $arr);
            $api_module = new ApiModule;
            $result = $api_module->changeApiGroup($api_ids, $group_id);
            //验证结果是否成功
            if ($result) {
                //删除api成功
                $this->returnJson['statusCode'] = '000000';
            } else {
                //删除api失败
                $this->returnJson['statusCode'] = '160000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 批量导出接口
     */
    public function exportApi()
    {
        $project_id = securelyInput('projectID');
        // 接口ID
        $ids = quickInput('apiID');
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr); // 去掉数组中不是数字的ID

        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '160003';
        }// 判断ID数组是否为空
        elseif (empty($arr)) {
            // apiID格式不合法
            $this->returnJson['statusCode'] = '160001';
        } else {
            $project_module = new ProjectModule();
            $user_type = $project_module->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $api_ids = implode(',', $arr);
                $api_module = new ApiModule();
                $result = $api_module->exportApi($project_id, $api_ids);
                // 判断结果是否成功
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['fileName'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '160000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 批量导入接口
     */
    public function importApi()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        $group_id = securelyInput('groupID');
        // 判断分组ID是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '160005';
        } //判断导入数据是否为空
        elseif (empty($data)) {
            $this->returnJson['statusCode'] = '160006';
            exitOutput($this->returnJson);
        } else {
            $group_module = new GroupModule();
            $user_type = $group_module->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $server = new ApiModule();
                $result = $server->importApi($group_id, $data);
                //验证结果
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    $this->returnJson['statusCode'] = '160000';
                }
            }
        }
        exitOutput($this->returnJson);
    }
}

?>