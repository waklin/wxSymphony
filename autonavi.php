<!DOCTYPE html>  
<html>  
<head>  
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
<title>范围内查询</title>  
<link rel="stylesheet" type="text/css" href="http://api.amap.com/Public/css/demo.Default.css" /> 
<script language="javascript" src="http://webapi.amap.com/maps?v=1.2&key=de5f8435a2b1432039f7103e1eb8d7bc"></script>
<script language="javascript">
var mapObj;
var marker = new Array();
var windowsArr = new Array();
//基本地图加载
function mapInit(){   
    mapObj = new AMap.Map("iCenter");     
}
//地点查询函数     
function placeSearch(){
    alert('Welcome!');
    document.write("document.write");

	mapObj.clearMap();
    var geocoder;  
    //加载地理编码插件  
    mapObj.plugin(["AMap.Geocoder"], function() { //加载地理编码插件  
        geocoder = new AMap.Geocoder({  
            radius: 1000, //以已知坐标为中心点，radius为半径，返回范围内兴趣点和道路信息  
            extensions: "all" //返回地址描述以及附近兴趣点和道路信息，默认“base”  
        });  
        //返回地理编码结果  
        AMap.event.addListener(geocoder, "complete", geocoder_CallBack);   
        //逆地理编码  
        //geocoder.getAddress(new AMap.LngLat(116.359119, 39.972121));  
        geocoder.getLocation("牡丹园北");
    });
}
function geocoder_CallBack(data) {
    document.write(data.info);
    document.write(data.resultNum.toString());
    if (data.resultNum > 0) {
        document.write("loop start.");
        for (var i = 0, l = data.resultNum; i < l; i ++) {
            document.write("loop 1" + "<br/>");
            var v = data.geocodes[i];
            document.write(v.location.toString() + v.formattedAddress + "<br/>");
        }
    }
}
</script>  
</head>  
<body onload="mapInit();">  
    <div id="iCenter"></div>
    <div class="demo_box">
        <p><input type="button" value="查询" onclick="placeSearch()"/><br />
        </p>
        <div id="r_title"><b>范围内查询结果:</b></div>
        <div id="result"> </div>
    </div>        
</body>
</html>
