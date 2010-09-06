<?php
function CreateSAMSUser()
{
  $adduser1=0;
  $adduser2=0;
  if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
  if(isset($_GET["suname"])) $suname=$_GET["suname"];
  if(isset($_GET["supasswd"])) $supasswd=$_GET["supasswd"];
  if(isset($_GET["samsusername"])) $samsusername=$_GET["samsusername"];
  if(isset($_GET["samsuserpasswd"])) $samsuserpasswd=$_GET["samsuserpasswd"];

echo " $hostname-$suname-$supasswd-$samsusername-$samsuserpasswd";
  $link=mysql_connect($hostname,$suname,$supasswd);
  if($link!=NULL)
    echo "<BR>Connect to database = Ok";
  $result=mysql_query("GRANT ALL ON $SAMSConf->SAMSDB.* TO $samsusername@$hostname IDENTIFIED BY '$samsuserpasswd'");
  $row=mysql_fetch_array($result);
  if($result!=NULL)
   {
      echo "<BR>user $samsusername created";
      $adduser1=1;
   }
  $result=mysql_query("GRANT ALL ON $SAMSConf->LOGDB.* TO $samsusername@$hostname IDENTIFIED BY '$samsuserpasswd'");
  $row=mysql_fetch_array($result);
  if($result!=NULL)
   {
      echo "<BR>user $samsusername created";
      $adduser2=1;
   }
  if($adduser1!=0&&$adduser2!=0)
    echo "<BR>replace user name and pasword into sams.conf";
      
  //print("<FORM NAME=\"back\" ACTION=\"main.php?show=exe&function=loadsamsdbform&filename=createdb.php&setup=setup \" METHOD=POST>\n");
  print("<FORM NAME=\"back\" ACTION=\"main.php?show=exe&function=userdoc \" METHOD=POST>\n");
  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Ok\">\n");
  printf("</FORM>");

}

function CreateSAMSDatabase()
{
  if(isset($_GET["hostname"])) $hostname=$_GET["hostname"];
  if(isset($_GET["suname"])) $suname=$_GET["suname"];
  if(isset($_GET["supasswd"])) $supasswd=$_GET["supasswd"];
  if(isset($_GET["MAX_FILES_SIZE"])) $MAX_FILES_SIZE=$_GET["MAX_FILES_SIZE"];
//  if(isset($_GET["filename"])) $filename=$_GET["filename"];

//echo "-$suname-@-$hostname-:-$supasswd  $MAX_FILES_SIZE<BR>";
  echo "<H3>Create database to $hostname, user $suname</H3>";
//  $listfilename=$_FILES["userfile"]["name"];
  $filename=$_FILES['userfile']['tmp_name'];
  $fname=$_FILES['userfile']['name'];
echo "<H1>filename=$filename=$_FILES[userfile][tmp_name]=$fname</H1>";
exit(0);
  $link=@mysql_connect($hostname, $suname, $supasswd) || die (mysql_error());
//  if($link && mysql_select_db($this->MYSQLDATABASE)==FALSE)
//        echo "Error connection to database<BR>";

//  echo "LISTFILENAME = $listfilename <BR>";
//  copy($_FILES['userfile']['tmp_name'], "data/urllist.txt");
//  $finp=fopen("data/urllist.txt","r");
  $finp=fopen($filename,"r");
  while(feof($finp)==0)
    {
       $string=fgets($finp, 10000);
       $string=trim($string);
     //print("INSERT INTO urls SET urls.url=\"$string\",type=\"$id\" <BR> ");
       $length=strlen($string);
//       echo "$length <BR>";
       if($length>1)
	 {
           $result=mysql_query($string);
	   printf("$string => ");
	   if($result>0)
		printf(" Ok<BR>");
	   else
		printf(" No<BR>");
	 }
    }
  fclose($finp);

}


function loadsamsdbform()
{
    print("<HTML><HEAD>");
    print("<META  content=\"text/html; charset=$CHARSET\" http-equiv='Content-Type'>");
    print("<META HTTP-EQUIV=\"expires\" CONTENT=\"THU, 01 Jan 1970 00:00:01 GMT\">");
    print("<META HTTP-EQUIV=\"pragma\" CONTENT=\"no-cache\">");
    print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">\n");

    print("</head>\n");
    print("<body LINK=\"#ffffff\" VLINK=\"#ffffff\">\n");//     if($autherrorc==1&&$autherrort>0)
    print("<center>\n");

//      echo "<IMG SRC=\"icon/classic/warning.jpg\" ALIGN=LEFT>";
//      echo "Web интерфейс не смог подсоединиться к базе SAMS.<BR>";
//      echo "Сейчас вы сможете создать базы SAMS или проверить соединение с ними";


  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  print("<TR>");
  print("<TD><IMG SRC=\"icon/classic/db_48.jpg\">\n");
  print("<TD><FONT COLOR=\"BLUE\"><H2>Create SAMS database</H2></FONT>\n");
  print("</TABLE>");
  print("<P>");
//if (errors = false) {document.myForm.submit();}

       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function SetLocalhost(formname,formname2,userform)\n");
       print("{\n");
       print("    formname.hostname.value=userform.hostname.value;\n");
       print("    formname2.hostname.value=userform.hostname.value;\n");
       print("}\n");
       print("function SetRootName(formname,formname2,userform)\n");
       print("{\n");
       print("    formname.suname.value=userform.suname.value;\n");
       print("    formname2.suname.value=userform.suname.value;\n");
       print("}\n");
       print("function SetRootPasswd(formname,formname2,userform)\n");
       print("{\n");
       print("    formname.supasswd.value=userform.supasswd.value;\n");
       print("    formname2.supasswd.value=userform.supasswd.value;\n");
       print("}\n");
       print("</SCRIPT>\n");

  print("<FORM NAME=\"createsamsuser\" action=\"main.php\" TARGET=\"BLANK\">\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"createsamsuser\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"createdb.php\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"setup\" value=\"setup\" >\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  print("<TR>");
  print("<TD><B>Введите имя пользователя MySQL, имеющего право на создание базы</B><TD> <INPUT TYPE=\"TEXT\" NAME=\"suname\" VALUE=\"root\" OnChange=SetRootName(SAMSDB,SQUIDDB,createsamsuser) >\n");
  print("<TR>");
  print("<TD><B>введите пароль:</B><TD> <INPUT TYPE=\"PASSWORD\" NAME=\"supasswd\" OnChange=SetRootPasswd(SAMSDB,SQUIDDB,createsamsuser)>\n");
  print("<TR>");
  print("<TD><B>Хост:</B><TD> <INPUT TYPE=\"TEXT\" NAME=\"hostname\" OnChange=SetLocalhost(SAMSDB,SQUIDDB,createsamsuser)>\n");
  print("</TABLE>");

  print("<P>");
  print("<H3>Создать пользователя sams в mysql</H3>");
  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  print("<TR>");
  print("<TD><B>User SAMS name:</B><TD> <INPUT TYPE=\"TEXT\" NAME=\"samsusername\" VALUE=\"sams\" >\n");
  print("<TR>");
  print("<TD><B>User SAMS password:</B><TD> <INPUT TYPE=\"PASSWORD\" NAME=\"samsuserpasswd\" >\n");
  print("<TR>");
  print("<TD><TD><INPUT TYPE=\"SUBMIT\" value=\"Create user\" >\n");
  print("</TABLE>");
  print("</FORM>\n");


  print("<P>");
  print("<H3>Создать базу sams squidctrl в mysql</H3>");
  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  print("<FORM NAME=\"SAMSDB\" ACTION=\"main.php\" TARGET=\"BLANK\">\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"createsamsdatabase\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"createdb.php\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"setup\" value=\"setup\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"suname\" VALUE=\"root\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"supasswd\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"hostname\" VALUE=\"localhost\"  >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILES_SIZE\" value=\"1048576\">\n");
  print("<TR>");
  print("<TD>Select file sams_db.sql<TD><INPUT TYPE=\"FILE\" NAME=\"userfile\" value=\"Загрузить файл\">\n");
  print("<TR>");
  print("<TD><TD><INPUT TYPE=\"SUBMIT\" value=\"Load file\">\n");
  print("</FORM>\n");
  print("</TABLE>");


  print("<P>");
  print("<H3>Создать базу sams squidctrl в mysql</H3>");
  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  print("<FORM NAME=\"SQUIDDB\" ACTION=\"main.php\" TARGET=\"BLANK\">\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"createsamsdatabase\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"createdb.php\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"setup\" value=\"setup\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"suname\" VALUE=\"root\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"supasswd\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"hostname\" VALUE=\"localhost\"  >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILES_SIZE\" value=\"1048576\">\n");
  print("<TR>");
  print("<TD>Select file squid_db.sql<TD><INPUT TYPE=\"FILE\" NAME=\"userfile\" value=\"Загрузить файл\">\n");
  print("<TR>");
  print("<TD><TD><INPUT TYPE=\"SUBMIT\" value=\"Load file\">\n");
  print("</FORM>\n");
  print("</TABLE>");


  
  print("</center>\n");
  print("</body></html>\n");

}


/*
function loadsamsdbform()
{
    print("<HTML><HEAD>");
    print("<META  content=\"text/html; charset=$CHARSET\" http-equiv='Content-Type'>");
    print("<META HTTP-EQUIV=\"expires\" CONTENT=\"THU, 01 Jan 1970 00:00:01 GMT\">");
    print("<META HTTP-EQUIV=\"pragma\" CONTENT=\"no-cache\">");
    print("<link rel=\"STYLESHEET\" type=\"text/css\" href=\"$SAMSConf->ICONSET/tree.css\">\n");

    print("</head>\n");
    print("<body LINK=\"#ffffff\" VLINK=\"#ffffff\">\n");//     if($autherrorc==1&&$autherrort>0)
    print("<center>\n");

//      echo "<IMG SRC=\"icon/classic/warning.jpg\" ALIGN=LEFT>";
//      echo "Web интерфейс не смог подсоединиться к базе SAMS.<BR>";
//      echo "Сейчас вы сможете создать базы SAMS или проверить соединение с ними";

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  print("<TR>");
  print("<TD><IMG SRC=\"icon/classic/db_48.jpg\">\n");
  print("<TD><FONT COLOR=\"BLUE\"><H2>Create SAMS database</H2></FONT>\n");
  print("</TABLE>");
  print("<P>");
//if (errors = false) {document.myForm.submit();}
       print("<SCRIPT language=JAVASCRIPT>\n");
       print("function CreateSAMSUser(formname)\n");
       print("{\n");
       print("    var hostname=formname.hostname.value\n");
       print("    var suname=formname.suname.value\n");
       print("    var supasswd=formname.supasswd.value\n");
       print("    var username=formname.samsusername.value\n");
       print("    var userpasswd=formname.samsuserpasswd.value\n");
       print("    var string=\"main.php?show=exe&function=createsamsuser&filename=createdb.php&setup=setup&hostname=\" + hostname + \"&suname=\" + suname+\"&supasswd=\" + supasswd+\"&samsusername=\" + username + \"&samsuserpasswd=\" + userpasswd\n");
       print("    value=window.confirm( string  );\n");
       //print("parent.basefrm.location.href=\"main.php?show=exe&function=createsamsuser&filename=createdb.php&setup=setup&\"+string;\n");  
       print("parent.basefrm.location.href=string;\n");  
       print("}\n");
       
       print("function CreateSAMSDatabase(formname)\n");
       print("{\n");
       print("    var hostname=formname.hostname.value\n");
       print("    var suname=formname.suname.value\n");
       print("    var supasswd=formname.supasswd.value\n");
       print("    var samsdb=formname.samsdb.value\n");
       print("    formname.dbname.value=\"samsdb\"\n");
       print("    value=window.confirm( \"Create db\"+formname.dbname.value  );\n");
       print("    formname.submit();\n");
       //print("    var string=\"hostname=\"+hostname+\"&suname=\"+suname+\"&supasswd=\"+supasswd+\"&filename=\"+samsdb\n");
       //print("    value=window.confirm( string  );\n");
       //print("parent.basefrm.location.href=\"main.php?show=exe&function=createsamsdatabase&filename=createdb.php&setup=setup&\"+string;\n");  
       print("}\n");

       
       print("function CreateSQUIDDatabase(formname)\n");
       print("{\n");
       print("    var hostname=formname.hostname.value\n");
       print("    var suname=formname.suname.value\n");
       print("    var supasswd=formname.supasswd.value\n");
       print("    var samsdb=formname.squiddb.value\n");
       print("    formname.dbname.value=\"squiddb\"\n");
       print("    value=window.confirm( \"Create db\"+formname.dbname.value  );\n");
       print("    formname.submit();\n");
//       print("    var string=\"hostname=\"+hostname+\"&suname=\"+suname+\"&supasswd=\"+supasswd+\"&filename=\"+samsdb\n");
//       print("    value=window.confirm( string  );\n");
//       print("parent.basefrm.location.href=\"main.php?show=exe&function=createsamsdatabase&filename=createdb.php&setup=setup&\"+string;\n");  
       print("}\n");
       print("</SCRIPT>\n");


//  PageTop("import_48.jpg","CREATE DATABASE");
//  print("<FORM NAME=\"createdbform\" ENCTYPE=\"multipart/form-data\" ACTION=\"main.php?\" METHOD=POST>\n");
//   print("<FORM NAME=\"createdbform\" ENCTYPE=\"multipart/form-data\" ACTION=\"main.php\" METHOD=POST>\n");
// ACTION=\"main.php?show=exe&function=createsamsdatabase&filename=createdb.php&setup=setup \" METHOD=POST>\n");
  print("<FORM NAME=\"createdbform\" enctype=\"multipart/form-data\" action=\"main.php\" method=\"post\">\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"createsamsdatabase\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"filename\" value=\"createdb.php\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"setup\" value=\"setup\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"dbname\" >\n");
  print("    <INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILES_SIZE\" value=\"1048576\">\n");

  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  print("<TR>");
  print("<TD><B>Введите имя пользователя MySQL, имеющего право на создание базы</B><TD> <INPUT TYPE=\"TEXT\" NAME=\"suname\" VALUE=\"root\" >\n");
  print("<TR>");
  print("<TD><B>введите пароль:</B><TD> <INPUT TYPE=\"PASSWORD\" NAME=\"supasswd\">\n");
  print("<TR>");
  print("<TD><B>Хост:</B><TD> <INPUT TYPE=\"TEXT\" NAME=\"hostname\">\n");
  print("</TABLE>");

  print("<P>");
  print("<H3>Создать пользователя sams в mysql</H3>");
  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  print("<TR>");
  print("<TD><B>User SAMS name:</B><TD> <INPUT TYPE=\"TEXT\" NAME=\"samsusername\" VALUE=\"sams\" >\n");
  print("<TR>");
  print("<TD><B>User SAMS password:</B><TD> <INPUT TYPE=\"PASSWORD\" NAME=\"samsuserpasswd\" >\n");
  print("<TR>");
  print("<TD><TD><INPUT TYPE=\"BUTTON\" value=\"Create user\" OnClick=CreateSAMSUser(createdbform)>\n");
  print("</TABLE>");

  print("<P>");
  print("<H3>Создать базу sams squidctrl в mysql</H3>");
  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  print("<TR>");
  print("<TD>Select file sams_db.sql<TD><INPUT TYPE=\"FILE\" NAME=\"samsdb\" value=\"Select sams_db.sql\">\n");
  print("<BR><INPUT TYPE=\"BUTTON\" value=\"Загрузить файл sams_db.sql и создать базу\" OnClick=CreateSAMSDatabase(createdbform)>\n");
  print("</TABLE>");

  print("<P>");
  print("<H3>Создать базу sams squidlog в mysql</H3>");
  print("<TABLE CLASS=samstable WIDTH=\"90%\" BORDER=0>\n");
  print("<TR>");
  print("<TD>Select file squid_db.sql<TD><INPUT TYPE=\"FILE\" NAME=\"squiddb\" value=\"Select squid_db.sql\">\n");
  print("<BR><INPUT TYPE=\"BUTTON\" value=\"Загрузить файл squid_db.sql и создать базу\" OnClick=CreateSQUIDDatabase(createdbform)>\n");
  print("</TABLE>");

  print("<BR><INPUT TYPE=\"SUBMIT\" value=\" файл и создать базу\">\n");
  print("</FORM>\n");
  
  print("</center>\n");
  print("</body></html>\n");

}
/*

?>
