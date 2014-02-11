<?php
	require_once("global.php");
	require_once(HANDLERS_MODULE_PATH . "IHandler.php");
	require_once(MESSAGES_MODULE_PATH . "include.php");

	class TextHandler implements IHandler
	{
		private $_requestString;
		function __construct($requestString)
		{
			$this->_requestString = $requestString;
		}

		public function handleRequest()
		{
			$textMessage = TextMessage::loadFromXml("TextMessage", $this->_requestString);

			$textMessage->ToUserName = "xyc";
			$textMessage->FromUserName = "gl";
			$textMessage->Content = "i love you too!";

			//return $textMessage->generateContent();
			return $textMessage;
		}
	}
?>
