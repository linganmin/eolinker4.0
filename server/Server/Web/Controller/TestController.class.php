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

class TestController
{
    //返回Json类型
    private $returnJson = array('type' => 'test');

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
     * get测试
     */
    public function get()
    {
        $method = 'GET';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $requestParam = json_decode(quickInput('params'), TRUE);
        $apiID = securelyInput('apiID');

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            //apiID格式非法
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            //转成数字索引的数组
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }

        if ($requestParam) {
            //			foreach ($requestParam as $key => $value)
            //			{
            //				$arr[] = $key . '=' . $value;
            //			}
            //			$URL = $URL . '?' . join('&', $arr);
            $URL = $URL . '?' . http_build_query($requestParam);

        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        //URL格式非法
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader);

        if ($result) {
            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => 0,
                'params' => $requestParamInfo ? $requestParamInfo : array()
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                //添加测试记录失败
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210002';
        }
        exitOutput($this->returnJson);

    }

    /**
     * post测试
     */
    public function post()
    {
        $method = 'POST';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $apiID = securelyInput('apiID');
        $requestType = quickInput('requestType');
        switch ($requestType) {
            case 0 :
                {
                    $param = json_decode(quickInput('params'), TRUE);
                    foreach ($param as $key => $value) {
                        $requestParamInfo[] = array(
                            'key' => $key,
                            'value' => $value
                        );
                    }
                    break;
                }
            case 1 :
                {
                    $param = quickInput('params');
                    break;
                }
            default :
                {
                    //请求参数类型错误
                    $this->returnJson['statusCode'] = '210013';
                    exitOutput($this->returnJson);
                }
        }

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            //apiID格式非法
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            //转成数字索引的数组
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        //URL格式非法
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $param);

        if ($result) {
            //判断请求参数的类型
            if ($requestType == 0) {
                //表单类型
                $requestParam = $requestParamInfo ? $requestParamInfo : array();
            } else {
                //源文本类型
                $requestParam = $param;
            }

            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => $requestType,
                'params' => $requestParam
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                //添加测试记录失败
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210003';
        }
        exitOutput($this->returnJson);
    }

    /**
     * delete测试
     */
    public function delete()
    {
        $method = 'DELETE';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $requestParam = json_decode(quickInput('params'), TRUE);
        $apiID = securelyInput('apiID');

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            //apiID格式非法
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            //转成数字索引的数组
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        //URL格式非法
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $requestParam);

        if ($result) {
            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => 0,
                'params' => $requestParamInfo ? $requestParamInfo : array()
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                //添加测试记录失败
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * head测试
     */
    public function head()
    {
        $method = 'HEAD';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $requestParam = json_decode(quickInput('params'), TRUE);
        $apiID = securelyInput('apiID');

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            //apiID格式非法
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            //转成数字索引的数组
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }
        if ($requestParam) {
            foreach ($requestParam as $key => $value) {
                $requestParamInfo[] = array(
                    'key' => $key,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        //URL格式非法
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $requestParam);

        if ($result) {
            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => 0,
                'params' => $requestParamInfo ? $requestParamInfo : array()
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                //添加测试记录失败
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210005';
        }
        exitOutput($this->returnJson);
    }

    /**
     * options测试
     */
    public function options()
    {
        $method = 'OPTIONS';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $requestParam = json_decode(quickInput('params'), TRUE);
        $apiID = securelyInput('apiID');

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            //apiID格式非法
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            //转成数字索引的数组
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }
        if ($requestParam) {
            foreach ($requestParam as $key => $value) {
                $requestParamInfo[] = array(
                    'key' => $key,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        //URL格式非法
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $requestParam);

        if ($result) {
            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => 0,
                'params' => $requestParamInfo ? $requestParamInfo : array()
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                //添加测试记录失败
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210006';
        }
        exitOutput($this->returnJson);
    }

    /**
     * patch测试
     */
    public function patch()
    {
        $method = 'PATCH';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $requestParam = json_decode(quickInput('params'), TRUE);
        $apiID = securelyInput('apiID');

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            //apiID格式非法
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            //转成数字索引的数组
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }
        if ($requestParam) {
            foreach ($requestParam as $key => $value) {
                $requestParamInfo[] = array(
                    'key' => $key,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        //URL格式非法
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $requestParam);

        if ($result) {
            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => 0,
                'params' => $requestParamInfo ? $requestParamInfo : array()
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                //添加测试记录失败
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210007';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除测试记录
     */
    public function deleteTestHistory()
    {
        $testID = securelyInput('testID');

        if (!preg_match('/^[0-9]{1,11}$/', $testID)) {
            //testID格式非法
            $this->returnJson['statusCode'] = '210010';
        } else {
            $service = new TestHistoryModule;
            $result = $service->deleteTestHistory($testID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                //删除测试记录失败
                $this->returnJson['statusCode'] = '210011';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 获取测试记录信息
     */
    public function getTestHistory()
    {
        $testID = securelyInput('testID');

        if (!preg_match('/^[0-9]{1,11}$/', $testID)) {
            //testID格式非法
            $this->returnJson['statusCode'] = '210010';
        } else {
            $service = new TestHistoryModule;
            $result = $service->getTestHistory($testID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['projectID'] = $result['projectID'];
                $this->returnJson['apiID'] = $result['apiID'];
                $this->returnJson['testID'] = $result['testID'];
                $this->returnJson['requestInfo'] = json_decode($result['requestInfo'], TRUE);
                $this->returnJson['resultInfo'] = json_decode($result['resultInfo'], TRUE);
                $this->returnJson['testTime'] = $result['testTime'];
            } else {
                $this->returnJson['statusCode'] = '210012';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * put测试
     */
    public function put()
    {
        $method = 'PUT';
        $apiProtocol = quickInput('apiProtocol');
        $URL = quickInput('URL');
        $headers = json_decode(quickInput('headers'), TRUE);
        $apiID = securelyInput('apiID');
        $requestType = quickInput('requestType');
        switch ($requestType) {
            case 0 :
                {
                    $param = json_decode(quickInput('params'), TRUE);
                    foreach ($param as $key => $value) {
                        $requestParamInfo[] = array(
                            'key' => $key,
                            'value' => $value
                        );
                    }
                    break;
                }
            case 1 :
                {
                    $param = quickInput('params');
                    break;
                }
            default :
                {
                    //请求参数类型错误
                    $this->returnJson['statusCode'] = '210013';
                    exitOutput($this->returnJson);
                }
        }

        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            //apiID格式非法
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        }

        if ($headers) {
            //转成数字索引的数组
            foreach ($headers as $name => $value) {
                $requestHeader[] = $name . ': ' . $value;
                $requestHeaderInfo[] = array(
                    'name' => $name,
                    'value' => $value
                );
            }
        }

        if ($apiProtocol == 0) {
            $completeURL = 'http://' . $URL;
        } else {
            $completeURL = 'https://' . $URL;
        }

        //URL格式非法
        if (!$completeURL || !filter_var($completeURL, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_QUERY_REQUIRED)) {
            $this->returnJson['statusCode'] = '210001';
            exitOutput($this->returnJson);
        }

        $service = new ProxyModule;
        $result = $service->proxyToDesURL($method, $completeURL, $requestHeader, $param);

        if ($result) {
            //判断请求参数的类型
            if ($requestType == 0) {
                //表单类型
                $requestParam = $requestParamInfo ? $requestParamInfo : array();
            } else {
                //源文本类型
                $requestParam = $param;
            }

            $requestInfo = json_encode(array(
                'apiProtocol' => $apiProtocol,
                'method' => $method,
                'URL' => $URL,
                'headers' => $requestHeaderInfo ? $requestHeaderInfo : array(),
                'requestType' => $requestType,
                'params' => $requestParam
            ));
            $resultInfo = json_encode(array(
                'headers' => $result['testResult']['headers'],
                'body' => $result['testResult']['body'],
                'httpCode' => $result['testHttpCode'],
                'testDeny' => $result['testDeny']
            ));
            $testTime = $result['testTime'];
            $server = new TestHistoryModule;
            $testID = $server->addTestHistory($apiID, $requestInfo, $resultInfo, $testTime);
            if ($testID) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testHttpCode'] = $result['testHttpCode'];
                $this->returnJson['testResult'] = $result['testResult'];
                $this->returnJson['testDeny'] = $result['testDeny'];
                $this->returnJson['testID'] = $testID;
            } else {
                //添加测试记录失败
                $this->returnJson['statusCode'] = '210009';
            }
        } else {
            $this->returnJson['statusCode'] = '210013';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除所有测试记录
     */
    public function deleteAllTestHistory()
    {
        $apiID = securelyInput('apiID');
        if (!preg_match('/^[0-9]{1,11}$/', $apiID)) {
            //apiID格式非法
            $this->returnJson['statusCode'] = '210008';
            exitOutput($this->returnJson);
        } else {
            $module = new TestHistoryModule();
            $result = $module->deleteAllTestHistory($apiID);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '210000';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 添加测试历史
     */
    public function addTestHistory()
    {
        $api_id = securelyInput('apiID');
        $request_info = quickInput('requestInfo');
        $result_info = quickInput('resultInfo');
        $test_time = date('Y-m-d H:i:s', time());
        if (!preg_match('/^[0-9]{1,11}$/', $api_id)) {
            //apiID格式非法
            $this->returnJson['statusCode'] = '210008';
        } else {
            $server = new TestHistoryModule();
            $result = $server->addTestHistory($api_id, $request_info, $result_info, $test_time);
            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['testID'] = $result;
            } else {
                $this->returnJson['statusCode'] = '210000';
            }
        }
        exitOutput($this->returnJson);
    }
}

?>