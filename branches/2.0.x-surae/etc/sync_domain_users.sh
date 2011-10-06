#!/bin/sh
# this script imports new domain users info SAMS2 database (courrently only MySQL supported)
DOMAIN=$1
NEW_USERS_GROUP='Новые пользователи домена - автодобавление'
NEW_USERS_TEMPLATE='Новые пользователи домена - автодобавление'
if [ ! $DOMAIN ]; then DOMAIN=$(/usr/bin/wbinfo --own-domain); fi

ID_NEW_USERS_GROUP=$(/usr/bin/mysql --user sams --database sams2db -p'$sams200' -e "select s_group_id from sgroup where s_name = '$NEW_USERS_GROUP';" --raw --batch --skip-column-names);
if [ $ID_NEW_USERS_GROUP ]
then 
echo new users group id is $ID_NEW_USERS_GROUP
else 
/usr/bin/mysql --user sams --database sams2db -p'$sams200' -e "insert into sgroup ( s_name ) values ( '$NEW_USERS_GROUP' );"
ID_NEW_USERS_GROUP=$(/usr/bin/mysql --user sams --database sams2db -p'$sams200' -e "select s_group_id from sgroup where s_name = '$NEW_USERS_GROUP';" --raw --batch --skip-column-names)
echo just added new users group and its id is $ID_NEW_USERS_GROUP
fi

ID_NEW_USERS_TEMPLATE=$(/usr/bin/mysql --user sams --database sams2db -p'$sams200' -e "select s_shablon_id from shablon where s_name = '$NEW_USERS_TEMPLATE';" --raw --batch --skip-column-names);
if [ $ID_NEW_USERS_TEMPLATE ]
then 
echo new users template id is $ID_NEW_USERS_TEMPLATE
else 
/usr/bin/mysql --user sams --database sams2db -p'$sams200' -e "insert into shablon ( s_name, s_auth, s_quote, s_period, s_clrdate, s_alldenied, s_shablon_id2 ) values ( '$NEW_USERS_TEMPLATE', 'ntlm', '1', 'M', '1980-01-01', '0', '-1' );"
ID_NEW_USERS_TEMPLATE=$(/usr/bin/mysql --user sams --database sams2db -p'$sams200' -e "select s_shablon_id from shablon where s_name = '$NEW_USERS_TEMPLATE';" --raw --batch --skip-column-names)
echo just added new users template and its id is $ID_NEW_USERS_TEMPLATE
fi

sams2userlist=$(/usr/bin/mysql --user sams --database sams2db -p'$sams200' -e "select s_nick from squiduser where s_domain = '$DOMAIN';" --raw --batch --skip-column-names)
echo sams2 user list is $sams2userlist
for domain_user in $(/usr/bin/wbinfo --domain-users --domain $DOMAIN | /usr/bin/cut --delimiter $(/usr/bin/wbinfo --separator) --fields 2);
do 
	/bin/echo $sams2userlist | /usr/bin/tr ' ' '\n' | /bin/grep --quiet -x $domain_user
	if [ $? -eq 1 ]; then /usr/bin/mysql --user sams --database sams2db -p'$sams200' -e "insert into squiduser ( s_nick, s_domain, s_enabled, s_group_id, s_shablon_id, s_name, s_family, s_quote, s_size, s_soname, s_ip, s_passwd, s_hit, s_autherrorc, s_autherrort ) VALUES ( '$domain_user', '$DOMAIN', '-1', '$ID_NEW_USERS_GROUP', '$ID_NEW_USERS_TEMPLATE', '', '', '0', '0', '', '', '', '0', '0', '0' );"; echo new user $domain_user insert query was made; fi
done	
