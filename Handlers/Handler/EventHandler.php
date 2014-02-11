<?php
	require_once("global.php");
	require_once(HANDLERS_MODULE_PATH . "IHandler.php");
	require_once(MESSAGES_MODULE_PATH . "include.php");

	class EventHandler implements IHandler
	{
		private $_requestString;
		function __construct($requestString)
		{
			$this->_requestString = $requestString;
		}

		public function handleRequest()
		{
			$result = null;

			$simpleXml = simplexml_load_string($this->_requestString, "SimpleXMLElement", LIBXML_NOCDATA);

			if ($simpleXml->Event == EVENTTYPE_SUBSCRIBE_TAG ||
				$simpleXml->Event == EVENTTYPE_UNSUBSCRIBE_TAG)
			{
				$requestEvent = Semaphore::loadFromXml("SubscribeEvent", $this->_requestString);

				$responseMsg = new TextMessage();

				$temp = $requestEvent->ToUserName;
				$responseMsg->ToUserName = $requestEvent->FromUserName;
				$responseMsg->FromUserName = $temp;

				if ($requestEvent->Event == EVENTTYPE_UNSUBSCRIBE_TAG)
					$responseMsg->Content = "goodbye!";
				else
					$responseMsg->Content = "welcome join!";

				$responseMsg->CreateTime = date("Y-m-d H:i:s", time());
				$responseMsg->MsgId = 0;

				$result = $responseMsg->generateContent();
			}

			return $result;
		}
	}
?>
