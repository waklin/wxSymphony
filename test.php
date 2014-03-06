<?php
    require_once("global.php");
    require_once(DBACCESS_MODULE_PATH . "DBAccess.php");

    $dbAccess = new DBAccess();
    $sql = "select station from station";
    $result = $dbAccess->execSql($sql);

    $index = 0;

    echo('<select name="list" multiple="true"  size="10">');
    while ($row=$result->fetch_assoc()) {
        $line = sprintf('<option value= "%d">%s</option>', index, $row['station']);
        echo($line);
        $index++;
    }
    echo('</select>');

    echo("<br/>");
    var_dump($index);
?>
