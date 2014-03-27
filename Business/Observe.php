<?php
	require_once("global.php");
	require_once(DBACCESS_MODULE_PATH . "include.php");
	require_once(BUSINESS_MODULE_PATH. "common.php");

	/**
	 * 线路观察类
	 * 观察: {o [线路名称] [方向]} 
	 */
	class Observe
	{
		public function __construct()
		{
		}

		/**
		 * 处理文本消息
		 */
		public function perform($textMsg) {
			$userInfo = BusinessCommand::GetUserInfo($textMsg->FromUserName);
			$sql = sprintf("select id from attention"
				. " where user = '%s'",
				$userInfo["id"]
			);

			$content = "";
			$result = BusinessCommand::ExecSql($sql);
			while ($row = $result->fetch_assoc()) {
				$attention = BusinessCommand::FetchAttentionInfo($row["id"], $textMsg->CreateTime);
				$sql = sprintf("select station.station, count(station.station), max(track.lastUpdateTime)"
				    . " from track, route, station"
					. " where track.state = station.id"
					. " and track.route = route.id"
					. " and track.route = %s"
					. " and track.pm = %s"
					. " group by station.station",

					$attention["route"],
					$attention["pm"]
				);

				$result = BusinessCommand::ExecSql($sql);
				$arrivalInfos = array();
				while ($row = $result->fetch_row()) {
					$item = array(
						"station" => $row[0],
						"count" => $row[1],
						"lasttime" => $row[2],
					);
					$arrivalInfos[] = $item;
				}
				xDump($arrivalInfos);

				if (!empty($arrivalInfos)) {
					foreach ($arrivalInfos as $info) {
						$span = floor((strtotime($textMsg->CreateTime)-strtotime($info["lasttime"]))%86400/60); 
						$infos = $infos . sprintf("  %s-%s-%s分钟前\n", $info["station"], $info["count"], $span);
					}
				}
				else {
					$infos = "  目前没有人跟踪此线路。";
				}

				$lineInfo = sprintf("%s\n"
					. "[%s]" . RIGHTARROW . "[%s]"
					. "%s"
					. SPLITLINE,

					$attention["linename"],
					$attention["departure"],
					$attention["arrival"],
					$infos
				);
				xDump($lineInfo);

				$content = $content . $lineInfo;
			}

			$responseMsg = new TextMessage();
			$responseMsg->FromUserName = $textMsg->ToUserName;
			$responseMsg->ToUserName = $textMsg->FromUserName;
			$responseMsg->Content = $content;
			$responseMsg->CreateTime = date('Y-m-d H:i:s', time());
			return $responseMsg->generateContent();
		}
	}
?>
