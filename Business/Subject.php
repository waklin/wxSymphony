<?php
	session_start();

	/**
	 * 
	 */
	class Subject
	{
		
		private $_dbAccess = new DBAccess();

		/**
		 * 
		 */
		public function __construct()
		{
			;
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
			$assignAttention = ($cmd.count() > 1);		// 没有指定序号
			$attentionIndex = 0
			if ($assignAttention) {
				$attentionIndex = intval($cmd[1])
			}

			if (!isset(($_SESSION[$wxAppId]))) {
				$userId = 0;
				$sql = sprintf("select user.id, user.city_id from user, attention"
					. " where user.id = attention.user "
					. " and user.wxAppId = '%s'", $wxAppId);
				$result = $_dbAccess->execSql($sql);
				$num = $result->num_rows;
				if ($num <= 0) {
					// 返回没有配置用户线路的链接信息
				}
				else if ($num == 1) {
					;
				}
				else{
					;
				}

				$_SESSION[$textMsg->FromUserName] = true;
			}
		}

		/**
		 * 加入跟踪信息到track表中
		 */
		public function track($cityId, $userId, $trackId, $pm) {
			// 线路名称，上行/下行
			// 加入到track表中
			// 创建一个session维护subject信息，
			$sql = sprintf("insert into track(city,user,state,lastupdate,pm)
				values(%d,%d,%d,'%s',%d)",
					$cityId,
					$userId,
					$trackId,
					$pm);
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
