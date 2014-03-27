<?php
	require_once("global.php");
	require_once(MESSAGES_MODULE_PATH . "include.php");
	require_once(HANDLERS_MODULE_PATH . "include.php");
	require_once(DBACCESS_MODULE_PATH . "include.php");

	define("TOKEN", "weixin");
	$wxSvr = new wxService();
	$wxSvr->response();

	class wxService
	{
		public function response()
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
			sort($tmpArr, SORT_STRING);
			$tmpStr = implode( $tmpArr );
			$tmpStr = sha1( $tmpStr );
			
			if( $tmpStr == $signature ){
				return true;
			}else{
				return false;
			}
		}
	}
	if(defined(DEPLOY_BAE))
		exit();

	//$xmlString = "<xml>
				//<ToUserName><![CDATA[gl]]></ToUserName>
				//<FromUserName><![CDATA[waklin]]></FromUserName>
				//<CreateTime>1156219870</CreateTime>
				//<MsgType><![CDATA[event]]></MsgType>
				//<Event><![CDATA[subscribe]]></Event>
				//</xml>";
	
	//$xmlString = "<xml>
				//<ToUserName><![CDATA[gl]]></ToUserName>
				//<FromUserName><![CDATA[waklin]]></FromUserName>
				//<CreateTime>1156219870</CreateTime>
				//<MsgType><![CDATA[text]]></MsgType>
				//<Content><![CDATA[s425]]></Content>
				//<FuncFlag>0</FuncFlag>
				//</xml>";

	//$xmlString = "<xml>
				//<ToUserName><![CDATA[gl]]></ToUserName>
				//<FromUserName><![CDATA[waklin]]></FromUserName>
				//<CreateTime>1351776360</CreateTime>
				//<MsgType><![CDATA[location]]></MsgType>
				//<Location_X>39.980348</Location_X>
				//<Location_Y>116.368154</Location_Y>
				//<Scale>20</Scale>
				//<Label><![CDATA[位置信息]]></Label>
				//<MsgId>1234567890123456</MsgId>
				//</xml>";
	
	$xmlString = sprintf("<xml>
				<ToUserName><![CDATA[gl]]></ToUserName>
				<FromUserName><![CDATA[waklin]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[text]]></MsgType>
				<Content><![CDATA[o]]></Content>
				<FuncFlag>0</FuncFlag>
				</xml>",
				time()
			);

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

	 //$handler = HandlerFactory::createHandler($xmlString);
	 //$child = $handler->handleRequest();
	 //echo($child);

	/*
	 *echo($child->generateContent());
	 *$fp = fopen("1.txt", "w+");
	 *fputs($fp, $child->generateContent());
	 *fclose($fp);
	 *echo("<br>");
	 */
	
	echo("hello world!");
?>
