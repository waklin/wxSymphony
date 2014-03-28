<?php
	require_once(MESSAGES_MODULE_PATH . "include.php");

	/**
	 * 查询线路
	 * 查询:	{线路名称}
	 */
	class QueryLine
	{
		private $_requestMsg;

		/**
		 * 
		 */
		public function __construct($requestMsg)
		{
			$this->_requestMsg = $requestMsg;
		}

		public function query()
		{
			$wxAppId = $this->_requestMsg->FromUserName;
			$userInfo = BusinessCommand::GetUserInfo($wxAppId);

			$lineName = $this->_requestMsg->Content;
			$lines = BusinessCommand::QueryLine($userInfo["city"], $lineName);

			if (!empty($lines)) {
				$content = "";
				$n = 0;
				foreach ($lines as $line) {
					$item = "";
					$pos = strpos($line["linetime"], "|");
					if ($pos === false) {
						$item = sprintf("%s\n%s\n", 
							$line["linename"],
							"③ " . $line["linetime"]);
					}
					else {
						$split = explode("|", $line["linetime"]);
						$item = sprintf("%s\n%s\n%s\n", 
							$line["linename"],
							"① " . $split[0],
							"② " . $split[1]);
					}
					if ($n > 0) {
						$content = $content . SPLITLINE;
					}
					$content = $content . $item;
					$n = $n + 1;
				}

				$textMessage = new TextMessage();
				$textMessage->ToUserName = $this->_requestMsg->FromUserName;
				$textMessage->FromUserName = $this->_requestMsg->ToUserName;
				$textMessage->Content = $content;
				return $textMessage->generateContent();
			}

			return false;
		}
	}
?>
