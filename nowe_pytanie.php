<?php session_start(); 
include("access.php");
mysql_connect ($host,$login,$haslo) or 
die ("Nie można połączyć się z MySQL");
mysql_query("SET NAMES 'utf8'");
mysql_select_db ($select) or 
die ("Nie można połączyć się z bazą danych");
if (($_SESSION['login']) && ($_SESSION['pass'])) {
	$query="select pass from users where login='".$_SESSION['login']."';";
	$tab=mysql_fetch_assoc(mysql_query($query));
	$pass=$tab['pass'];	
	if ($pass==$_SESSION['pass'] && ($_SESSION['user_status']=='a' || $_SESSION['user_status']=='m')) { $access=1; }
	else { 	$access=0; }
}
else { $access=0;}
if (!$access) {
	$_SESSION['unauth']=1;
	header("Location: index.php"); 
}	
//jeśli wszystko jest wprowadzone
if ($_POST['quest'] && $_POST['kat']!=0 && $_POST['ans1'] && $_POST['ans2'] && $_POST['ans3'] && $_POST['ans4'] && $_POST['r']) {
	$_POST['quest']=mysql_real_escape_string($_POST['quest']);
	mysql_query("INSERT INTO questions (question,kat_id) VALUES ('".$_POST['quest']."','".$_POST['kat']."');");	//wprowadzenie pytania i kategorii
	$tab_q=mysql_fetch_assoc(mysql_query("select question_id from questions order by question_id DESC LIMIT 1;"));
	$id=$tab_q['question_id'];
	$t=$_POST['r'];	
	for ($i=1;$i<=4;$i++) {
		$answer=$_POST["ans$i"];	
		echo($answer."<br>");
		mysql_query("INSERT INTO answers (question_id,answer_id,answer,tf) VALUES ('$id','$i','$answer','f');");
	}
	mysql_query("UPDATE answers SET tf='t' where question_id=$id and answer_id=$t");
	$plik_tmp = $_FILES['plik']['tmp_name']; 
	$plik_nazwa = $_FILES['plik']['name']; 
	$plik_rozmiar = $_FILES['plik']['size']; 
	$file_type=$_FILES['plik']['type']; 
	if(is_uploaded_file($plik_tmp)) { 
		$tablica_nazwy=explode(".",$plik_nazwa);
		$tablica_nazwy_ilosc=count($tablica_nazwy);
		echo($tablica_nazwy_ilosc);
		$tablica_nazwy_ilosc=$tablica_nazwy_ilosc-1;
		$rozszerzenie=$tablica_nazwy[$tablica_nazwy_ilosc];
		$plik_nazwa="$id.$rozszerzenie";
		move_uploaded_file($plik_tmp, "pict/$plik_nazwa");
		chmod("pict/$plik_nazwa",0755);
		$q_and_image=$_POST['quest'].' <br><img src=pict/'.$plik_nazwa.' width=';
		mysql_query('UPDATE questions SET question=\''.$q_and_image.'\'	where question_id=\''.$id.'\';');
		}
	//sprawdzenie poprawności wprowadzonych danych - na zasadzie wyświetlenia ostatniego rekordu z bazy
	if ($_POST['bw']=='on') {
		$tabcheck_q=mysql_fetch_assoc(mysql_query("select * from questions order by question_id DESC limit 1;"));
		$max_id=$tabcheck_q['question_id'];		//sprawdzenie najwyższego id w tabeli pytań (to które zostało własnie wprowadzone powinno mieć najwyższe)
		$check_q=$tabcheck_q['question'];	//treść pytania
		$check_k=$tabcheck_q['kat_id']; //numer kategorii - powyższe 3 dane jeżeli są pobrane właściwie to znaczy że są wprowadzone
		echo("<div class=\"wprowadzanie_check\"><table border=\"1\"  align=\"right\" width=\"60%\"><tr><td>ID pytania:</td><td>$max_id</td></tr>");
		echo("<tr><td>Treść: ");
		if ($plik_nazwa) { echo'<br><br>Nazwa pliku: '.$plik_nazwa; }	//wyświetla typ zdjęcia
		echo("</td><td>$check_q");
		if ($plik_nazwa) { echo'500>';}	//wyświetla zdjęcie
		echo("</td></tr>");
		echo("<tr><td>Kategoria</td><td>$check_k</td></tr>");
		$odpowiedzi=mysql_query("select * from answers where question_id='".$id."';");	//ściąga odpowiedzi z bazy
		while($tabcheck_a=mysql_fetch_assoc($odpowiedzi)) {
			$check_id_q=$tabcheck_a['question_id'];	//id pytania (w tabeli odpowiedzi)
			$check_id_a=$tabcheck_a['answer_id']; //id odpowiedzi
			$check_a=$tabcheck_a['answer'];	//treść odpowiedzi
			$check_tf=$tabcheck_a['tf'];	//  T/F
			if ($check_tf=='t') { //wyświetla dla odpowiedzi dobrej
				echo("<tr><td class=\"dobra\">Odpowiedź $check_id_a dla pytania $check_id_q:</td><td class=\"dobra\">$check_a</td></tr>");
			}
			elseif($check_tf=='f') {	// wyświetla dla odpowiedzi złej
				echo("<tr><td class=\"zla\">Odpowiedź $check_id_a dla pytania $check_id_q:</td><td class=\"zla\">$check_a</td></tr>");
			}
			else { $blad_insertu=1; }	//zabezpieczenie błędu
		}
		echo'<tr>';
		if ($blad_insertu==1) { echo('<td colspan=2 class="zla">BŁĄD WPROWADZENIA REKORDÓW. SPRAWDŹ BAZĘ!!!'); }	//jeśli wystąpił błąd
		else { echo'<td colspan=2 class="dobra">Rokordy wprowadzone poprawnie!'; }	// gdy nie wykryto błędów
		echo'</td></tr></table></div><br><br><br>';
	}
	$plik_nazwa=0;
}

//jeśli dane nie są wprowadzone, lub czegoś brakuje
else { echo('<font color="red"><b>formularz pusty lub źle uzupełniony</b></font>'); }
?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - Nowe Pytanie</title>
<link rel="stylesheet" type="text/css" href="style.css">
<script type="text/javascript" language="javascript"> //skrypt wykonujący przycisk 'wstaw kod'
function znaczniki() {
	nazwa=document.getElementById('pytanie');
	wartosc_pola_old=nazwa.value;
	pole_new=wartosc_pola_old+'<code><table border=1 align=center><tr><td cellspacing=3>   </td></tr></table></code>';
	pytanie.value=pole_new;
}
</script>
</head>
<body>
<!-- Wyświetlenie formularza -->
<div class="wprowadzanie">
<form enctype="multipart/form-data"  name="wprowadzanie rekordów" action="nowe_pytanie.php" method=POST>
<table align="center" border=1>
<?php 
echo('<tr><td colspan=2>Pokaż potwierdzenie (obciąża serwer)</td><td><input type="checkbox" name="bw"');
if ($_POST['bw']=='on') { echo(' checked="on" ');  }	
echo('></td></tr>');
?>
<tr><td>Pytanie:</td><td colspan="2"><input type="text" size=80 name="quest" id="pytanie"><br>
		<input type=button value=Kod onclick=znaczniki()></td></tr>
<tr><td>Kategoria:</td><td colspan="2">
	<select name="kat">
		<option selected=selected>Wybierz kategorię</option>
		<?php
		$kategorie_q=mysql_query("SELECT * from kat;");
		while($rekord=mysql_fetch_assoc($kategorie_q)) {
			$kat_name=$rekord['kat_name'];
			$kat_id=$rekord['kat_id'];
			echo"<option name=$kat_id value=$kat_id>$kat_name</option>";
		}
		?>
	</select>
	</td></tr>
<tr><td>Zdjęcie</td><td colspan="2"><input type="file" name="plik" value=""></td></tr>
<tr><td>Odpowiedź 1:</td><td><input type="text" name="ans1" size=80></td><td><input type="radio" name="r" value="1"></td></tr>
<tr><td>Odpowiedź 2:</td><td><input type="text" name="ans2" size=80></td><td><input type="radio" name="r" value="2"></td></tr>
<tr><td>Odpowiedź 3:</td><td><input type="text" name="ans3" size=80></td><td><input type="radio" name="r" value="3"></td></tr>
<tr><td>Odpowiedź 4:</td><td><input type="text" name="ans4" size=80></td><td><input type="radio" name="r" value="4"></td></tr>
<tr><td><input type="submit" value="Wprowadź"></td><td colspan=2><a href=index.php><input type=button value=Powrót></a></td></tr>
</table>
</form>
</div>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
