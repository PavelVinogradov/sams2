#!/bin/sh

# В этом скрипте нужно обращать внимание только на массивы versions и db_cmd.
# Количество элементов в них должно совпадать, а идеология такая:
# Чтобы обновить базу с версии versions[i-1] до версии versions[i], нужно выполнить команды db_cmd[i]
# Первый элемент в массиве db_cmd должен быть пустой

versions=("1.9.9" "2.9.9" "3.9.9" "4.9.9" "5.9.9" "6.9.9" "7.9.9")
db_cmd[0]=""
db_cmd[1]="CREATE TABLE sysinfo (                              \
 s_proxy_id INT              NOT NULL ,                        \
 s_name     VARCHAR( 50 )    NOT NULL ,                        \
 s_version  VARCHAR( 10 )    NOT NULL ,                        \
 s_author   VARCHAR( 30 )        NULL DEFAULT 'anonymous',     \
 s_info     VARCHAR( 1024 )  NOT NULL DEFAULT 'not available', \
 s_date     DATETIME         NOT NULL ,                        \
 s_status   INT              NOT NULL                          \
);"
db_cmd[2]="ALTER TABLE redirect ADD s_dest VARCHAR( 128 ) NULL;"
db_cmd[3]="ALTER TABLE shablon ADD s_shablon_id2 BIGINT( 20 ) UNSIGNED NULL;"

db_cmd[4]="CREATE TABLE auth_param (	\
 s_auth varchar(4) default '', 		\
 s_param varchar(50) default '', 	\
 s_value varchar(50) default ''		\
);					\
INSERT INTO auth_param VALUES('ncsa', 'enabled', '0');	\
INSERT INTO auth_param VALUES('ldap', 'enabled', '0');	\
INSERT INTO auth_param VALUES('adld', 'enabled', '0');	\
INSERT INTO auth_param VALUES('ncsa', 'enabled', '0');	\
INSERT INTO auth_param VALUES('ip', 'enabled', '1');"
db_cmd[5]="ALTER TABLE squiduser ADD s_ulimit VARCHAR(1);"
db_cmd[6]="TRUNCATE TABLE sysinfo; \
ALTER TABLE sysinfo ADD s_row_id SERIAL PRIMARY KEY FIRST;"



DBUSER=`grep ^DB_USER /etc/sams2.conf | awk -F'=' '{print $2}'`
DBPASS=`grep ^DB_PASSWORD /etc/sams2.conf | awk -F'=' '{print $2}'`
DBNAME=`grep ^SAMS_DB /etc/sams2.conf | awk -F'=' '{print $2}'`
DBVERSION=`mysql --user=$DBUSER --password=$DBPASS --database=$DBNAME -ss --execute="select s_version from websettings;"`

idx=0
while [ $idx -lt ${#versions[*]} ] ; do
    prev_idx=$idx
    idx=$(($idx + 1))
    from_v=${versions[$prev_idx]}
    to_v=${versions[$idx]}
    if [ x"${db_cmd[$idx]}" == x ] ; then
        continue
    fi

    if [ $DBVERSION == $from_v ] ; then
        echo "*** Upgrading from $from_v to $to_v"
        mysql --user=$DBUSER --password=$DBPASS --database=$DBNAME -ss --execute="${db_cmd[$idx]}"
        DBVERSION=${versions[$idx]}
        mysql --user=$DBUSER --password=$DBPASS --database=$DBNAME -ss --execute="update websettings set s_version='$DBVERSION'"
    fi
done

