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

class DatabaseTableFieldController
{
    //返回Json类型
    private $returnJson = array('type' => 'database_table_field');

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
     * 添加字段
     */
    public function addField()
    {
        $tableID = securelyInput('tableID');
        $module = new DatabaseTableModule();
        $userType = $module->getUserType($tableID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $nameLen = mb_strlen(quickInput('fieldName', 'utf8'));
        $fieldName = securelyInput('fieldName');
        $typeLen = mb_strlen(quickInput('fieldType', 'utf8'));
        $fieldType = securelyInput('fieldType');
        $fieldLength = securelyInput('fieldLength');
        $isNotNull = securelyInput('isNotNull');
        $isPrimaryKey = securelyInput('isPrimaryKey');
        $descLen = mb_strlen(quickInput('fieldDescription', 'utf8'));
        $fieldDescription = securelyInput('fieldDescription');
        $fieldDefaultValue = securelyInput('defaultValue');

        if (!preg_match('/^[0-9]{1,11}$/', $tableID)) {
            $this->returnJson['statusCode'] = '240001';
        } elseif (!($nameLen >= 1 && $nameLen <= 255)) {
            // 字段名长度不合法
            $this->returnJson['statusCode'] = '240002';
        } elseif (!($typeLen >= 1 && $typeLen <= 255)) {
            $this->returnJson['statusCode'] = '240003';
        } elseif (!preg_match('/^[0-9]{1}$/', $isNotNull) || !preg_match('/^[0-9]{1}$/', $isPrimaryKey)) {
            $this->returnJson['statusCode'] = '240004';
        } elseif (!($descLen >= 0 && $descLen <= 255)) {
            // 字段描述长度不合法
            $this->returnJson['statusCode'] = '240005';
        } else {
            $service = new DatabaseTableFieldModule;
            $result = $service->addField($tableID, $fieldName, $fieldType, $fieldLength, $isNotNull, $isPrimaryKey, $fieldDescription, $fieldDefaultValue);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['fieldID'] = $result;
            } else {
                $this->returnJson['statusCode'] = '240006';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 删除字段
     */
    public function deleteField()
    {
        $fieldID = securelyInput('fieldID');
        $module = new DatabaseTableFieldModule();
        $userType = $module->getUserType($fieldID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        if (!preg_match('/^[0-9]{1,11}$/', $fieldID)) {
            $this->returnJson['statusCode'] = '240007';
        } else {
            $service = new DatabaseTableFieldModule;
            $result = $service->deleteField($fieldID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '240008';
            }
        }
        exitOutput($this->returnJson);

    }

    /**
     * 获取字段列表
     */
    public function getField()
    {
        $tableID = securelyInput('tableID');

        if (!preg_match('/^[0-9]{1,11}$/', $tableID)) {
            $this->returnJson['statusCode'] = '240001';
        } else {
            $service = new DatabaseTableFieldModule;
            $result = $service->getField($tableID);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
                $this->returnJson['fieldList'] = $result;
            } else {
                $this->returnJson['statusCode'] = '240009';
            }
        }
        exitOutput($this->returnJson);
    }

    /**
     * 修改字段
     */
    public function editField()
    {
        $fieldID = securelyInput('fieldID');
        $module = new DatabaseTableFieldModule();
        $userType = $module->getUserType($fieldID);
        if ($userType < 0 || $userType > 2) {
            $this->returnJson['statusCode'] = '120007';
            exitOutput($this->returnJson);
        }
        $nameLen = mb_strlen(quickInput('fieldName', 'utf8'));
        $fieldName = securelyInput('fieldName');
        $typeLen = mb_strlen(quickInput('fieldType', 'utf8'));
        $fieldType = securelyInput('fieldType');
        $fieldLength = securelyInput('fieldLength');
        $isNotNull = securelyInput('isNotNull');
        $isPrimaryKey = securelyInput('isPrimaryKey');
        $descLen = mb_strlen(quickInput('fieldDescription', 'utf8'));
        $fieldDescription = securelyInput('fieldDescription');
        $fieldDefaultValue = securelyInput('defaultValue');

        if (!preg_match('/^[0-9]{1,11}$/', $fieldID)) {
            $this->returnJson['statusCode'] = '240007';
        } elseif (!($nameLen >= 1 && $nameLen <= 255)) {
            // 字段名长度不合法
            $this->returnJson['statusCode'] = '240002';
        } elseif (!($typeLen >= 1 && $typeLen < 255)) {
            $this->returnJson['statusCode'] = '240003';
        } elseif (!preg_match('/^[0-9]{1}$/', $isNotNull) || !preg_match('/^[0-9]{1}$/', $isPrimaryKey)) {
            $this->returnJson['statusCode'] = '240004';
        } elseif (!($descLen >= 0 && $descLen <= 255)) {
            // 字段描述长度不合法
            $this->returnJson['statusCode'] = '240005';
        } else {
            $service = new DatabaseTableFieldModule;
            $result = $service->editField($fieldID, $fieldName, $fieldType, $fieldLength, $isNotNull, $isPrimaryKey, $fieldDescription, $fieldDefaultValue);

            if ($result) {
                $this->returnJson['statusCode'] = '000000';
            } else {
                $this->returnJson['statusCode'] = '240010';
            }
        }
        exitOutput($this->returnJson);

    }

}

?>