#-*- encoding:utf-8 -*-
import MySQLdb
import sqlite3
import sys

reload(sys)
sys.setdefaultencoding('utf-8')

def transfer(sqliteConn, mysqlConn, srcTableName, dstTableName):
    # 从sqlite中读取数据放到list对象
    sqliteCursor = sqliteConn.cursor()
    sqliteCursor.execute("select * from %s" % srcTableName)
    rows = []
    while True:
        row =sqliteCursor.fetchone()
        if row != None:
            rows.append(row)
        else:
            break

    mysqlCursor = mysqlConn.cursor()
    ts = ["%s"] * len(rows[0])
    fmtStr = ",".join(ts)
    sql = "delete from %s" % dstTableName
    mysqlCursor.execute(sql)

    sql = "insert %s values(%s)" % (dstTableName, fmtStr)
    print sql
    
    while True:
        if len(rows) > 1000:
            mysqlCursor.executemany(sql, rows[:1000])
            rows = rows[1001:]
        else:
            mysqlCursor.executemany(sql, rows)
            break

sqliteConn = sqlite3.connect("./beijing")
conn=MySQLdb.connect(host='127.0.0.1',user='root',passwd='811225',port=3306,charset="utf8")
#conn.select_db("symphony")
conn.select_db("new_db")

transfer(sqliteConn, conn, "astation", "astation")
transfer(sqliteConn, conn, "category", "category")
transfer(sqliteConn, conn, "company", "company")
transfer(sqliteConn, conn, "coordinate", "coordinate")
transfer(sqliteConn, conn, "estation", "estation")
transfer(sqliteConn, conn, "station", "station")
transfer(sqliteConn, conn, "lines", "route")
transfer(sqliteConn, conn, "stations", "stations")

conn.commit()
conn.close()
