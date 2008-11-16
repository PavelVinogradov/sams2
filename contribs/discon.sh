#!/bin/bash
#
# Author: Kudryashov Dmitriy (overmailed)
# License: GPL2
#
# DESCRIPTION
# Скрипт для принудительного отключения пользователя качающего файл, длина которого превышает
# установленный пользователю лимит. 
# 
# SETUP
# 1. Нужно положить в директорию scripts sams'а 
# 2. Выбрать в настройках sams выполнять его при отключении пользователя ("скрипт, используемый для отправки сообщения при отключении пользователей").
#
# LIMITATION
# Из недостатков можно отметить, что при отключении одного из пользователей, прерываются (но 
# не остаются долго запрещёнными) все соеинения идущие с его хоста. 
#
# LOGIC
# 1. из логов сквида выгрепываем последнюю запись о нашем юзере, из которой берём его ip
# 2. из netstat выгрепываем ESTABLISHED соединения с этого ip до сквида
# 3. на каждое соединение натравиливаем tcpkill
#
# BUGS
# tcpkill вешается только на ethernet-интерфейсы, при том на все разом
#
# имя пользователя получается параметром. закомментируйте, чтобы запускать напрямую из SAMS
# SAMSUSERNAME=$1

# конфиг SAMS
SAMSCONF=/etc/sams.conf

# наш лог
MYLOG="/var/log/sams_discon.log"


DATE=`date`
echo "--" >> $MYLOG
echo "Started at ${DATE}." >> $MYLOG

SQUIDLOGDIR=`cat $SAMSCONF | sed -n "/SQUIDLOGDIR=*/ s/SQUIDLOGDIR=// p"`
SQUIDCACHEFILE=`cat $SAMSCONF | sed -n "/SQUIDCACHEFILE=*/ s/SQUIDCACHEFILE=// p"`
SQUIDROOTDIR=`cat $SAMSCONF | sed -n "/SQUIDROOTDIR=*/ s/SQUIDROOTDIR=// p"`

# лог и конфиг сквида
SQUIDLOG="${SQUIDLOGDIR}/$SQUIDCACHEFILE"
SQUIDCONF="${SQUIDROOTDIR}/squid.conf"

# список ethernet-интерфейсов - на них будем вешать tcpkill
ETHERIFS=`/sbin/ifconfig | sed -n "/encap:Ethernet/ s/ .*//g p"`

#получаем порты сквида в формате "ip:port" или "port"
SQUIDIPPORTS=`cat $SQUIDCONF | grep http_port | grep -v --regexp=# | awk '{print $2}'`

# если в формате "port" то преобразуем в ":port"
SINGLEPORT=`echo $SQUIDIPPORTS | awk 'BEGIN{FS=":"};{print $2}'`
if [ -z "$SINGLEPORT" ] ; then
    SQUIDIPPORTS=":$SQUIDIPPORTS"
fi

# последняя строчка в логе сквида, касающаяся $SAMSUSERNAME
LAST_ENTRY=`tac $SQUIDLOG | grep -m 1 -i $SAMSUSERNAME`
SAMSUSERIP=`echo $LAST_ENTRY | awk {'print $3'}`

echo "${SAMSUSERNAME}'s source ip is $SAMSUSERIP" >> $MYLOG 


function get_samsuserconn {
    SAMSUSERCONN=""
    for i in $SQUIDIPPORTS ; do
        NEW_SAMSUSERCONN=`netstat --ip --tcp -n | sed -n "/${i}.*${SAMSUSERIP}.*ESTABLISHED/ s/ /_/g p" `	
	SAMSUSERCONN="$SAMSUSERCONN $NEW_SAMSUSERCONN"
    done
}

# получаем соединения нашего клиента со сквидом в список SAMSUSERCONN
get_samsuserconn

# убиваем все соединения из списка
for i in $SAMSUSERCONN ; do
    i=`echo $i | sed -e "s/_/ /g"`
    SRC=`echo $i | awk '{print $5}' | awk 'BEGIN{FS=":"};{print $1}'`
    SPORT=`echo $i | awk '{print $5}' | awk 'BEGIN{FS=":"};{print $2}'`
    DST=`echo $i | awk '{print $4}' | awk 'BEGIN{FS=":"};{print $1}'`
    DPORT=`echo $i | awk '{print $4}' | awk 'BEGIN{FS=":"};{print $2}'`

    # проходимся по всем ethernet интерфейсам
    for j in $ETHERIFS ; do
	echo "Killing connection ${SRC}:$SPORT -> ${DST}:$DPORT on ${j}" >> $MYLOG
        tcpkill -i $j src host $SRC and src port $SPORT and dst host $DST and dst port ${DPORT} &
        NEW_TCPKILL_PID=$!
        TCPKILL_PIDS="$TCPKILL_PIDS $NEW_TCPKILL_PID"
    done
done

# нет соединений с клиентом
if [ -z $SAMSUSERCONN ]; then
    echo "No connections with ${SAMSUSERNAME}. Not dropping" >> $MYLOG
    DATE=`date`
    echo "Finished at ${DATE}." >> $MYLOG
    exit 0
fi


echo -n "Waiting for connections to become unestablised " >> $MYLOG

# ждём пока соединения от нашего клиента не закроются
while [ true ] ; do
    if [ -z $SAMSUSERCONN ] ; then
	echo " done." >> $MYLOG
	break
    fi
    echo -n "." >> $MYLOG
    sleep 5
    get_samsuserconn
done

# убиваем все tcpkill
echo -n "Killing tcpkills " >> $MYLOG
for i in $TCPKILL_PIDS ; do
    echo -n "." >> $MYLOG
    kill $i
done
echo " done." >> $MYLOG

DATE=`date`
echo "Finished at ${DATE}." >> $MYLOG
