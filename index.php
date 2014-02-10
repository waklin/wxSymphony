<?php
	//require("Messages/TextMessage.php");

	require_once("/common.php");
	require_once(MESSAGES_MODULE_PATH . "include.php");
	require_once(HANDLERS_MODULE_PATH . "include.php");

	/*
	$xmlString = "<xml>
				<ToUserName><![CDATA[gl]]></ToUserName>
				<FromUserName><![CDATA[xyc]]></FromUserName>
				<CreateTime>2014/02/10</CreateTime>
				<MsgType><![CDATA[text]]></MsgType>
				<Content><![CDATA[i love you]]></Content>
				<FuncFlag>0</FuncFlag>
				</xml>";
	 */

	$xmlString = "<xml>
				<ToUserName><![CDATA[toUser]]></ToUserName>
				<FromUserName><![CDATA[xuyingchun]]></FromUserName>
				<CreateTime>123456789</CreateTime>
				<MsgType><![CDATA[event]]></MsgType>
				<Event><![CDATA[subscribe]]></Event>
				</xml>";

	//$child = TextMessage::loadFromXml("SubscribeEvent", $xmlString);
	$handler = HandlerFactory::CreateHandler($xmlString);
	$child = $handler->handleRequest();

	echo($child->FromUserName);
	echo("<br>");

	echo($child->ToUserName);
	echo("<br>");

	echo($child->Content);
	echo("<br>");

	echo($child->MsgType);
	echo("<br>");

	/*
	echo($child->generateContent());
	$fp = fopen("1.txt", "w+");
	fputs($fp, $child->generateContent());
	fclose($fp);
	echo("<br>");
	 */

	echo("hello world!");
?>
