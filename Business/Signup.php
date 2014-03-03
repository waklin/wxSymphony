<?php 
	require_once("global.php");
	require_once(DBACCESS_MODULE_PATH . "include.php");

	/**
	 * 关注/取消关注
	 */
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

		public function join($wxAppId, $time) {
            $sql = sprintf("INSERT INTO user(wxAppId, joinTime)"
                . " VALUES('%s', '%s')", 
                $wxAppId,
                $time
            );
            xDump($sql);

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
