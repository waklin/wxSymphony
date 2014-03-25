<?php
	require_once("global.php");
	require_once(MESSAGES_MODULE_PATH . "Semaphore.php");

	class TextMessage extends MessageBase
	{
		public $Content;

		private static $_template = null;

		function __construct()
		{
			$this->MsgType = "text";
		}

		protected function readPlusNode($simpleXml)
		{
			parent::readPlusNode($simpleXml);

			$this->Content = (string)$simpleXml->Content;
		}

		public function generateContent()
		{
			$template = $this->getTemplate();

			$result = sprintf($template,
								$this->ToUserName,
								$this->FromUserName,
								$this->CreateTime,
								$this->MsgType,
								$this->Content);
			return $result;
		}

		public function getTemplate()
		{
			if (TextMessage::$_template == null)
			{
				TextMessage::$_template = "<xml>
											<ToUserName><![CDATA[%s]]></ToUserName>
											<FromUserName><![CDATA[%s]]></FromUserName>
											<CreateTime>%s</CreateTime>
											<MsgType><![CDATA[%s]]></MsgType>
											<Content><![CDATA[%s]]></Content>
											<MsgId>0</MsgId>
											</xml>";
			}

			return TextMessage::$_template;
		}
	}
?>
