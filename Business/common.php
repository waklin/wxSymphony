<?php
	require_once("global.php");
	require_once(DBACCESS_MODULE_PATH . "include.php");

	
	/**
	 * 
	 */
	class BusinessCommand
	{
		private static $_dbAccess = null;
		static function _getDBAccess() {
			if (self::$_dbAccess == null) {
				self::$_dbAccess = new DBAccess();
			}
			return self::$_dbAccess;
		}

		public static function ExecSql($sql) {
			$db = self::_getDBAccess();
			return $db->execSql($sql);
		}

		public static function GetUserInfo($wxAppId) {
			$ret = array();

			$sql = sprintf("select user.id, user.city from user"
				. " where user.wxAppId = '%s'",
				$wxAppId
			);
			xDump($sql);

			$result = self::ExecSql($sql);
			if ($result && $result->num_rows > 0) {
				$row = $result->fetch_assoc();
				xDump($row);
				$ret["id"] = $row["id"];
				$ret["city"] = $row["city"];
				$result->free();
			}

			return $ret;
		}
	}
?>
