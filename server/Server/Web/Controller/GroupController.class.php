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

class GroupController
{
    // 返回json类型
    private $returnJson = array('type' => 'group');

    /**
     * 检查登录状态
     */
    public function __construct()
    {
        // 身份验证
        $server = new GuestModule;
        if (!$server->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * 添加项目api分组
     */
    public function addGroup()
    {
        $nameLen = mb_strlen(quickInput('groupName'), 'utf8');
        $projectID = securelyInput('projectID');
        $isChild = securelyInput('isChild', 0);
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $groupName = securelyInput('groupName');
        $parentGroupID = securelyInput('parentGroupID', NULL);
        // 判断项目ID和组名格式是否合法
        if (preg_match('/^[0-9]{1,11}$/', $projectID) && $nameLen >= 1 && $nameLen <= 30) {
            // 项目ID和组名合法
            $service = new GroupModule();
            $result = $service->addGroup($projectID, $groupName, $parentGroupID, $isChild);
            // 判断添加项目api分组是否成功
            if ($result) {
                // 添加项目api分组成功
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['groupID'] = $result;
            } else
                // 添加项目api分组失败
                $this->returnJson['statusCode'] = '150001';
        } else {
            $this->returnJson['statusCode'] = '150002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除项目api分组
     */
    public function deleteGroup()
    {
        $groupID = securelyInput('groupID');
        $module = new GroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        // 判断分组ID格式是否合法
        if (preg_match('/^[0-9]{1,11}$/', $groupID)) {
            // 分组ID格式合法
            $service = new GroupModule();
            $result = $service->deleteGroup($groupID);
            // 判断删除项目api分组是否成功
            if ($result)
                // 删除项目api分组成功
                $this->returnJson['statusCode'] = '000000';
            else
                // 删除api分组失败
                $this->returnJson['statusCode'] = '150003';
        } else {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '150004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取项目api分组列表
     */
    public function getGroupList()
    {
        $projectID = securelyInput('projectID');
        if (preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $service = new GroupModule;
            $result = $service->getGroupList($projectID);
            $orderList = $service->getGroupOrderList($projectID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['groupList'] = $result;
                $this->returnJson['groupOrder'] = $orderList;
            } else {
                $this->returnJson['statusCode'] = '150008';
            }
        } else {
            $this->returnJson['statusCode'] = '150007';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改api分组
     */
    public function editGroup()
    {
        $nameLen = mb_strlen(quickInput('groupName'), 'utf8');
        $groupID = securelyInput('groupID');
        $parentGroupID = securelyInput('parentGroupID');
        $isChild = securelyInput('isChild');
        $module = new GroupModule();
        $userType = $module->getUserType($groupID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $groupName = securelyInput('groupName');
        // 判断分组ID和组名格式是否合法
        if (preg_match('/^[0-9]{1,11}$/', $groupID) && $nameLen >= 1 && $nameLen <= 30) {
            if ($groupID == $parentGroupID) {
                $this->returnJson['statusCode'] = '150008';
                exitOutput($this->returnJson);
            }
            $service = new GroupModule();
            $result = $service->editGroup($groupID, $groupName, $parentGroupID, $isChild);
            if ($result)
                // 修改api分组成功
                $this->returnJson['statusCode'] = '000000';
            else
                // 修改api分组失败
                $this->returnJson['statusCode'] = '150005';
        } else {
            // 分组ID和组名格式不合法
            $this->returnJson['statusCode'] = '150002';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改分组列表排序
     */
    public function sortGroup()
    {
        $projectID = securelyInput('projectID');
        $module = new ProjectModule();
        $userType = $module->getUserType($projectID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        //排序json字符串
        $orderList = quickInput('orderList');
        //判断排序格式是否合法
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '150007';
        } else if (empty($orderList)) {
            //排序格式非法
            $this->returnJson['statusCode'] = '150004';
        } else {
            $service = new GroupModule;
            $result = $service->sortGroup($projectID, $orderList);
            //验证结果
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '150000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导出接口分组
     */
    public function exportGroup()
    {
        //分组ID
        $group_id = securelyInput('groupID');
        if (!preg_match('/^[0-9]{1,11}$/', $group_id)) {
            // 分组ID格式不合法
            $this->returnJson['statusCode'] = '150003';
        } else {
            $service = new GroupModule();
            $user_type = $service->getUserType($group_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            } else {
                $result = $service->exportGroup($group_id);
                if ($result) {
                    $this->returnJson['statusCode'] = '000000';
                    $this->returnJson['fileName'] = $result;
                } else {
                    $this->returnJson['statusCode'] = '150000';
                }
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导入接口分组
     */
    public function importGroup()
    {
        $project_id = securelyInput('projectID');
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '150007';
        } //判断导入数据是否为空
        elseif (empty($data)) {
            $this->returnJson['statusCode'] = '150005';
            exitOutput($this->returnJson);
        } else {
            $service = new ProjectModule();
            $user_type = $service->getUserType($project_id);
            if ($user_type < 0 || $user_type > 2) {
                $this->returnJson['statusCode'] = '120007';
            }
            $server = new GroupModule();
            $result = $server->importGroup($project_id, $data);
            //验证结果
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '150000';
            }
        }
        exitOutput($this->returnJson);
    }
}

?>