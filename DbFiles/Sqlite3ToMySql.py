#-*- encoding:utf-8 -*-
import MySQLdb
import sqlite3
import sys

reload(sys)
sys.setdefaultencoding('utf-8')


#rows = []
#while True:
    #row =sqliteCurosor.fetchone()
    #if row != None:
        #rows.append(row)
    #else:
        #break

def transfer(sqliteConn, mysqlConn, srcTableName, dstTableName):
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
    mysqlCursor.executemany(sql, rows)

sqliteConn = sqlite3.connect("./wxSymphony/DbFiles/beijing")
conn=MySQLdb.connect(host='127.0.0.1',user='root',passwd='811225',port=3306,charset="utf8")
conn.select_db("symphony")

transfer(sqliteConn, conn, "lines", "buslines")

conn.commit()
conn.close()
