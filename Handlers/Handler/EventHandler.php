<?php
	require_once("/common.php");
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
				$requestEvent = SubscribeEvent::loadFromXml("SubscribeEvent", $this->_requestString);

				$responseMessage = new TextMessage();
				$responseMessage->FromUserName = "wxSymphony";
				$responseMessage->ToUserName = $requestEvent->FromUserName;

				if ($requestEvent->Event == EVENTTYPE_UNSUBSCRIBE_TAG)
					$responseMessage->Content = "goodbye!";
				else
					$responseMessage->Content = "welcom join!";

				$responseMessage->CreateTime = date("Y-m-d H:i:s", time());
				$responseMessage->MsgId = 0;

				return $responseMessage; 
			}

			return $result;
		}
	}
?>
