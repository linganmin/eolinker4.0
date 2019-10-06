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

class DatabaseModule
{
	private static $instance;
	private static $db_con;
	private $db_history;
	//上一次操作结果
	private $last_result;
	private $last_sql;

	/**
	 * 获取实例
	 */
	public static function getInstance()
	{
		//如果已经含有一个实例则直接返回实例
		if (!is_null(self::$instance))
		{
			return self::$instance;
		}
		else
		{
			//如果没有实例则新建
			return self::getNewInstance();
		}
	}

	/**
	 * 获取一个新的实例
	 */
	public static function getNewInstance()
	{
		self::$instance = null;
		self::$instance = new self;
		return self::$instance;
	}

	/**
	 * 创建对象时自动连接数据库
	 */
	protected function __construct()
	{
		self::connect();
	}

	/**
	 * 销毁对象时自动断开数据库连接
	 */
	function __destruct()
	{
		self::close();
	}

	/**
	 * 连接主机
	 */
	private function connect()
	{
		$conInfo = DB_TYPE . ':host=' . DB_URL . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8';

		//是否保持持久化链接
		if (DB_PERSISTENT_CONNECTION)
		{
			$option = array(
				\PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
				\PDO::ATTR_PERSISTENT => TRUE,
				\PDO::ATTR_EMULATE_PREPARES => FALSE,
                \PDO::ATTR_STRINGIFY_FETCHES => FALSE
			);
		}
		else
		{
			$option = array(
				\PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
				\PDO::ATTR_EMULATE_PREPARES => FALSE,
                \PDO::ATTR_STRINGIFY_FETCHES => FALSE
			);
		}

		//尝试连接数据库
		try
		{
			self::$db_con = new \PDO($conInfo, DB_USER, DB_PASSWORD, $option);

		}
		catch(\PDOException $e)
		{
			if (DEBUG)
				print_r($e -> getMessage());
			exit ;
		}
	}

	/**
	 * 关闭主机连接
	 */
	public function close()
	{
		self::$db_con = NULL;
		self::$instance = NULL;
	}

	/**
	 * 执行无返回值的数据库操作并且返回受影响的记录条数
	 */
	public function execute($sql)
	{
		$this -> last_sql = $sql;
		$result = self::$db_con -> exec($sql);
		$this -> getError();
		return $result;
	}

	/**
	 * 执行操作并返回一条数据
	 */
	public function query($sql)
	{
		$this -> last_sql = $sql;
		$this -> db_history = self::$db_con -> query($sql);
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetch(\PDO::FETCH_ASSOC);
		return $this -> last_result;
	}

	/**
	 * 执行操作并返回多条数据(如果可能)
	 */
	public function queryAll($sql)
	{
		$this -> last_sql = $sql;
		$this -> db_history = self::$db_con -> query($sql);
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetchAll(\PDO::FETCH_ASSOC);
		return $this -> last_result;
	}

	/**
	 * prepare方式执行操作，返回一条数据，防止sql注入
	 */
	public function prepareExecute($sql, $params = NULL)
	{
		$this -> last_sql = $sql;
		$this -> db_history = self::$db_con -> prepare($sql);
		$this -> getError();
		if (is_null($params))
		{
			$this -> db_history -> execute();
		}
		else
		{
			$this -> db_history -> execute($params);
		}
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetch(\PDO::FETCH_ASSOC);

		return $this -> last_result;
	}

	/**
	 * prepare方式执行操作，返回多条数据（如果可能），防止sql注入
	 */
	public function prepareExecuteAll($sql, $params = NULL)
	{
		$this -> last_sql = $sql;
		$this -> db_history = self::$db_con -> prepare($sql);
		$this -> getError();
		if (is_null($params))
		{
			$this -> db_history -> execute();
		}
		else
		{
			$this -> db_history -> execute($params);
		}
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetchAll(\PDO::FETCH_ASSOC);

		return $this -> last_result;
	}

	/**
	 * prepare方式，以新的参数重新执行一次查询，返回一条数据
	 */
	public function prepareRexecute($params)
	{
		$this -> db_history -> execute($params);
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetch(\PDO::FETCH_ASSOC);
		return $this -> last_result;
	}

	/**
	 * prepare方式，以新的参数重新执行一次查询，返回多条数据（如果可能）
	 */
	public function prepareRexecuteAll($params)
	{
		$this -> db_history -> execute($params);
		$this -> getError();
		$this -> last_result = $this -> db_history -> fetchAll(\PDO::FETCH_ASSOC);
		return $this -> last_result;
	}

	/**
	 * 获取上一次操作影响的行数
	 */
	public function getAffectRow()
	{
		if (is_null($this -> db_history))
		{
			return 0;
		}
		else
		{
			return $this -> db_history -> rowCount();
		}
	}

	/**
	 * 获取最后执行的SQL语句
	 */
	public function getLastSQL()
	{
		return $this -> last_sql;
	}

	/**
	 * 获取最后插入行的ID或序列值
	 */
	public function getLastInsertID()
	{
		return self::$db_con -> lastInsertId();
	}

	/**
	 * 获取错误信息
	 */
	public function getError()
	{
		$result = self::$db_con -> errorInfo();
		if (DEBUG)
		{
			if ($result[0] != 00000)
			{
				$error = json_encode(self::$db_con -> errorInfo());
				throw new ExceptionModule(12000, "database error in:$error");
			}
		}
		else
		{
			if ($result[0] != 00000)
			{
				return FALSE;
			}
			else
				return TRUE;
		}
	}

	/**
	 * 开始事务
	 */
	public function beginTransaction()
	{
		self::$db_con -> beginTransaction();
	}

	/**
	 * 回滚事务
	 */
	public function rollback()
	{
		self::$db_con -> rollback();
	}

	/**
	 * 提交事务
	 */
	public function commit()
	{
		self::$db_con -> commit();
	}

}
?>
