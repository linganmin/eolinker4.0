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

class ImportController
{
    // 返回json类型
    private $returnJson = array('type' => 'import');

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
     * 导入eoapi数据
     */
    public function importEoapi()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (!$data) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        $result = $server->eoapiImport($data);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310005';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导入DHC数据
     */
    public function importDHC()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        if (!$data) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        $result = $server->importDHC($data);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310001';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导入postman数据
     */
    public function importPostman()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        $version = securelyInput('version');
        if (!$data) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        } elseif ($version != 1 and $version != 2) {
            $this->returnJson['statusCode'] = '310002';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        if ($version == 1) {
            $result = $server->importPostmanV1($data);
        } else {
            $result = $server->importPostmanV2($data);
        }
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310003';
        }
        exitOutput($this->returnJson);
    }

    /**
     * 导入swagger数据
     */
    public function importSwagger()
    {
        $data = quickInput('data');
        $json = json_decode($data, TRUE);
        if (empty($json)) {
            $this->returnJson['statusCode'] = '310004';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule;
        $result = $server->importSwagger($data);
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310001';
        }
        exitOutput($this->returnJson);
    }

    /**
     *导入RAP
     */
    public function importRAP()
    {
        $json = quickInput('data');
        $data = json_decode($json, TRUE);
        //判断数据是否为空
        if (empty($data['modelJSON'])) {
            $this->returnJson['statusCode'] = '310001';
            exitOutput($this->returnJson);
        }
        $model_json = json_decode(str_replace("\'", "'", $data['modelJSON']), TRUE);
        //以json格式解析modelJSON失败
        if (empty($model_json)) {
            $this->returnJson['statusCode'] = '310003';
            exitOutput($this->returnJson);
        }
        $server = new ImportModule();
        $result = $server->importRAP($model_json);
        //验证结果
        if ($result) {
            $this->returnJson['statusCode'] = '000000';
        } else {
            $this->returnJson['statusCode'] = '310000';
        }
        exitOutput($this->returnJson);
    }
}

?>