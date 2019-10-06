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

class DocumentController
{
    // 返回json类型
    private $returnJson = array('type' => 'document');

    //用户ID
    private $user_id;

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
        } else {
            $this->user_id = $_SESSION['userID'];
        }
    }

    /**
     * 添加文档
     */
    public function addDocument()
    {
        //分组ID
        $group_id = securelyInput('groupID');
        //题目
        $title = securelyInput('title');
        $content = quickInput('content');
        $content_raw = quickInput('contentRaw', '');
        $content_type = quickInput('contentType');

        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            //分组ID格式不合法
            $this->returnJson['statusCode'] = '230001';
        } elseif ($content_type != 0 && $content_type != 1) {
            //文档描述格式不合法
            $this->returnJson['statusCode'] = '230002';
        } else {
            $group_module = new DocumentGroupModule();
            $user_type = $group_module->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $service = new DocumentModule();
                $result = $service->addDocument($this->user_id, $group_id, $content_type, $content, $content_raw, $title);

                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['documentID'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '230000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改文档
     */
    public function editDocument()
    {
        $document_id = securelyInput('documentID');
        $title = securelyInput('title');
        $group_id = securelyInput('groupID');
        $content = quickInput('content');
        $content_raw = quickInput('contentRaw', '');
        $content_type = securelyInput('contentType');

        if (!preg_match('/^[0-9]{1,11}$/', $document_id)) {
            //文档ID格式非法
            $this->returnJson['statusCode'] = '230003';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            //分组ID格式非法
            $this->returnJson['statusCode'] = '230001';
        } else {
            $service = new DocumentModule();
            $user_type = $service->getUserType($document_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->editDocument($this->user_id, $group_id, $document_id, $content_type, $content, $content_raw, $title);

                if ($result) {
                    //成功
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    //失败
                    $this->returnJson['statusCode'] = '230000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取文档列表
     */
    public function getDocumentList()
    {
        $group_id = securelyInput('groupID');

        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            //分组ID格式不合法
            $this->returnJson['statusCode'] = '230001';
        } else {
            $service = new DocumentModule();
            $result = $service->getDocumentList($group_id, $this->user_id);

            if ($result) {
                //成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['documentList'] = $result;
            } else {
                //失败
                $this->returnJson['statusCode'] = '230000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取所有文档列表
     */
    public function getAllDocumentList()
    {
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            //项目ID格式不合法
            $this->returnJson['statusCode'] = '230004';
        } else {
            $service = new DocumentModule();
            $result = $service->getAllDocumentList($project_id, $this->user_id);
            //验证结果
            if ($result) {
                //成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['documentList'] = $result;
            } else {
                //失败
                $this->returnJson['statusCode'] = '230000';
            }
        }

        exitOutput($this->returnJson);
    }

    /**
     * 搜索文档
     */
    public function searchDocument()
    {
        $tips_length = mb_strlen(quickInput('tips'), 'utf8');
        $tips = securelyInput('tips');
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            //项目ID格式不合法
            $this->returnJson['statusCode'] = '230004';
        } elseif ($tips_length < 1 || $tips_length > 255) {
            //判断关键字长度是否合法
            $this->returnJson['statusCode'] = '230005';
        } else {
            $service = new DocumentModule();
            $result = $service->searchDocument($project_id, $tips, $this->user_id);
            //验证结果
            if ($result) {
                //成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['documentList'] = $result;
            } else {
                //失败
                $this->returnJson['statusCode'] = '230000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取文档详情
     */
    public function getDocument()
    {
        $document_id = securelyInput('documentID');
        if (!preg_match('/^[0-9]{1,11}$/', $document_id)) {
            //文档ID格式不合法
            $this->returnJson['statusCode'] = '230003';
        } else {
            $service = new DocumentModule();
            $result = $service->getDocument($document_id, $this->user_id);

            if ($result) {
                //成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['documentInfo'] = $result;
            } else {
                //失败
                $this->returnJson['statusCode'] = '230000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 批量删除文档
     */
    public function deleteDocuments()
    {
        $ids = quickInput('documentID');
        $arr = json_decode($ids);
        $arr = preg_grep('/^[0-9]{1,11}$/', $arr);
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            //项目ID格式不合法
            $this->returnJson['statusCode'] = '230004';
        } elseif (empty($arr)) {
            //文档ID格式不合法
            $this->returnJson['statusCode'] = '230003';
        } else {
            $project_module = new ProjectModule();
            $user_type = $project_module->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $document_ids = implode(',', $arr);
                $service = new DocumentModule();
                $result = $service->deleteDocuments($project_id, $this->user_id, $document_ids);
                //验证结果
                if ($result) {
                    //成功
                    $this->returnJson['statusCode'] = '000000';
                } else {
                    //失败
                    $this->returnJson['statusCode'] = '230000';
                }
            }
        }
        exitOutput($this->returnJson);
    }
}