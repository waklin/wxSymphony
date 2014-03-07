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
<script charset="utf-8" src="http://map.qq.com/api/js?v=2.exp"></script>
<script>
var geocoder,map,marker = null;
var num = 0;
var init = function() {
	//var center = new soso.maps.LatLng(39.916527,116.397128);
	//map = new qq.maps.Map(document.getElementById('container'),{
		//center: center,
		//zoomLevel: 15
	//});
}

function queryOnlyOne() {
	//创建对象实例
	geocoder = new qq.maps.Geocoder({
		//若服务请求成功，则运行以下函数，并将结果传入
		complete:function(result){
			alert('成功：'+ result.detail.address + result.detail.location);
		},
		//若服务请求失败，则运行以下函数
		error:function(){
			alert('出错啦~');
		}
	});
    var address = document.getElementById("address").value;
	//地址解析
	geocoder.getLocation(address);
}

var sort = 0;
var oFso,oFile,sFile,sContent;
var staArray = new Array();

function queryOneReadFormControl() {
    var staList = document.getElementById("stationList");
    var locList = document.getElementById("locationList");
    var unknownList = document.getElementById("unknownList");
	var sortTextbox = document.getElementById("sort");

	//创建对象实例
	geocoder = new qq.maps.Geocoder({
		//若服务请求成功，则运行以下函数，并将结果传入
		complete:function(result){
			var staId = staList.options[sort].value;
			var staName = staList.options[sort].innerHTML;

			var item = "<" + result.detail.location + ">" + "," + result.detail.address;
			//locList.options[locList.options.length] = new Option(staId + "_" + item, sort.toString());
			var line = staId + "," + staName + "," + item;
			oFile.WriteLine(line);

			sort++;
			sortTextbox.value = sort.toString();
			if (sort < staList.options.length) {
				setTimeout("queryOneReadFormControl()", 50);
			}
			else {
				oFile.Close();
				alert("complete");
			}
		},
		//若服务请求失败，则运行以下函数
		error:function(){
			var staId = staList.options[sort].value;
			var staName = staList.options[sort].innerHTML;
			unknownList.options[unknownList.options.length] = new Option(staId + "_" + staName, sort.toString());
			var line = staId + "," + "fail";
			oFile.WriteLine(line);

			sort++;
			sortTextbox.value = sort.toString();
			if (sort < staList.options.length) {
				setTimeout("queryOneReadFormControl()", 10);
			}
			else {
				oFile.Close();
				alert("complete");
			}
		}
	});

	var staName = staList.options[sort].innerHTML;
	var address = "北京," + staName;
	if (staName.indexOf("地铁") < 0) {
		address = address + "公交站";
	}

	geocoder.getLocation(address);
}

function queryAllReadStationFile() {
	////写文件
	oFso = new ActiveXObject("Scripting.FileSystemObject");  
	sFile = "d:/luckty.txt";
	oFile = oFso.OpenTextFile(sFile,8,true); //写方式打开

	var rFile = oFso.OpenTextFile("d:/stations.txt", 1, false);
	while (!rFile.AtEndOfStream) {
		staArray.push(rFile.ReadLine());
	}
	rFile.Close();

	_queryOneReadStationFile();
}

function queryAllReadFormControl()
{
	////写文件
	oFso = new ActiveXObject("Scripting.FileSystemObject");  
	sFile = "d:/luckty.txt";
	oFile = oFso.OpenTextFile(sFile,8,true); //写方式打开

	queryOneReadFormControl();
}

//! 从站点列表文件读取一行，向qq地图查询经纬度信息
function _queryOneReadStationFile() {
	var sortTextbox = document.getElementById("sort");
	var staId = staArray[sort].substr(0, staArray[sort].indexOf(','));
	var staName = staArray[sort].substr(staArray[sort].indexOf(',') + 1);

	//创建对象实例
	geocoder = new qq.maps.Geocoder({
		//若服务请求成功，则运行以下函数，并将结果传入
		complete:function(result){
			var item = "<" + result.detail.location + ">" + "," + result.detail.address;
			var line = staId + "," + staName + "," + item;
			oFile.WriteLine(line);

			sort++;
			sortTextbox.value = sort.toString();
			if (sort < staArray.length) {
				setTimeout("_queryOneReadStationFile()", 50);
			}
			else {
				oFile.Close();
				alert("complete");
			}
		},
		//若服务请求失败，则运行以下函数
		error:function(){
			var line = staId + "," + "fail";
			oFile.WriteLine(line);

			sort++;
			sortTextbox.value = sort.toString();
			if (sort < staArray.length) {
				setTimeout("_queryOneReadStationFile()", 10);
			}
			else {
				oFile.Close();
				alert("complete");
			}
		}
	});

	var address = "北京," + staName;
	if (staName.indexOf("地铁") < 0) {
		address = address + "公交站";
	}
	//alert(staId + "," + staName + "," + address);
	geocoder.getLocation(address);
}

// 将站点列表的内容写入到文本本件
function writeStations(){
    var staList = document.getElementById("stationList");

	oFso = new ActiveXObject("Scripting.FileSystemObject");  
	sFile = "d:/stations.txt";
	oFile = oFso.OpenTextFile(sFile,8,true); //写方式打开

	for (var i = 0, l = staList.options.length; i < l; i ++) {
		oFile.WriteLine(staList.options[i].value + "," + staList.options[i].innerHTML);
	}
	oFile.Close();
	alert("complete.");
}

</script>
</head>
<body onload="init()">

<div>
    <?php
        require_once("global.php");
        require_once(DBACCESS_MODULE_PATH . "DBAccess.php");

        $dbAccess = new DBAccess();
		$sql = "select id,station from station";
		//$sql = "select id,station from station" . " where id between 610 and 620";
        $result = $dbAccess->execSql($sql);

        $index = 0;

        //echo('<select id="stationList" name="list" multiple="true" size="10">');
        //while ($row=$result->fetch_assoc()) {
            //$line = sprintf('<option value= "%s">%s</option>', $row['id'], $row['station']);
            //echo($line);
            //$index++;
			////if ($index > 10) {
				////break;
			////}
        //}
        //echo('</select>');

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
	<input type="textbox" name="" id="sort" value="0" />
    <button onclick="queryAllReadStationFile()">queryAll</button>
    <button onclick="queryOnlyOne()">queryInput</button>
    <button onclick="getnums()">info</button>
</div>

<div style="width:603px;height:300px" id="container"></div>

<p>输入地址，点击search进行地址解释。</p>

</body>
</html>
