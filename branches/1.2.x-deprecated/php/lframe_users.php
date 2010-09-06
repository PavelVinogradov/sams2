<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function lframe_users()
{
  global $SAMSConf;
  
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  
  if($SAMSConf->SHOWUTREE=="Y"||$SAMSConf->access>0||strlen($SAMSConf->groupauditor)>0)
    {
      switch ($SAMSConf->SHOWNAME)
        { 
	  case "fam":
            $SORDER = "family";
          break;

          case "famn":
            $SORDER = "family, name";
          break;

          case "nickd":
            $SORDER = "domain, nick";
          break;

          case "nick":
          default:
            $SORDER = "nick";
        }

      
      $count=0;
      print("   users = insFld(foldersTree, gFld2(\"$lframe_sams_UserFolder_1\", \"tray.php?show=exe&function=userstray\", \"paddressbook.gif\"))\n");
      if(strlen($SAMSConf->groupauditor)>1)
        {
          $result=mysql_query("SELECT * FROM groups WHERE groups.name=\"$SAMSConf->groupauditor\" ");
 //echo "SAMSConf->groupauditor = $SAMSConf->groupauditor\n\n\n";     
	}  
      else
        {
          $result=mysql_query("SELECT * FROM ".$SAMSConf->SAMSDB.".groups ORDER BY nick");
	}  
      while($row=mysql_fetch_array($result))
         {
	    $metka="users$count";
            print("     $metka = insFld(users, gFld2(\"$row[nick]\", \"tray.php?show=usergrouptray&groupname=$row[name]&groupnick=$row[nick]\", \"pgroup.gif\"))\n");
            $result_=mysql_query("SELECT * FROM squidusers WHERE squidusers.group=\"$row[name]\" ORDER BY $SORDER");
            while($row_=mysql_fetch_array($result_))
               {
 //echo "SAMSConf->groupauditor = $SAMSConf->groupauditor\n result_=$result_\n\n";     
                if($row_['enabled']>0)
                    {
                      if($SAMSConf->realtraffic=="real")
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
	     $count++;  
         }
    }

}

?>
