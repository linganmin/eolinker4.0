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

class MockController
{

    /**
     * 返回示例结果(简易mock)
     */
    public function simple()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET,PUT,DELETE,PATCH,OPTIONS');
        header('Access-Control-Allow-Headers:x-requested-with,content-type,x-custom-header,Accept,Authorization,other_header,x-csrf-token');
        header("Content-type: text/html; charset=UTF-8");

        $project_id = $_GET['projectID'];
        $result_type = $_GET['resultType'] ? $_GET['resultType'] : 'success';
        $type = array(
            'POST' => '0',
            'GET' => '1',
            'PUT' => '2',
            'DELETE' => '3',
            'HEAD' => '4',
            'OPTIONS' => '5',
            'PATCH' => '6'
        );
        $request_type = $type[$_SERVER['REQUEST_METHOD']];
        $api_uri = $_GET['uri'];

        $service = new MockModule();
        switch ($result_type) {
            case 'success':
                {
                    $result = $service->success($project_id, $api_uri, $request_type);
                    break;
                }
            case 'failure':
                {
                    $result = $service->failure($project_id, $api_uri, $request_type);
                    break;
                }
            default:
                {
                    exit('error result type.');
                }
        }
        if ($result) {
            exit($result);
        } else {
            exit('sorry,this api without the mock data.');
        }
    }

    /**
     * 获取高级mock结果
     */
    public function mock()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET,PUT,DELETE,PATCH,OPTIONS');
        header('Access-Control-Allow-Headers:x-requested-with,content-type,x-custom-header,Accept,Authorization,other_header,x-csrf-token');
        header("Content-type: application/json; charset=UTF-8");

        $project_id = $_GET['projectID'];
        $type = array(
            'POST' => '0',
            'GET' => '1',
            'PUT' => '2',
            'DELETE' => '3',
            'HEAD' => '4',
            'OPTIONS' => '5',
            'PATCH' => '6'
        );
        $request_type = $type[$_SERVER['REQUEST_METHOD']];
        $api_uri = $_GET['uri'];

        $module = new MockModule();
        $result = $module->getMockResult($project_id, $api_uri, $request_type);
        if ($result) {
            $decoded_result = htmlspecialchars_decode($result);
            if ($decoded_result) {
                exit($decoded_result);
            }
            exit($result);
        } else {
            exit('sorry,this api without the mock data.');
        }
    }
}