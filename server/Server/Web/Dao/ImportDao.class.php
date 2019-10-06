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

class ImportDao
{

    /**
     * 导入eolinker
     * @param $data array 从eolinker导出的json格式数据
     * @param $user_id int 用户ID
     * @return bool
     */
    public function importEoapi(&$data, &$user_id)
    {
        $db = getDatabase();
        try {
            // 开始事务
            $db->beginTransaction();

            // 插入项目
            $db->prepareExecute('INSERT INTO eo_project(eo_project.projectName,eo_project.projectType,eo_project.projectVersion,eo_project.projectUpdateTime) VALUES (?,?,?,?);', array(
                $data['projectInfo']['projectName'],
                $data['projectInfo']['projectType'],
                $data['projectInfo']['projectVersion'],
                date('Y-m-d H:i:s', time())
            ));
            if ($db->getAffectRow() < 1)
                throw new \PDOException("addProject error");

            // 获取projectID
            $project_id = $db->getLastInsertID();

            // 生成项目与用户的联系
            $db->prepareExecute('INSERT INTO eo_conn_project(eo_conn_project.projectID,eo_conn_project.userID,eo_conn_project.userType) VALUES (?,?,0);', array(
                $project_id,
                $user_id
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addConnProject error");

            if (!empty($data['apiGroupList'])) {
                // 插入接口分组信息
                foreach ($data['apiGroupList'] as $api_group) {
                    $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID) VALUES (?,?);', array(
                        $api_group['groupName'],
                        $project_id
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addGroup error");

                    $group_id = $db->getLastInsertID();
                    if ($api_group['apiList']) {
                        foreach ($api_group['apiList'] as $api) {
                            // 插入api基本信息
                            $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID,eo_api.mockResult,eo_api.mockRule,eo_api.mockConfig,eo_api.apiFailureStatusCode,eo_api.apiSuccessStatusCode,eo_api.beforeInject,eo_api.afterInject) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
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
                                $user_id,
                                $api['mockInfo']['result'] ? $api['mockInfo']['result'] : '',
                                $api['mockInfo']['rule'] ? json_encode($api['mockInfo']['rule']) : '',
                                json_encode($api['mockInfo']['mockConfig']),
                                $api['baseInfo']['apiFailureStatusCode'] ? $api['baseInfo']['apiFailureStatusCode'] : '200',
                                $api['baseInfo']['apiSuccessStatusCode'] ? $api['baseInfo']['apiSuccessStatusCode'] : '200',
                                $api['baseInfo']['beforeInject'],
                                $api['baseInfo']['afterInject']
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
                    // 二级分组代码
                    if ($api_group['apiGroupChildList']) {
                        $group_parent_id = $group_id;
                        foreach ($api_group['apiGroupChildList'] as $api_group_child) {
                            $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID, eo_api_group.isChild) VALUES (?,?,?,?);', array(
                                $api_group_child['groupName'],
                                $project_id,
                                $group_parent_id,
                                1
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildGroup error");

                            $group_id = $db->getLastInsertID();

                            // 如果当前分组没有接口，则跳过到下一分组
                            if (empty($api_group_child['apiList']))
                                continue;

                            foreach ($api_group_child['apiList'] as $api) {
                                // 插入api基本信息
                                $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID,eo_api.mockResult,eo_api.mockRule,eo_api.mockConfig,eo_api.apiFailureStatusCode,eo_api.apiSuccessStatusCode,eo_api.beforeInject,eo_api.afterInject) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
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
                                    $user_id,
                                    $api['mockInfo']['result'] ? $api['mockInfo']['result'] : '',
                                    $api['mockInfo']['rule'] ? json_encode($api['mockInfo']['rule']) : '',
                                    json_encode($api['mockInfo']['mockConfig']),
                                    $api['baseInfo']['apiFailureStatusCode'] ? $api['baseInfo']['apiFailureStatusCode'] : '200',
                                    $api['baseInfo']['apiSuccessStatusCode'] ? $api['baseInfo']['apiSuccessStatusCode'] : '200',
                                    $api['baseInfo']['beforeInject'],
                                    $api['baseInfo']['afterInject']
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addChildApi error");

                                $api_id = $db->getLastInsertID();

                                // 插入header信息
                                foreach ($api['headerInfo'] as $header) {
                                    $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                        $header['headerName'],
                                        $header['headerValue'],
                                        $api_id
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildHeader error");
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
                                        throw new \PDOException("addChildRequestParam error");

                                    $param_id = $db->getLastInsertID();
                                    if ($request['paramValueList']) {
                                        foreach ($request['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildApi error");
                                        };
                                    }
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
                                        throw new \PDOException("addChildResultParam error");

                                    $param_id = $db->getLastInsertID();
                                    if ($result['paramValueList']) {
                                        foreach ($result['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildParamValue error");
                                        };
                                    }
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
                                    throw new \PDOException("addChildApiCache error");
                                }
                            }
                            if ($api_group_child['apiGroupChildList']) {
                                $parent_id = $group_id;
                                foreach ($api_group_child['apiGroupChildList'] as $group_child) {
                                    $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID, eo_api_group.isChild) VALUES (?,?,?,?);', array(
                                        $group_child['groupName'],
                                        $project_id,
                                        $parent_id,
                                        2
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildGroup error");

                                    $group_id = $db->getLastInsertID();

                                    // 如果当前分组没有接口，则跳过到下一分组
                                    if (empty($group_child['apiList']))
                                        continue;

                                    foreach ($group_child['apiList'] as $api) {
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
                                            throw new \PDOException("addChildApi error");

                                        $api_id = $db->getLastInsertID();

                                        // 插入header信息
                                        foreach ($api['headerInfo'] as $header) {
                                            $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                                $header['headerName'],
                                                $header['headerValue'],
                                                $api_id
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildHeader error");
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
                                                throw new \PDOException("addChildRequestParam error");

                                            $param_id = $db->getLastInsertID();
                                            if ($request['paramValueList']) {
                                                foreach ($request['paramValueList'] as $value) {
                                                    $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                                        $param_id,
                                                        $value['value'],
                                                        $value['valueDescription']
                                                    ));

                                                    if ($db->getAffectRow() < 1)
                                                        throw new \PDOException("addChildApi error");
                                                };
                                            }
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
                                                throw new \PDOException("addChildResultParam error");

                                            $param_id = $db->getLastInsertID();
                                            if ($result['paramValueList']) {
                                                foreach ($result['paramValueList'] as $value) {
                                                    $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                        $param_id,
                                                        $value['value'],
                                                        $value['valueDescription']
                                                    ));

                                                    if ($db->getAffectRow() < 1)
                                                        throw new \PDOException("addChildParamValue error");
                                                };
                                            }
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
                                            throw new \PDOException("addChildApiCache error");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            //插入状态码
            if (!empty($data['statusCodeGroupList'])) {
                // 导入状态码
                foreach ($data['statusCodeGroupList'] as $status_codeGroup) {
                    // 插入分组
                    $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName) VALUES (?,?);', array(
                        $project_id,
                        $status_codeGroup['groupName']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("addChildstatusCodeGroup error");
                    }

                    $group_id = $db->getLastInsertID();

                    if (empty($status_codeGroup['statusCodeList']))
                        continue;

                    // 插入状态码
                    foreach ($status_codeGroup['statusCodeList'] as $status_code) {
                        $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array(
                            $group_id,
                            $status_code['code'],
                            $status_code['codeDescription']
                        ));

                        if ($db->getAffectRow() < 1) {
                            throw new \PDOException("add statusCode error");
                        }
                    }
                    if ($status_codeGroup['statusCodeGroupChildList']) {
                        $group_id_parent = $group_id;
                        foreach ($status_codeGroup['statusCodeGroupChildList'] as $status_codeGroup_child) {
                            // 插入分组
                            $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $status_codeGroup_child['groupName'],
                                $group_id_parent,
                                1
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("addChildStatusCodeGroup error");
                            }

                            $group_id = $db->getLastInsertID();
                            if (empty($status_codeGroup_child['statusCodeList']))
                                continue;

                            // 插入状态码
                            foreach ($status_codeGroup_child['statusCodeList'] as $status_code) {
                                $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array(
                                    $group_id,
                                    $status_code['code'],
                                    $status_code['codeDescription']
                                ));

                                if ($db->getAffectRow() < 1) {
                                    throw new \PDOException("addChildStatusCode error");
                                }
                            }

                            if ($status_codeGroup_child['statusCodeGroupChildList']) {
                                $parent_id = $group_id;
                                foreach ($status_codeGroup_child['statusCodeGroupChildList'] as $second_status_code_group_child) {
                                    // 插入分组
                                    $db->prepareExecute('INSERT INTO eo_project_status_code_group (eo_project_status_code_group.projectID,eo_project_status_code_group.groupName,eo_project_status_code_group.parentGroupID,eo_project_status_code_group.isChild) VALUES (?,?,?,?);', array(
                                        $project_id,
                                        $second_status_code_group_child['groupName'],
                                        $parent_id,
                                        2
                                    ));
                                    if ($db->getAffectRow() < 1) {
                                        throw new \PDOException("addChildStatusCodeGroup error");
                                    }

                                    $group_id = $db->getLastInsertID();
                                    if (empty($second_status_code_group_child['statusCodeList']))
                                        continue;

                                    // 插入状态码
                                    foreach ($second_status_code_group_child['statusCodeList'] as $status_code) {
                                        $db->prepareExecute('INSERT INTO eo_project_status_code (eo_project_status_code.groupID,eo_project_status_code.code,eo_project_status_code.codeDescription) VALUES (?,?,?);', array(
                                            $group_id,
                                            $status_code['code'],
                                            $status_code['codeDescription']
                                        ));

                                        if ($db->getAffectRow() < 1) {
                                            throw new \PDOException("addChildStatusCode error");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            //插入文档信息
            if (!empty($data['pageGroupList'])) {
                //导入状态码
                foreach ($data['pageGroupList'] as $pageGroup) {
                    //插入分组
                    $db->prepareExecute('INSERT INTO eo_project_document_group(eo_project_document_group.projectID,eo_project_document_group.groupName) VALUES (?,?);', array(
                        $project_id,
                        $pageGroup['groupName']
                    ));

                    if ($db->getAffectRow() < 1) {
                        throw new \PDOException("add pageGroup error");
                    }

                    $group_id = $db->getLastInsertID();
                    //插入状态码
                    foreach ($pageGroup['pageList'] as $page) {
                        $db->prepareExecute('INSERT INTO eo_project_document(eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
                            $group_id,
                            $project_id,
                            $page['contentType'],
                            $page['contentRaw'],
                            $page['content'],
                            $page['title'],
                            $page['updateTime'],
                            $user_id,
                        ));

                        if ($db->getAffectRow() < 1) {
                            throw new \PDOException("add page error");
                        }
                    }
                    if ($pageGroup['pageGroupChildList']) {
                        $group_id_parent = $group_id;
                        foreach ($pageGroup['pageGroupChildList'] as $page_group_child) {
                            //插入分组
                            $db->prepareExecute('INSERT INTO eo_project_document_group(eo_project_document_group.projectID,eo_project_document_group.groupName,eo_project_document_group.parentGroupID,eo_project_document_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $page_group_child['groupName'],
                                $group_id_parent,
                                1,
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("add pageGroup error");
                            }

                            $group_id = $db->getLastInsertID();
                            //插入状态码
                            foreach ($page_group_child['pageList'] as $page) {
                                $db->prepareExecute('INSERT INTO eo_project_document(eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
                                    $group_id,
                                    $project_id,
                                    $page['contentType'],
                                    $page['contentRaw'],
                                    $page['content'],
                                    $page['title'],
                                    $page['updateTime'],
                                    $user_id,
                                ));
                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("add page error");
                            }
                            if ($page_group_child['pageGroupChildList']) {
                                $parent_id = $group_id;
                                foreach ($page_group_child['pageGroupChildList'] as $second_page_group_child) {
                                    //插入分组
                                    $db->prepareExecute('INSERT INTO eo_project_document_group(eo_project_document_group.projectID,eo_project_document_group.groupName,eo_project_document_group.parentGroupID,eo_project_document_group.isChild) VALUES (?,?,?,?);', array(
                                        $project_id,
                                        $second_page_group_child['groupName'],
                                        $parent_id,
                                        2
                                    ));
                                    if ($db->getAffectRow() < 1) {
                                        throw new \PDOException("add pageGroup error");
                                    }

                                    $group_id = $db->getLastInsertID();
                                    //插入状态码
                                    foreach ($second_page_group_child['pageList'] as $page) {
                                        $db->prepareExecute('INSERT INTO eo_project_document(eo_project_document.groupID,eo_project_document.projectID,eo_project_document.contentType,eo_project_document.contentRaw,eo_project_document.content,eo_project_document.title,eo_project_document.updateTime,eo_project_document.userID) VALUES (?,?,?,?,?,?,?,?);', array(
                                            $group_id,
                                            $project_id,
                                            $page['contentType'],
                                            $page['contentRaw'],
                                            $page['content'],
                                            $page['title'],
                                            $page['updateTime'],
                                            $user_id,
                                        ));
                                        if ($db->getAffectRow() < 1)
                                            throw new \PDOException("add page error");
                                    }
                                }
                            }
                        }
                    }
                }
            }
            //插入环境信息
            if (!empty($data['env'])) {
                foreach ($data['env'] as $env) {
                    $db->prepareExecute("INSERT INTO eo_api_env (eo_api_env.envName,eo_api_env.projectID) VALUES (?,?);", array(
                        $env['envName'],
                        $project_id
                    ));
                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("add env error");
                    $env_id = $db->getLastInsertID();
                    $db->prepareExecute("INSERT INTO eo_api_env_front_uri (eo_api_env_front_uri.envID,eo_api_env_front_uri.applyProtocol,eo_api_env_front_uri.uri) VALUES (?,?,?);", array(
                        $env_id,
                        $env['frontURI']['applyProtocol'],
                        $env['frontURI']['uri']
                    ));
                    foreach ($env['headerList'] as $header) {
                        $db->prepareExecute("INSERT INTO eo_api_env_header (eo_api_env_header.envID,eo_api_env_header.applyProtocol,eo_api_env_header.headerName,eo_api_env_header.headerValue) VALUES (?,?,?,?);", array(
                            $env_id,
                            $header['applyProtocol'],
                            $header['headerName'],
                            $header['headerValue']
                        ));
                    }
                    foreach ($env['paramList'] as $param) {
                        $db->prepareExecute("INSERT INTO eo_api_env_param (eo_api_env_param.envID,eo_api_env_param.paramKey,eo_api_env_param.paramValue) VALUES (?,?,?);", array(
                            $env_id,
                            $param['paramKey'],
                            $param['paramValue']
                        ));
                    }
                }
            }
            //插入自动化测试信息
            if (!empty($data['caseGroupList'])) {
                foreach ($data['caseGroupList'] as $case_group) {
                    // 插入分组
                    $db->prepareExecute('INSERT INTO eo_project_test_case_group (eo_project_test_case_group.projectID,eo_project_test_case_group.groupName) VALUES (?,?);', array(
                        $project_id,
                        $case_group['groupName']
                    ));
                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addCaseGroup error");
                    $group_id = $db->getLastInsertID();
                    if ($case_group['caseList']) {
                        // 插入状态码
                        foreach ($case_group['caseList'] as $case) {
                            $db->prepareExecute('INSERT INTO eo_project_test_case(eo_project_test_case.projectID,eo_project_test_case.userID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.createTime,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case.groupID,eo_project_test_case.caseCode)VALUES(?,?,?,?,?,?,?,?,?);', array(
                                $project_id,
                                $user_id,
                                $case['caseName'],
                                $case['caseDesc'],
                                date('Y-m-d H:i:s', time()),
                                date('Y-m-d H:i:s', time()),
                                $case['caseType'],
                                $group_id,
                                $case['caseCode']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addCase error");
                            $case_id = $db->getLastInsertID();
                            if ($case['caseSingleList']) {
                                foreach ($case['caseSingleList'] as $single_case) {
                                    $match = array();
                                    // 匹配<response[]>，当没有匹配结果的时候跳过
                                    if (preg_match_all('#<response\[(\d+)\]#', $single_case['caseData'], $match) > 0) {
                                        // 遍历匹配结果，对原字符串进行多次替换
                                        foreach ($match[1] as $response_id) {
                                            for ($i = 0; $i < count($case['caseSingleList']); $i++) {
                                                if ($case['caseSingleList'][$i]['connID'] == $response_id) {
                                                    $result = $db->prepareExecute("SELECT connID FROM eo_project_test_case_single WHERE apiName = ? AND apiURI = ? AND caseID = ?;", array(
                                                        $case['caseSingleList'][$i]['apiName'],
                                                        $case['caseSingleList'][$i]['apiURI'],
                                                        $case_id
                                                    ));
                                                    $single_case['caseData'] = str_replace("<response[" . $response_id, "<response[" . $result['connID'], $single_case['caseData']);
                                                }
                                            }
                                        }
                                    }

                                    $db->prepareExecute('INSERT INTO eo_project_test_case_single(eo_project_test_case_single.caseID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule, eo_project_test_case_single.apiName, eo_project_test_case_single.apiURI, eo_project_test_case_single.apiRequestType,eo_project_test_case_single.orderNumber)VALUES(?,?,?,?,?,?,?,?,?,?);', array(
                                        $case_id,
                                        $single_case['caseData'],
                                        $single_case['caseCode'],
                                        $single_case['statusCode'],
                                        $single_case['matchType'],
                                        $single_case['matchRule'],
                                        $single_case['apiName'],
                                        $single_case['apiURI'],
                                        $single_case['apiRequestType'],
                                        $single_case['orderNumber']
                                    ));
                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException('addSingleCase error');
                                }
                            }
                        }
                    }
                    if ($case_group['caseChildGroupList']) {
                        $group_id_parent = $group_id;
                        foreach ($case_group['caseChildGroupList'] as $child_group) {
                            // 插入分组
                            $db->prepareExecute('INSERT INTO eo_project_test_case_group (eo_project_test_case_group.projectID,eo_project_test_case_group.groupName,eo_project_test_case_group.parentGroupID,eo_project_test_case_group.isChild) VALUES (?,?,?,?);', array(
                                $project_id,
                                $child_group['groupName'],
                                $group_id_parent,
                                1
                            ));
                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("addCaseGroup error");
                            }
                            $group_id = $db->getLastInsertID();
                            if ($child_group['caseList']) {
                                // 插入状态码
                                foreach ($child_group['caseList'] as $case) {
                                    $db->prepareExecute('INSERT INTO eo_project_test_case(eo_project_test_case.projectID,eo_project_test_case.userID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.createTime,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case.groupID,eo_project_test_case.caseCode)VALUES(?,?,?,?,?,?,?,?,?);', array(
                                        $project_id,
                                        $user_id,
                                        $case['caseName'],
                                        $case['caseDesc'],
                                        date('Y-m-d H:i:s', time()),
                                        date('Y-m-d H:i:s', time()),
                                        $case['caseType'],
                                        $group_id,
                                        $case['caseCode']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addCase error");
                                    $case_id = $db->getLastInsertID();
                                    if ($case['caseSingleList']) {
                                        foreach ($case['caseSingleList'] as $single_case) {
                                            $match = array();
                                            // 匹配<response[]>，当没有匹配结果的时候跳过
                                            if (preg_match_all('#<response\[(\d+)\]#', $single_case['caseData'], $match) > 0) {
                                                // 遍历匹配结果，对原字符串进行多次替换
                                                foreach ($match[1] as $response_id) {
                                                    for ($i = 0; $i < count($case['caseSingleList']); $i++) {
                                                        if ($case['caseSingleList'][$i]['connID'] == $response_id) {
                                                            $result = $db->prepareExecute("SELECT connID FROM eo_project_test_case_single WHERE apiName = ? AND apiURI = ? AND caseID = ?;", array(
                                                                $case['caseSingleList'][$i]['apiName'],
                                                                $case['caseSingleList'][$i]['apiURI'],
                                                                $case_id
                                                            ));
                                                            $single_case['caseData'] = str_replace("<response[" . $response_id, "<response[" . $result['connID'], $single_case['caseData']);
                                                        }
                                                    }
                                                }
                                            }
                                            $db->prepareExecute('INSERT INTO eo_project_test_case_single(eo_project_test_case_single.caseID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule, eo_project_test_case_single.apiName, eo_project_test_case_single.apiURI, eo_project_test_case_single.apiRequestType,eo_project_test_case_single.orderNumber)VALUES(?,?,?,?,?,?,?,?,?,?);', array(
                                                $case_id,
                                                $single_case['caseData'],
                                                $single_case['caseCode'],
                                                $single_case['statusCode'],
                                                $single_case['matchType'],
                                                $single_case['matchRule'],
                                                $single_case['apiName'],
                                                $single_case['apiURI'],
                                                $single_case['apiRequestType'],
                                                $single_case['orderNumber']
                                            ));
                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException('addSingleCase error');
                                        }
                                    }
                                }
                            }
                            if ($child_group['caseChildGroupList']) {
                                $parent_id = $group_id;
                                foreach ($child_group['caseChildGroupList'] as $second_child_group) {
                                    // 插入分组
                                    $db->prepareExecute('INSERT INTO eo_project_test_case_group (eo_project_test_case_group.projectID,eo_project_test_case_group.groupName,eo_project_test_case_group.parentGroupID,eo_project_test_case_group.isChild) VALUES (?,?,?,?);', array(
                                        $project_id,
                                        $second_child_group['groupName'],
                                        $parent_id,
                                        2
                                    ));
                                    if ($db->getAffectRow() < 1) {
                                        throw new \PDOException("addCaseGroup error");
                                        var_dump($project_id, $second_child_group['groupName'], $parent_id);
                                    }
                                    $group_id = $db->getLastInsertID();
                                    if ($second_child_group['caseList']) {
                                        // 插入状态码
                                        foreach ($second_child_group['caseList'] as $case) {
                                            $db->prepareExecute('INSERT INTO eo_project_test_case(eo_project_test_case.projectID,eo_project_test_case.userID,eo_project_test_case.caseName,eo_project_test_case.caseDesc,eo_project_test_case.createTime,eo_project_test_case.updateTime,eo_project_test_case.caseType,eo_project_test_case.groupID,eo_project_test_case.caseCode)VALUES(?,?,?,?,?,?,?,?,?);', array(
                                                $project_id,
                                                $user_id,
                                                $case['caseName'],
                                                $case['caseDesc'],
                                                date('Y-m-d H:i:s', time()),
                                                date('Y-m-d H:i:s', time()),
                                                $case['caseType'],
                                                $group_id,
                                                $case['caseCode']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addCase error");
                                            $case_id = $db->getLastInsertID();
                                            if ($case['caseSingleList']) {
                                                foreach ($case['caseSingleList'] as $single_case) {
                                                    $match = array();
                                                    // 匹配<response[]>，当没有匹配结果的时候跳过
                                                    if (preg_match_all('#<response\[(\d+)\]#', $single_case['caseData'], $match) > 0) {
                                                        // 遍历匹配结果，对原字符串进行多次替换
                                                        foreach ($match[1] as $response_id) {
                                                            for ($i = 0; $i < count($case['caseSingleList']); $i++) {
                                                                if ($case['caseSingleList'][$i]['connID'] == $response_id) {
                                                                    $result = $db->prepareExecute("SELECT connID FROM eo_project_test_case_single WHERE apiName = ? AND apiURI = ? AND caseID = ?;", array(
                                                                        $case['caseSingleList'][$i]['apiName'],
                                                                        $case['caseSingleList'][$i]['apiURI'],
                                                                        $case_id
                                                                    ));
                                                                    $single_case['caseData'] = str_replace("<response[" . $response_id, "<response[" . $result['connID'], $single_case['caseData']);
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $db->prepareExecute('INSERT INTO eo_project_test_case_single(eo_project_test_case_single.caseID,eo_project_test_case_single.caseData,eo_project_test_case_single.caseCode,eo_project_test_case_single.statusCode,eo_project_test_case_single.matchType,eo_project_test_case_single.matchRule, eo_project_test_case_single.apiName, eo_project_test_case_single.apiURI, eo_project_test_case_single.apiRequestType,eo_project_test_case_single.orderNumber)VALUES(?,?,?,?,?,?,?,?,?,?);', array(
                                                        $case_id,
                                                        $single_case['caseData'],
                                                        $single_case['caseCode'],
                                                        $single_case['statusCode'],
                                                        $single_case['matchType'],
                                                        $single_case['matchRule'],
                                                        $single_case['apiName'],
                                                        $single_case['apiURI'],
                                                        $single_case['apiRequestType'],
                                                        $single_case['orderNumber']
                                                    ));
                                                    if ($db->getAffectRow() < 1)
                                                        throw new \PDOException('addSingleCase error');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            $db->rollBack();
            return FALSE;
        }
        $db->commit();
        return TRUE;
    }

    /**
     * 导入其他
     * @param $projectInfo array 项目信息
     * @param $groupInfoList array 分组信息
     * @param $userID int 用户ID
     * @return bool
     */
    public function importOther(&$projectInfo, &$groupInfoList, &$userID)
    {
        $db = getDatabase();
        try {
            // 开始事务
            $db->beginTransaction();
            // 插入项目
            $db->prepareExecute('INSERT INTO eo_project(eo_project.projectName,eo_project.projectType,eo_project.projectVersion,eo_project.projectUpdateTime) VALUES (?,?,?,?);', array(
                $projectInfo['projectName'],
                $projectInfo['projectType'],
                $projectInfo['projectVersion'],
                date('Y-m-d H:i:s', time())
            ));
            if ($db->getAffectRow() < 1)
                throw new \PDOException("addProject error");

            $projectID = $db->getLastInsertID();

            // 生成项目与用户的联系
            $db->prepareExecute('INSERT INTO eo_conn_project (eo_conn_project.projectID,eo_conn_project.userID,eo_conn_project.userType) VALUES (?,?,0);', array(
                $projectID,
                $userID
            ));

            if ($db->getAffectRow() < 1)
                throw new \PDOException("addConnProject error");

            if (is_array($groupInfoList)) {
                foreach ($groupInfoList as $groupInfo) {
                    $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID) VALUES (?,?);', array(
                        $groupInfo['groupName'],
                        $projectID
                    ));

                    if ($db->getAffectRow() < 1)
                        throw new \PDOException("addGroup error");

                    $groupID = $db->getLastInsertID();
                    if (is_array($groupInfo['apiList'])) {
                        foreach ($groupInfo['apiList'] as $api) {
                            // 插入api基本信息
                            $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                                $api['baseInfo']['apiName'],
                                $api['baseInfo']['apiURI'],
                                $api['baseInfo']['apiProtocol'],
                                $api['baseInfo']['apiSuccessMock'],
                                $api['baseInfo']['apiFailureMock'],
                                $api['baseInfo']['apiRequestType'],
                                $api['baseInfo']['apiStatus'],
                                $groupID,
                                $projectID,
                                $api['baseInfo']['starred'],
                                $api['baseInfo']['apiNoteType'],
                                $api['baseInfo']['apiNoteRaw'],
                                $api['baseInfo']['apiNote'],
                                $api['baseInfo']['apiRequestParamType'],
                                $api['baseInfo']['apiRequestRaw'],
                                $api['baseInfo']['apiUpdateTime'],
                                $userID
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addApi error");

                            $apiID = $db->getLastInsertID();

                            // 插入header信息
                            if (is_array($api['headerInfo'])) {
                                foreach ($api['headerInfo'] as $param) {
                                    $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                        $param['headerName'],
                                        $param['headerValue'],
                                        $apiID
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addHeader error");
                                }
                            }

                            // 插入api请求值信息
                            if (is_array($api['requestInfo'])) {
                                foreach ($api['requestInfo'] as $param) {
                                    $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                        $apiID,
                                        $param['paramName'],
                                        $param['paramKey'],
                                        ($param['paramValue']) ? $param['paramValue'] : "",
                                        $param['paramLimit'],
                                        $param['paramNotNull'],
                                        $param['paramType']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addRequestParam error");

                                    $paramID = $db->getLastInsertID();

                                    if (is_array($param['paramValueList'])) {
                                        foreach ($param['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);;', array(
                                                $paramID,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addRequestParamValue error");
                                        };
                                    }
                                };
                            }

                            // 插入api返回值信息
                            if (is_array($api['resultInfo'])) {
                                foreach ($api['resultInfo'] as $param) {
                                    $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                        $apiID,
                                        $param['paramName'],
                                        $param['paramKey'],
                                        $param['paramNotNull']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addResultParam error");

                                    $paramID = $db->getLastInsertID();

                                    if (is_array($param['paramValueList'])) {
                                        foreach ($param['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                $paramID,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addResultParamValue error");
                                        };
                                    }
                                };
                            }

                            // 插入api缓存数据用于导出
                            $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                                $projectID,
                                $groupID,
                                $apiID,
                                json_encode($api),
                                0
                            ));

                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("addApiCache error");
                            }
                        }
                    }

                    if (is_array($groupInfo['childGroupList'])) {
                        foreach ($groupInfo['childGroupList'] as $childGroupInfo) {
                            $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID,eo_api_group.isChild) VALUES (?,?,?,?);', array(
                                $childGroupInfo['groupName'],
                                $projectID,
                                $groupID,
                                1
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildGroup error");

                            $childGroupID = $db->getLastInsertID();

                            if (is_array($childGroupInfo['apiList'])) {
                                foreach ($childGroupInfo['apiList'] as $api) {
                                    // 插入api基本信息
                                    $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime,eo_api.updateUserID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                                        $api['baseInfo']['apiName'],
                                        $api['baseInfo']['apiURI'],
                                        $api['baseInfo']['apiProtocol'],
                                        $api['baseInfo']['apiSuccessMock'],
                                        $api['baseInfo']['apiFailureMock'],
                                        $api['baseInfo']['apiRequestType'],
                                        $api['baseInfo']['apiStatus'],
                                        $childGroupID,
                                        $projectID,
                                        $api['baseInfo']['starred'],
                                        $api['baseInfo']['apiNoteType'],
                                        $api['baseInfo']['apiNoteRaw'],
                                        $api['baseInfo']['apiNote'],
                                        $api['baseInfo']['apiRequestParamType'],
                                        $api['baseInfo']['apiRequestRaw'],
                                        $api['baseInfo']['apiUpdateTime'],
                                        $userID
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildGroupApi error");

                                    $apiID = $db->getLastInsertID();

                                    // 插入header信息
                                    if (is_array($api['headerInfo'])) {
                                        foreach ($api['headerInfo'] as $param) {
                                            $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                                $param['headerName'],
                                                $param['headerValue'],
                                                $apiID
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildGroupHeader error");
                                        }
                                    }

                                    // 插入api请求值信息
                                    if (is_array($api['requestInfo'])) {
                                        foreach ($api['requestInfo'] as $param) {
                                            $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                                $apiID,
                                                $param['paramName'],
                                                $param['paramKey'],
                                                ($param['paramValue']) ? $param['paramValue'] : "",
                                                $param['paramLimit'],
                                                $param['paramNotNull'],
                                                $param['paramType']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildGroupRequestParam error");

                                            $paramID = $db->getLastInsertID();

                                            if (is_array($param['paramValueList'])) {
                                                foreach ($param['paramValueList'] as $value) {
                                                    $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);;', array(
                                                        $paramID,
                                                        $value['value'],
                                                        $value['valueDescription']
                                                    ));

                                                    if ($db->getAffectRow() < 1)
                                                        throw new \PDOException("addChildGroupRequestParamValue error");
                                                };
                                            }
                                        };
                                    }

                                    // 插入api返回值信息
                                    if (is_array($api['resultInfo'])) {
                                        foreach ($api['resultInfo'] as $param) {
                                            $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                                $apiID,
                                                $param['paramName'],
                                                $param['paramKey'],
                                                $param['paramNotNull']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildGroupResultParam error");

                                            $paramID = $db->getLastInsertID();

                                            if (is_array($param['paramValueList'])) {
                                                foreach ($param['paramValueList'] as $value) {
                                                    $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                        $paramID,
                                                        $value['value'],
                                                        $value['valueDescription']
                                                    ));

                                                    if ($db->getAffectRow() < 1)
                                                        throw new \PDOException("addChildGroupResultParamValue error");
                                                };
                                            }
                                        };
                                    }

                                    // 插入api缓存数据用于导出
                                    $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                                        $projectID,
                                        $childGroupID,
                                        $apiID,
                                        json_encode($api),
                                        0
                                    ));

                                    if ($db->getAffectRow() < 1) {
                                        throw new \PDOException("addChildGroupApiCache error");
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            $db->rollBack();
            return FALSE;
        }
        $db->commit();
        return TRUE;
    }
}

?>