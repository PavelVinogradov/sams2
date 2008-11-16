<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AllUserTray()
{
  global $adminname;
  global $AUTH;
  global $LANG;

  $lang="./lang/lang.$LANG";
  require($lang);
  $access=UserAccess();

  print("<SCRIPT>\n");
  print("        parent.basefrm.location.href=\"main.php?show=newuserform\";\n");
  print("</SCRIPT> \n");

  print("<P>\n");
  print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
  print("<TR>\n");
  print("<TD VALIGN=\"TOP\" WIDTH=\"30%\"\">");
  print("<B><FONT COLOR=\"BLUE\">$user_allusertray1</FONT></B>\n");

  if($access==2&&$AUTH=="ntlm")
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      GraphButton("main.php?show=adduserfromdomainform","basefrm","white_data/domain-32.jpg","white_data/domain-48.jpg","$user_allusertray2");
    }
  if($access>0)
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      GraphButton("main.php?show=allusertrafficform","basefrm","white_data/traffic_32.jpg","white_data/traffic_48.jpg","$user_allusertray3");

      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");

      GraphButton("main.php?show=allfilesizeform&groupname=$groupname&groupnick=$groupnick","basefrm","white_data/ftraffic_32.jpg","white_data/ftraffic_48.jpg","$user_allusertray4");

      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      GraphButton("main.php?show=showgrpercenttrafficform","basefrm","white_data/persent_32.jpg","white_data/persent_48.jpg","$user_allusertray5");

      print("<SCRIPT language=JAVASCRIPT>\n");
      print("function ReloadBaseFrame()\n");
      print("{\n");
      print("   window.location.reload();\n");
      print("}\n");
      print("function ClearCounter(username,userid)\n");
      print("{\n");
      print("  value=window.confirm(\"$user_allusertray6 \"+username+\"? \" );\n");
      print("  if(value==true) \n");
      print("     {\n");
      print("        parent.basefrm.location.href=\"main.php?show=clearallusertrafficcount\";\n");
      print("        window.setInterval(\"ReloadBaseFrame()\",500)\n");
      print("     }\n");
      print("}\n");
      print("function RecalcCounter(username,userid)\n");
      print("{\n");
      print("  value=window.confirm(\"$user_allusertray7 \" );\n");
      print("  if(value==true) \n");
      print("     {\n");
      print("        parent.basefrm.location.href=\"main.php?show=recalcallusertrafficcount\";\n");
//      print("        window.setInterval(\"ReloadBaseFrame()\",500)\n");
      print("     }\n");
      print("}\n");
      print("</SCRIPT> \n");
    }
  if($access==2)
    {
      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      print("<IMAGE id=Trash name=\"Trash\" src=\"white_data/recalc_32.jpg\" \n ");
      print("TITLE=\"$user_allusertray8\"  border=0 ");
      print("onclick=RecalcCounter(\"$row[nick]\",\"$row[id]\") \n");
      print("onmouseover=\"this.src='white_data/recalc_48.jpg'\" \n");
      print("onmouseout= \"this.src='white_data/recalc_32.jpg'\" >\n");


      print("<TD VALIGN=\"TOP\" WIDTH=\"10%\">\n");
      print("<IMAGE id=Trash name=\"Trash\" src=\"white_data/erase_32.jpg\" \n ");
      print("TITLE=\"$user_allusertray9\"  border=0 ");
      print("onclick=ClearCounter(\"$row[nick]\",\"$row[id]\") \n");
      print("onmouseover=\"this.src='white_data/erase_48.jpg'\" \n");
      print("onmouseout= \"this.src='white_data/erase_32.jpg'\" >\n");
    }

  print("<TD>\n");
  print("</TABLE>\n");

}






/****************************************************************/

/****************************************************************/


/****************************************************************/



/****************************************************************/


/****************************************************************/



?>
