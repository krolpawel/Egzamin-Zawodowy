<?php
mysql_connect ("mysql4.yoyo.pl", "db1237430", "tTS172nRpWO9A4J") or 
die ("Nie mo�na popo��czy� si� z MySQL");
mysql_query("SET NAMES 'utf8'");			
mysql_select_db ("db1237430") or 			
die ("Nie mo�na po��czy� si� z baz� danych");


	$kat_all_q=mysql_query("select * from users;");
	//echo'<form name=kat_select method=get><select>';
	while($rekord=mysql_fetch_assoc($kat_all_q)) {
		$katid=$rekord['user_id'];
		$nazwa=$rekord['login'];
		//echo"<option name='".$katid_."' value='".$nazwa_."'>$nazwa_</option>";
		echo ($katid." = ".$nazwa."<br>");
	}
	
?>