<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function lframe_users()
{
  global $SAMSConf;
  $DB=new SAMSDB("$SAMSConf->DB_ENGINE", "0", $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB);
  $DB2=new SAMSDB("$SAMSConf->DB_ENGINE", "0", $SAMSConf->DB_SERVER, $SAMSConf->DB_USER, $SAMSConf->DB_PASSWORD, $SAMSConf->SAMSDB);
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($SAMSConf->SHOWUTREE=="1"||$SAMSConf->access>0||strlen($SAMSConf->groupauditor)>0)
    {
      switch ($SAMSConf->SHOWNAME)
        { 
	  case "fam":
            $SORDER = "s_family";
          break;

          case "famn":
            $SORDER = "s_family, s_name";
          break;

          case "nickd":
            $SORDER = "s_domain, s_nick";
          break;

          case "nick":
          default:
            $SORDER = "s_nick";
        }

      
      $count=0;
      print("   users = insFld(foldersTree, gFld2(\"$lframe_sams_UserFolder_1 \", \"tray.php?show=exe&filename=userstray.php&function=userstray\", \"paddressbook.gif\"))\n");
      if(strlen($SAMSConf->groupauditor)>1)
        {
          $num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup WHERE s_name='$SAMSConf->groupauditor' ");
 //echo "SAMSConf->groupauditor = $SAMSConf->groupauditor\n\n\n";     
	}  
      else
        {
          $num_rows=$DB->samsdb_query_value("SELECT * FROM sgroup ORDER BY s_name");
	}  
      while($row=$DB->samsdb_fetch_array())
         {
	    $metka="users$count";
            print("     $metka = insFld(users, gFld2(\"$row[s_name]\", \"tray.php?show=exe&filename=grouptray.php&function=grouptray&id=$row[s_group_id]\", \"pgroup.gif\"))\n");
            $num_rows_=$DB2->samsdb_query_value("SELECT * FROM squiduser WHERE s_group_id='$row[s_group_id]' ORDER BY $SORDER");
/**/
            while($row_=$DB2->samsdb_fetch_array())
               {
                if($row_['s_enabled']>0)
                    {
                      if($SAMSConf->realtraffic=="real")
		        $traffic=$row_['s_size']-$row_['s_hit'];
                      else
		        $traffic=$row_['s_size'];
		      if($row_['s_quote']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE>=$traffic||$row_['s_quote']<=0)
                         $gif="puser.gif";
                      else
                        if($row_['s_quote']>0)
                          $gif="quote_alarm.gif";
                    }
                 if($row_['s_enabled']==0)
                    {
                       $gif="puserd.gif";
                    }
                 if($row_['s_enabled']<0)
                    {
                       $gif="duserd.gif";
                    }
                 if($SAMSConf->SHOWNAME=="fam")
                       $name="$row_[s_family]";
                 else if($SAMSConf->SHOWNAME=="famn")
                       $name="$row_[s_family] $row_[s_name]";
                 else if($SAMSConf->SHOWNAME=="nickd")
                       $name="$row_[s_nick] / $row_[s_domain]";
                 else 
                       $name="$row_[s_nick]";
		    
                
                 print("        insDoc($metka, gLnk(\"D\", \"$name\", \"tray.php?show=exe&filename=usertray.php&function=usertray&id=$row_[s_user_id]\",\"$gif\"))\n");
               }
/**/
	     $count++;  
         }
    }

}

?>
