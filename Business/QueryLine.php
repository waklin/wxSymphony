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
			$db = new DBAccess();
			$lineName = $this->_requestMsg->Content;
			$sql = sprintf("select linename,linetime from route where linename like '%%%s%%'", $lineName);
			$result = $db->execSql($sql);

			$num = $result->num_rows;
			if ($num > 0) {
				$content = "";
				while ($row = $result->fetch_assoc()) {

					$item = "";
					$pos = strpos($row["linetime"], "|");
					if ($pos === false) {
						$item = sprintf("%s\n%s\n", 
							$row["linename"],
							"③ " . $row["linetime"]);
					}
					else {
						$split = explode("|", $row["linetime"]);
						$item = sprintf("%s\n%s\n%s\n", 
							$row["linename"],
							"① " . $split[0],
							"② " . $split[1]);
					}
					$content = $content . $item . "--------------------------";
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
