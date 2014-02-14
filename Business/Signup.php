<?php 
	require_once("DBAccess.php");

	class Signup
	{
		private $_dbAccess;

		function __construct()
		{
			$this->_dbAccess = new DBAccess();
		}

		private function _execSql($sql) {
			return $this->_dbAccess->execSql($sql);
		}

		public function join($wxAppId) {
			$sql = sprintf("INSERT INTO user(wxAppId) VALUES('%s')", $wxAppId);
			$ret = $this->_execSql($sql);
			if (!$ret) {
				die(__FUNCTION__  . mysql_error());
			}
		}

		public function leave($wxAppId) {
			$sql = sprintf("DELETE FROM user WHERE wxAppId='%s'", $wxAppId);
			$ret = $this->_execSql($sql);
			if (!$ret) {
				die(__FUNCTION__  . mysql_error());
			}
		}
	}
?>
