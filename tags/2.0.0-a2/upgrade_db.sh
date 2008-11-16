#!/bin/sh

# В этом скрипте нужно обращать внимание только на массивы versions и db_cmd
# количество элементов в них должно совпадать, а идеология такая:
# чтобы обновить базу с версии versions[i] до следующей, нужно выполнить команды db_cmd[i]
# последний элемент в массиве versions должен содержать предыдущую версию БД
# Если же там текущая версия БД, то последний элемент в массиве db_cmd должен быть пустой

versions=("1.9.9" "2.9.9" "3.9.9")
db_cmd[0]="select s_version from websettings;"
db_cmd[1]="select s_proxy_id, s_description from proxy;"
db_cmd[2]=""




DBUSER=`grep ^DB_USER /etc/sams2.conf | awk -F'=' '{print $2}'`
DBPASS=`grep ^DB_PASSWORD /etc/sams2.conf | awk -F'=' '{print $2}'`
DBNAME=`grep ^SAMS_DB /etc/sams2.conf | awk -F'=' '{print $2}'`
DBVERSION=`mysql --user=$DBUSER --password=$DBPASS --database=$DBNAME -ss --execute="select s_version from websettings;"`

idx=0
for v in ${versions[*]} ; do
#    vv=${versions[$idx]}
    next_idx=$(($idx + 1))
    if [ $DBVERSION == $v ] ; then
        if [ x"${db_cmd[$idx]}" == x ] ; then
            continue
        fi
        echo "Upgrading from $v"
        mysql --user=$DBUSER --password=$DBPASS --database=$DBNAME -ss --execute="${db_cmd[$idx]}"
        DBVERSION=${versions[$next_idx]}
        #mysql --user=$DBUSER --password=$DBPASS --database=$DBNAME -ss --execute="update websettings set s_version='$DBVERSION'"
    fi
    idx=$next_idx
done
