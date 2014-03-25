<?php
	require_once("global.php");
	require_once(DBACCESS_MODULE_PATH . "include.php");
	require_once(BUSINESS_MODULE_PATH. "common.php");

	/**
	 * 线路观察类
	 *
	 * 观察: {a [线路名称|q]} 
	 *			[线路名称]:	追踪指定的线路, 没有指定线路意味着追踪attention中唯一的线路
	 *			[q]:		退出追踪
	 */
	class Subject
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

			$result = BusinessCommand::ExecSql($sql);
			while ($row = $result->fetch_assoc()) {
				$attention = BusinessCommand::FetchAttentionInfo($row["id"], $textMsg->CreateTime);
			}
		}
	}
?>
