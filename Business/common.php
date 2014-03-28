<?php
	require_once("global.php");
	require_once(DBACCESS_MODULE_PATH . "include.php");

	define("SPLITLINE", "--------------------------\n");
	define("RIGHTARROW", "→");
	
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
		 * 查询线路信息
	     * return: array["route", "linename", "linetime"]
		 */
		public static function QueryLine($cityId, $lineName) {
			if (ctype_digit($lineName)) {
				$sql = sprintf("select id, linename,linetime from route where number = %s and city = %s", 
					$lineName, $cityId);
			}
			else {
				$sql = sprintf("select id, linename,linetime from route where linename like '%%%s%%' and city=%s", 
					$lineName, $cityId);
			}

			$lines = array();
			$result = BusinessCommand::ExecSql($sql);
			while ($row = $result->fetch_assoc()) {
				$line = array(
					"id" => $row["id"],
					"linename" => $row["linename"],
					"linetime" => $row["linetime"],
					"circle" => !(strpos($row["linetime"], "|")),
				);
				$lines[] = $line;
			}

			return $lines;
		}

		/**
		 * 获取关注线路的信息 
		 * param $attention attention's id
		 * param $time 时间
		 * return array["route", "linename", "pm", "departure", "arrival"]
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
