<?php

class DATESELECT
{
  var $s_day;
  var $s_month;
  var $s_year;
  var $e_day;
  var $e_month;
  var $e_year;

function SetPeriod()
{
	print("<TABLE>\n");
	$this->StartDate();
	$this->EndDate();
	print("</TABLE>\n");

}

function SetPeriod2($head, $select)
{
	print("<INPUT TYPE=\"HIDDEN\" NAME=\"desc\" id='desc' value=\"desc\" onclick=SetDescMode(descvalue)>\n");
print("<SCRIPT LANGUAGE=JAVASCRIPT>\n");
print("function SetDescMode(descvalue) \n");
print("{\n");
print("  document.getElementById('desc').value = descvalue; \n");
print("}\n");
print("</SCRIPT>\n");

	print("<TABLE>\n");
	$this->StartDate();
	$this->EndDate();
	print("  <TR><TD><FONT COLOR=\"BLUE\"><B>$head</B>\n");
	for($i=0; $i<count($select); $i++)
	{
		print("  <TR>\n");
		print("  <TD><B>".$select[$i][0].":</B>\n");
		print("  <TD><INPUT TYPE=\"RADIO\" NAME=\"sort\" VALUE=\"".$select[$i][1]."\"   onclick=SetDescMode(\"".$select[$i][2]."\") ".$select[$i][3]."> \n");
	}
	print("</TABLE>\n");

}

function StartDate()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	print("  <TR>\n");
	print("  <TD><B>$mysqltools_dateselect1:</B>\n");
	print("  <TD><SELECT NAME=\"SDay\"> \n");
	for($i=1;$i<32;$i++)
   	{
     		if($this->s_day==$i)
        		print("	       <OPTION value=$i  SELECTED>$i\n");
		else
        		print("	       <OPTION value=$i>$i\n");
	}
	print("	       </SELECT> \n");
	print("     <SELECT NAME=\"SMon\" size=1> \n");
	for($i=1;$i<13;$i++)
   	{
		if($this->s_month==$i)
			print("	       <OPTION value=$i SELECTED>$month[$i]\n");
		else
			print("	       <OPTION value=$i>$month[$i]\n");
	}
	print("	       </SELECT> \n");
	print("     <SELECT NAME=\"SYea\" size=1> \n");
	for( $i=$this->s_year-5; $i<$this->s_year+5; $i++ )
	{
		if($this->s_year==$i)
			print("	       <OPTION value=$i SELECTED>$i\n");
		else
			print("	       <OPTION value=$i>$i\n");
	}
	print("     </SELECT> \n");
	print("        <TD><INPUT TYPE=\"SUBMIT\" NAME=\"sbutton\" id=sbutton value=\"$mysqltools_dateselect2\" >\n");
}

function EndDate()
{
  global $SAMSConf;
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

	print("  <TR>\n");
	print("  <TD><B>$mysqltools_dateselect3:</B>\n");
	print("  <TD><SELECT NAME=\"EDay\"> \n");
	for($i=1;$i<32;$i++)
   	{
     		if($this->e_day==$i)
        		print("	       <OPTION value=$i  SELECTED>$i\n");
		else
        		print("	       <OPTION value=$i>$i\n");
	}
	print("	       </SELECT> \n");
	print("     <SELECT NAME=\"EMon\" size=1> \n");
	for($i=1;$i<13;$i++)
   	{
		if($this->e_month==$i)
			print("	       <OPTION value=$i SELECTED>$month[$i]\n");
		else
			print("	       <OPTION value=$i>$month[$i]\n");
	}
	print("	       </SELECT> \n");
	print("     <SELECT NAME=\"EYea\" size=1> \n");
	for( $i=$this->e_year-5; $i<$this->e_year+5; $i++ )
	{
		if($this->e_year==$i)
			print("	       <OPTION value=$i SELECTED>$i\n");
		else
			print("	       <OPTION value=$i>$i\n");
	}
	print("     </SELECT> \n");
}

function DATESELECT($sdate, $edate)
{
	if($sdate!="")
	{
		$a=explode("-",$sdate);
		$this->s_day=$a[2];
		$this->s_month=$a[1];
		$this->s_year=$a[0];
	}
	else
	{
		$this->s_day=1;
		$this->s_month=date("n");
		$this->s_year=date("Y");
	}
	if($edate!="")
	{
		$e=explode("-",$edate);
		$this->e_day=$e[2];
		$this->e_month=$e[1];
		$this->e_year=$e[0];
	}
	else
	{
		$this->e_day=date("d");
		$this->e_month=date("n");
		$this->e_year=date("Y");
	}
}

}

?>