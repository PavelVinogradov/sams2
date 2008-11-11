#!/bin/sh

# В этом скрипте нужно обращать внимание только на массивы versions и db_cmd.
# Количество элементов в них должно совпадать, а идеология такая:
# Чтобы обновить базу с версии versions[i-1] до версии versions[i], нужно выполнить команды db_cmd[i]
# Первый элемент в массиве db_cmd должен быть пустой

versions=("1.9.9" "2.9.9" "3.9.9")
db_cmd[0]=""
db_cmd[1]="CREATE TABLE sysinfo (                              \
 s_proxy_id INT              NOT NULL ,                        \
 s_name     VARCHAR( 50 )    NOT NULL ,                        \
 s_version  VARCHAR( 10 )    NOT NULL ,                        \
 s_author   VARCHAR( 30 )        NULL DEFAULT 'anonymous',     \
 s_info     VARCHAR( 1024 )  NOT NULL DEFAULT 'not available', \
 s_date     DATETIME         NOT NULL ,                        \
 s_status   INT              NOT NULL                          \
) ENGINE = MYISAM;"
db_cmd[2]="ALTER TABLE redirect ADD s_dest VARCHAR( 128 ) NULL ;"




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

