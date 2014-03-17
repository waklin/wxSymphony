<?php
	require_once("global.php");
	require_once(MESSAGES_MODULE_PATH . "Semaphore.php");

	class Article
	{
		public $Title;
		public $Description;
		public $PicUrl;
		public $Url;
	}

	class NewsMessage extends MessageBase
	{
		private $_articles;
		private static $_template = null;

		function __construct()
		{
			$this->MsgType = "news";
			$this->_articles = array();
		}

		public function addArticle($article) {
			$this->_articles[] = $article;
			xDump($this->_articles);
		}

		public function generateContent()
		{
			$template = $this->getTemplate();

			$itemTpl = "<item>
						<Title><![CDATA[%s]]></Title>
						<Description><![CDATA[%s]]></Description>
						<PicUrl><![CDATA[%s]]></PicUrl>
						<Url><![CDATA[%s]]></Url>
						</item>
						";

			$artItems = "";
			for ($i = 0; $i < count($this->_articles); $i++) {
				$item = $this->_articles[$i];
				xDump($item);
				$itemStr = sprintf($itemTpl,
								$item->Title,
								$item->Description,
								$item->PicUrl,
								$item->Url);
				$artItems = $artItems . $itemStr;
			}

			$result = sprintf($template,
								$this->ToUserName,
								$this->FromUserName,
								$this->CreateTime,
								$this->MsgType,
								count($this->_articles),
								$artItems);
			return $result;
		}

		public function getTemplate()
		{
			if (NewsMessage::$_template == null)
			{
				NewsMessage::$_template = "<xml>
											<ToUserName><![CDATA[%s]]></ToUserName>
											<FromUserName><![CDATA[%s]]></FromUserName>
											<CreateTime>%s</CreateTime>
											<MsgType><![CDATA[%s]]></MsgType>
											<ArticleCount>%d</ArticleCount>
											<Articles>
											%s
											</Articles>
											</xml>";
			}

			return NewsMessage::$_template;
		}
	}
?>
