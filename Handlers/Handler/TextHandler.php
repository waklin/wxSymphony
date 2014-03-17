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
			$cmd = substr(trim($textMessage->Content), 0, 1);

			if ($cmd == "h") {
				$newsMsg = new NewsMessage();

				$art = new Article();
				$art->Title = "帮助";
				$art->Description = "描述";
				$art->PicUrl = null;
				$art->Url = "www.baidu.com";
				$newsMsg->addArticle($art);

				$newsMsg->FromUserName = $textMessage->ToUserName;
				$newsMsg->ToUserName= $textMessage->FromUserName;
				$newsMsg->CreateTime = date('Y-m-d H:i:s', time());
				return $newsMsg->generateContent();
			}
			else if ($cmd == "s") {
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
