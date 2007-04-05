#!/usr/bin/php

<?

$filein="lang.KOI8-R";
$fileout="lang.WIN1251";
$filetmp="lang.WIN1251_";

  'iconv -f KOI8-R -t cp1251 $filein > $filetmp';
  $finp=fopen($filetmp,"rt");
  $fout=fopen($fileout,"wt");
  while(feof($finp)==0)
     {
        $string=fgets($finp, 10000);
        fputs($fout,"$string\n");

     }
  fclose($finp);
  fclose($fout);

?>