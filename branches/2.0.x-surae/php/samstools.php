<?php

class SamsTools {
	function GetUpTime() {
		$phpos=PHP_OS;
		$value=$this->ExecuteShellScript("uptime",$phpos);
		return($value);
	}

	function GetHostName() {
		if(!($value=getenv('SERVER_NAME'))) {  
			$value="N.A."; 
		}
		return($value);
	}

	function GetIPAddr() {
		if(!($value=getenv('SERVER_ADDR'))) {  
			$value="N.A."; 
		}
		return($value);
	}

	function MemoryUsage() {
		$result = "";
		$phpos=PHP_OS;
		$value=$this->ExecuteShellScript("freemem",$phpos);
		$swapvalue=$this->ExecuteShellScript("freeswap",$phpos);
		
		$a=explode(" ",$value);
		for($i=1;$i<4;$i++)
		{
			$mem[$i-1]=$a[$i];
		}
		
		$a=explode(" ",$swapvalue);
		for($i=1;$i<4;$i++)
		{
			$swap[$i-1]=$a[$i];
		}
		
		$result .= "<P><TABLE CLASS=samstable>";
		$result .= "<TR >";
		$result .= "<TH>";
		$result .= "<TH><B>Total</B>";
		$result .= "<TH><B>Used</B>";
		$result .= "<TH><B>Free</B>\n";
		$result .= "<TR >";
		$result .= "<TD>Memory";
		$result .= "<TD>".$mem[0];
		$result .= "<TD>".$mem[1];
		$result .= "<TD>".$mem[2];
		$result .= "<TR >";
		$result .= "<TD>Swap";
		$result .= "<TD>".$swap[0];
		$result .= "<TD>".$swap[1];
		$result .= "<TD>".$swap[2]."\n";
		$result .= "</TABLE>";
		
		return $result;
	}

	function FileSystemUsage()
	{
		$result = "";
		$fstest=$this->ExecuteShellScript("fsusage","");
		$a=explode(" ",$fstest);
		$acount=count($a)/6;
		
		$result .= "<P><TABLE CLASS=samstable>";
		$result .= "<TR>";
		$result .= "<TH><B>Filesystem</B>";
		$result .= "<TH><B>Size</B>";
		$result .= "<TH><B>Used</B>";
		$result .= "<TH><B>Available</B>";
		$result .= "<TH><B>Use%</B>";
		$result .= "<TH><B>Mounted on</B>";
		
		for($i=0;$i<$acount;$i++)
        {
			$result .= "<TR>";
			for($j=0;$j<6;$j++)
			{
				$fs=$a[$i*6+$j];
				$result .= "<TD>".$fs;
			}
         }
		
		$result .= "</TABLE>";
		
		return $result;
}

function UserDoc()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);


  print("<H2>$admintray_UserDoc_2 </H2>\n");
  
  if($SAMSConf->SHOWUTREE=="Y")
    {
      PageTop("user.jpg","$admintray_UserDoc_1");
      print("</CENTER>");
      print("<IMG SRC=\" $SAMSConf->ICONSET/lframe.jpg \" ALIGN=LEFT>\n");
      print("$admintray_UserDoc_3");
      print("$admintray_UserDoc_4");
    }
  else
    {
/***/
      print("<P><B>$AdminTray_UserDoc_5</B>\n");
      print("<P>");
      print("<FORM NAME=\"NUSERPASSWORD\" ACTION=\"main.php\" METHOD=\"POST\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"show\" value=\"exe\">\n");
      print("<INPUT TYPE=\"HIDDEN\" NAME=\"function\" value=\"nuserauth\">\n");
      print("<TABLE WIDTH=\"90%\">\n");
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
	$result = "";
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
									$files[$count]=$file;
									$count++;
								}
						}
				}
		}
	sort($files);  
	for($i=0;$i<$count;$i++)
		{
			if(strstr($files[$i],"~")==NULL)
				{ 	
					$funcname=str_replace(".php","",$files[$i]);		
					require("$path/$files[$i]");
					if($id!=1)
						$result .= $funcname($id);
					else
						$result .= $funcname();
				}	
		}
	
	return($result);
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

function PrintFormattedSize($size, $align) {
	global $SAMSConf;
	$result = "";
	
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
   
        if (empty($align) || is_null($align))
          $align="RIGHT";
	$result .= "  <TD ALIGN=$align>&nbsp;";
	if($gsize>0)
		$result .= "<B>". $gsize ."</B>&nbsp;Gb ";
	if($gsize>0||$msize>0)
		$result .= "<B>". $msize ."</B>&nbsp;Mb";
	$result .= "<B>&nbsp;". $ksize ."</B>&nbsp;Kb</TD>\n";
	
	return $result;
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

function PageTop($imgname,$text) 
{
	global $SAMSConf;
	$result = "";
	
	$result .= "<CENTER>\n";
	$result .= "<TABLE WIDTH=\"95%\" border=0>\n";
	$result .= "<TR>\n";
	$result .= "<TD WIDTH=\"10%\"  valign=\"middle\">\n";
	$result .= "<img src=\"".$SAMSConf->ICONSET."/".$imgname."\" align=\"RIGHT\" valign=\"middle\" >\n";
	$result .= "<TD  valign=\"middle\">\n";
	$result .= "<h2  align=\"CENTER\">".$text."</h2>\n";
	$result .= "</TABLE>\n";
	$result .= "</CENTER>\n";
	$result .= "<BR>\n";
	
	return $result;
}

function ATableCell($data,$url)
{
	$result = "";
	//$result .= "<TD bgcolor=blanchedalmond align=right><font size=-1>";
	$result .= "<TD align=right><font size=-1>";
	$result .= "<A HREF=\"".$url."\">".$data."</A></TD>"; 
	return $result;
}

function TableCell($data)
{
	$result = "";
	//$result .= "<TD bgcolor=blanchedalmond align=right><font size=-1>";
	$result .= "<TD  NOWRAP>";
	$result .= "&nbsp;".$data."&nbsp;</TD>"; 
	return $result;
}
function RTableCell($data,$percent)
{
	$result = "";
	$result .= "<TD WIDTH=\"".$percent."%\" align=right NOWRAP>";
	$result .= "&nbsp;".$data."&nbsp;</TD>"; 
	return $result;
}
function LTableCell($data,$percent)
{
	$result = "";
	$result .= "<TD WIDTH=\"".$percent."%\" align=left NOWRAP>";
	$result .= "&nbsp;".$data."&nbsp;</TD>"; 
	return $result;
}
function RBTableCell($data,$percent)
{
	$result = "";
	$result .= "<TD WIDTH=\"".$percent."%\" align=right NOWRAP>";
	$result .= "&nbsp;<B>".$data."&nbsp;</TD>"; 
	return $result;
}
function LBTableCell($data,$percent)
{
	$result = "";
	$result .= "<TD WIDTH=\"".$percent."%\" align=left NOWRAP>";
	$result .= "&nbsp;<B>".$data."&nbsp;</TD>"; 
	return $result;
}

function GraphButton($url,$target,$img_small,$img_big,$title)
{
	global $SAMSConf;
	$result = "";
	
	$result .= "<A HREF=\"".$url."\" target=\"".$target."\">\n";
	$result .= "<IMAGE id=Trash name=\"Trash\" src=\"".$SAMSConf->ICONSET."/".$img_small."\" BORDER=0 \n ";
	$result .= "TITLE=\"".$title."\" border=0\n";
	$result .= "onmouseover=\"this.src='".$SAMSConf->ICONSET."/".$img_big.
"'\" \n";
	$result .= "onmouseout= \"this.src='".$SAMSConf->ICONSET."/".$img_small."'\"> \n";
	$result .= "</A>\n";
	
	return $result;
}
}
?>
