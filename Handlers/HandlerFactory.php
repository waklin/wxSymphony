<?php
	require_once("global.php");

	require_once(HANDLERS_MODULE_PATH . "IHandler.php");
	require_once(HANDLERS_MODULE_PATH . "Handler/TextHandler.php");
	require_once(HANDLERS_MODULE_PATH . "Handler/EventHandler.php");

	require_once(MESSAGES_MODULE_PATH . "include.php");

	class HandlerFactory
	{
		static function CreateHandler($requestString)
		{
			$result = null;

			$simpleXml = simplexml_load_string($requestString, "SimpleXMLElement", LIBXML_NOCDATA);

			if ($simpleXml->MsgType == MSGTYPE_TEXTTAG)
			{
				$result = new TextHandler($requestString);
			}
			else if	($simpleXml->MsgType == MSGTYPE_EVENTTAG)
			{
				$result = new EventHandler($requestString);
			}
			return $result;
		}
	}
?>
