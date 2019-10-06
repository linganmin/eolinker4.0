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

class UpdateModule
{
    /**
     * 自动更新项目
     * @param $updateURI string 更新地址
     * @return bool
     * @throws Exception
     */
    public function autoUpdate($updateURI)
    {
        try {
            set_error_handler('err_handler');
            $ch = curl_init($updateURI);
            //跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            //检查证书中是否设置域名
            @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            file_put_contents('../release.zip', curl_exec($ch));
            curl_close($ch);
            $zip = new ZipArchive;
            if ($zip->open('../release.zip'))
                $zip->extractTo('../');
            $zip->close();

            //备份数据库
            $backupDao = new BackupDao();
            $sql = $backupDao->getDatabaseBackupSql();
            $file_name = "eoLinker_backup_database_" . time() . '.sql';
            if (!file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $file_name, $sql)) {
                return FALSE;
            }
            //接下来开始获取旧数据库的全部结构
            $updateDao = new UpdateDao;
            $updateDao->updateDatabase();

            //执行额外的更新操作，主要用于在版本过渡的过程中，数据以及文件发生变化等情况
            if (file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'Common/UpdateFunction.php'))
                quickRequire(PATH_FW . DIRECTORY_SEPARATOR . 'Common/UpdateFunction.php');

            return TRUE;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 100001);
        }

    }

    /**
     * 手动更新项目
     * @return bool
     * @throws Exception
     */
    public function manualUpdate()
    {
        try {
            //备份数据库
            $backupDao = new BackupDao();
            $sql = $backupDao->getDatabaseBackupSql();
            $file_name = "eoLinker_backup_database_" . time() . '.sql';
            if (!file_put_contents(realpath('./dump') . DIRECTORY_SEPARATOR . $file_name, $sql)) {
                return FALSE;
            }
            //接下来开始获取旧数据库的全部结构
            $updateDao = new UpdateDao;
            $updateDao->updateDatabase();

            //执行额外的更新操作，主要用于在版本过渡的过程中，数据以及文件发生变化等情况
            if (file_exists(PATH_FW . DIRECTORY_SEPARATOR . 'Common/UpdateFunction.php'))
                quickRequire(PATH_FW . DIRECTORY_SEPARATOR . 'Common/UpdateFunction.php');

            return TRUE;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 100001);
        }
    }

}

?>