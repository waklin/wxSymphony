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
			//$dbName = $this->_getDbPath();
			$dbName = "DbFiles/beijing";
			$lineName = $this->_requestMsg->Content;

			$db = new SQLite3($dbName);
			$sql = sprintf("select linename,linetime from lines where linename like '%%%s%%'", $lineName);
			$result = $db->query($sql);

			$num = 0;
			$content = "";
			while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
				$line = $row['linename'] . "        " .  $row['linetime'];
				$content = $content . $line . "<br/>";
				$num++;
			}

			if ($num == 0) {
				$content = "no line.";
			}

			$textMessage = new TextMessage();
			$textMessage->ToUserName = $this->_requestMsg->FromUserName;
			$textMessage->FromUserName = $this->_requestMsg->ToUserName;
			$textMessage->Content = $content;

			return $textMessage->generateContent();
		}

		private function _getDbPath() {
			// body...
		}
	}
?>
