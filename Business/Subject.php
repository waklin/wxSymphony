<?php
	$result = session_start();
	xDump($result);
	$_SESSION['favcolor'] = 'green';
	xDump($_SESSION['favcolor']);

	require_once("global.php");
	require_once("DBAccess.php");

	/**
	 * 变量解释
	 * route: 	线路
	 * pm:		线路的方向 1-始发站to终点站 2-终点站to始发站 3-环线
	 */
	define("ROUTE", "route");
	define("PM", "pm");

	/**
	 * 指令详解
	 * s[线路名称] 追踪指定线路
	 * s 追踪唯attention中唯一的线路
	 * s[q]	退出追踪
	 */

	class SubjectResult
	{
		const UNKNOWN_USER = 1;

	   	const ADD_NOATTENTION = 11;
		const ADD_MULTIATTENTION = 12;
		const ADD_EXSITTRACK = 13;
		const ADD_SUCCESSFUL = 20;

		const REMOVE_NOTRACK = 21;
		const REMOVE_SUCCESSFUL = 30;

		public static function ToString($result) {
			$str = null;

			switch ($result)
			{
			case self::ADD_NOATTENTION:
				$str = "no attention route.";
				break;
			case self::ADD_MULTIATTENTION:
				$str = "so much attention.";
				break;
			case self::ADD_SUCCESSFUL:
				$str = "add track successful.";
				break;
			case self::ADD_EXSITTRACK:
				$str = "you have a track already, please send [sq] to quit.";
				break;
			case self::UNKNOWN_USER:
				$str = "unknown user.";
				break;
			case self::REMOVE_SUCCESSFUL:
				$str = "remove track successful.";
				break;
			case self::REMOVE_NOTRACK:
				$str = "you have not track any route.";
				break;
			default:
				$str = "unknown result.";
				break;
			}
			return $str;
		}
	}

	/**
	 * 线路追踪类
	 */
	class Subject
	{
		private $_dbAccess;

		public function __construct()
		{
			$this->_dbAccess = new DBAccess();
		}

		/**
		 * 处理文本消息
		 * 如果s后跟着q，那么调用remove，否则调用remove
		 */
		public function perform($textMsg) {
			$responseMsg = new TextMessage();
			$responseMsg->FromUserName = $textMsg->ToUserName;
			$responseMsg->ToUserName = $textMsg->FromUserName;

			$wxAppId = $textMsg->FromUserName;
			$sql = sprintf("select user.id, user.city_id from user"
				. " where user.wxAppId = '%s'",
				$wxAppId
			);
			xDump($sql);			
			$result = $this->_execSql($sql);
			$userId = null;
			$cityId = null;
			if ($result) {
				$row = $result->fetch_assoc();
				xDump($row);
				$userId = $row["id"];
				$cityId = $row["city_id"];

				$result->free();
			}

			if (!isset($userId)) {
				$responseMsg->Content = SubjectResult::ToString(SubjectResult::UNKNOWN_USER);
			}
			else {
				$cmd = trim($textMsg->Content);
				$secondChr = substr($cmd, 1, 1);
				xDump($secondChr);

				if ($secondChr && $secondChr == "q") {
					$result = $this->remove($wxAppId, $userId);
				}
				else {
					$result = $this->add($wxAppId, $userId, $cityId, $textMsg->Content);
				}
				$responseMsg->Content = SubjectResult::ToString($result);
			}

			$responseMsg->CreateTime = date('Y-m-d H:i:s', time());
			xDump($responseMsg);

			return $responseMsg->generateContent();
		}

		/**
		 * 添加一条记录到track表
		 */
		function _insertTrack($cityId, $userId, $routeId, $pm) {
			$sql = sprintf("insert into track(city, user, route, pm)"
				. " values(%s, %s, %s, %s)",
				$cityId,
				$userId,
				$routeId,
				$pm
			);
			xDump($sql);
			return $this->_execSql($sql);
		}

		/**
		 * 加入一条线路追踪信息
		 */
		public function add($wxAppId, $userId, $cityId, $cmd) {
			xDump($_SESSION[$wxAppId]);
			if (isset($_SESSION[$wxAppId])) {
				return SubjectResult::ADD_EXSITTRACK;
			}

			$paramStr = substr($cmd, 1);
			xDump($paramStr);
			$array = $this->_parseCmdForAdd($userId, $paramStr);
			xDump($array);

			if ($array) {
				if ($array[ROUTE] > 0) {
					if ($this->_insertTrack($cityId, $userId, $array[ROUTE], $array[PM])){
						$_SESSION[$wxAppId] = $array[ROUTE];
						return SubjectResult::ADD_SUCCESSFUL;
					}
				}
				else if ($array[ROUTE] == 0) {
					return SubjectResult::ADD_NOATTENTION;
				}
				else {
					return SubjectResult::ADD_MULTIATTENTION;
				}
			}
			else {
				return SubjectResult::ADD_NOATTENTION;
			}
		}

		/**
		 * 更新线路的站点
		 */
		public function track($locationMsg) {
			// body...
		}

		/**
		 * 删除一条线路追踪信息
		 */
		function remove($wxAppId, $userId) {
			if (!isset($_SESSION[$wxAppId])) {
				return SubjectResult::REMOVE_NOTRACK;
			}

			$sql = sprintf("delete from track"
				. " where user = '%s'",
				$userId
			);
			xDump($sql);

			if ($this->_execSql($sql)) {
				unset($_SESSION[$wxAppId]);
				return SubjectResult::REMOVE_SUCCESSFUL;
			}
		}

		function _execSql($sql) {
			return $this->_dbAccess->execSql($sql);
		}

		/**
		 * 解析命令: 
		 * 以s开头的文本消息的内容
		 * 1. 如果s后为空，则从attention表中找到唯一的记录(如果不唯一则提示用户输入完整的s命令: s[线路名称])
		 * 2. 如果s后不为空，则假设s后的字符为线路名称
		 *
		 * param 
		 *   $paramStr s后的字符串
		 *
		 * return 
		 *   1. 数组[route, pm] 
		 *     route -1:多条关注线路 0:没有找到指定的线路 >0:线路的id
		 *     pm 方向
		 *   2. null 没有关注任何线路
		 */
		function _parseCmdForAdd($userId, $paramStr) {
			$array = array(
				ROUTE => 0,
				PM => 1
			);

			$lineName = null;
			if (strlen($paramStr) > 0) {
				$lineName = trim($paramStr);
			}

			$sql = sprintf("select route.id, route.linename"
				. " from route, attention, user"
				. " where user.id = attention.user"
				. " and attention.route = route.id"
				. " and user.id = %s",
				$userId
			);

			xDump($sql);

			$result = $this->_execSql($sql);
			$num = $result->num_rows;
			if ($num > 0) {
				if (isset($lineName)) {		// s [线路名称]
					$matchTimes = 0;
					while ($row=$result->fetch_assoc()) {
						$match = strstr($row["linename"], $lineName);
						if ($match) {
							$array[ROUTE] = $row["id"];
							$matchTimes++;
						}
					}

					if ($matchTimes == 0) {		// 没有匹配到关注线路
						$array[ROUTE] = 0;
					}
					else if ($matchTimes > 1) {	// 匹配到多个相似的线路
						$array[ROUTE] = -1;
					}
				}
				else {	// s
					if ($num == 1) {
						$row=$result->fetch_assoc();
						$array["route"] = $row["id"];
					}
					else {
						$array["route"] = -1;
					}
				}
				$result->free();
			}
			else {
				// 没有追踪任何线路
				return null;
			}

			return $array;
		}

		public function untrack($textMsg) {
			// 从track表中删除用户的track记录
		}

		public function updateLocation($locationMsg) {
			// 根据locationMsg寻找指定的站点，并更新track表
			// 更新成功提示成功更新站点信息
			// 失败提示更新站点失败的原因(未找到此站点)
		}
	}
?>
