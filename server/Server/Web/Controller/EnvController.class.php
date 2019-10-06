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

class EnvController
{
    // 返回json类型
    private $returnJson = array('type' => 'environment');

    /**
     * 构造函数,在此判断用户登录状态以及初始化各变量
     */
    public function __construct()
    {
        // 身份验证
        $module = new GuestModule;
        if (!$module->checkLogin()) {
            $this->returnJson['statusCode'] = '120005';
            exitOutput($this->returnJson);
        }
    }

    /**
     * 获取项目环境列表
     */
    public function getEnvList()
    {
        $service = new EnvModule;
        $projectID = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '170003';
        } else {
            $result = $service->getEnvList($projectID);
            //验证结果
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['envList'] = $result;
            } else {
                //环境列表为空
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 添加项目环境
     */
    public function addEnv()
    {
        //环境名称
        $env_name = securelyInput('envName');
        //环境名称长度
        $name_length = mb_strlen(quickInput('envName'), 'utf8');
        //前置URI地址
        $front_uri = securelyInput('frontURI');
        //请求头部
        $headers = json_decode(quickInput('headers'), TRUE);
        //全局变量
        $params = json_decode(quickInput('params'), TRUE);
        //额外参数
        $additional_params = json_decode(quickInput('additionalParams'), TRUE);
        $projectID = securelyInput('projectID');
        $apply_protocol = -1;
        if (!preg_match('/^[0-9]{1,11}$/', $projectID)) {
            $this->returnJson['statusCode'] = '170003';
        } //判断名称长度是否合法
        elseif ($name_length < 1 || $name_length > 32) {
            //环境名称格式非法
            $this->returnJson['statusCode'] = '170001';
        } else {
            $service = new EnvModule;
            $result = $service->addEnv($projectID, $env_name, $front_uri, $headers, $params, $apply_protocol, $additional_params);
            //验证结果是否成功
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['envID'] = $result;
            } else {
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除项目环境
     */
    public function deleteEnv()
    {
        $env_id = securelyInput('envID');
        $project_id = securelyInput('projectID');
        if (!preg_match('/^[0-9]{1,11}$/', $project_id)) {
            $this->returnJson['statusCode'] = '170003';
        } //判断环境ID是否合法
        elseif (!preg_match('/^[0-9]{1,11}$/', $env_id)) {
            //环境ID不合法
            $this->returnJson['statusCode'] = '170002';
        } else {
            $service = new EnvModule();
            if ($service->deleteEnv($project_id, $env_id)) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                //删除环境失败，projectID与envID不匹配
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改项目环境
     */
    public function editEnv()
    {
        $env_id = securelyInput('envID');
        $env_name = securelyInput('envName');
        $name_length = mb_strlen(quickInput('envName'), 'utf8');
        //前置URI地址
        $front_uri = securelyInput('frontURI');
        //请求头部
        $headers = json_decode(quickInput('headers'), TRUE);
        //全局变量
        $params = json_decode(quickInput('params'), TRUE);
        //额外参数
        $additional_params = json_decode(quickInput('additionalParams'), TRUE);
        $apply_protocol = -1;
        if ($name_length < 1 || $name_length > 32) {
            //环境名称格式非法
            $this->returnJson['statusCode'] = '170001';
        } elseif (!preg_match('/^[0-9]{1,11}$/', $env_id)) {
            //环境ID不合法
            $this->returnJson['statusCode'] = '170002';
        } else {
            $service = new EnvModule();
            if ($service->editEnv($env_id, $env_name, $front_uri, $headers, $params, $apply_protocol, $additional_params)) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                //修改失败
                $this->returnJson['statusCode'] = '170000';
            }
        }
        exitOutput($this->returnJson);
    }
}