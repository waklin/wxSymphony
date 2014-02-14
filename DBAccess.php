<?php
	require_once("global.php");

	class DBAccess
	{
		private $_connect = false;

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
			$DB_DBNAME = 'symphony';

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
			if (!$this->_connect) {
				echo(sprintf("host=%s, userName=%s, password = %s<br/>", 
							$this->_host,
							$this->_userName,
							$this->_password));
				$this->_connect = mysql_connect($this->_host . ":" . $this->_port, $this->_userName, $this->_password);

				if (!$this->_connect){
					die(__FUNCTION__ . __LINE__ . mysql_error());
				}

				if (!mysql_select_db($this->_dbName, $this->_connect)){
					die(__FUNCTION__ . __LINE__ . mysql_error());
				}
			}

			return $this->_connect;
		}

		public function disConnect() {
			mysql_close($this->_connect);
			$this->_connect = false;
		}

		public function execSql($sql) {
			$this->connect();
			return mysql_query($sql, $this->_connect);
		}

	}
?>
