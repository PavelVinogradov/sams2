<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function UpdatePool()
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

  if(isset($_GET["id"]))       $id=$_GET["id"];
  if(isset($_GET["name"]))     $name=$_GET["name"];
  if(isset($_GET["class"]))    $class=$_GET["class"];
  if(isset($_GET["agg1"]))     $agg1=$_GET["agg1"];
  if(isset($_GET["agg2"]))     $agg2=$_GET["agg2"];
  if(isset($_GET["net1"]))     $net1=$_GET["net1"];
  if(isset($_GET["net2"]))     $net2=$_GET["net2"];
  if(isset($_GET["ind1"]))     $ind1=$_GET["ind1"];
  if(isset($_GET["ind2"]))     $ind2=$_GET["ind2"];
  if(isset($_GET["template"])) $template=$_GET["template"];
  if(isset($_GET["trange"]))   $trange=$_GET["trange"];
  if(isset($_GET["urlgroup"])) $urlgroup=$_GET["urlgroup"];


  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{       exit;     }

  $DB->samsdb_query("UPDATE delaypool SET s_name='$name', s_class='$class', s_agg1='$agg1', s_agg2='$agg2', s_net1='$net1', s_net2='$net2', s_ind1='$ind1', s_ind2='$ind2' WHERE s_pool_id='$id'");

  $DB->samsdb_query("DELETE FROM d_link_s WHERE s_pool_id='$id'");
  foreach ($template as $key => $value)
    $DB->samsdb_query("INSERT INTO d_link_s (s_pool_id,s_shablon_id,s_negative) values ('$id','$value',0)");

  $DB->samsdb_query("DELETE FROM d_link_t WHERE s_pool_id='$id'");
  foreach ($trange as $key => $value)
    $DB->samsdb_query("INSERT INTO d_link_t (s_pool_id,s_trange_id,s_negative) values ('$id','$value',0)");

  $DB->samsdb_query("DELETE FROM d_link_r WHERE s_pool_id='$id'");
  foreach ($urlgroup as $key => $value)
    $DB->samsdb_query("INSERT INTO d_link_r (s_pool_id,s_redirect_id,s_negative) values ('$id','$value',0)");

  print("<SCRIPT>\n");
  print("  parent.lframe.location.href=\"lframe.php\"; \n");
  print("  parent.tray.location.href=\"tray.php?show=exe&filename=pooltray.php&function=pooltray&id=$id\"; \n");
  print("</SCRIPT> \n");

}




function UpdatePoolForm()
{
  global $SAMSConf;
  global $POOLConf;
  global $USERConf;
  $DB=new SAMSDB();

  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if(isset($_GET["type"])) $type=$_GET["type"];

  if($USERConf->ToWebInterfaceAccess("C")!=1 )
	{       exit;     }

  PageTop("delaypool_32.png","Configure delay pool <BR><FONT COLOR=\"BLUE\">$POOLConf->s_name</FONT>");
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
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" id=function value=\"updatepool\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"filename\" id=filename value=\"poolbuttom_1_prop.php\">\n");
  print("<INPUT TYPE=\"HIDDEN\" NAME=\"id\" id=id value=\"$POOLConf->s_pool_id\">\n");

  print("<TABLE  BORDER=0>\n");
  print("<TR>\n");
  print("<TD><B>Name:</B></TD>\n");
  print("<TD><INPUT TYPE=\"TEXT\" NAME=\"name\" VALUE=\"$POOLConf->s_name\" SIZE=50></TD> \n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>Class:</B></TD>\n");
  print("  <TD>\n");
  print("  <SELECT NAME=\"class\" onchange=ClassChanged(POOL)>\n");
  if ($POOLConf->s_class == 1)
    print("  <OPTION value=1 SELECTED>1</OPTION>\n");
  else
    print("  <OPTION value=1>1</OPTION>\n");

  if ($POOLConf->s_class == 2)
    print("  <OPTION value=2 SELECTED>2</OPTION>\n");
  else
    print("  <OPTION value=2>2</OPTION>\n");

  if ($POOLConf->s_class == 3)
    print("  <OPTION value=3 SELECTED>3</OPTION>\n");
  else
    print("  <OPTION value=3>3</OPTION>\n");

  print("  </SELECT>\n");
  print("  </TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>Aggregate bucket restore:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"agg1\" VALUE=\"$POOLConf->s_agg1\" SIZE=10></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>Aggregate bucket size:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"agg2\" VALUE=\"$POOLConf->s_agg2\" SIZE=10></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>Network bucket restore:</B></TD>\n");
  if ($POOLConf->s_class < 3)
    $DISABLED="DISABLED";
  else
    $DISABLED="";
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"net1\" VALUE=\"$POOLConf->s_net1\" $DISABLED SIZE=10></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>Network bucket size:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"net2\" VALUE=\"$POOLConf->s_net2\" $DISABLED SIZE=10></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>Individual bucket restore:</B></TD>\n");
  if ($POOLConf->s_class < 2)
    $DISABLED="DISABLED";
  else
    $DISABLED="";
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"ind1\" VALUE=\"$POOLConf->s_ind1\" $DISABLED SIZE=10></TD>\n");
  print("</TR>\n");
  print("<TR>\n");
  print("  <TD><B>Individual bucket size:</B></TD>\n");
  print("  <TD><INPUT TYPE=\"TEXT\" NAME=\"ind2\" VALUE=\"$POOLConf->s_ind2\" $DISABLED SIZE=10></TD>\n");
  print("</TR>\n");
  print("</TABLE>\n");

  print("<HR>\n");

  print("<TABLE>\n");
  print("<TR>\n");

  print("<TD>\n");
  print("Template<BR>\n");
  $map_template=array();
  $num_rows=$DB->samsdb_query_value("select s_shablon_id from d_link_s where s_pool_id=$POOLConf->s_pool_id");
  while($row=$DB->samsdb_fetch_array())
    $map_template[ $row[ 's_shablon_id' ] ]="SELECTED";

  print("<SELECT NAME=\"template[]\" SIZE=10 MULTIPLE >\n");
  $num_rows=$DB->samsdb_query_value("SELECT * from shablon ORDER BY s_name");
  while($row=$DB->samsdb_fetch_array())
  {
    if(!isset($map_template[ $row[ 's_shablon_id' ] ])) $map_template[ $row[ 's_shablon_id' ] ]="";
    print("<OPTION ID=\"$row[s_shablon_id]\" VALUE=\"$row[s_shablon_id]\"".$map_template [ $row['s_shablon_id'] ]." >$row[s_name]\n");
  }
  print("</SELECT>\n");
  print("</TD>\n");

  print("<TD>\n");
  print("Time interval<BR>\n");
  $map_trange=array();
  $num_rows=$DB->samsdb_query_value("select s_trange_id from d_link_t where s_pool_id=$POOLConf->s_pool_id");
  while($row=$DB->samsdb_fetch_array())
    $map_trange[ $row[ 's_trange_id' ] ]="SELECTED";
  print("<SELECT NAME=\"trange[]\" SIZE=10 MULTIPLE >\n");
  $num_rows=$DB->samsdb_query_value("SELECT * from timerange ORDER BY s_name");
  while($row=$DB->samsdb_fetch_array())
  {
    if(!isset($map_trange[ $row[ 's_trange_id' ] ])) $map_trange[ $row[ 's_trange_id' ] ]="";
    print("<OPTION ID=\"$row[s_trange_id]\" VALUE=\"$row[s_trange_id]\"".$map_trange [ $row['s_trange_id'] ].">$row[s_name]\n");
  }
  print("</SELECT>\n");
  print("</TD>\n");

  print("<TD>\n");
  print("Url group<BR>\n");
  $map_urlgroup=array();
  $num_rows=$DB->samsdb_query_value("select s_redirect_id from d_link_r where s_pool_id=$POOLConf->s_pool_id");
  while($row=$DB->samsdb_fetch_array())
    $map_urlgroup[ $row[ 's_redirect_id' ] ]="SELECTED";
  print("<SELECT NAME=\"urlgroup[]\" SIZE=10 MULTIPLE >\n");
  $num_rows=$DB->samsdb_query_value("SELECT * from redirect ORDER BY s_name");
  while($row=$DB->samsdb_fetch_array())
  {
    if(!isset($map_urlgroup[ $row[ 's_redirect_id' ] ])) $map_urlgroup[ $row[ 's_redirect_id' ] ]="";
    print("<OPTION ID=\"$row[s_redirect_id]\" VALUE=\"$row[s_redirect_id]\"".$map_urlgroup [ $row['s_redirect_id'] ].">$row[s_name]\n");
  }
  print("</SELECT>\n");
  print("</TD>\n");

  print("</TR>\n");
  print("</TABLE>\n");

  print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Change\">\n");
  print("</FORM>\n");



  print("<P><TABLE WIDTH=\"90%\"><TR><TD WIDTH=\"15%\"><A HREF=\"doc/$SAMSConf->LANGCODE/urllists.html\">");
  print("<IMG SRC=\"$SAMSConf->ICONSET/help.jpg\" ALIGN=RIGHT>");
  print("<TD>$redirlisttray_AddRedirListForm_4");

}


function poolbuttom_1_prop()
{
  global $SAMSConf;
  global $POOLConf;
  global $USERConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($USERConf->ToWebInterfaceAccess("C")==1 )
    {
       GraphButton("main.php?show=exe&function=updatepoolform&filename=poolbuttom_1_prop.php&id=$POOLConf->s_pool_id",
	               "basefrm","config_32.jpg","config_48.jpg","$lframe_sams_DelayPools_EditButton '$POOLConf->s_name'");
    }
}

?>
