<?php

function SearchAuthParameter($auth,$parameter,$value)
{
  global $SAMSConf;
  $DB=new SAMSDB();
  $num_rows=$DB->samsdb_query_value("SELECT s_value FROM auth_param WHERE s_auth='$auth' AND s_param='$parameter' AND s_value='$value' ");
  return($num_rows);
}

function SAMSLangToUTF8($inp)
{
  global $SAMSConf;
  $charset=explode(",",$_SERVER['HTTP_ACCEPT_CHARSET']);
	$out=$inp;
	if($SAMSConf->LANG!="UTF8" && $SAMSConf->LANG!="EN")
	{
		$out=iconv($SAMSConf->CHARSET, "UTF-8", $inp);
	}
	if($SAMSConf->LANG=="EN")
	{
		return($inp);
//		$out=iconv("UTF-8", $charset[0], $inp);
	}
  return($out);
}

function UTF8ToSAMSLang($inp)
{
  global $SAMSConf;
  $charset=explode(",",$_SERVER['HTTP_ACCEPT_CHARSET']);
	$out=$inp;
	if($SAMSConf->LANG!="UTF8" && $SAMSConf->LANG!="EN")
	{
		$out=iconv("UTF-8", $SAMSConf->CHARSET, $inp);
	}
	if($SAMSConf->LANG=="EN")
	{
		return($inp);
//		$out=iconv("UTF-8", $charset[0], $inp);
	}
  return($out);
}

function UpdateAuthParameter($auth,$parameter)
{
  global $SAMSConf;

  if(isset($_GET["$parameter"])) $value=$_GET["$parameter"];
  if($value!="")
  {
	$DB=new SAMSDB();
	$num_rows=$DB->samsdb_query_value("SELECT s_value FROM auth_param WHERE s_auth='$auth' AND s_param='$parameter' ");
	if($num_rows>0)
	{
		$row=$DB->samsdb_fetch_array();
		if($row['s_value']!=$value)
			$query="UPDATE auth_param SET s_value='$value' WHERE s_auth='$auth' AND s_param='$parameter'";
		else
			return(0);
	}
	else
	{
		$query="INSERT INTO auth_param VALUES('$auth','$parameter','$value')";
	}
	$DB->samsdb_query($query);
  }

}


function GetAuthParameter($auth,$parameter)
{
  global $SAMSConf;
  $value="";
  $DB=new SAMSDB();
  $num_rows=$DB->samsdb_query_value("SELECT s_value FROM auth_param WHERE s_auth='$auth' AND s_param='$parameter' ");
  if($num_rows>0)
  {
	$row=$DB->samsdb_fetch_array();
	return($row['s_value']);
  }
  return("");
}



function ReturnDate($string)
{
  $newstring=sprintf("%s.%s.%s",substr($string,8,2),substr($string,5,2),substr($string,0,4));
  return($newstring);
}

function UserAuth()
{
  global $SAMSConf;
  $DB=new SAMSDB();

  if(isset($_POST["userid"])) $password=$_POST["userid"];
  if(isset($_POST["id"])) $id=$_POST["id"];
  $grauditor=0;
  $SAMSConf->domainusername="";

	$SAMSConf->USERPASSWD=0;
	$num_rows=$DB->samsdb_query_value("SELECT squiduser.*,shablon.s_auth as s_auth FROM squiduser LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id WHERE s_user_id='$id'; ");
	$row=$DB->samsdb_fetch_array();

	if($num_rows>0)
	{
		$SAMSConf->USERID=$row['s_user_id'];
		$SAMSConf->USERWEBACCESS=$row['s_webaccess'];
		$SAMSConf->AUTHERRORRC=$row['s_autherrorc'];
		$SAMSConf->AUTHERRORRT=$row['s_autherrort'];
	}

	if($row['s_auth']=="ip")
	{
		$passwd=crypt($password, substr($password, 0, 2));
		if($row['s_passwd']==$passwd)
		{
			$SAMSConf->domainusername=$row['s_nick'];
			$SAMSConf->USERPASSWD=1;
		}
	}
	if($row['s_auth']=="adld")
	{
		require_once("adldap.php");
		//create the LDAP connection
		$pdc=array("$SAMSConf->LDAPSERVER");
		$options=array(account_suffix=>"@$SAMSConf->LDAPDOMAIN", base_dn=>"$SAMSConf->LDAPBASEDN",domain_controllers=>$pdc, 
		ad_username=>"$SAMSConf->LDAPUSER",ad_password=>"$SAMSConf->LDAPUSERPASSWD","","","");
		$ldap=new adLDAP($options);
//		if ($ldap->authenticate($userdomain,$password))
		if ($ldap->authenticate($row['s_nick'],$password))
		{
			$aflag=1;
			$SAMSConf->domainusername=$row['s_nick'];
			$SAMSConf->USERPASSWD=1;
		}
	}

	if($row['s_auth']=="ntlm")
	{
		$e = escapeshellcmd("$row[s_nick] $password");
		$aaa=ExecuteShellScript("testwbinfopasswd", $e);
		$aflag=0;
		if(stristr($aaa,"authentication succeeded" )!=false||stristr($aaa,"NT_STATUS_OK" )!=false)
		{ 
			$aflag=1;
			$SAMSConf->domainusername=$row['s_nick'];
			$SAMSConf->USERPASSWD=1;
		}  
	}

  $grauditor=0;
  if($row['s_gauditor']>0&&strlen($SAMSConf->domainusername)>0)
    {
         $grauditor=$row['s_group'];
         print("<SCRIPT>\n");
         print(" parent.lframe.location.href=\"lframe.php\"; \n");
         print("</SCRIPT> \n");
    }
 return($grauditor);
}

function NotUsersTreeUserAuth()
{
  global $SAMSConf;
  $DB=new SAMSDB();

  if(isset($_POST["userid"])) $password=$_POST["userid"];
  if(isset($_POST["user"])) $userdomain=$_POST["user"];
  $grauditor=0;
  $SAMSConf->domainusername="";

	$SAMSConf->USERPASSWD=0;
	$num_rows=$DB->samsdb_query_value("SELECT squiduser.*,shablon.s_auth as s_auth FROM squiduser LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id WHERE s_nick='$userdomain'; ");
	$row=$DB->samsdb_fetch_array();

	if($num_rows>0)
	{
		$SAMSConf->USERID=$row['s_user_id'];
		$SAMSConf->USERWEBACCESS=$row['s_webaccess'];
		$SAMSConf->AUTHERRORRC=$row['s_autherrorc'];
		$SAMSConf->AUTHERRORRT=$row['s_autherrort'];
	}

	if($row['s_auth']=="ip")
	{
		$passwd=crypt($password, substr($password, 0, 2));
		if($row['s_passwd']==$passwd)
		{
			$SAMSConf->domainusername=$row['s_nick'];
			$SAMSConf->USERPASSWD=1;
		}
	}
	if($row['s_auth']=="adld")
	{
		require_once("adldap.php");
		//create the LDAP connection
		$pdc=array("$SAMSConf->LDAPSERVER");
		$options=array(account_suffix=>"@$SAMSConf->LDAPDOMAIN", base_dn=>"$SAMSConf->LDAPBASEDN",domain_controllers=>$pdc, 
		ad_username=>"$SAMSConf->LDAPUSER",ad_password=>"$SAMSConf->LDAPUSERPASSWD","","","");
		$ldap=new adLDAP($options);
//		if ($ldap->authenticate($userdomain,$password))
		if ($ldap->authenticate($row['s_nick'],$password))
		{
			$aflag=1;
			$SAMSConf->domainusername=$row['s_nick'];
			$SAMSConf->USERPASSWD=1;
		}
	}

	if($row['s_auth']=="ntlm")
	{
		$e = escapeshellcmd("$row[s_nick] $password");
		$aaa=ExecuteShellScript("testwbinfopasswd", $e);
		$aflag=0;
		if(stristr($aaa,"authentication succeeded" )!=false||stristr($aaa,"NT_STATUS_OK" )!=false)
		{ 
			$aflag=1;
			$SAMSConf->domainusername=$row['s_nick'];
			$SAMSConf->USERPASSWD=1;
		}  
	}

  $grauditor=0;
  if($row['s_gauditor']>0&&strlen($SAMSConf->domainusername)>0)
    {
         $grauditor=$row['s_group'];
         print("<SCRIPT>\n");
         print(" parent.lframe.location.href=\"lframe.php\"; \n");
         print("</SCRIPT> \n");
    }
     
 return($grauditor);
}

function UserDoc()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  if($SAMSConf->SHOWUTREE=="1")
    {
      PageTop("user.jpg","$admintray_UserDoc_1");
      print("<TABLE WIDTH=\"90%\" BORDER=0>\n");
      print("<TR><TD><IMG SRC=\" $SAMSConf->ICONSET/lframe.jpg \" ALIGN=LEFT>\n");
      print("<TD>$admintray_UserDoc_3 ");
      print("$admintray_UserDoc_4 ");
      print("</TABLE>\n");
    }
  else
    {
/***/
      print("<P>");
      print("<FORM NAME=\"NUSERPASSWORD\" ACTION=\"main.php\" METHOD=\"POST\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"nuserauth\">\n");
      print("<TABLE WIDTH=\"90%\">\n");
      print("<TR>\n");
      print("<TD COLSPAN=2><H2>$admintray_UserDoc_2</H2>\n");
      print("<TR>\n");
      print("<TD><B>login:</B>\n");
      print("<TD><INPUT TYPE=\"TEXT\" NAME=\"user\" SIZE=30> \n");
      print("<TR>\n");
      print("<TD><B>password:</B>\n");
      print("<TD><INPUT TYPE=\"PASSWORD\" NAME=\"userid\" SIZE=30> \n");
      print("</TABLE>\n");
      print("<BR><INPUT TYPE=\"SUBMIT\" value=\"Ok\">\n");
      print("</FORM>\n");

/***/
    }  
}

function SetCheckBoxValue($value)
{
  if($value=="on")
	return(1);
  else
	return(0);
}

function ExecuteFunctions($path, $mask, $id)
{
  $files=array();
  $count=0;
    if ($handle2 = opendir($path))
        {
	  while (false !== ($file = readdir($handle2)))
            {
	      if(strstr($file, $mask)!=FALSE)
                {
		  if(strpos($file, $mask)==0)
		    {  
//echo "// ======================================================= $count: $file ===========\n";
		  	$files[$count]=$file;
		  	$count++;
		    }
                }
            }
        }
   sort($files);  
   for($i=0;$i<$count;$i++)
     {
//echo "$i/$count: $files[$i] <BR>";
	if(strstr($files[$i],"~")==NULL)
	{ 	
	    $funcname=str_replace(".php","",$files[$i]);		
//echo "// =$path/$files[$i]=<BR>\n";
	    require("$path/$files[$i]");
	    if($id!=1)
		$funcname($id);
	    else
		$funcname();
	}	

     }
  return($files);
}

function UserAuthenticate($user,$passwd)
{
  global $SAMSConf;

  if(strlen($user)>0 && strlen($passwd)>0)
	{

	  $DB=new SAMSDB();
	  $num_rows=$DB->samsdb_query_value("SELECT s_user FROM passwd WHERE s_user='$user' and s_pass='$passwd' ");

//echo "<h1>=$num_rows=</h1>";
	  if($num_rows > 0)
		{
		$row=$DB->samsdb_fetch_array();
	  	return("$row[s_user]");
		}
	}
  return("");
}

function UserAccess()
{
  global $SAMSConf;
  $len=strlen($SAMSConf->adminname);
  $access=0;
  if(strlen($SAMSConf->adminname)>0)
    {
        if(strtolower($SAMSConf->adminname)=="auditor")
          {
            $access=1;
          }
        else
          $access=2;
    }
  return($access);
}


function FormattedString($size)
{
  $count=0;
  $newsize="";
  $len=strlen(trim($size));
  for($i=$len-1;$i>-1;$i--)     
     {
       $newsize=sprintf("%s%s",substr($size,$i,1),$newsize);
       $count++;
       if($count==3)
          {  
	    $newsize=sprintf("%s%s"," ",$newsize);
	    $count=0;
	  }    
     }
  return($newsize);
}

function PrintFormattedSize($size)
{
 global $SAMSConf;
 $kbsize=$SAMSConf->KBSIZE;
 $gsize=floor($size/($kbsize*$kbsize*$kbsize));
 $ostatok=$size-$gsize*$kbsize*$kbsize*$kbsize;
 $msize=floor($ostatok/($kbsize*$kbsize));
 $ostatok=$size-$gsize*$kbsize*$kbsize*$kbsize-$msize*$kbsize*$kbsize;
 $ksize=floor($ostatok/$kbsize);
 if($msize<10&&$gsize>0)
   $msize="0$msize";
 if($msize<100&&$gsize>0)
   $msize="0$msize";
 if($ksize<10&&$msize>0)
   $ksize="0$ksize";
 if($ksize<100&&$msize>0)
   $ksize="0$ksize";
   
  print("  <TD ALIGN=RIGHT>&nbsp;");
  if($gsize>0)
    print("<B>$gsize</B>&nbsp;Gb ");
  if($gsize>0||$msize>0)
    print("<B>$msize</B>&nbsp;Mb");
  print("<B>&nbsp;$ksize</B>&nbsp;kb</TD>\n");
}

function ExecuteShellScript($script, $str)
{
  $phpos=PHP_OS;
  $bin=0;
  $length=strlen($str);
  if(!strcasecmp($phpos,"FreeBSD"))
   {
     if($length>0)
       {
         $e = escapeshellcmd($str);
         $value=exec("bin/$script $e");
       }	
     else
         $value=exec("bin/$script");
       
     $bin=1;
   }
  else
   {
     if($length>0)
       {
         $e = escapeshellcmd($str);
         $value=exec("$script $e");
       }	 
     else
       $value=exec("$script");
   }
  if(strlen($value)<2)
   {
     if($bin==0)
        {
          if($length>0)
            {
              $e = escapeshellcmd($str);
              $value=exec("bin/$script $e");
            }	
          else
              $value=exec("bin/$script");
        }
     else
       {  
         if($length>0)
           {
             $e = escapeshellcmd($str);
             $value=exec("$script $e");
           }	 
         else
            $value=exec("$script");
       }
   }
  return($value);
}

function BlankPage()
{

}

function ReturnLanguage($filename)
{
  $finp=fopen($filename,"rt");
  while(feof($finp)==0)
     {
       $string=fgets($finp, 10000);
         if(strstr($string,"#LANGUAGE:" )!=FALSE)
          {
              $language=str_replace("#LANGUAGE:","",$string);
              return($language);
	  }
     }
  fclose($finp);
}

function PageTop($imgname,$text)
{
  global $SAMSConf;

  print("<TABLE  CLASS=pagetop>\n");
  print("<TR><TD WIDTH=\"10%\">\n");
  print("<img src=\"$SAMSConf->ICONSET/$imgname\">\n");
  print("<TD><h2>$text</h2>\n");
  print("</TABLE>\n");
  print("<BR>\n");
}

function ATableCell($data,$url)
{
  //print("<TD bgcolor=blanchedalmond align=right><font size=-1>");
  print("<TD align=right><font size=-1>");
  print("<A HREF=\"$url\">$data</A></TD>\n"); 
}
function TableCell($data)
{
//  print("<TD bgcolor=blanchedalmond align=right><font size=-1>");
  print("<TD  NOWRAP>");
  print("&nbsp;$data&nbsp;</TD>\n"); 
}
function RTableCell($data,$percent)
{
  print("<TD WIDTH=\"$percent%\" align=right NOWRAP>");
//  print("&nbsp;$data&nbsp;</TD>\n"); 
  print("$data</TD>\n"); 
}
function LTableCell($data,$percent)
{
  print("<TD WIDTH=\"$percent%\" align=left NOWRAP>");
  print("$data</TD>\n"); 
}
function RBTableCell($data,$percent)
{
  print("<TD WIDTH=\"$percent%\" align=right NOWRAP>");
  print("&nbsp;<B>$data&nbsp;</TD>\n"); 
}
function LBTableCell($data,$percent)
{
  print("<TD WIDTH=\"$percent%\" align=left NOWRAP>");
  print("&nbsp;<B>$data&nbsp;</TD>\n"); 
}

function GraphButton($url,$target,$img_small,$img_big,$title)
{
  global $SAMSConf;
//  print("<TD WIDTH=50 HEIGHT=50 VALIGN=CENTER ALIGN=CENTER>\n");
  print("<TD CLASS=\"samstraytd\">\n");
  print("<A HREF=\"$url\" target=\"$target\">\n");
  print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/$img_small\" BORDER=0 \n ");
  print("TITLE=\"$title\" \n");
  print("onmouseover=\"this.src='$SAMSConf->ICONSET/$img_big'\" \n");
  print("onmouseout= \"this.src='$SAMSConf->ICONSET/$img_small'\"> \n");
  print("</A></TD>\n");
}

?>
