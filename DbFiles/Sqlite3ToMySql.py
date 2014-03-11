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
    #sql = "delete from %s" % dstTableName
    #mysqlCursor.execute(sql)

    sql = "insert %s values(%s)" % (dstTableName, fmtStr)
    print sql
    
    while True:
        if len(rows) > 1000:
            mysqlCursor.executemany(sql, rows[:1000])
            rows = rows[1000:]
        else:
            mysqlCursor.executemany(sql, rows)
            break

    mysqlConn.commit()
    mysqlCursor.close()

sqliteConn = sqlite3.connect("./beijing")

conn=MySQLdb.connect(host='127.0.0.1',user='root',passwd='811225',port=3306,charset="utf8")
#conn.select_db("symphony")
conn.select_db("new_db")

transfer(sqliteConn, conn, "astation", "astation")
transfer(sqliteConn, conn, "category", "category")
transfer(sqliteConn, conn, "company", "company")
transfer(sqliteConn, conn, "lines", "route")
#transfer(sqliteConn, conn, "coordinate", "coordinate")
transfer(sqliteConn, conn, "estation", "estation")
transfer(sqliteConn, conn, "station", "station")
transfer(sqliteConn, conn, "stations", "stations")

#读取coordinatefromtecent_2文件，将每一行信息插入到coordinate表中
f = open("./fetchCoordinate/coordinatefromtecent_2.txt")
lines = f.readlines();
for line in lines:
    item = line.split(",")
    if len(item) < 5:
        continue
    mysqlCursor = conn.cursor()
    sql = "insert into coordinate(id,longitude,latitude,type) values(%s,%s,%s,0)" % (item[0], item[3].rstrip(">").strip(), item[2].lstrip("<").strip())
    mysqlCursor.execute(sql)

conn.commit()
mysqlCursor.close()

sqliteConn.close()
conn.close()
