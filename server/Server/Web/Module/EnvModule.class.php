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

class EnvModule
{
    public function __construct()
    {
        @session_start();
    }

    /**
     * 获取环境列表
     * @param $project_id int 项目的数字ID
     * @return bool|array
     */
    public function getEnvList(&$project_id)
    {
        $projectDao = new ProjectDao;
        if (!$projectDao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $env_dao = new EnvDao;
        return $env_dao->getEnvList($project_id);
    }

    /**
     * 添加环境
     * @param $project_id int 项目的数字ID
     * @param $env_name string 环境名称
     * @param $front_uri string 前置URI
     * @param $headers array 请求头部
     * @param $params array 全局变量
     * @param $apply_protocol int 应用的请求类型,[-1]=>[所有请求类型]
     * @param $additional_params array 额外参数
     * @return bool|int
     */
    public function addEnv(&$project_id, &$env_name, &$front_uri, &$headers, &$params, $apply_protocol, &$additional_params)
    {
        $env_dao = new EnvDao;
        $projectDao = new ProjectDao;
        if (!$projectDao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            return FALSE;
        }
        $env_id = $env_dao->addEnv($project_id, $env_name, $front_uri, $headers, $params, $apply_protocol, $additional_params);
        if ($env_id) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_ENVIRONMENT, $env_id, ProjectLogDao::$OP_TYPE_ADD, "添加环境:'{$env_name}'", date("Y-m-d H:i:s", time()));
            return $env_id;
        } else {
            return FALSE;
        }
    }

    /**
     * 删除环境
     * @param $project_id int 项目的数字ID
     * @param $env_id int 环境的数字ID
     * @return bool
     */
    public function deleteEnv(&$project_id, &$env_id)
    {
        $env_dao = new EnvDao;
        $projectDao = new ProjectDao;
        if ($projectDao->checkProjectPermission($project_id, $_SESSION['userID'])) {
            if (!$env_dao->checkEnvPermission($env_id, $_SESSION['userID'])) {
                return FALSE;
            }
            $env_name = $env_dao->getEnvName($env_id);
            if ($env_dao->deleteEnv($project_id, $env_id)) {
                //将操作写入日志
                $log_dao = new \ProjectLogDao();
                $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_ENVIRONMENT, $env_id, ProjectLogDao::$OP_TYPE_DELETE, "删除环境:'$env_name'", date("Y-m-d H:i:s", time()));

                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 修改环境
     * @param $env_id int 环境的数字ID
     * @param $env_name string 环境名称
     * @param $front_uri string 前置URI
     * @param $headers array 请求头部
     * @param $params array 全局变量
     * @param $apply_protocol int 应用的请求类型,[-1]=>[所有请求类型]
     * @param $additional_params array 额外参数
     * @return bool
     */
    public function editEnv(&$env_id, &$env_name, &$front_uri, &$headers, &$params, $apply_protocol, &$additional_params)
    {
        $env_dao = new EnvDao;
        if (!($project_id = $env_dao->checkEnvPermission($env_id, $_SESSION['userID']))) {
            return FALSE;
        }
        if ($env_dao->editEnv($env_id, $env_name, $front_uri, $headers, $params, $apply_protocol, $additional_params)) {
            //将操作写入日志
            $log_dao = new ProjectLogDao();
            $log_dao->addOperationLog($project_id, $_SESSION['userID'], ProjectLogDao::$OP_TARGET_ENVIRONMENT, $project_id, ProjectLogDao::$OP_TYPE_UPDATE, "修改环境:'{$env_name}'", date("Y-m-d H:i:s", time()));

            return TRUE;
        } else {
            return FALSE;
        }
    }
}