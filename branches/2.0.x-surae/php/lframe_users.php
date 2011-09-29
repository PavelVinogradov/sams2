<?php
/*
 * SAMS (Squid Account Management System)
 * Author: Dmitry Chemerik chemerik@mail.ru
 * (see the file 'main.php' for license details)
 */

function lframe_users()
{

 global $SAMSConf;
 global $USERConf;
 global $SquidUSERConf;
  $DB=new SAMSDB();
  $DB2=new SAMSDB();
  $lang="./lang/lang.$SAMSConf->LANG";
  require($lang);
  if($SAMSConf->SHOWUTREE=="1"||$USERConf->ToWebInterfaceAccess("GSAUC")==1)
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
	echo "<style type=\"text/css\">\n
	.filetree span.users { padding: 1px 0 1px 25px; display: block; }\n
	.filetree span.users { background: url($SAMSConf->ICONSET/paddressbook.gif) 0 0 no-repeat; }\n
	.filetree span.groups { padding: 1px 0 1px 25px; display: block; }\n
	.filetree span.groups { background: url($SAMSConf->ICONSET/pgroup.gif) 0 0 no-repeat; }\n

	.filetree span.user_active { padding: 1px 0 1px 25px; display: block; }\n
	.filetree span.user_active { background: url($SAMSConf->ICONSET/user_active.png) 0 0 no-repeat; }\n

	.filetree span.user_inactive { padding: 1px 0 1px 25px; display: block; }\n
	.filetree span.user_inactive { background: url($SAMSConf->ICONSET/user_inactive.png) 0 0 no-repeat; }\n

	.filetree span.user_moved { padding: 1px 0 1px 25px; display: block; }\n
	.filetree span.user_moved { background: url($SAMSConf->ICONSET/user_moved.png) 0 0 no-repeat; }\n

	.filetree span.user_off { padding: 1px 0 1px 25px; display: block; }\n
	.filetree span.user_off { background: url($SAMSConf->ICONSET/user_off.png) 0 0 no-repeat; }\n

	.filetree span.quote_alarm { padding: 1px 0 1px 25px; display: block; }\n
	.filetree span.quote_alarm { background: url($SAMSConf->ICONSET/quote_alarm.gif) 0 0 no-repeat; }\n
	</style>\n";


	echo "<li class=\"closed collapsable\"> <div class=\"hitarea closed-hitarea collapsable-hitarea\"></div> <span class=\"users\"><A TARGET=\"tray\" HREF=\"tray.php?show=exe&filename=userstray.php&function=userstray\">$lframe_sams_UserFolder_1</A> </span>\n";
	echo "<ul style=\"display: block;\">\n";



      if(strlen($SAMSConf->groupauditor)>1)
        {
	  $query="SELECT * FROM sgroup WHERE s_name='$SAMSConf->groupauditor' ";
	}  
      else
        {
	  $query="SELECT * FROM sgroup ORDER BY s_name";
	}  
      $num_rows=$DB->samsdb_query_value($query);


      while($row=$DB->samsdb_fetch_array())
         {
	    $metka="users$count";
	    echo "<li class=\"closed collapsable\"> <div class=\"hitarea collapsable-hitarea\"></div> <span class=\"groups\"><A TARGET=\"tray\" HREF=\"tray.php?show=exe&filename=grouptray.php&function=grouptray&id=$row[s_group_id]\">$row[s_name]</A></span>\n";
	    echo "<ul id=\"group_$row[s_group_id]\">\n";

            $num_rows_=$DB2->samsdb_query_value("SELECT squiduser.* , shablon.s_auth as s_auth FROM squiduser LEFT JOIN shablon ON squiduser.s_shablon_id=shablon.s_shablon_id WHERE s_group_id='$row[s_group_id]' ORDER BY $SORDER");

            while($row_=$DB2->samsdb_fetch_array())
               {
                if($row_['s_enabled']==1)
                    {
                      if($SAMSConf->realtraffic=="real")
		        $traffic=$row_['s_size']-$row_['s_hit'];
                      else
		        $traffic=$row_['s_size'];
		      if($row_['s_quote']*$SAMSConf->KBSIZE*$SAMSConf->KBSIZE>=$traffic||$row_['s_quote']<=0)
                         $class="user_active";
                      else
                        if($row_['s_quote']>0)
                          $class="quote_alarm";
                    }
                 else if($row_['s_enabled']==0)
                    {
                       $class="user_inactive";
                    }
                 else if($row_['s_enabled']==2)
                    {
                       $class="user_moved";
                    }
                 else if($row_['s_enabled']<0)
                    {
                       $class="user_off";
                    }
                 if($SAMSConf->SHOWNAME=="fam")
                       $name="$row_[s_family]";
                 else if($SAMSConf->SHOWNAME=="famn")
                       $name="$row_[s_family] $row_[s_name]";
                 else if($SAMSConf->SHOWNAME=="nickd")
                       $name="$row_[s_nick] / $row_[s_domain]";
                 else 
                       $name="$row_[s_nick]";
		    
		echo "<li><span class=\"$class\"><A TARGET=\"tray\" HREF=\"tray.php?show=exe&filename=usertray.php&function=usertray&id=$row_[s_user_id]&auth=$row_[s_auth]\">$name</A></span></li>\n";

               }

	     $count++;  
	    echo "</ul>";
	    echo "</li>";
         }



	echo "</ul>";
	echo "</li>";

    }

}

?>
