<?php session_start(); 
include("access.php");
mysql_connect ($host,$login,$haslo) or 
die ("Nie można popołączyć się z MySQL");
mysql_query("SET NAMES 'utf8'");
mysql_select_db ($select) or 
die ("Nie można połączyć się z bazą danych");
//sprawdzanie uprawnień
if (($_SESSION['login']) && ($_SESSION['pass'])) {
	$query="select * from users where login='".$_SESSION['login']."';";
	$tab=mysql_fetch_assoc(mysql_query($query));
	$pass=$tab['pass'];
	if ($pass==$_SESSION['pass'] && ($_SESSION['user_status']=='a' || $_SESSION['user_status']=='m' || $_SESSION['user_status']=='u')) { $access=1; }
	else { 
		$access=0;
	}
}
else { 
	$access=0;
}
if (!$access) {
	$_SESSION['unauth']=1;
	header("Location: index.php"); 
}	

$login_usera=$_SESSION['login']; //stworzenie zmiennej lokalnej zawierającej login użytkownika wywołującego stronę

if($_SESSION['user_status']=='a' && isset($_GET['user'])) {
$login_usera=$_GET['user'];
$_SESSION['imie']=$_GET['imie'];
$_SESSION['nazwisko']=$_GET['nazwisko'];
}
//pobranie listy kategorii
$ilosc_kat_q='SELECT kat_id from kat order by kat_id DESC LIMIT 1;';
$katilosc=mysql_fetch_assoc(mysql_query($ilosc_kat_q));
//funkcja podmieniająca id kategorii na ich nazwy
function kat($n) {
	$nazwa_kat=mysql_fetch_assoc(mysql_query("select kat_name from kat where kat_id='$n'"));
	return $nazwa_kat['kat_name'];
}
?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - Wyniki</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<?php
//wyświetlenie imienia i nazwiska użytkownika z którym związane są poniższe statystyki
echo("<div align=center><font size=4>".$_SESSION['imie']." ".$_SESSION['nazwisko']."</font></div>");
print ("<div align=center width=800>");
echo("<table border=1 align=center cellpadding=5>
		<tr><td colspan=2><b>Wyniki z testu prawidłowego</b></td></tr>
		<tr><td>NR testu</td><td>Wynik %</td></tr>");
$wyniki_praw_tab=mysql_query("select * from result where login='$login_usera' order by test_nr");
while ($rekord=mysql_fetch_assoc($wyniki_praw_tab)) {
	echo"<tr><td>".$rekord['test_nr']."</td><td>".$rekord['result']."</td></tr>";
}
echo"</table><br>";
//pierwsza tabela treningowa
print ("<table border=0 align=center cellpadding=5><tr><td valign=top>
			<table border=1><tr><td colspan=3><b>Wyniki konkretnych podejść<br>w kategoriach:</b></td></tr>
			<tr><td>Kategoria</td><td>Podejcie</td><td>Wynik</td></tr>");
for ($i=1;$i<=$katilosc['kat_id'];$i++) {	//wyąwietlenie wyników wszystkich podejść we wszystkich kategoriach dla testów treningowych				
	$iloscpodejsc=mysql_fetch_assoc(mysql_query("SELECT podejscie from trening_result where login='$login_usera' and kat_id='".$i."' order by podejscie DESC LIMIT 1;"));
	$ipod=$iloscpodejsc['podejscie'];
	for	($j=1;$j<=$ipod;$j++) {			
		$qk=("select wynik from trening_result where login='".$login_usera."' and kat_id='".$i."' and podejscie='".$j."';");	//pobranie z bazy
		$tab=mysql_fetch_assoc(mysql_query($qk));
		$wynik=$tab['wynik'];
		if ($wynik) {
			if ($ilosc) { $ilosc++; } else {$ilosc=1;}
		print ("<tr><td>".kat($i)."</td><td>".$j."</td><td>".$wynik."</td></tr>");	//wyświetlenie wyników testów treningowych
		}
	}
	$ile[$i]=$ilosc;
	$ilosc=0;
}
print ("</table></td>");

//druga tabela
print ("<td valign=top><table border=1><tr><td colspan=2><b>Ilość podejść dla kategorii</b></td></tr>");	//podsumowanie dla kategorii
echo("<tr><td>Kategoria</td><td>podejścia</td></tr>");
for ($i=1;$i<=$katilosc['kat_id'];$i++) {
	if(!$ile[$i]) {$ile[$i]=0;}
	print ("<tr><td>".kat($i)."</td><td>".$ile[$i]."</td></tr>");
}
print ("</table></td>");

//trzecia tabela
$suma=0;
print ("<td valign=top><table border=1><tr><td colspan=2><b>Średni wynik w kategoriach</b></td></tr><tr><td>Kategoria</td><td>średnia</td></tr>");	//średni wynik w danych kategoriach
for ($i=1;$i<=$katilosc['kat_id'];$i++) {	
	$iloscpodejsc=mysql_fetch_assoc(mysql_query("SELECT podejscie from trening_result where login='".$login_usera."' and kat_id='".$i."' order by podejscie DESC LIMIT 1;"));
	$ipod=$iloscpodejsc['podejscie'];
	for	($j=1;$j<=$ipod;$j++) {																												
		$qk=("select * from trening_result where login=\"".$login_usera."\" and kat_id=\"".$i."\" and podejscie=\"".$j."\";");
		$tab=mysql_fetch_assoc(mysql_query($qk));
		$wynik=$tab['wynik'];
		if ($wynik) {
			if ($ilosc) { $ilosc++; } else {$ilosc=1;}
			$suma=$suma+$wynik;
		}
	}
	if ($ilosc) { $srednia=$suma/$ilosc; } else {$srednia=0;}	//wyliczenie średniej
	print ("<tr><td>".kat($i)."</td><td>".$srednia."</td></tr>");
	$ilosc=0;
	$suma=0;
	
}
echo'</table></td></tr></table><br>';
if($_SESSION['user_status']=='a') { echo"<a href=users_adm.php?opt_zk=".$_SESSION['opt_zk']."><input type=button value=Wróć></a>"; }
else { echo("<a href=index.php><input type=button value=Wróć></a>"); }
echo'</div>';
?>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>