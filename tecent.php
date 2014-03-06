<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>腾讯地图</title>
<style type="text/css">
*{
    margin:0px;
    padding:0px;
}
body, button, input, select, textarea {
    font: 12px/16px Verdana, Helvetica, Arial, sans-serif;
}
p{
    width:603px;
    padding-top:3px;
    margin-top:10px;
    overflow:hidden;
}
input{
	width:300px;
}
</style>
<script charset="utf-8" src="http://map.qq.com/api/js?v=1"></script>
<script>
var geocoder,map,marker = null;
var num = 0;
var init = function() {
    var center = new soso.maps.LatLng(39.916527,116.397128);
    map = new soso.maps.Map(document.getElementById('container'),{
        center: center,
        zoomLevel: 15
    });
    geocoder = new soso.maps.Geocoder();
}

function queryOnlyOne() {
    var address = document.getElementById("address").value;
    geocoder.geocode({'address': address}, function(results, status) {
        if (status == soso.maps.GeocoderStatus.OK) {
            alert(results.location.toString() + results.address);
        } else {
            alert("没有发现任何结果");
        }
        num++;
    });
}

var sort = 0;
function queryOne() {
    var staList = document.getElementById("stationList");
    var locList = document.getElementById("locationList");
    var unknownList = document.getElementById("unknownList");
	var staName = staList.options[sort].innerHTML;
	var address = "北京,"+staName +"公交站";

	geocoder.geocode({'address': address}, function(results, status) {
		if (status == soso.maps.GeocoderStatus.OK) {
			var item = results.location.toString() + results.address;
			locList.options[locList.options.length] = new Option(sort.toString() + "_" + item, sort.toString());
		} else {
			var staName1 = staList.options[sort].innerHTML;
			unknownList.options[unknownList.options.length] = new Option(sort.toString() + "_" + staName1, sort.toString());
		}
		sort++;
		if (sort < staList.options.length) {
			setTimeout("queryOne()", 10);
		}
	});
}

function queryAll() {
    alert("codeAddress");
	queryOne();
    //var staList = document.getElementById("stationList");
    //var locList = document.getElementById("locationList");
    //var unknownList = document.getElementById("unknownList");
    //for (var i = 0, l = staList.options.length; i < l; i ++) {
        //var staName = staList.options[i].innerHTML;
        //var address = "北京,"+staName +"公交站";
        //geocoder.geocode({'address': address}, function(results, status) {
            //if (status == soso.maps.GeocoderStatus.OK) {
                //var item = results.location.toString() + results.address;
                //locList.options[locList.options.length] = new Option(num.toString() + "_" + item, num.toString());
            //} else {
                //unknownList.options[unknownList.options.length] = new Option(num.toString() + "_" + results.address, num.toString());
            //}
            //num++;
        //});
    //}
}
function getnums() {
    var locList = document.getElementById("locationList");
    var unknownList = document.getElementById("unknownList");

    var nums = locList.options.length + unknownList.options.length;
    alert(nums.toString());
}
</script>
</head>
<body onload="init()">

<div>
    <?php
        require_once("global.php");
        require_once(DBACCESS_MODULE_PATH . "DBAccess.php");

        $dbAccess = new DBAccess();
        $sql = "select station from station";
        $result = $dbAccess->execSql($sql);

        $index = 0;

        echo('<select id="stationList" name="list" multiple="true" size="10">');
        while ($row=$result->fetch_assoc()) {
            $line = sprintf('<option value= "%d">%s</option>', index, $row['station']);
            echo($line);
            $index++;
            if ($index > 1001) {
                break;
            }
        }
        echo('</select>');

        echo('<select id="locationList" name="list" multiple="true" size="10">');
        $line = sprintf('<option value= "%d">%s</option>', 0, "坐标......................");
        echo($line);
        echo('</select>');

        echo('<select id="unknownList" name="list" multiple="true" size="10">');
        $line = sprintf('<option value= "%d">%s</option>', 0, "未找到....................");
        echo($line);
        echo('</select>');

        echo("<br/>");
        var_dump($index);
    ?>
</div>

<div>
    <input id="address" type="textbox" value="北京,东流水">
    <button onclick="queryAll()">search</button>
    <button onclick="getnums()">info</button>
</div>

<div style="width:603px;height:300px" id="container"></div>

<p>输入地址，点击search进行地址解释。</p>

</body>
</html>
