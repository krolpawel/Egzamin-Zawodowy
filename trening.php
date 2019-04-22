<?  
session_start();
include("access.php");
mysql_connect ($host,$login,$haslo) or
die ("Nie można połączyć się z MySQL");
mysql_select_db ($select) or 					
die ("Nie można połączyć się z bazą db1237430");

//usuwanie zmiennych tymczasowych po rezygnacji
if ($_GET['rezygnacja']=='on') {
	$q=1;
	$qa="1-1";
	while($_SESSION['wynikq'.$q]) {
		$_SESSION['wynikq'.$q]=0;
		for ($a=1;$a<=4;$a++) {
			$qa="$q-$a";
			$_SESSION['wynika'.$qa]=0;
			$_SESSION['wyniktf'.$qa]=0;
		}
		$q++;
	}
	$_GET['rezygnacja']=0;
}
//Sprawdzenie czy do strony może mieć dostęp osoba niezalogowana
$ann_acc=mysql_fetch_assoc(mysql_query("Select value1 from settings where id_ustawienia=2;"));
if($ann_acc['value1']==1) {
	if (isset($_SESSION['login']) && isset($_SESSION['pass'])) {				
		$query="select * from users where login='".$_SESSION['login']."';";
		$tab=mysql_fetch_assoc(mysql_query($query));	
		$pass=$tab['pass'];
		if ($pass!=$_SESSION['pass']) { 
			$_SESSION['unauth']=1;
			header("Location:index.php");
		}
	}
	else {
		$_SESSION['unauth']=1;
		header("Location:index.php");
	}
}
?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - Trening</title>
<link rel="stylesheet" type="text/css" href="style.css">
<style type="text/css">
<!--
td { text-align:center; }
-->
</style>
</head>
<body>
<div>

<!-- wyświetlana treść -->
<table border=0 align="center">
<tr><td colspan=2><a href=index.php><input type=button value=Wróć></a></td></tr>
<tr><td colspan=2><font color="red" size="7">TRENING</font><br><br></td>
<?php
$kategorie_q=mysql_query("SELECT * from kat;");
$i=1;
$b=0;
while($rekord=mysql_fetch_assoc($kategorie_q)) {
	if(isset($rekord['pict'])) { $pict=$rekord['pict']; }
	else { $pict='ann.gif'; }
	$kat_name=$rekord['kat_name'];
	$kat_name=str_replace('_',' ',$kat_name);
	$kat_name=ucwords($kat_name);
	if($i) { $i=0; echo'</tr><tr>'; } else { $i=1; } //mechanizm wyświetlania dwóch komórek w wierszu
	++$b;
	echo"<td><a href=trening_test.php?o=$b><img src=kat_img/$pict height=150><br>$kat_name</a></td>";
}
?>
</form></table>
</div>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
