#!/bin/sh
TMP=./tmp


##################################################################
#
#   Create SAMS user and databases
#
##################################################################
function CreateSAMSDatabases()
{
#Get MySQL root password
 rm -f $TMP/tempmsg2
 echo "Insert MySQL root password." > $TMP/tempmsg2
 dialog --title "Get MySQL root password" \
   --inputbox "`cat $TMP/tempmsg2`" 16 67 2> $TMP/newport
 if [ $? = 1 -o $? = 255 ]; then
    rm -f  $TMP/tempmsg2
    exit
 fi
ROOTPASS="`cat $TMP/newport 2> /dev/null`"
echo "1234567890" >  $TMP/newport

dialog --title "Create SAMS database " --menu "\n\
Create SAMS user for MySQL database?\n" 15 74 3 \
"Yes" "Create" \
"No" "No"  2> $TMP/typeselect
if [ $? = 1 -o $? = 255 ]; then
   rm -f $TMP/typeselect 
   exit
fi
TYPESELECT="`cat $TMP/typeselect 2> /dev/null`"
rm $TMP/typeselect
clear
if [ "$TYPESELECT" = "Yes" ]; then
  SAMSUSER=1

  #Get SAMS MySQL user password
  rm -f $TMP/tempmsg2
  echo "Insert NEW password for SAMS MySQL user." > $TMP/tempmsg2
  dialog --title "Create MySQL SAMS user" \
    --inputbox "`cat $TMP/tempmsg2`" 16 67 2> $TMP/newport
  if [ $? = 1 -o $? = 255 ]; then
    rm -f  $TMP/tempmsg2
    exit
  fi
  SAMSPASS="`cat $TMP/newport 2> /dev/null`"
  echo "1234567890" >  $TMP/newport
  mysql -u root --password="$ROOTPASS" --execute="GRANT ALL ON squidctrl.* TO sams@localhost IDENTIFIED BY \"$SAMSPASS\";"
  mysql -u root --password="$ROOTPASS" --execute="GRANT ALL ON squidlog.* TO sams@localhost IDENTIFIED BY \"$SAMSPASS\";"

  
  CONFIGFILEPATH="`cat $TMP/CONFIGFILEPATH 2> /dev/null`"
  dialog --title "Create MySQL SAMS user " --menu "\n\
  Insert user sams and password into sams.conf?\n$CONFIGFILEPATH" 15 74 3 \
  "Yes" "Insert" \
  "No" "No"  2> $TMP/typeselect
  if [ $? = 1 -o $? = 255 ]; then
     rm -f $TMP/typeselect 
     exit
  fi
  TYPESELECT="`cat $TMP/typeselect 2> /dev/null`"
  rm $TMP/typeselect
  clear
  if [ "$TYPESELECT" = "Yes" ]; then
     echo "MYSQLUSER=sams" >>  $CONFIGFILEPATH
     echo "MYSQLPASSWORD=$SAMSPASS" >> $CONFIGFILEPATH
  fi

fi



dialog --title "Create SAMS database " --menu "\n\
Create SAMS databases?\n" 15 74 3 \
"Yes" "Create" \
"No" "No"  2> $TMP/typeselect
if [ $? = 1 -o $? = 255 ]; then
   rm -f $TMP/typeselect 
   exit
fi
TYPESELECT="`cat $TMP/typeselect 2> /dev/null`"
rm $TMP/typeselect
clear
if [ "$TYPESELECT" = "Yes" ]; then
  mysql -u root --password="$ROOTPASS" <./mysql/sams_db.sql 
  mysql -u root --password="$ROOTPASS" <./mysql/squid_db.sql 
fi


echo "root passwd = $ROOTPASS"
echo "Create sams user? $SAMSUSER"
echo "sams passwd = $SAMSPASS"

return
}









##################################################################
#   checking for configure options
##################################################################
function CheckPCREHeaders()
{
	DST_PCRE_INC=""
	for ac_dir in /usr/local/include/pcre /usr/include/pcre /usr/local/include /usr/include; do
	    if test -f "$ac_dir/pcre.h"; then
	        DST_PCRE_INC=$ac_dir
	        break;
	    fi
	done
}
function CheckPCRELibraryes()
{
	DST_PCRE_LIBS=""
	for ac_dir in /usr/local/lib/pcre /usr/lib/pcre /usr/local/lib /usr/lib; do
	    if test -f "$ac_dir/libpcre.so"; then
	        DST_PCRE_LIBS=$ac_dir
	        break;
	    fi
	done
}

function CheckMySQLHeaders()
{
	DST_MYSQL_INC=""
	for ac_dir in /usr/local/mysql/include/mysql /usr/local/include/mysql /usr/include/mysql /usr/local/include /usr/include; do
	    if test -f "$ac_dir/mysql.h"; then
	        DST_MYSQL_INC=$ac_dir
	        break;
	    fi
	done
}
function CheckMySQLLibraryes()
{
	DST_MYSQL_LIBS=""
	for ac_dir in /usr/local/mysql/lib/mysql /usr/local/lib/mysql /usr/lib/mysql /usr/local/lib /usr/lib; do
	    if test -f "$ac_dir/libmysqlclient.so"; then
	        DST_MYSQL_LIBS=$ac_dir
	        break;
	    fi
	done
}
function CheckHttpdRootFolder()
{
	DST_HTTPD_LOC=""
	for ac_dir in /var/www/html /var/www/htdocs; do
	    if test -d "$ac_dir"; then
	        DST_HTTPD_LOC=$ac_dir
	        break;
	    fi
	done
}
function CheckRCDFolder()
{
	DST_RCD_LOC='/etc/init.d'
	for ac_dir in /etc/init.d /usr/local/etc/rc.d; do
	    if test -f "$ac_dir"; then
	        DST_RCD_LOC=$ac_dir
	        break;
	    fi
	done
}

##################################################################
#
#   Run configure script
#
##################################################################
function RunConfigureScriptOld()
{
 ALLFOUND="YES"
 PREFIX="/usr/local"
 echo "Install architecture-independent files in PREFIX: $PREFIX\n" >$TMP/cofigure.cache
 CONFIGFILEPATH="/etc/sams.conf"
 echo "Default runtime config file:                      $CONFIGFILEPATH\n" >>$TMP/cofigure.cache
 CheckMySQLHeaders
 echo "Path for MySQL includes:                          $DST_MYSQL_INC\n" >>$TMP/cofigure.cache
 CheckMySQLLibraryes
 echo "Path for MySQL libraryes:                         $DST_MYSQL_LIBS\n" >>$TMP/cofigure.cache
 CheckPCREHeaders
 echo "Path for PCRE includes:                          $DST_PCRE_INC\n" >>$TMP/cofigure.cache
 CheckPCRELibraryes
 echo "Path for PCRE libraryes:                         $DST_PCRE_LIBS\n" >>$TMP/cofigure.cache
 CheckHttpdRootFolder
 echo "Path for HTTPD root folder:                       $DST_HTTPD_LOC\n" >>$TMP/cofigure.cache
 CheckRCDFolder 
 echo "Path for the startup scripts folder:              $DST_RCD_LOC\n" >>$TMP/cofigure.cache
 echo "" >>$TMP/cofigure.cache

 if [ $DST_PCRE_INC -eq "-1" ]; then
       ALLFOUND="-1"
       echo "Path for PCRE includes\n" > $TMP/notfoundoptions
 fi
 if [ $DST_PCRE_LIBS -eq "-1" ]; then
      ALLFOUND="-1"
       echo "Path for PCRE libraryes\n" > $TMP/notfoundoptions
 fi
 if [ $DST_MYSQL_INC -eq "-1" ]; then
       ALLFOUND="-1"
       echo "Path for MySQL includes\n" > $TMP/notfoundoptions
 fi
 if [ $DST_MYSQL_LIBS -eq "-1" ]; then
      ALLFOUND="-1"
       echo "Path for MySQL libraryes\n" > $TMP/notfoundoptions
 fi
 if [ $DST_HTTPD_LOC -eq "-1" ]; then
      ALLFOUND="-1"
       echo "Path for HTTPD root folder\n" > $TMP/notfoundoptions
 fi
 
 if [ $ALLFOUND -eq "-1" ]; then
    NOTFOUNDOPTIONS="`cat $TMP/notfoundoptions 2> /dev/null`"
    TYPESELECT="`cat $TMP/cofigure.cache 2> /dev/null`"
    dialog --title "Check the configure script options:" --menu "\n\
    $TYPESELECT\nNot found next options:\n$NOTFOUNDOPTIONS\nSet up configure options?\n" 21 74 3 \
    "Yes" "Set up configure options" \
    "No" "Exit"  2> $TMP/typeselect
    if [ $? = 1 -o $? = 255 ]; then
       rm -f $TMP/typeselect 
       exit
    fi
    rm -f $TMP/notfoundoptions
    TYPESELECT="`cat $TMP/typeselect 2> /dev/null`"
    rm $TMP/typeselect

    case $TYPESELECT in
      "No")
         return
         ;;
      "Yes")
         rm -f $TMP/options 
	 
	 if [ $DST_MYSQL_INC -eq "-1" ]; then
             echo "Insert path to MySQL header files." > $TMP/tempmsg2
             dialog --title "path:" \
             --inputbox "`cat $TMP/tempmsg2`" 16 67 2>> $TMP/tempmsg
            if [ $? = 1 -o $? = 255 ]; then
                rm -f $TMP/tempmsg $TMP/tempmsg2
                exit
            fi
            MYSQLINCPATH="`cat $TMP/tempmsg 2> /dev/null`"
            echo "--with-mysql-includes=$MYSQLINCPATH \ " >> $TMP/options
            rm -f $TMP/tempmsg $TMP/tempmsg2
         fi
         
	 if [ $DST_MYSQL_LIBS -eq "-1" ]; then
             echo "Insert path to MySQL linrary files." > $TMP/tempmsg2
             dialog --title "path:" \
             --inputbox "`cat $TMP/tempmsg2`" 16 67 2>> $TMP/tempmsg
            if [ $? = 1 -o $? = 255 ]; then
                rm -f $TMP/tempmsg $TMP/tempmsg2
                exit
            fi
            MYSQLLIBPATH="`cat $TMP/tempmsg 2> /dev/null`"
            echo "--with-mysql-libpath=$MYSQLLIBPATH \ " >> $TMP/options
            rm -f $TMP/tempmsg $TMP/tempmsg2
         fi
	 
	 if [ $DST_HTTPD_LOC -eq "-1" ]; then
             echo "Insert path to HTTP server root folder" > $TMP/tempmsg2
             dialog --title "path:" \
             --inputbox "`cat $TMP/tempmsg2`" 16 67 2>> $TMP/tempmsg
            if [ $? = 1 -o $? = 255 ]; then
                rm -f $TMP/tempmsg $TMP/tempmsg2
                exit
            fi
            HTTPROOTPATH="`cat $TMP/tempmsg 2> /dev/null`"
            echo "--with-httpd-locations=$HTTPROOTPATH \ " >> $TMP/options
            rm -f $TMP/tempmsg $TMP/tempmsg2
         fi
         echo " " >> $TMP/options
	 
	 
	 ;;
    esac
    TYPESELECT="`cat $TMP/cofigure.cache 2> /dev/null`"
    OPTIONS="`cat $TMP/options 2> /dev/null`"
    dialog --title "Run the configure script" --menu "\n\
    configure \n$OPTIONS\n \nRun the configure script?\n" 21 74 3 \
    "Yes" "Run" \
    "No" "Exit"  2> $TMP/typeselect
    TYPESELECT="`cat $TMP/typeselect 2> /dev/null`"
    rm $TMP/typeselect

    case $TYPESELECT in
      "No")
         return
         ;;
      "Yes")
         dialog --infobox "wait... " 5 74 
         ./configure $OPTIONS > $TMP/cofigure.cache 2>$TMP/cofigure.cache
         dialog --title "configure" --textbox "$TMP/cofigure.cache" 22 77
         rm -f $TMP/options $TMP/cofigure.cache 2>/dev/null
	 ;;
    esac
  
    
    
 else
    TYPESELECT="`cat $TMP/cofigure.cache 2> /dev/null`"
    dialog --title "Check the configure script options" --menu "\n\
    $TYPESELECT\n Run the configure script?\n" 21 74 3 \
    "Yes" "Run" \
    "No" "Exit"  2> $TMP/typeselect
    TYPESELECT="`cat $TMP/typeselect 2> /dev/null`"
    rm $TMP/typeselect
    case $TYPESELECT in
      "No")
         return
         ;;
      "Yes")
         dialog --infobox "wait... " 5 74 
         ./configure > $TMP/cofigure.cache 2>$TMP/cofigure.cache
         dialog --title "configure" --textbox "$TMP/cofigure.cache" 22 77
         rm -f $TMP/options $TMP/cofigure.cache 2>/dev/null
	 ;;
    esac
 
 fi
 return
}



##################################################################
#
#   Run make
#
##################################################################
function RunMake()
{
  dialog --infobox "wait... " 5 74 
  make > $TMP/make.cache 2>$TMP/make.cache
  dialog --title "make" --textbox "$TMP/make.cache" 22 77
  rm -f $TMP/options $TMP/make.cache 2>/dev/null

}

##################################################################
#
#   Run make install
#
##################################################################
function RunMakeInstall()
{
  dialog --infobox "wait... " 5 74 
  make install > $TMP/make.cache 2>$TMP/make.cache
  dialog --title "make install" --textbox "$TMP/make.cache" 22 77
  rm -f $TMP/options $TMP/make.cache 2>/dev/null

}

##################################################################
#
#   Run make uninstall
#
##################################################################
function RunMakeUnInstall()
{
         dialog --infobox "wait... " 5 74 
         make uninstall > $TMP/make.cache 2>$TMP/make.cache
         dialog --title "make uninstall" --textbox "$TMP/make.cache" 22 77
         rm -f $TMP/options $TMP/make.cache 2>/dev/null

}


##################################################################
#
#   Run configure script
#
##################################################################
function EditPathToPCREIncludeFiles()
{
  rm -f $TMP/tempmsg2
  echo "Insert path" > $TMP/tempmsg2
 dialog --title "Insert path to PCRE includes" \
   --inputbox "`cat $TMP/tempmsg2`" 16 67 "$DST_PCRE_INC" 2> $TMP/DST_PCRE_INC
 if [ $? = 1 -o $? = 255 ]; then
    rm -f  $TMP/tempmsg2
    exit
 fi
 DST_PCRE_INC="`cat $TMP/DST_PCRE_INC 2> /dev/null`"
 return
}

function EditPathToPCRELibraryesFiles()
{
  rm -f $TMP/tempmsg2
  echo "Insert path" > $TMP/tempmsg2
 dialog --title "Insert path to PCRE libraryes" \
   --inputbox "`cat $TMP/tempmsg2`" 16 67 "$DST_PCRE_LIBS" 2> $TMP/DST_PCRE_LIBS
 if [ $? = 1 -o $? = 255 ]; then
    rm -f  $TMP/tempmsg2
    exit
 fi
 DST_PCRE_LIBS="`cat $TMP/DST_PCRE_LIBS 2> /dev/null`"
 return
}

function EditPathToMySQLIncludeFiles()
{
  rm -f $TMP/tempmsg2
  echo "Insert path" > $TMP/tempmsg2
 dialog --title "Insert path to MySQL includes" \
   --inputbox "`cat $TMP/tempmsg2`" 16 67 "$DST_MYSQL_INC" 2> $TMP/DST_MYSQL_INC
 if [ $? = 1 -o $? = 255 ]; then
    rm -f  $TMP/tempmsg2
    exit
 fi
 DST_MYSQL_INC="`cat $TMP/DST_MYSQL_INC 2> /dev/null`"
 return
}

function EditPathToMySQLLibraryesFiles()
{
  rm -f $TMP/tempmsg2
  echo "Insert path" > $TMP/tempmsg2
 dialog --title "Insert path to MySQL libraryes" \
   --inputbox "`cat $TMP/tempmsg2`" 16 67 "$DST_MYSQL_LIBS" 2> $TMP/DST_MYSQL_LIBS
 if [ $? = 1 -o $? = 255 ]; then
    rm -f  $TMP/tempmsg2
    exit
 fi
 DST_MYSQL_LIBS="`cat $TMP/DST_MYSQL_LIBS 2> /dev/null`"
 return
}

function EditPathToHTTPDRootFolder()
{
  rm -f $TMP/tempmsg2
  echo "Insert path" > $TMP/tempmsg2
 dialog --title "Insert path to HTTPD root folder" \
   --inputbox "`cat $TMP/tempmsg2`" 16 67 "$DST_HTTPD_LOC" 2> $TMP/DST_HTTPD_LOC
 if [ $? = 1 -o $? = 255 ]; then
    rm -f  $TMP/tempmsg2
    exit
 fi
 DST_HTTPD_LOC="`cat $TMP/DST_HTTPD_LOC 2> /dev/null`"
 return
}

function EditPathToRCDFolder()
{
  rm -f $TMP/tempmsg2
  echo "Insert path" > $TMP/tempmsg2
 dialog --title "Insert path to HTTPD root folder" \
   --inputbox "`cat $TMP/tempmsg2`" 16 67 "$DST_RCD_LOC" 2> $TMP/DST_RCD_LOC
 if [ $? = 1 -o $? = 255 ]; then
    rm -f  $TMP/tempmsg2
    exit
 fi
 DST_RCD_LOC="`cat $TMP/DST_RCD_LOC 2> /dev/null`"
 return
}

function EditPathToConfFile()
{
  rm -f $TMP/tempmsg2
  echo "Insert path" > $TMP/tempmsg2
 dialog --title "Insert path to HTTPD root folder" \
   --inputbox "`cat $TMP/tempmsg2`" 16 67 "$CONFIGFILEPATH" 2> $TMP/CONFIGFILEPATH
 if [ $? = 1 -o $? = 255 ]; then
    rm -f  $TMP/tempmsg2
    exit
 fi
 CONFIGFILEPATH="`cat $TMP/CONFIGFILEPATH 2> /dev/null`"
 return
}

function EditPrefixPath()
{
  rm -f $TMP/tempmsg2
  echo "Insert path" > $TMP/tempmsg2
 dialog --title "Insert path to HTTPD root folder" \
   --inputbox "`cat $TMP/tempmsg2`" 16 67 "$PREFIX" 2> $TMP/PREFIX
 if [ $? = 1 -o $? = 255 ]; then
    rm -f  $TMP/tempmsg2
    exit
 fi
 PREFIX="`cat $TMP/PREFIX 2> /dev/null`"
 return
}

function RunConfigureScript()
{
 
 CSELECT=" "  
 ALLFOUND="YES"
 PREFIX="/usr/local"
 echo "Install architecture-independent files in PREFIX: $PREFIX\n" >$TMP/cofigure.cache
 echo "$PREFIX" >$TMP/PREFIX

 CONFIGFILEPATH="/etc/sams.conf"
 echo "Default runtime config file:                      $CONFIGFILEPATH\n" >>$TMP/cofigure.cache
 echo "$CONFIGFILEPATH" >$TMP/CONFIGFILEPATH
 
 CheckMySQLHeaders
 echo "Path for MySQL includes:                          $DST_MYSQL_INC\n" >>$TMP/cofigure.cache
 echo "$DST_MYSQL_INC" >$TMP/DST_MYSQL_INC
 
 CheckMySQLLibraryes
 echo "Path for MySQL libraryes:                         $DST_MYSQL_LIBS\n" >>$TMP/cofigure.cache
 echo "$DST_MYSQL_LIBS" >$TMP/DST_MYSQL_LIBS
 
 CheckPCREHeaders
 echo "Path for PCRE includes:                          $DST_PCRE_INC\n" >>$TMP/cofigure.cache
 echo "$DST_PCRE_INC" >$TMP/DST_PCRE_INC
 
 CheckPCRELibraryes
 echo "Path for PCRE libraryes:                         $DST_PCRE_LIBS\n" >>$TMP/cofigure.cache
 echo "$DST_PCRE_LIBS" >$TMP/DST_PCRE_LIBS
 
 CheckHttpdRootFolder
 echo "Path for HTTPD root folder:                       $DST_HTTPD_LOC\n" >>$TMP/cofigure.cache
 echo "$DST_HTTPD_LOC" >$TMP/DST_HTTPD_LOC
 
 CheckRCDFolder 
 echo "Path for the startup scripts folder:              $DST_RCD_LOC\n" >>$TMP/cofigure.cache
 echo "$DST_RCD_LOC" >>$TMP/DST_RCD_LOC
 
 echo "" >>$TMP/cofigure.cache

 while [ "$CSELECT" != "Exit" ]
do

 PCRE_INC="`wc -L $TMP/DST_PCRE_INC |  tr "$TMP/DST_PCRE_INC" "\0" `"
 PCRE_LIBS="`wc -L $TMP/DST_PCRE_LIBS |  tr "$TMP/DST_PCRE_LIBS" "\0" `"
 MYSQL_INC="`wc -L $TMP/DST_MYSQL_INC |  tr "$TMP/DST_MYSQL_INC" "\0" `"
 MYSQL_LIBS="`wc -L $TMP/DST_MYSQL_LIBS |  tr "$TMP/DST_MYSQL_LIBS" "\0" `"
 HTTPD_LOC="`wc -L $TMP/DST_HTTPD_LOC |  tr "$TMP/DST_HTTPD_LOC" "\0" `"

 rm -f $TMP/str1 
 rm -f $TMP/str2 
 
 if [ $PCRE_INC -eq 0 ]; then
     echo " insert path to:" > $TMP/str1 
     echo "     PCRE includes" >> $TMP/str2 
 fi
 if [ $PCRE_LIBS -eq 0 ]; then
     echo " insert path to:" > $TMP/str1 
     echo "     PCRE libraries" >> $TMP/str2 
 fi
 if [ $MYSQL_INC -eq 0 ]; then
     echo " insert path to:" > $TMP/str1 
     echo "     MySQL includes" >> $TMP/str2 
 fi
 if [ $MYSQL_LIBS -eq 0 ]; then
     echo " insert path to:" > $TMP/str1 
     echo "     MySQL libraries" >> $TMP/str2 
 fi
 if [ $HTTPD_LOC -eq 0 ]; then
     echo " insert path to:" > $TMP/str1 
     echo "     HTTPD root folder" >> $TMP/str2 
 fi
 
 STR1="`cat $TMP/str1 2> /dev/null`"
 STR2="`cat $TMP/str2 2> /dev/null`"

 dialog --title "" --menu "\n Edit the configure script options and run configure \
 \n$STR1\n$STR2" 21 78 14 \
"prefix" "Edit path to SAMS files: $PREFIX" \
"sams.conf" "Edit path to SAMS config file: $CONFIGFILEPATH" \
"rc.d" "Edit path for the startup scripts folder: $DST_RCD_LOC"  \
"includes" "Edit path for MySQL includes: $DST_MYSQL_INC" \
"libraryes" "Edit path for MySQL libraryes: $DST_MYSQL_LIBS" \
"pcreinc" "Edit path for PCRE includes: $DST_PCRE_INC" \
"pcrelib" "Edit path for PCRE libraryes: $DST_PCRE_LIBS" \
"httpd" "Edit path for HTTPD root folder: $DST_HTTPD_LOC" \
"configure" "RUN the configure script" \
"exit" "Exit"  2> $TMP/typeselect
if [ $? = 1 -o $? = 255 ]; then
   rm -f $TMP/typeselect 
   exit
fi

  TYPESELECT="`cat $TMP/typeselect 2> /dev/null`"
  rm $TMP/typeselect
  case $TYPESELECT in
  ########### Выход из программы #############
    "exit")
        CSELECT="Exit"
        ;;
    "prefix")
        clear
        EditPrefixPath
        ;;
    "sams.conf")
        clear
        EditPathToConfFile
        ;;
    "rc.d")
        clear
        EditPathToRCDFolder
        ;;
    "pcreinc")
        clear
        EditPathToPCREIncludeFiles
        ;;
    "pcrelib")
        clear
        EditPathToPCRELibraryesFiles
        ;;
    "includes")
        clear
        EditPathToMySQLIncludeFiles
        ;;
    "libraryes")
        clear
        EditPathToMySQLLibraryesFiles
        ;;
    "httpd")
        clear
        EditPathToHTTPDRootFolder
        ;;
  ####### Run the configure script ############
    "configure")
        dialog --infobox "wait... " 5 74 
        ./configure --prefix=$PREFIX --with-configfile=$CONFIGFILEPATH 	--with-rcd-locations=$DST_RCD_LOC --with-httpd-locations=$DST_HTTPD_LOC 	--with-mysql-includes=$DST_MYSQL_INC  --with-mysql-libpath=$DST_MYSQL_LIBS > $TMP/cofigure.cache 2>$TMP/cofigure.cache
        dialog --title "configure" --textbox "$TMP/cofigure.cache" 22 77
        rm -f $TMP/options $TMP/cofigure.cache 2>/dev/null
        
	;;
  
  esac
 
done
    
 return
}

function RunNewInstallation()
{
while [ "$NSELECT" != "Exit" ]
do

dialog --title "SAMS new installation" --menu "\n Select: $CSELECT" 15 74 6 \
"configure" "Run the configure script" \
"make" "Will now build up SAMS" \
"make install" "Will now install SAMS"  \
"mysql" "Will now create SAMS database into MySQL"  \
"exit" "Exit"  2> $TMP/typeselect2
if [ $? = 1 -o $? = 255 ]; then
   rm -f $TMP/typeselect2
   exit
fi
TYPESELECT2="`cat $TMP/typeselect2 2> /dev/null`"
rm $TMP/typeselect2

case $TYPESELECT2 in
########### Выход из программы #############
  "exit")
      NSELECT="Exit"
  ;;
####### Create SAMS MySQL database ############
"mysql")
   clear
   CreateSAMSDatabases
  ;;
####### make ############
"make")
   clear
   RunMake
  ;;
####### make ############
"make install")
   clear
   RunMakeInstall
  ;;
####### Run the configure script ############
"configure")
   clear
   CSELECT=" "  
   RunConfigureScript
  ;;
  
esac

 if [ $NSELECT = "Exit" ]; then
    rm $TMP/SELECT2 
     break
 fi
done

}




##################################################################
#
#   Run make update
#
##################################################################
function RunMakeUpdate()
{
         dialog --infobox "wait... " 5 74 
         make update > $TMP/make.cache 2>$TMP/make.cache
         dialog --title "make update" --textbox "$TMP/make.cache" 22 77
         rm -f $TMP/options $TMP/make.cache 2>/dev/null

}
##################################################################
#
#   update SAMS databases
#
##################################################################
function UpdateSAMSDatabases()
{
         dialog --infobox "wait... " 5 74 
         cd update  
         php upgrade_mysql_table.php >../$TMP/make.cache 2>../$TMP/make.cache
         dialog --title "make update" --textbox "../$TMP/make.cache" 22 77
	 cd ../
         rm -f $TMP/options $TMP/make.cache 2>/dev/null

}

function UpdateSAMS()
{
while [ "$USELECT" != "Exit" ]
do

dialog --title "Update SAMS " --menu " \n
Stop all SAMS daemons and squid.\n" 15 74 6 \
"configure" "Run the configure script" \
"make" "Will now build up SAMS" \
"samsbin" "Update the SAMS scripts and binaryes" \
"samsbase" "Update the SAMS databases" \
"exit" "Exit"  2> $TMP/typeselect2
if [ $? = 1 -o $? = 255 ]; then
   rm -f $TMP/typeselect2
   exit
fi
TYPESELECT2="`cat $TMP/typeselect2 2> /dev/null`"
rm $TMP/typeselect2

case $TYPESELECT2 in
########### Выход из программы #############
  "exit")
      USELECT="Exit"
  ;;
####### Update SAMS binaryes ############
"samsbin")
   RunMakeUpdate
   clear
  ;;
####### Update SAMS MySQL database ############
"samsbase")
   clear
   UpdateSAMSDatabases
  ;;
####### make ############
"make")
   clear
   RunMake
  ;;
####### Run the configure script ############
"configure")
   clear
   CSELECT=" "
   RunConfigureScript
  ;;
  
esac

 if [ $USELECT = "Exit" ]; then
    rm $TMP/USELECT 
     break
 fi
done

}








##################################################################
#
#   Тело программы
#
##################################################################

FOUNDDIALOG=0
for ac_dir in /usr /usr/local/bin /usr/bin; do
    if test -f "$ac_dir/dialog"; then
        FOUNDDIALOG=1
        break;
    fi
done

# if [ $FOUNDDIALOG -eq "0" ]; then
#       echo "dialog (http://hightek.org/dialog) not found"
#       echo "dialog is a tool to display dialog boxes from shell scripts" 
#       exit
# fi



if [ ! -d $TMP ]; then
  mkdir -p $TMP
#else 
#   rm $TMP/*
fi

while [ "$SELECT1" != "Exit" ]
do

dialog --title "SAMS setup " --menu "\n Welcome\
 to SAMS (SQUID Account Management System) Setup." 15 74 6 \
"new" "Run the new installation of the SAMS" \
"update" "Update SAMS" \
"uninstall" "Delete SAMS" \
"exit" "Exit"  2> $TMP/typeselect
if [ $? = 1 -o $? = 255 ]; then
   rm -f $TMP/typeselect 
   exit
fi
TYPESELECT="`cat $TMP/typeselect 2> /dev/null`"
rm $TMP/typeselect

case $TYPESELECT in
########### Выход из программы #############
  "exit")
      SELECT1="Exit"
  ;;
####### make ############
"new")
  NSELECT=" "
  TYPESELECT2=" "
  RunNewInstallation
  ;;
####### Run the configure script ############
"update")
   clear
   UpdateSAMS
  ;;
####### Run the configure script ############
"uninstall")
   clear
   RunMakeUnInstall
  ;;
  
esac

 if [ $SELECT1= "Exit" ]; then
    rm $TMP/SELECT 
     break
 fi
done
