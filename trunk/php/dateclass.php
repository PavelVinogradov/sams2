<?php

class DATE
{
  var $sday,$smon,$syea,$shou,$eday,$emon,$eyea,$ehou,$sdate,$edate;
  function DATE($mas, $sdate, $edate)
    {
       if(strlen($sdate)<=1&&strlen($edate)<=1)
		list($this->sday,$this->smon,$this->syea,$this->shou,$this->eday,$this->emon,$this->eyea,$this->ehou)=$mas;
       else
         {
           list($this->sday,$this->smon,$this->syea,$this->shou,$this->eday,$this->emon,$this->eyea,$this->ehou)=$mas;
           $this->sdate=$sdate;
           $this->syea=strtok($sdate,"-");
           $this->smon=strtok("-");
           $this->sday=strtok("-");
	   
	   $this->edate=$edate;
           $this->eyea=strtok($edate,"-");
           $this->emon=strtok("-");
           $this->eday=strtok("-");
	 }  
          
    }
  function BeginDate()
    {
       return("$this->sday.$this->smon.$this->syea"); 
    }
  function EndDate()
    {
       return("$this->eday.$this->emon.$this->eyea");
    }
  function sdate()
    {
       return("$this->syea-$this->smon-$this->sday");
    }
  function edate()
    {
       return("$this->eyea-$this->emon-$this->eday");
    }
}

?>