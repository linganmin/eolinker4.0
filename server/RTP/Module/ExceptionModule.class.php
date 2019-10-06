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
class ExceptionModule extends \Exception
{

	/**
	 * 构造方法，传递错误信息以及错误码
	 */
	public function __construct($code = 10000, $info)
	{
		//如果记录文件夹不存在则新建
		if (!file_exists('./log/'))
		{
			//如果新建失败则抛出异常，可能权限不足
			if (!mkdir('./log/'))
			{
				//此处不用ExceptionModule进行异常抛出，因为如果权限不足，此异常会无限抛出进入死循环
				throw new \Exception("can not create directory,please check you app's root file system authorization", 13001);
			}
		}
		parent::__construct($info, $code);
	}

	public function printError($isStop = FALSE)
	{
		//如果非调试模式，则取消所有的错误输出
		if (!DEBUG)
		{
			$infoJson = array('errorCode' => $this -> getCode());

			//输出json
			echo json_encode($infoJson);

			$infoJson = array(
				'datetime' => date('Y/M/d H:i:s', time()),
				'errorCode' => $this -> getCode(),
				'info' => $this -> getMessage(),
				'wrongFile' => $this -> getFile(),
				'wrongLine' => $this -> getLine()
			);

			//将错误信息记录到文件
			$logInfo = "{$infoJson['datetime']}=>[code:{$infoJson['errorCode']};info:{$infoJson['info']};wrongFile:{$infoJson['wrongFile']};wrongLine:{$infoJson['wrongLine']}];\n";
			file_put_contents('./log/' . date('Y_M_d', time()) . '.txt', $logInfo, FILE_APPEND);
		}
		else
		{
			$infoJson = array(
				'datetime' => date('Y/M/d H:i:s', time()),
				'errorCode' => $this -> getCode(),
				'info' => $this -> getMessage(),
				'wrongFile' => $this -> getFile(),
				'wrongLine' => $this -> getLine()
			);

			//输出自然语言
			printFormatted($infoJson);

			$logInfo = "{$infoJson['datetime']}=>[code:{$infoJson['errorCode']};info:{$infoJson['info']};wrongFile:{$infoJson['wrongFile']};wrongLine:{$infoJson['wrongLine']}];\n";
			file_put_contents('./log/' . date('Y_M_d', time()) . '.txt', $logInfo, FILE_APPEND);
		}

		if ($isStop)
			exit ;
	}

}
?>