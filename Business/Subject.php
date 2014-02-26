<?php
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

	//session_start();

	/**
	 * 
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
			$cmd = trim($textMsg->Content);
			$secondChr = substr($textMsg, 1, 1);

			$responseMsg = new TextMessage();
			$responseMsg->FromUserName = $textMsg->ToUserName;
			$responseMsg->ToUserName = $textMsg->FromUserName;
			if ($secondChr && $secondChr == "q") {
				$this->remove($textMsg->FromUserName);
				$responseMsg->Content = "quit track!";
			}
			else {
				$this->add($textMsg);
				$responseMsg->Content = "add track!";
			}
			$responseMsg->CreateTime = date('Y-m-d H:i:s', time());
			xDump($responseMsg, "responseMsg");
			return $responseMsg->generateContent();
		}

		/**
		 * 加入一条线路追踪信息
		 */
		public function add($textMsg) {
			$wxAppId = $textMsg->FromUserName;
			$sql = sprintf("select user.id, user.city_id from user"
				. " where user.wxAppId = '%s'",
				$wxAppId
			);

			xDump($sql, "sql");

			$result = $this->_execSql($sql);
			$userId = null;
			$cityId = null;
			if ($result) {
				$row = $result->fetch_assoc();
				xDump($row, "row");
				$userId = $row["id"];
				$cityId = $row["city_id"];

				$result->free();
			}

			$paramStr = substr($textMsg->Content, 1);
			xDump($paramStr, "paramStr");

			$array = $this->_parseCmdForAdd($userId, $paramStr);
			xDump($array, "array");

			if ($array) {
				if ($array[ROUTE] > 0) {
					$sql = sprintf("insert into track(city, user, route, pm)"
						. " values(%s, %s, %s, %s)",
						$cityId,
						$userId,
						$array[ROUTE],
						$array[PM]
					);
					xDump($sql, "sql");
					$this->_execSql($sql);
				}
			}
			else {
				;
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
		public function remove($wxAppId) {
			// body...
		}

		function _execSql($sql) {
			return $this->_dbAccess->execSql($sql);
		}

		/**
		 * 解析命令: 
		 * 以s开头的文本消息的内容
		 * 1. 如果s后为空，则从attention表中找到唯一的记录(如果不唯一则提示用户输入完整的s命令: s[线路名称])
		 * 2. 如果s后不为空，则假设s后的字符为线路名称
		 * param $paramStr 
		 * return route's id, pm
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

			xDump($sql,"sql");

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
						$array["route"] = $row["route.id"];
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

		/**
		 * 添加一条记录到track表
		 */
		function _generate($cityId, $userId, $routeId, $pm) {
			// body...
		}


		/**
		 * s [attention中的序号]
		 * s [1]
		 * 完善文本消息
		 */
		public function prepare($textMsg) {
			$wxAppId = $textMsg->FromUserName;
			$content = $textMsg->Content;

			$cmd = explode(" ", $content);
			$explicit = ($cmd.count() > 1);		// 指定了序号
			$attentionIndex = 0;
			if ($explicit) {
				$attentionIndex = intval($cmd[1]);
			}

			if (!isset($_SESSION[$wxAppId])) {
				$sql = sprintf("select user.id, user.city_id, from user, attention"
					. " where user.id = attention.user "
					. " and user.wxAppId = '%s'", $wxAppId);
				$result = $_dbAccess->execSql($sql);
				$num = $result->num_rows;
				if ($num <= 0) {
					// 返回没有配置用户线路的链接信息
				}
				else {
					$index = 0;
					while ($row = $result->fetch_assoc()) {
						if ($index == $attentionIndex) {
							$userId = $row["user.id"];
							$cityId = $row["user.city_id"];
							$routeId = $row["attention.route"];
							$pm = $row["attention.pm_morning"];
							$now = date("Y-m-d H:i:s", time());
							$this->track($cityId, $userId, $routeId, $pm, $now);
							break;
						}
					}
					$result->free();
				}

				$_SESSION[$textMsg->FromUserName] = true;
			}
		}

		/**
		 * 加入跟踪信息到track表中
		 */
		public function track1($cityId, $userId, $trackId, $pm, $lastUpdate) {
			// 线路名称，上行/下行
			// 加入到track表中
			// 创建一个session维护subject信息，
			$sql = sprintf("insert into track(city,user,state,lastupdate,pm)
				values(%d,%d,%d,'%s',%d)",
					$cityId,
					$userId,
					$trackId,
					$lastUpdate,
					$pm);

			$this->_dbAccess.execSql($sql);
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
