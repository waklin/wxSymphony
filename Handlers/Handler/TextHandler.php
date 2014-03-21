<?php
	require_once("global.php");
	require_once(HANDLERS_MODULE_PATH . "IHandler.php");
	require_once(MESSAGES_MODULE_PATH . "include.php");
	require_once(BUSINESS_MODULE_PATH . "include.php");

	class TextHandler implements IHandler
	{
		private $_requestString;
		function __construct($requestString)
		{
			$this->_requestString = $requestString;
		}

		public function handleRequest()
		{
			$textMessage = Semaphore::loadFromXml("TextMessage", $this->_requestString);
			$content = trim($textMessage->Content);
			$firstChr = substr($content, 0, 1);

			if ($firstChr == "h") {
				//$newsMsg = new NewsMessage();

				//$art = new Article();
				//$art->Title = "帮助";
				//$art->Description = "描述";
				//$art->PicUrl = null;
				//$art->Url = "www.baidu.com";
				//$newsMsg->addArticle($art);

				//$newsMsg->FromUserName = $textMessage->ToUserName;
				//$newsMsg->ToUserName= $textMessage->FromUserName;
				//$newsMsg->CreateTime = date('Y-m-d H:i:s', time());
				//return $newsMsg->generateContent();

				$txtMsg = new TextMessage();
				$txtMsg->FromUserName = $textMessage->ToUserName;
				$txtMsg->ToUserName= $textMessage->FromUserName;
				$txtMsg->CreateTime = date('Y-m-d H:i:s', time());

				$txtMsg->Content ='<a href="http://www.baidu.com">' . "这是一个链接" . "</a>";

				return $txtMsg->generateContent();
			}
			else if ($firstChr == "a" || $firstChr == "r" || $firstChr == "l") {
				$attention = new Attention();
				return $attention->perform($textMessage);
			}
			else if ($firstChr== "s") {
				$subject = new Subject();
				return $subject->perform($textMessage);
			}
			else {
				$queryLine = new QueryLine($textMessage);
				return $queryLine->query();
			}
		}
	}
?>
