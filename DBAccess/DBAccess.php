<?php
	require_once("global.php");

	class DBAccess
	{
		private $_mysqli = false;

		private $_host;
		private $_port;
		private $_userName;
		private $_password;
		private $_dbName;
		/*
		 *public function __construct($srvName, $userName, $password)
		 *{
		 *    $this->_srvName = $srvName;
		 *    $this->_userName = $userName;
		 *    $this->_password = $password;
		 *}
		 */

		function __construct() 
		{
			$DB_HOST = 'localhost';
			$DB_PORT = 3306;
			$DB_USER = 'root';
			$DB_PWD = '811225';
			$DB_DBNAME = 'new_db';

			if (defined("DEPLOY_BAE")) 
			{
				//从环境变量里取出数据库连接需要的参数
				$DB_HOST = getenv('HTTP_BAE_ENV_ADDR_SQL_IP');
				$DB_PORT = getenv('HTTP_BAE_ENV_ADDR_SQL_PORT');
				$DB_USER = getenv('HTTP_BAE_ENV_AK');
				$DB_PWD = getenv('HTTP_BAE_ENV_SK');
				$DB_DBNAME = 'iLexxDbZHtwbJjXzheUF';
			}

			$this->_host = $DB_HOST;
			$this->_port = $DB_PORT;
			$this->_userName = $DB_USER;
			$this->_password = $DB_PWD;
			$this->_dbName = $DB_DBNAME;
		}

		public function connect() {
			if (!$this->_mysqli) {

				//echo(sprintf("host=%s, port=%s, userName=%s, password = %s, dbname=%s<br/>", 
							//$this->_host,
                            //$this->_port,
							//$this->_userName,
							//$this->_password,
                            //$this->_dbName));

				$this->_mysqli = new mysqli($this->_host,
					$this->_userName, 
					$this->_password, 
					$this->_dbName,
					$this->_port);

				if ($this->_mysqli->connect_error) {
					die(__FUNCTION__ . __LINE__ . $this->_mysqli->connect_error);
				}
				$this->_mysqli->query("SET NAMES 'UTF8'");
			}

			return $this->_mysqli;
		}

		public function disConnect() {
			$this->_mysqli->close();
		}

		public function execSql($sql) {
			$this->connect();
			return $this->_mysqli->query($sql);
		}
	}
?>
