<html>
<BODY>
<link rel=stylesheet type=text/css href="styles.css">
<table border="0" width="550" cellspacing="1" cellpadding="3" align="center">
 <tr>
  <td width="100%" align="center">
   <h6>������ � �����<br>(����� � � � ��� �������)</h6>
  </td>
 </tr>
 <tr>
  <td>
    <div align=center>
	<a href="http://www.gismeteo.ru/towns/28225.htm">
		<img src="http://informer.gismeteo.ru/28225-8.GIF" alt="GISMETEO.RU: ������ � �. �����" border=0>
	</a>
<?PHP
	// ������� ������ � ����������� ���������

	// ���� ��� �� ������ ���� ���������� ����������
	$save = @file('.save');
	$save_date = $save[0];
	if ((time() - $save_date) < 4)//24*60*60)
	{
		echo "use old!";
		include('pogoda.html');	 // ����� ��� ���������� ������ ����� ��� �� ������
	}
	else
	{
		echo "use new!";
		/*****************************************************************
		*  ��� ���� ��� ������
		*  Warning: file(): php_network_getaddresses: gethostbyname failed in c:\program files\easyphp1-7\www\weather.php on line 42
		*  Warning: file(http://www.gismeteo.ru/towns/28225.htm): failed to open stream: No error in c:\program files\easyphp1-7\www\weather.php on line 42
		*  Warning: implode(): Bad arguments. in c:\program files\easyphp1-7\www\weather.php on line 42
		******************************************************************/
		$to_save = implode("",file('http://www.gismeteo.ru/towns/28225.htm')); // ������ ����� ����
		//$to_save = implode("",file('http://www.serge2.ru/pogoda.htm')); // ������ ����� ����
		// save	date
		$f = fopen('.save','w'); // ��������� ���� ���������� ����������
		fputs($f,time()."\n");
		fclose($f); 
		// save file
		$f = fopen('pogoda.html','w'); // ��������� ��� ����
		fputs($f,$to_save);
		fclose($f);
		// show!
		include('pogoda.html');
	}
	// ��������� ������ � �����������
	//include('./pogoda.htm');
?>
    </div>
  </td>
 </tr>
</table>
</body>
</html>