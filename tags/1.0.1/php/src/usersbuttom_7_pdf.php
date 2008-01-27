<?php
/*  
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */


 
function usersbuttom_7_pdf()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);

  $SAMSConf->access=UserAccess();

  if($SAMSConf->access==2)
    {
       if(file_exists("0.pdf")==TRUE)
         {
           print("<TD VALIGN=\"TOP\" WIDTH=\"50\" HEIGHT=\"50\" >\n");
           print("<A HREF=\"0.pdf\" >\n");
           print("<IMAGE id=Trash name=\"Trash\" src=\"$SAMSConf->ICONSET/pdf_32.jpg\" \n ");
           print("TITLE=\"Load PDF file\"  border=0 ");
           print("onclick=DeleteUser(\"$userid\") \n");
           print("onmouseover=\"this.src='$SAMSConf->ICONSET/pdf_48.jpg'\" \n");
           print("onmouseout= \"this.src='$SAMSConf->ICONSET/pdf_32.jpg'\" >\n");
           print("</A>\n");
         }
    }
     

}




?>
