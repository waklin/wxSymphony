<?php
	require_once("global.php");
	require_once(HANDLERS_MODULE_PATH . "IHandler.php");
	require_once(MESSAGES_MODULE_PATH . "include.php");
	require_once(BUSINESS_MODULE_PATH . "Signup.php");

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
				$responseMsg->CreateTime = date("Y-m-d H:i:s", time());
				$responseMsg->MsgId = 0;

				$signup = new Signup();
				if ($requestEvent->Event == EVENTTYPE_UNSUBSCRIBE_TAG)
				{
					$responseMsg->Content = "goodbye!";
					$signup->leave($requestEvent->FromUserName);
				}
				else
				{
					$responseMsg->Content = "welcome join!";
					$signup->join($requestEvent->FromUserName, $requestEvent->CreateTime);
				}
				$result = $responseMsg->generateContent();
			}

			return $result;
		}
	}
?>
