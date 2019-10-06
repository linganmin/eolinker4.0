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

class UpdateController
{
    //返回Json类型
    private $returnJson = array('type' => 'update');

    /**
     * 检查是否有更新
     */
    public function checkUpdate()
    {
        if (ALLOW_UPDATE) {
            $server = new ProxyModule;
            $updateInfo = $server->proxyToDesURL('GET', 'https://api.eolinker.com/openSource/Update/checkout');
            $result = json_decode($updateInfo['testResult']['body'], TRUE);
            if ($result) {
                if (OS_VERSION_CODE < $result['versionCode']) {
                    $this->returnJson['statusCode'] = '000000';
                    if (!is_session_started()) {
                        session_start();
                    }
                    if (is_session_started()) {
                        session_destroy();
                    }
                } else {
                    $this->returnJson['statusCode'] = '320002';
                }
            } else {
                $this->returnJson['statusCode'] = '320001';
            }
            exitOutput($this->returnJson);
        } else {
            //更新已被禁用
            $this->returnJson['statusCode'] = '320004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 自动更新项目
     */
    public function autoUpdate()
    {
        ini_set("max_execution_time", 0);
        if (ALLOW_UPDATE) {
            try {

                $proxyServer = new ProxyModule;
                $updateInfo = $proxyServer->proxyToDesURL('GET', 'https://api.eolinker.com/openSource/Update/checkout');
                $result = json_decode($updateInfo['testResult']['body'], TRUE);
                if ($result) {
                    if (OS_VERSION_CODE < $result['versionCode']) {
                        $updateServer = new UpdateModule;
                        if ($updateServer->autoUpdate($result['updateUrl'])) {
                            $this->returnJson['statusCode'] = '000000';
                            if (!is_session_started()) {
                                session_start();
                            }
                            if (is_session_started()) {
                                session_destroy();
                            }
                        } else {
                            //更新失败
                            $this->returnJson['statusCode'] = '320003';
                        }
                    } else {
                        //已是最新版本，无需更新
                        $this->returnJson['statusCode'] = '320002';
                    }
                } else {
                    //无法获取更新信息(可能断网等)
                    $this->returnJson['statusCode'] = '320001';
                }
            } catch (Exception $e) {
                //更新失败
                $this->returnJson['statusCode'] = '320003';
                $this->returnJson['errorMsg'] = $e->getMessage();
            }
        } else {
            //更新已被禁用
            $this->returnJson['statusCode'] = '320004';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 手动更新项目
     */
    public function manualUpdate()
    {
        ini_set("max_execution_time", 0);
        if (ALLOW_UPDATE) {
            try {
                $updateServer = new UpdateModule;
                if ($updateServer->manualUpdate()) {
                    $this->returnJson['statusCode'] = '000000';
                    if (!is_session_started()) {
                        session_start();
                    }
                    if (is_session_started()) {
                        session_destroy();
                    }
                } else {
                    //更新失败
                    $this->returnJson['statusCode'] = '320003';
                }
            } catch (\Exception $e) {
                $this->returnJson['statusCode'] = '320003';
                $this->returnJson['errorMsg'] = $e->getMessage();
            }
        } else {
            //更新已被禁用
            $this->returnJson['statusCode'] = '320004';
        }
        exitOutput($this->returnJson);
    }

}

?>
