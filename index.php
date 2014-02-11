<?php
	require_once("global.php");
	require_once(MESSAGES_MODULE_PATH . "include.php");
	require_once(HANDLERS_MODULE_PATH . "include.php");

	define("TOKEN", "weixin");
	$wxSvr = new wxService();
	$wxSvr->reponse();

	class wxService
	{
		public function reponse()
		{
        	$echoStr = $_GET["echostr"];

			if(isset($echoStr))
			{
				if($this->checkSignature())
					echo($echoStr);
				exit;
			}
			else
			{
				$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
				if (!empty($postStr))
				{
					$handler = HandlerFactory::createHandler($postStr);
					$responseStr = $handler->handleRequest();
					echo($responseStr);
				}
			}
		}

		private function checkSignature()
		{
			$signature = $_GET["signature"];
			$timestamp = $_GET["timestamp"];
			$nonce = $_GET["nonce"];	
						
			$token = TOKEN;
			$tmpArr = array($token, $timestamp, $nonce);
			sort($tmpArr);
			$tmpStr = implode( $tmpArr );
			$tmpStr = sha1( $tmpStr );
			
			if( $tmpStr == $signature ){
				return true;
			}else{
				return false;
			}
		}
	}

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
				<ToUserName><![CDATA[gl]]></ToUserName>
				<FromUserName><![CDATA[waklin]]></FromUserName>
				<CreateTime>123456789</CreateTime>
				<MsgType><![CDATA[event]]></MsgType>
				<Event><![CDATA[subscribe]]></Event>
				</xml>";

	$handler = HandlerFactory::createHandler($xmlString);
	$child = $handler->handleRequest();
	$child = Semaphore::loadFromXml("TextMessage", $child);

	echo($child->ToUserName);
	echo("<br>");

	echo($child->FromUserName);
	echo("<br>");

	echo($child->Content);
	echo("<br>");

	echo($child->MsgType);
	echo("<br>");

	//echo($child->generateContent());
	//$fp = fopen("1.txt", "w+");
	//fputs($fp, $child->generateContent());
	//fclose($fp);
	//echo("<br>");
	
	echo("hello world!");
?>
