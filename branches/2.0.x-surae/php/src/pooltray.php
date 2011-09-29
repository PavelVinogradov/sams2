<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function AddPool()
{
  global $SAMSConf;
  global $USERConf;
  $DB=new SAMSDB();
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $agg1="-1";
  $agg2="-1";
  $net1="-1";
  $net2="-1";
  $ind1="-1";
  $ind2="-1";
  if(isset($_GET["name"])) $name=$_GET["name"];
  if(isset($_GET["class"])) $class=$_GET["class"];
  if(isset($_GET["agg1"])) $agg1=$_GET["agg1"];
  if(isset($_GET["agg2"])) $agg2=$_GET["agg2"];
  if(isset($_GET["net1"])) $net1=$_GET["net1"];
  if(isset($_GET["net2"])) $net2=$_GET["net2"];
  if(isset($_GET["ind1"])) $ind1=$_GET["ind1"];
  if(isset($_GET["ind2"])) $ind2=$_GET["ind2"];

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

   $DB->samsdb_query("INSERT INTO delaypool ( s_name, s_class, s_agg1, s_agg2, s_net1, s_net2, s_ind1, s_ind2 ) VALUES ( '$name', '$class', '$agg1', '$agg2', '$net1', '$net2', '$ind1', '$ind2' ) ");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print(" parent.basefrm.location.href = \"main.php?show=exe&filename=pooltray.php&function=addpoolform\"; \n");
  print("</SCRIPT> \n");
}


function AddPoolForm()
{
  global $SAMSConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["type"])) $type=$_GET["type"];

  print("<SCRIPT>\n");
  print("        parent.tray.location.href=\"tray.php\";\n");
  print("</SCRIPT> \n");

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  PageTop("delaypool_32.png",$lframe_sams_DelayPools_Add);
  print("<BR>\n");
 
  print("<SCRIPT language=JAVASCRIPT>\n");
  print("function TestName(formname)\n");
  print("{\n");
  print("  var shablonname=formname.name.value; \n");
  print("  if(shablonname.length==0) \n");
  print("    {\n");
  print("       alert(\"Name of the pool is not defined\");\n");
  print("       return false");
  print("    }\n");
  print("  return true");
  print("}\n");
  print("function ClassChanged(formname) \n");
  print("{ \n");
  print("  var class=formname.class.value; \n");
  print("  if(class==\"1\") \n");
  print("    {\n");
  print("      formname.net1.disabled=true;  \n");
  print("      formname.net2.disabled=true;  \n");
  print("      formname.ind1.disabled=true;  \n");
  print("      formname.ind2.disabled=true;  \n");
  print("    }\n");
  print("  else if(class==\"2\")\n");
  print("    {\n");
  print("      formname.net1.disabled=true;  \n");
  print("      formname.net2.disabled=true;  \n");
  print("      formname.ind1.disabled=false;  \n");
  print("      formname.ind2.disabled=false;  \n");
  print("    }\n");
  print("  else if(class==\"3\")\n");
  print("    {\n");
  print("      formname.net1.disabled=false;  \n");
  print("      formname.net2.disabled=false;  \n");
  print("      formname.ind1.disabled=false;  \n");
  print("      formname.ind2.disabled=false;  \n");
  print("    }\n");
  print("}\n");
  print("</SCRIPT> \n");


  print("<FORM NAME=\"POOL\" ACTION=\"main.php\" onsubmit=\"return TestName(POOL)\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" id=Show value=\"exe\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"addpool\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"pooltray.php\">\n");


  print("<TABLE  BORDER=0>\n");
  print("<TR>\n");
  print("  <TD><B>$lframe_sams_DelayPools_Field_Name:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"name\" SIZE=50></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>$lframe_sams_DelayPools_Field_Class:</B></TD>\n");
  print("  <TD>\n");
  print("  <SELECT NAME=\"class\" onchange=ClassChanged(POOL)>\n");
  print("  <OPTION value=1 SELECTED>1</OPTION>\n");
  print("  <OPTION value=2>2</OPTION>\n");
  print("  <OPTION value=3>3</OPTION>\n");
  print("  </SELECT>\n");
  print("  </TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>$lframe_sams_DelayPools_AgrBucket_Restore:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"agg1\" VALUE=\"-1\" SIZE=10></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>$lframe_sams_DelayPools_AgrBucket_Size:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"agg2\" VALUE=\"-1\" SIZE=10></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>$lframe_sams_DelayPools_NetBucket_Restore:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"net1\" VALUE=\"-1\" DISABLED SIZE=10></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>$lframe_sams_DelayPools_NetBucket_Size:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"net2\" VALUE=\"-1\" DISABLED SIZE=10></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>$lframe_sams_DelayPools_IndBucket_Restore:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"ind1\" VALUE=\"-1\" DISABLED SIZE=10></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>$lframe_sams_DelayPools_IndBucket_Size:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"ind2\" VALUE=\"-1\" DISABLED SIZE=10></TD>\n");
  print("</TR>\n");
  print("</TABLE>\n");


  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"$lframe_sams_DelayPools_AddButton\">\n");
  print("</FORM>\n");

  //print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/urllists.html\">");
  //print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
  //print("<TD>$redirlisttray_AddRedirListForm_4");

}

function JSPoolInfo()
{
  global $SAMSConf;
  global $USERConf;
  global $POOLConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  $code="<HTML><BODY><CENTER>";
  $code=$code."<TABLE WIDTH=\"95%\" border=0><TR><TD WIDTH=\"10%\"  valign=\"middle\">";
  $code=$code."<img src=\"$SAMSConf->ICONSET/delaypool_32.png\" align=\"RIGHT\" valign=\"middle\" >";
  $code=$code."<TD valign=\"middle\">";
  $code=$code."<h2 align=\"CENTER\">$lframe_sams_DelayPool <FONT COLOR=\"BLUE\">$POOLConf->s_name</FONT></h2>";
  $code=$code."</TABLE>";

  $code=$code."<TABLE>";
  $code=$code."<TR><TD>$lframe_sams_DelayPools_AgrBucket_Restore:</TD><TD>$POOLConf->s_agg1</TD></TR>";
  $code=$code."<TR><TD>$lframe_sams_DelayPools_AgrBucket_Size:</TD><TD>$POOLConf->s_agg2</TD></TR>";
  if ($POOLConf->s_class == 3)
  {
    $code=$code."<TR><TD>$lframe_sams_DelayPools_NetBucket_Restore:</TD><TD>$POOLConf->s_net1</TD></TR>";
    $code=$code."<TR><TD>$lframe_sams_DelayPools_NetBucket_Size:</TD><TD>$POOLConf->s_net2</TD></TR>";
  }
  if ($POOLConf->s_class > 1)
  {
    $code=$code."<TR><TD>$lframe_sams_DelayPools_IndBucket_Restore:</TD><TD>$POOLConf->s_ind1</TD></TR>";
    $code=$code."<TR><TD>$lframe_sams_DelayPools_IndBucket_Size:</TD><TD>$POOLConf->s_ind2</TD></TR>";
  }
  $code=$code."</TABLE>";
  $code=$code."</CENTER></BODY></HTML>";

  $code=str_replace("\"","\\\"",$code);
  $code=str_replace("\n","",$code);
  print(" parent.basefrm.document.write(\"$code\");\n");
  print(" parent.basefrm.document.close();\n");
}

function PoolTray()
{
  if(isset($_GET["id"])) $id=$_GET["id"];

  global $SAMSConf;
  global $USERConf;
  global $POOLConf;

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	exit;

  print("<SCRIPT>\n");
  JSPoolInfo();
  print("</SCRIPT> \n");

  print("<TABLE WIDTH=\"100%\" BORDER=0>\n");
  print("<TR HEIGHT=60>\n");
  print("<TD WIDTH=25%>");
  print("<B>Delay pool <BR><FONT COLOR=\"BLUE\">$POOLConf->s_name</FONT></B>\n");

  ExecuteFunctions("./src", "poolbuttom","1");
  
  print("<TD>\n");
  print("</TABLE>\n");
}

?>
