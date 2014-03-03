<?php
	//define("DEPLOY_BAE", true);

	chdir(dirname(__FILE__));

	define("MESSAGES_MODULE_PATH", "Messages/");
	define("HANDLERS_MODULE_PATH", "Handlers/");
	define("BUSINESS_MODULE_PATH", "Business/");
	define("DBACCESS_MODULE_PATH", "DBAccess/");

	function xDump($var, $allStack = false) {
		if (defined(DEPLOY_BAE)) {
			return;
		}

		echo("<br/>");
		$array = debug_backtrace();
		if (!$allStack) {
			$codePos = sprintf(__FUNCTION__ ."_" . "<B>%s on line %s:</B>", $array[0]['file'], $array[0]['line']);
		}
		else
		{
			$codePos = "====call stack====<br/>";
			foreach ($array as $val) {
				$codePos = $codePos . sprintf(__FUNCTION__ ."_" . "<B>%s on line %s:</B>", $val['file'], $val['line']);
				$codePos = $codePos . "<br/>";
			}
		}
		echo($codePos);
		var_dump($var);
		echo("<br/>");
	}
?>
