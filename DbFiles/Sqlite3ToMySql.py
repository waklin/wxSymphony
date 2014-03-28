#-*- encoding:utf-8 -*-
import MySQLdb
import sqlite3
import sys
import codecs

reload(sys)
sys.setdefaultencoding('utf-8')

def transfer(sqliteConn, mysqlConn, srcTableName, dstTableName, baseNo, ids, cityNo = 0):
    mysqlCursor = mysqlConn.cursor()
    sql = "select * from %s" % dstTableName
    mysqlCursor.execute(sql)

    columns = [i[0] for i in mysqlCursor.description]
    columnsCopy = [i for i in columns if i != "city"]

    l = range(0, len(columnsCopy))
    for i in l:
        if columnsCopy[i] in ids:
            columnsCopy[i] = "%s + %d" % (columnsCopy[i], baseNo)
    fmtStr = ",".join(columnsCopy)

    # 从sqlite中读取数据放到list对象
    sqliteCursor = sqliteConn.cursor()
    sqliteCursor.execute("select %s from %s" % (fmtStr, srcTableName))
    rows = []
    while True:
        row =sqliteCursor.fetchone()
        if row != None:
            rowList = [i for i in row]
            if "city" in columns:
                rowList.insert(columns.index("city"), cityNo)
            rows.append(rowList)
        else:
            break

    fmtStr = ",".join(["%s"] * len(columns))
    sql = "insert %s values(%s)" % (dstTableName, fmtStr)
    while True:
        if len(rows) > 1000:
            mysqlCursor.executemany(sql, rows[:1000])
            rows = rows[1000:]
        else:
            mysqlCursor.executemany(sql, rows)
            break

    mysqlConn.commit();

# 导入城市数据
def importCity(cityDb, mysqlConn, baseNo, cityNo):
    sqliteConn = sqlite3.connect(cityDb)
    ids = ["id"]    #值需要在加上baseNo的字段名称
    transfer(sqliteConn, conn, "station", "station", baseNo, ids, cityNo)
    transfer(sqliteConn, conn, "lines", "route", baseNo, ids, cityNo)
    ids = ["id", "station", "lineid"]
    transfer(sqliteConn, conn, "stations", "stations", baseNo, ids)

    sqliteConn.close()

conn=MySQLdb.connect(host='127.0.0.1',user='root',passwd='811225',port=3306,charset="utf8")
#conn.select_db("symphony")
conn.select_db("new_db")

#importCity("./beijing", conn, 100000, 1)
importCity("./beijing", conn, 200000, 2)

conn.commit()
