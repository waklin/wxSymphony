<?php
	require_once("global.php");
	require_once(DBACCESS_MODULE_PATH . "include.php");
	require_once(BUSINESS_MODULE_PATH. "common.php");

	/**
	 * 变量解释
	 * route: 	线路
	 * pm:		线路的方向 1-始发站to终点站 2-终点站to始发站 3-环线
	 */
	define("ROUTE", "route");
	define("PM", "pm");

    /**
     * 
     */
    class SessionItem
    {
        public $_wxAppId;
        public $_generateTime;
        public function __construct($wxAppId)
        {
            $this->_wxAppId = $wxAppId;
            $this->_generateTime = time();
        }
    }

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
			case self::ADD_EXSITTRACK:
				$str = "you have a track already, please send [sq] to quit.";
				break;
			case self::UNKNOWN_USER:
				$str = "unknown user.";
				break;
			case self::REMOVE_SUCCESSFUL:
				$str = "你已经退出跟踪的线路，祝你工作生活愉快。";
				break;
			case self::REMOVE_NOTRACK:
				$str = "你还没有跟踪任何线路。";
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
	 *
	 * 追踪: {s [线路名称|q]} 
	 *			[线路名称]:	追踪指定的线路, 没有指定线路意味着追踪attention中唯一的线路
	 *			[q]:		退出追踪
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
		 * 如果s后跟着q，那么调用remove，否则调用add
		 */
		public function perform($textMsg) {
			$responseMsg = new TextMessage();
			$responseMsg->FromUserName = $textMsg->ToUserName;
			$responseMsg->ToUserName = $textMsg->FromUserName;

			$wxAppId = $textMsg->FromUserName;
			$userInfo = BusinessCommand::GetUserInfo($wxAppId);
			$userId = $userInfo["id"];
			$cityId = $userInfo["city"];

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
					$result = $this->add($wxAppId, $userId, $cityId, $textMsg->CreateTime, $textMsg->Content);
				}
				if (is_string($result)) {
					$responseMsg->Content = $result;
				}
				else{
					$responseMsg->Content = SubjectResult::ToString($result);
				}
			}

			$responseMsg->CreateTime = date('Y-m-d H:i:s', time());
			return $responseMsg->generateContent();
		}

		/**
		 * 添加一条记录到track表
		 */
		function _insertTrack($cityId, $userId, $routeId, $pm, $time) {
			$sql = sprintf("insert into track(city, user, route, pm, generateTime)"
				. " values(%s, %s, %s, %s, '%s')",
				$cityId,
				$userId,
				$routeId,
                $pm,
                $time
			);
			return BusinessCommand::ExecSql($sql);
		}

		/**
			* 用户是否追踪线路
			* return false/['trackId', 'routeId', "linename"]
		 */
        public function _isTracking($userId) {
			$ret = false;
			$sql = sprintf("select track.id, track.route, route.linename"
			    . " from track,route"
				. " where track.route = route.id"
                . " and user = %s",
                $userId
            );

            $result = $this->_execSql($sql);
            if ($result) {
                if ($result->num_rows > 0){
					$row = $result->fetch_assoc();

					$ret = array(
						"track" => $row["id"],
						"route" => $row["route"],
						"linename" => $row["linename"],
					);
				}
            }

            return $ret;
        }

		/**
		 * 加入一条线路追踪信息
		 */
		public function add($wxAppId, $userId, $cityId, $time, $cmd) {
			$trackInfo = $this->_isTracking($userId);
            if ($trackInfo) {
				$content = sprintf("你正在跟踪%s\n"
					. "请先发送文字消息 [sq] 退出当前跟踪的线路。",
					$trackInfo["linename"]
				);

				return $content;
			}

			$paramStr = trim(substr($cmd, 1));
			$attentions = $this->_parseCmdForAdd($userId, $paramStr);

			if (!empty($attentions)) {
				if (count($attentions) == 1) {
					$attentionInfo = BusinessCommand::FetchAttentionInfo($attentions[0], $time);
					if ($this->_insertTrack($cityId, $userId, $attentionInfo["route"], $attentionInfo["pm"], $time)){
						$content = sprintf("感谢你参与跟踪%s\n"
							. "[%s]" . RIGHTARROW . "[%s]\n"
							. "现在你可以发送位置消息，上报抵达的站点(本订阅号暂不支持自动获取用户位置，每抵达一个站点都需要你发送位置信息)。",

							$attentionInfo["linename"],
							$attentionInfo["departure"],
							$attentionInfo["arrival"]
						);

						return $content;
					}
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
			$wxAppId = $locationMsg->FromUserName;
			$userInfo = BusinessCommand::GetUserInfo($wxAppId);

			$userId = $userInfo['id'];
			$responseMsg = new TextMessage();
			$responseMsg->FromUserName = $locationMsg->ToUserName;
			$responseMsg->ToUserName = $locationMsg->FromUserName;

			$content = NULL;
			$trackInfo = $this->_isTracking($userId);
			if ($trackInfo) {
				$pm = 1;

				// 从stations表中获取跟踪线路的全部站点信息
				$sql = sprintf("select station.id, station.station, coordinate.longitude, coordinate.latitude"
				    . " from stations, coordinate, station"
					. " where stations.station = coordinate.id"
					. " and stations.station = station.id"
					. " and stations.lineid= %s"
					. " and pm%d > 0"
					. " order by pm%d",
					$trackInfo["route"],
					$pm,
					$pm
				);
				xDump($sql);

				$location = array(
					'lon' => (float)($locationMsg->Location_Y),
					'lat' => (float)($locationMsg->Location_X)
				);

				// 遍历线路的站点，是否匹配locationMsg的经纬度
				$result = $this->_execSql($sql);
				xDump($result);
				while ($row = $result->fetch_assoc()) {
					xDump($row);
					$stationLocation = array(
						"lon" => (float)$row["longitude"],
						"lat" => (float)$row["latitude"]
					);

					$distance = $this->_distance($stationLocation, $location);
					xDump($distance);
					if ($distance < 0.002) {
						$sql = sprintf("update track"
							. " set state = %s"
							. " where id = %s",
							$row["id"],
							$trackInfo["track"]
						);

						$content = "已到达:" . $row["station"];
						
						xDump($sql);
						$this->_execSql($sql);
						break;
					}
				}
			}
			if (!isset($content)) {
				$content = "未知站点";
			}

			$responseMsg->Content = sprintf("%s\n longitude=%s\n latitude=%s",
				$content,
				$locationMsg->Location_Y,
				$locationMsg->Location_X
			);

			$responseMsg->CreateTime = date('Y-m-d H:i:s', time());
			return $responseMsg->generateContent();
		}

		function _distance($pt1, $pt2) {
			$a = abs($pt1["lon"] - $pt2["lon"]);
			$b = abs($pt1["lat"] - $pt2["lat"]);
			return Sqrt($a * $a + $b * $b);
		}

		/**
		 * 删除一条线路追踪信息
		 */
		function remove($wxAppId, $userId) {
			$trackInfo = $this->_isTracking($userId);
			if ($trackInfo === false) {
				return SubjectResult::REMOVE_NOTRACK;
			}

			$sql = sprintf("delete from track"
				. " where user = %s",
				$userId
			);

			if ($this->_execSql($sql)) {
				$content = sprintf("你已经退出跟踪%s"
					. "感谢你的付出，祝你工作生活愉快。",
					$trackInfo["linename"]
				);
				return $content;
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
		 *   attention表的id数组
		 */
		function _parseCmdForAdd($userId, $paramStr) {
			if (strlen(trim($paramStr)) > 0) {
				$lineName = trim($paramStr);
			}

			// 查询用户关注的所有线路
			$sql = sprintf("select attention.id, route.linename"
				. " from route, attention, user"
				. " where user.id = attention.user"
				. " and attention.route = route.id"
				. " and user.id = %s",
				$userId
			);

			$result = BusinessCommand::ExecSql($sql);
			$num = $result->num_rows;
			if ($num > 0) {
				$attentions = array();
				if (isset($lineName)) {		// s [线路名称]
					while ($row = $result->fetch_assoc()) {
						$match = strstr($row["linename"], $lineName);
						if ($match) {
							$attentions[] = $row["id"];
						}
					}
				}
				else {
					while ($row = $result->fetch_assoc()) {
						$attentions[] = $row["id"];
					}
				}
			}
			$result->free();

			return $attentions;
		}
	}
?>
