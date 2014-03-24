<?php
	require_once("global.php");
	require_once(DBACCESS_MODULE_PATH . "include.php");

	define("SPLITLINE", "----------------\n");
	define("DOWNARROW", "        ↓\n");
	
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

		public static function GetLastInsertId() {
			$sql = "SELECT LAST_INSERT_ID()";
			$result = BusinessCommand::ExecSql($sql);
			$row = $result->fetch_row();
			return $row[0];
		}

		/**
		 * 关注线路时，关注成功后，获取出发->达到的信息
		 * 跟踪线路时，根据当前时间，调整pm（往返）/route（环线），跟踪成功后，获取出发->到达的信息
		 */
		public static function FetchAttentionInfo($attention, $time) {
			$sql = sprintf("select route.linetime, route.linename, attention.route, attention.pm_morning, attention.route_opp"
				. " from attention, route"
				. " where attention.route = route.id"
				. " and attention.id = %s",
				$attention
			);
			$result = self::ExecSql($sql);

			$row = $result->fetch_assoc();
			$pm = $row["pm_morning"];
			$linetime = $row["linetime"];
			$linename = $row["linename"];
			$route = $row["route"];

			$time = strtotime($time);
			$h = intval(date("H", $time));

			if ($h >= 12) {		// 过了中午12点，则取返程的信息
				if ($pm == 3) {	// 环线
					$route = $row["route_opp"];
					$sql = sprintf("select route.linetime, route.linename"
						. " from route"
						. " where route.id = %s",
						$route
					);
					$result = self::ExecSql($sql);
					$row = $result->fetch_assoc();

					$linetime = $row["linetime"];
					$linename = $row["linename"];
				}
				else if ($pm == 1) {
					$pm = 2;
				}
				else if ($pm == 2) {
					$pm = 1;
				}
			}

			xDump($pm);
			if ($pm == 3) {
				$departure = $linetime;
				$arrival = $linetime;
			}
			else {
				$split = explode("|", $linetime);
				if ($pm == 1) {
					$departure = $split[0];
					$arrival = $split[1];
				}
				else if ($pm == 2) {
					$departure = $split[1];
					$arrival = $split[0];
				}
			}

			return array(
				"route" => $route,
				"linename" => $linename,
				"pm" => $pm,
				"departure" => $departure,
				"arrival" => $arrival,
			);
		}
	}
?>
