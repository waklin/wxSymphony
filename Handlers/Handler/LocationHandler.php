<?php
	require_once("global.php");
	require_once(HANDLERS_MODULE_PATH . "IHandler.php");
	require_once(MESSAGES_MODULE_PATH . "include.php");
	require_once(BUSINESS_MODULE_PATH . "include.php");

	class LocationHandler implements IHandler
	{
		private $_requestString;
		function __construct($requestString)
		{
			$this->_requestString = $requestString;
		}

		public function handleRequest()
		{
			$locationMsg = Semaphore::loadFromXml("LocationMessage", $this->_requestString);
			$subject = new Subject();
			return $subject->track($locationMsg);
		}
	}
?>
