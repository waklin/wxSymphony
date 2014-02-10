<?php
	interface IMessage
	{
		function getTemplate();
		function generateContent();
	}

	interface IEvent
	{
		
	}

	abstract class Semaphore
	{
		public $FromUserName;
		public $ToUserName;
		public $MsgType;
		public $CreateTime;

		abstract protected function readPlusNode($simpleXml);

		protected function readBaseNode($simpleXml)
		{
			$this->FromUserName = $simpleXml->FromUserName;
			$this->ToUserName = $simpleXml->ToUserName;
			$this->CreateTime = $simpleXml->CreateTime;
		}

		public static function loadFromXml($messageClassName, $xmlString)
		{
			$result = null;

			if (!empty($xmlString))
			{
				$cls = new ReflectionClass($messageClassName);
				$result = $cls->newInstanceArgs();

				$simpleXml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
				$result->readBaseNode($simpleXml);
				$result->readPlusNode($simpleXml);
			}

			return $result;
		}
	}

	abstract class MessageBase extends Semaphore implements IMessage
	{
		public $MsgId;

		abstract public function generateContent();
		abstract public function getTemplate();

		protected function readPlusNode($simpleXml)
		{
			$this->MsgId = $simpleXml->MsgId;
		}
	}

	abstract class EventBase extends Semaphore 
	{
		public $Event;

		function __construct()
		{
			$this->MsgType = "event";
		}

		protected function readPlusNode($simpleXml)
		{
			$this->Event = $simpleXml->Event;
		}
	}
?>
