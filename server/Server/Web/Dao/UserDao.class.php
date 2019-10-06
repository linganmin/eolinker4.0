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

class UserDao
{
    /**
     * 修改密码
     * @param $hashPassword string 新密码
     * @param $userID int 用户ID
     * @return bool
     */
    public function changePassword($hashPassword, $userID)
    {
        $db = getDatabase();

        $db->prepareExecute('UPDATE eo_user SET eo_user.userPassword =? WHERE eo_user.userID = ?;', array(
            $hashPassword,
            $userID
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 修改昵称
     * @param $userID int 用户ID
     * @param $nickName string 昵称
     * @return bool
     */
    public function changeNickName(&$userID, &$nickName)
    {
        $db = getDatabase();
        $db->prepareExecute('UPDATE eo_user SET eo_user.userNickName =? WHERE eo_user.userID = ?;', array(
            $nickName,
            $userID
        ));

        if ($db->getAffectRow() < 1)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 检查用户是否存在
     * @param $userName 用户名
     * @return bool|array
     */
    public function checkUserExist(&$userName)
    {
        $db = getDatabase();
        $result = $db->prepareExecute('SELECT eo_user.userID,eo_user.userNickName FROM eo_user WHERE eo_user.userName = ?;', array($userName));

        if (empty($result))
            return FALSE;
        else
            return $result;
    }

}

?>