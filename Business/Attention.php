<?php
	require_once(MESSAGES_MODULE_PATH . "include.php");
	require_once(BUSINESS_MODULE_PATH . "common.php");

	class AttentionResult
	{
		const ADD_UNKNOWN_LINE = 1;		// 未知线路
		const ADD_AMBIGUOUS_LINE = 2;	// 多条匹配的线路
		const ADD_UNKNOWN_PM = 3;		// 未知方向
		const ADD_UNKNOWN_RETURNLINE = 4;	// 未知返程线路
		const ADD_AMBIGUOUS_RETURNLINE = 5;	// 多条匹配的返程线路
		const ADD_NOTCIRCLE_RETURNLINE = 6;	// 返程线路不是环线
		const ADD_INVALIDPARAM = 7;			// 无效的参数
		const ADD_SUCCESSFUL = 10;

		const RM_AMBIGUOUS_LINE = 101;	// 多条匹配的线路
		const RM_UNKNOWN_LINE = 102;		// 未知线路
		const RM_NOATTENTION_LINE = 103;		// 没有关注指定线路
		const RM_SUCCESSFUL = 200;

		const LS_NOATTENTION_LINE = 201;

		const AR_SUCCESSFUL = 0;

		public static function ToString($result) {
			$str = "";
			switch ($result) {
			case self::ADD_SUCCESSFUL:
				$str = "关注线路成功。";
				break;
			case self::ADD_UNKNOWN_LINE:
				$str = "关注线路失败[无效的线路名称]。";
				break;
			case self::ADD_UNKNOWN_PM:
				$str = "关注线路失败[无效的清晨乘车方向]。";
				break;
			case self::ADD_INVALIDPARAM:
				$str = "关注线路失败[指令参数不全]。";
				break;
			case self::ADD_AMBIGUOUS_LINE:
				$str = "关注线路失败[发现多条相似线路，请进一步明确线路名称]。";
				break;

			case self::RM_SUCCESSFUL:
				$str = "取消关注线路成功。";
				break;
			case self::RM_NOATTENTION_LINE:
				$str = "取消关注失败[你没有关注此线路]。";
				break;
			case self::RM_AMBIGUOUS_LINE:
				$str = "取消关注失败[发现多条相似线路，请进一步明确线路名称]。";
				break;
			case self::RM_UNKNOWN_LINE:
				$str = "取消关注失败[无效的线路名称]。";
				break;
			case self::LS_NOATTENTION_LINE:
				$str = "你没有关注任何线路。";
				break;
			default:
				$str = "出现错误，请检查输入参数。";
				break;
			}
			return $str;
		}
	}

	/**
	 * 管理关注线路
	 * 添加关注线路: 	{a 线路名称 [方向|返程线路]}
	 * 								[方向]: 	非环线线路早晨上班时的方向 1/2
	 * 								[返程线路]: 环线必须设置返程线路，返程线路也必须为环线
	 * 删除关注线路: 	{r 线路名称}
	 * 列出关注的线路:	{ls}
	 */
	class Attention
	{
		public function __construct()
		{
			;
		}

		public function perform($textMsg) {
			$content = trim($textMsg->Content);
			$firstChr = substr($content, 0, 1);

			$userInfo = BusinessCommand::GetUserInfo($textMsg->FromUserName);
			$result = null;
			if ($firstChr == "a") {
				$result = $this->_add(substr($content,1), $userInfo);
			}
			else if ($firstChr == "r") {
				$result = $this->_rm(substr($content,1), $userInfo);
			}
			else if ($firstChr == "l"){
				$result = $this->_ls($userInfo);
			}
			
			$responseMsg = new TextMessage();
			$responseMsg->FromUserName = $textMsg->ToUserName;
			$responseMsg->ToUserName = $textMsg->FromUserName;
			if (is_int($result)) {
				$responseMsg->Content = AttentionResult::ToString($result);
			}
			else {
				$responseMsg->Content = $result;
			}

			$responseMsg->CreateTime = date('Y-m-d H:i:s', time());
			return $responseMsg->generateContent();
		}

		function _getLineInfo($lineName) {
			$array = NULL;

			// 线路是否唯一
			$sql = sprintf("select id,linename,linetime from route"
				. " where linename like '%%%s%%'",
				$lineName
			);

			$result = BusinessCommand::ExecSql($sql);
			if ($result->num_rows > 0) {
				$array = array();
				while ($row = $result->fetch_assoc()) {
					$item = array(
						"id"=> $row["id"],
						"linename" => $row["linename"],
						"linetime" => $row["linetime"],
						"circle" => !(strpos($row["linetime"], "|")),
						);
					$array[] = $item;
				}
			}

			return $array;
		}

		function _add($cmd, $userInfo) {
			// body...
			$params = explode(" ", trim($cmd));
			if (count($params) < 2) {
				return AttentionResult::ADD_INVALIDPARAM;
			}

			$lineName = trim($params[0]);
			$lineInfos = $this->_getLineInfo($lineName);

			// 线路是否存在
			if (!$lineInfos) {
				return AttentionResult::ADD_UNKNOWN_LINE;
			}

			// 线路是否唯一
			if (count($lineInfos) > 1) {
				return AttentionResult::ADD_AMBIGUOUS_LINE;
			}

			$sql = "";
			// 线路是否环线
			$isCircle = $lineInfos[0]["circle"];
			if (!$isCircle) {
				$pm = intval(trim($params[1]));
				if ($pm != 1 && $pm !=2) {
					return AttentionResult::ADD_UNKNOWN_PM;	
				}
				$sql = sprintf("delete from attention"
					. " where user = %s"
					. " and route = %s",
					$userInfo["id"],
					$lineInfos[0]["id"]
				);
				BusinessCommand::ExecSql($sql);

				$sql = sprintf("insert into attention(user,route,pm_morning,route_opp)"
					. " values(%s,%s,%d,%d)",					
					$userInfo["id"],
					$lineInfos[0]["id"],
					$pm,
					0
					);
			}
			else {
				;
			}
			BusinessCommand::ExecSql($sql);

			return AttentionResult::ADD_SUCCESSFUL;
		}

		function _rm($cmd, $userInfo)
		{
			$lineName = trim($cmd);
			$lineInfos = $this->_getLineInfo($lineName);
			xDump(!isset($lineInfos));

			// 线路是否存在
			if (!isset($lineInfos)) {
				return AttentionResult::RM_UNKNOWN_LINE;
			}

			// 线路是否唯一
			if (count($lineInfos) > 1) {
				return AttentionResult::RM_AMBIGUOUS_LINE;
			}

			$sql = sprintf("select * from attention"
				. " where user = %s"
				. " and route = %s",
				$userInfo["id"],
				$lineInfos[0]["id"]
			);
			$result = BusinessCommand::ExecSql($sql);
			if ($result->num_rows < 1) {
				return AttentionResult::RM_NOATTENTION_LINE;
			}

			$sql = sprintf("delete from attention"
				. " where user = %s"
				. " and route = %s",
				$userInfo["id"],
				$lineInfos[0]["id"]
			);

			BusinessCommand::ExecSql($sql);
			return AttentionResult::RM_SUCCESSFUL;
		}

		function _ls($userInfo) {
			$ret = AttentionResult::AR_SUCCESSFUL;

			$sql = sprintf("select route.linetime, route.linename, attention.pm_morning from attention,route"
				. " where attention.route = route.id"
				. " and attention.user = %s",
				$userInfo["id"]
			);

			$result = BusinessCommand::ExecSql($sql);
			if ($result->num_rows < 1) {
				return AttentionResult::LS_NOATTENTION_LINE;
			}
			else{
				$content = "";
				while ($row = $result->fetch_assoc()) {
					$pm = intval($row["pm_morning"]);
					if ($pm > 0) {
						$split = explode("|", $row["linetime"]);
						if ($pm == 1) {
							$geton = $split[0];
							$getoff = $split[1];
						}
						else{
							$geton = $split[1];
							$getoff = $split[0];
						}
						$item = sprintf("%s\n%s\n↓\n%s\n",
							$row["linename"],
							$geton,
							$getoff
						);
					}
					else {
						$item = sprintf("%s\n%s\n",
							$row["linename"],
							$row["linetime"]
						);
					}
					$content = $content . $item . "--------------------------";
				}

				return $content;
			}
		}
	}
?>
