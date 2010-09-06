<?
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

 function lffolder_6_users($access,$sams)
 { 
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($SAMSConf->SHOWUTREE=="Y"||$SAMSConf->access>0)
    {
      $count=0;
      print("   users = insFld($sams, gFld2(\"$lframe_sams_UserFolder_1\", \"tray.php?show=exe&function=userstray\", \"paddressbook.gif\"))\n");
      $result=mysql_query("SELECT * FROM groups ORDER BY nick");
      while($row=mysql_fetch_array($result))
         {
            $metka="users$count";
            print("     $metka = insFld(users, gFld2(\"$row[nick]\", \"tray.php?show=usergrouptray&groupname=$row[name]&groupnick=$row[nick]\", \"pgroup.gif\"))\n");
            $result_=mysql_query("SELECT * FROM squidusers WHERE squidusers.group=\"$row[name]\" ORDER BY nick");
            while($row_=mysql_fetch_array($result_))
               {
                 if($row_['enabled']>0)
                    {
                      if($realtraffic=="real")
		        $traffic=$row_['size']-$row_['hit'];
                      else
		        $traffic=$row_['size'];
		      if($row_['quotes']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE>=$traffic||$row_['quotes']<=0)
                         $gif="puser.gif";
                      else
                        if($row_['quotes']>0)
                          $gif="quote_alarm.gif";
                    }
                 if($row_['enabled']==0)
                    {
                       $gif="puserd.gif";
                    }
                 if($row_['enabled']<0)
                    {
                       $gif="duserd.gif";
                    }
                 if($SAMSConf->SHOWNAME=="fam")
                       $name="$row_[family]";
                 else if($SAMSConf->SHOWNAME=="famn")
                       $name="$row_[family] $row_[name]";
                 else if($SAMSConf->SHOWNAME=="nickd")
                       $name="$row_[nick] / $row_[domain]";
                 else 
                       $name="$row_[nick]";
		    
                
                 print("        insDoc($metka, gLnk(\"D\", \"$name\", \"tray.php?show=usertray&userid=$row_[id]&usergroup=$row_[group]\",\"$gif\"))\n");
               }
         }
    }
 }
 
 

 ?>