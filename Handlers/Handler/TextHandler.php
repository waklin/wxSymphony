<?php
	require_once("global.php");
	require_once(HANDLERS_MODULE_PATH . "IHandler.php");
	require_once(MESSAGES_MODULE_PATH . "include.php");
	require_once(BUSINESS_MODULE_PATH . "include.php");

	class TextHandler implements IHandler
	{
		private $_requestString;
		function __construct($requestString)
		{
			$this->_requestString = $requestString;
		}

		public function handleRequest()
		{
			$textMessage = Semaphore::loadFromXml("TextMessage", $this->_requestString);

			/*
			$temp = $textMessage->ToUserName;
			$textMessage->ToUserName = $textMessage->FromUserName;
			$textMessage->FromUserName = $temp;
			$textMessage->Content = "i love you too!";

			return $textMessage->generateContent();
			 */

			$cmd = substr(trim($textMessage->Content), 0, 1);
			if ($cmd == "s") {
				$subject = new Subject();
				return $subject->perform($textMessage);
			}
			else {
				$queryLine = new QueryLine($textMessage);
				return $queryLine->query();
			}
		}
	}
?>
