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
	$sday=$this->sday;
	$smon=$this->smon;
	if($this->sday<10 && strlen($this->sday)<=1)
		$sday="0$this->sday";
	if($this->smon<10 && strlen($this->smon)<=1)
		$smon="0$this->smon";
	return("$this->syea-$smon-$sday"); 
    }
  function edate()
    {
	$eday=$this->eday;
	$emon=$this->emon;
	if($this->eday<10 && strlen($this->eday)<=1)
		$eday="0$this->eday";
	if($this->emon<10 && strlen($this->emon)<=1)
		$emon="0$this->emon";
	return("$this->eyea-$emon-$eday"); 
    }
}

?>
