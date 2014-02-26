<?php
	//define("DEPLOY_BAE", true);

	chdir(dirname(__FILE__));

	define("MESSAGES_MODULE_PATH", "Messages/");
	define("HANDLERS_MODULE_PATH", "Handlers/");
	define("BUSINESS_MODULE_PATH", "Business/");

	function xDump($var, $describe) {
		if (!defined(DEPLOY_BAE)) {
			return;
		}

		echo("<br/>");
		echo($describe . "=");
		var_dump($var);
		echo("<br/>");
	}
?>
