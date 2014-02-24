<?php
	require_once("DBAccess.php");
	require_once(MESSAGES_MODULE_PATH . "include.php");

	/**
	 * 
	 */
	class QueryLine
	{
		
		private $_lineName;
		private $_wxAppId;
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

			$content = "";
			$num = $result->num_rows;
			
			if ($num <= 0) {
				$content = "no line.";
			}
			else {
				while ($row = $result->fetch_array()) {
					$line = $row['linename'] . "        " .  $row['linetime'];
					$content = $content . $line . "\n";
				}
			}

			$textMessage = new TextMessage();
			$textMessage->ToUserName = $this->_requestMsg->FromUserName;
			$textMessage->FromUserName = $this->_requestMsg->ToUserName;
			$textMessage->Content = $content;

			return $textMessage->generateContent();
		}
	}
?>
