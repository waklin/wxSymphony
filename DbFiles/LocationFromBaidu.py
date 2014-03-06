#-*- encoding:utf-8 -*-
import MySQLdb
import sqlite3
import sys
import httplib
import urllib
import json

reload(sys)
sys.setdefaultencoding('utf-8')

def toHex(s):
    lst = []
    for ch in s:
        lst.append(hex(ord(ch)).replace("0x", "%"))
    return reduce(lambda x,y:x+y, lst)

#s = "?小"
#print toHex(s)

sqliteConn = sqlite3.connect("./beijing")
sqliteCursor = sqliteConn.cursor()
sqliteCursor.execute("select station from %s" % "station")
nums = 0
while True:
    row = sqliteCursor.fetchone()
    if row != None:
        #向baidu地图服务发送请求
        httpConn = httplib.HTTPConnection("api.map.baidu.com");
        #stationName = row[0] + "公交车站"
        #query = stationName.encode("u8")
        query = "公交车站"
        region = "北京"
        ak = "Pwo9XmZTNu6RHdG2R2K0Zqes"
        output = "json"
        #lat = 39.979165
        #lng = 116.369819
        lat = 40.043847
        lng = 116.330863
        radius = 1000
        url = "/place/v2/search?q=%s&location=%s,%s&radius=%s&output=%s&ak=%s" % (toHex(query), lat, lng, radius, output, ak)
        print url
        httpConn.request("GET", url)

        result = httpConn.getresponse()
        print result.status, result.reason
        data = result.read()
        #print data

        jsonDecoded = json.loads(data)
        results = jsonDecoded["results"]
        for item in results:
            print item["name"], item["location"]["lat"], item["location"]["lng"]
            nums = nums + 1

        break
    else:
        break
print "parser nums: ", nums
httpConn.close()
sqliteConn.close()
