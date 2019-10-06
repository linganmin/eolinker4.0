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


namespace RTP\Module;

class FileModule
{
	/**
	 * 自动在用户目录下面创建空白index.html文件，用于保护文件目录
	 */
	public static function createSecurityIndex()
	{
		$path = PATH_APP;
		$dirs = array();
		$ban_dirs = array(
			'./',
			'.',
			'../',
			'..'
		);
		self::getAllDirs($path, $dirs, $ban_dirs);

		foreach ($dirs as $dir)
		{
			if (file_exists($dir . '/index.html') || file_exists($dir . '/index.php'))
				continue;
			else
			{
				$file = fopen($dir . '/index.html', 'w');
				fwrite($file, '');
				fclose($file);
			}
		}
	}

	/**
	 * 获取路径下的所有目录
	 * @param String $path 目标路径
	 * @param array $dirs 用于储存返回路径的数组
	 * @param array $ban_dirs [可选]需要过滤的目录的相对地址的数组
	 */
	public static function getAllDirs($path, array &$dirs, array &$ban_dirs = array())
	{
		$paths = scandir($path);
		foreach ($paths as $nextPath)
		{
			if (!in_array($nextPath, $ban_dirs) && is_dir($path . DIRECTORY_SEPARATOR . $nextPath))
			{
				$dirs[] = realpath($path . DIRECTORY_SEPARATOR . urlencode($nextPath));
				self::getAllDirs($path . DIRECTORY_SEPARATOR . $nextPath, $dirs, $ban_dirs);
			}
		}
	}

	/**
	 * 获取路径下的所有文件
	 * @param String $path 目标路径
	 * @param array $dirs 用于储存返回路径的数组
	 * @param array $ban_dirs [可选]需要过滤的文件名的数组
	 */
	public static function getAllFiles($path, &$dirs, &$ban_dirs = array())
	{
		$paths = scandir($path);
		foreach ($paths as $nextPath)
		{
			if (!in_array($nextPath, $ban_dirs) && is_file($path . DIRECTORY_SEPARATOR . $nextPath))
			{
				$dirs[] = realpath($path . DIRECTORY_SEPARATOR . urlencode($nextPath));
				self::getAllFiles($path . DIRECTORY_SEPARATOR . $nextPath, $dirs, $ban_dirs);
			}
		}
	}

}
?>