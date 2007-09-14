<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function MoveUsersToShablon()
{
 if(isset($_GET["shablonname"])) $id=$_GET["shablonname"];
 if(isset($_GET["username"])) $users=$_GET["username"];

  for($i=0;$i<count($users);$i++)
    {
           $result=mysql_query("UPDATE squidusers SET shablon=\"$id\" WHERE squidusers.id=\"$users[$i]\"");
    }
  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=exe&function=shablonusers&id=$id&sid=ALL\";\n");
  print("</SCRIPT> \n");
}

function ShablonUsers()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];
  if(isset($_GET["sid"])) $sid=$_GET["sid"];

   $SAMSConf->access=UserAccess();
   if($SAMSConf->access!=2)     {       exit;     }
  
  db_connect($SAMSConf->SAMSDB) or exit();
  mysql_select_db($SAMSConf->SAMSDB)
       or print("Error\n");
  $result=mysql_query("SELECT * FROM shablons WHERE shablons.name=\"$id\" ");
  $row=mysql_fetch_array($result);
  $nick1=$row['nick'];
  PageTop("shablon.jpg","$shablon_1<BR>$shablontray_ShablonUsers_1 <FONT COLOR=\"BLUE\">$nick1</FONT>");

  print("<TABLE>\n");
  print("<TR><TD><B>$shablonbuttom_1_prop_UpdateShablonForm_19</B><TD><FONT COLOR=\"BLUE\">$row[auth]</FONT>\n");
  print("<TR><TD><B>$shablonbuttom_1_prop_UpdateShablonForm_4</B><TD><FONT COLOR=\"BLUE\">$row[traffic]</FONT>\n");
  if( $row['traffic'] == 0 )
 	print(" <FONT COLOR=\"BLUE\">(unlimited traffic)</FONT>");  
  print("<TR><TD><B>$shablonbuttom_1_prop_UpdateShablonForm_20</B><TD>");
  if($row['period']=="M")   
     print("<FONT COLOR=\"BLUE\">$shablonbuttom_1_prop_UpdateShablonForm_24</FONT>\n");
  else if($row['period']=="W")   
     print("<FONT COLOR=\"BLUE\">$shablonbuttom_1_prop_UpdateShablonForm_25</FONT>\n");
  else
	{
		print("<FONT COLOR=\"BLUE\">$row[period] $shablonbuttom_1_prop_UpdateShablonForm_17</FONT>\n");
		 print("<TR><TD>$shablonbuttom_1_prop_UpdateShablonForm_18:<TD><FONT COLOR=\"BLUE\">$row[clrdate]</FONT>");
	}
 $weekday=array("", "M","T","W","H","F","A","S");  
 print("<TR><TD><B>$shablonbuttom_1_prop_UpdateShablonForm_14 </B><TD><FONT COLOR=\"BLUE\">\n");
  for($i=1;$i<8;$i++)
     {
	if(strpos(" $row[days]","$weekday[$i]")>0)
		print("$week[$i] \n");
     }  
  print("<TR>\n");
  print("</TABLE>\n");


  $result=mysql_query("SELECT * FROM squidusers WHERE squidusers.shablon=\"$id\" ORDER BY nick");

 print("<H2>$shablontray_ShablonUsers_4: </H2>\n");
  print("<TABLE>\n");
  while($row=mysql_fetch_array($result))
      {
       print("<TR>\n");
       print("<TD>");
       if($row['enabled']>0)
         {
           if($SAMSConf->realtraffic=="real")
	     $traffic=$row['size']-$row['hit'];
           else
	     $traffic=$row['size'];
	   if($row['quotes']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE>=$traffic||$row['quotes']<=0)
              $gif="puser.gif";
           else
              if($row['quotes']>0)
                  $gif="quote_alarm.gif";
         }
       if($row['enabled']==0)
         {
            $gif="puserd.gif";
         }
       if($row['enabled']<0)
         {
            $gif="duserd.gif";
         }
       print("<IMG SRC=\"$SAMSConf->ICONSET/$gif\" TITLE=\"\"> ");
       print("<TD> <B>$row[nick] </B>");
       print("<TD> $row[family] ");
       print("<TD> $row[name] ");
       print("<TD> $row[soname] ");
      }
  print("</TABLE>\n");

  if($SAMSConf->access==2)
    {

	print("<SCRIPT language=JAVASCRIPT>\n");
        print("function SelectUsers(id)\n");
        print("{\n");
        print("   var shablon = \"main.php?show=exe&function=shablonusers&id=$id&sid=\" +  id ; \n");
        print("   parent.basefrm.location.href=shablon;\n");
        print("}\n");
	print("</SCRIPT>\n");

      print("<P><BR><B>$shablontray_ShablonUsers_2 $nick1:</B> ");
      print("<FORM NAME=\"moveform\" ACTION=\"main.php\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"moveuserstoshablon\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"shablonname\" value=\"$id\">\n");

      print("<SELECT NAME=\"shablonid\" onchange=SelectUsers(moveform.shablonid.value)>\n");
      $result=mysql_query("SELECT * FROM shablons ORDER BY nick");
      if($sid=="ALL")
        print("<OPTION VALUE=\"ALL\" SELECTED> ALL\n");
      else
        print("<OPTION VALUE=\"ALL\"> ALL\n");

      while($row=mysql_fetch_array($result))
         {
	    $SECTED="";
	    if($row['name']==$sid)
		$SECTED="SELECTED";
	    if($row['name']!=$id)
               print("<OPTION VALUE=\"$row[name]\" $SECTED> $row[nick]\n");
         }
      print("</SELECT>\n");

      print("<SELECT NAME=\"username[]\" SIZE=10 MULTIPLE >\n");
      if($sid=="ALL")
	$result=mysql_query("SELECT * FROM squidusers WHERE squidusers.shablon!=\"$id\" ORDER BY nick");
      else
	$result=mysql_query("SELECT * FROM squidusers WHERE squidusers.shablon=\"$sid\"&&squidusers.shablon!=\"$id\" ORDER BY nick");
      while($row=mysql_fetch_array($result))
         {
            print("<OPTION ID=\"$row[id]\" VALUE=$row[id]> $row[nick]\n");
         }
      print("</SELECT>\n");
      print("<P> <INPUT TYPE=\"SUBMIT\" VALUE=\"$shablontray_ShablonUsers_3 '$nick1'\" \n> ");
      print("</FORM>\n");
    } 

}


function ShablonTray()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if(isset($_GET["id"])) $id=$_GET["id"];

  if($SAMSConf->access==2)
    {
      db_connect($SAMSConf->SAMSDB) or exit();
      mysql_select_db($SAMSConf->SAMSDB);
      $result=mysql_query("SELECT * FROM shablons WHERE name=\"$id\" ");
      $row=mysql_fetch_array($result);

      print("<SCRIPT>\n");
      print("        parent.basefrm.location.href=\"main.php?show=exe&function=shablonusers&id=$id&sid=ALL\";\n");
      print("</SCRIPT> \n");

      print("<TABLE border=0 WIDTH=\"100%\">\n");
      print("<TR>\n");
      print("<TD VALIGN=\"TOP\" WIDTH=\"30%\">");
      print("<B>$shablontray_ShablonTray_1 <BR><FONT SIZE=\"+1\" COLOR=\"BLUE\">$row[nick]</FONT></B>\n");

      ExecuteFunctions("./src", "shablonbuttom","1");
    }
  print("<TD>\n");
  print("</TABLE>\n");


}

?>
