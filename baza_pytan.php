<?php session_start(); 
include("access.php");
mysql_connect ($host,$login,$haslo) or 
die ("Nie można popołączyć się z MySQL");
mysql_query("SET NAMES 'utf8'");
mysql_select_db ($select) or 
die ("Nie można połączyć się z bazą danych");
if (($_SESSION['login']) && ($_SESSION['pass'])) {
	$query="select * from users where login='".$_SESSION['login']."';";
	$tab=mysql_fetch_assoc(mysql_query($query));
	$pass=$tab['pass'];
	if ($pass==$_SESSION['pass'] && ($_SESSION['user_status']=='a' || $_SESSION['user_status']=='m')) { $access=1; }
	else { $access=0;}
}
else { 
	$access=0;
}
//czy zalogowany i ma uprawnienia
if (!$access) {
	$_SESSION['unauth']=1;
	header("Location: index.php"); 
}
//pobieranie ilosci pytan w bazie danych
$ilosc_pytan_t=mysql_fetch_assoc(mysql_query("select question_id from questions order by question_id DESC limit 1;"));
$ilosc_pytan=$ilosc_pytan_t['question_id'];

//pobieranie ilosci kategorii
$ilosc_kat_t=mysql_fetch_assoc(mysql_query("select kat_id from kat order by kat_id DESC limit 1;"));
$ilosc_kat=$ilosc_kat_t['kat_id'];

//usunięcie (po zatwierdzeniu)
if ($_GET['del_check']=='OK') {
	foreach($_GET as $rekord) {
			$tresc=mysql_query("select * from questions where question_id='$rekord';");
			while($pytanie=mysql_fetch_assoc($tresc)) {
				$pyt=htmlspecialchars($pytanie['question']);
				$del_pict_a=explode("src=",$pyt);
				$minus3=strlen($del_pict_a[1])-7;
				$del_pict=substr($del_pict_a[1], 0, $minus3);
				unlink($del_pict);
				mysql_query("DELETE from questions where question_id='$rekord';");
				mysql_query("DELETE from answers where question_id='$rekord';");
			}
	}
}	

//update pytania
if ($_POST) {
	for($i=1;$i<=$ilosc_pytan;$i++) {
		if ($_POST["p$i"]) {
			$q_content=$_POST["p$i"];
			$q_kategoria=$_POST["k$i"];
			mysql_query("UPDATE questions SET question='$q_content', kat_id='$q_kategoria' where question_id=$i;");
			for ($j=1;$j<=4;$j++) {
				$a_content=$_POST["$i-$j"];
				mysql_query("UPDATE answers SET answer='$a_content' where question_id=$i and answer_id=$j;");
				if ($_POST["r$i"]==$j) { mysql_query("UPDATE answers SET tf='t' where question_id=$i and answer_id=$j;"); }
				else { mysql_query("UPDATE answers SET tf='f' where question_id=$i and answer_id=$j;"); }
			}
			$updater=1;
		}
	}
}

//update rekordów - zmiana pliku
for ($i=1;$i<=$ilosc_pytan;$i++) {
	if ($_FILES["plik_nowy$i"]['tmp_name']) {
		$plik_tmp = $_FILES["plik_nowy$i"]['tmp_name']; 
		$plik_nazwa = $_FILES["plik_nowy$i"]['name']; 
		$plik_rozmiar = $_FILES["plik_nowy$i"]['size']; 
		$file_type=$_FILES["plik_nowy$i"]['type']; 
		$tablica_nazwy=explode(".",$plik_nazwa);
		$tablica_nazwy_ilosc=count($tablica_nazwy);
		$tablica_nazwy_ilosc=$tablica_nazwy_ilosc-1;
		$rozszerzenie=$tablica_nazwy[$tablica_nazwy_ilosc];	
		$new_plik="$i.$rozszerzenie";
		move_uploaded_file($plik_tmp, "pict/$new_plik"); 	
	}
	elseif($_FILES["plik_n$i"]['tmp_name']) {
		$plik_tmp = $_FILES["plik_n$i"]['tmp_name']; 
		$plik_nazwa = $_FILES["plik_n$i"]['name']; 
		$plik_rozmiar = $_FILES["plik_n$i"]['size']; 
		$file_type=$_FILES["plik_n$i"]['type']; 
		$tablica_nazwy=explode(".",$plik_nazwa);
		$tablica_nazwy_ilosc=count($tablica_nazwy);
		$tablica_nazwy_ilosc=$tablica_nazwy_ilosc-1;
		$rozszerzenie=$tablica_nazwy[$tablica_nazwy_ilosc];	
		$new_plik="$i.$rozszerzenie";
		move_uploaded_file($plik_tmp, "pict/$new_plik");
		chmod("pict/$new_plik",0755);		
		$qt=mysql_fetch_assoc(mysql_query("select question from questions where question_id='$i';"));
		$qn=$qt['question'];
		$qnn=$qn.' <br><img src=pict/'.$new_plik.' width=';
		mysql_query('UPDATE questions SET question=\''.$qnn.'\'	where question_id=\''.$i.'\';');
	}
}	
if ($updater==1) { 
	$dzial=$_POST['dzial'];
	header("Location: baza_pytan.php?dzial=$dzial"); }
?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - Baza Pytań</title>
<link rel="stylesheet" type="text/css" href="style.css">
<style type="text/css">
table,td {
	border:2px solid #708090;
	border-collapse: collapse;
	padding: 3px;
}
table { 
	width:80%;
	align:center;
	background-color: #87CEFA;
	
}
div.opis_tabeli {
	position:fixed;
	top:0px;
	left:0px;
}
table.opis_bazy {
	background-color: #DAA520;
	padding:0px;
}
</style>
<script type="text/javascript" language="javascript">
function znaczniki(pytanie) {
	nazwa=document.getElementById(pytanie);
	wartosc_pola_old=nazwa.value;
	pole_new=wartosc_pola_old+'<code><table border=1 align=center><tr><td cellspacing=3>   </td></tr></table></code>';
	nazwa.value=pole_new;
}
</script>
</head>
<body>
<?php

//funkcja od zmiany id kategorii na jej nazwe
function nazwa($idp) {
	$kat_name_t=mysql_fetch_assoc(mysql_query("Select kat_name from kat where kat_id='".$idp."'"));
	$kat_name_r=$kat_name_t['kat_name'];
	$kat_name=str_replace('_','<br>',$kat_name_r);
	return($kat_name);
}

//po kliknięciu USUŃ - wyświetlenie potwierdzenia
if ($_GET['del']) {
	echo'<form method=get><table border=1 align=center><tr><td>ID</td><td>PYTANIE</td></tr>';
	$dzial=$_GET['dzial'];
	$_GET['dzial']=false;
	foreach ($_GET as $rekord) {
			$q_q_ic=mysql_query('select * from questions where question_id='.$rekord.';');
			while($q_tab_ic=mysql_fetch_assoc($q_q_ic)) {
				$id_ic=$q_tab_ic['question_id'];
				$question_ic=$q_tab_ic['question'];
				$czy = strpos($question_ic, "<img");
				if ($czy==TRUE) { $question_ic.='300>'; }
				echo"<tr><td>$id_ic</td><td>$question_ic</td></tr>";
				echo"<input type=hidden name='$rekord' value='$rekord' checked=on>";
			}
	}
	echo"<tr><td><font color=red>Czy na pewno chcesz usunąć te rekordy?</font></td></tr>";
	echo'<tr><td colspan=2><input type=submit name=del_check value=OK><a href="baza_pytan.php?dzial='.$dzial.'"><input type=button value=Wróć></a></td><tr>';
	echo'</table></form>';
}
//widok edycji
elseif (isset($_GET['edit'])) { 
	echo'<form enctype="multipart/form-data" name="edycja" method=POST action="baza_pytan.php"><table border=1>';
	$dzial=$_GET['dzial'];
	$_GET['dzial']=0;
	unset($_GET['search']);
	foreach ($_GET as $rekord) {
		if($rekord!='Edytuj wybrane') {
			$pytanie_t=mysql_fetch_assoc(mysql_query("select * from questions where question_id=$rekord;"));
			$pytanie=$pytanie_t['question'];
			$kategoria=$pytanie_t['kat_id'];
			echo('<tr><td><b>Pytanie nr.'.$rekord.'</b></td><td colspan=2><input type="text" size=130 name=p'.$rekord.' id=p'.$rekord.' value=\''.$pytanie.'\'>');
			echo("<input type=button value=Kod onclick=znaczniki('p$rekord')></td></tr>");
			$czy = strpos($pytanie, "<img");
			if ($czy==TRUE) {
				echo'<input type=hidden name=foto value=new>';
				$od_kropki=explode(".",$pytanie);
				$liczba_elementow=count($od_kropki)-1;
				$dlugosc=strlen($od_kropki[$liczba_elementow])-7;
				$rozszerzenie=substr($od_kropki[$liczba_elementow],0,$dlugosc);
				echo"<tr><td>Zdjęcie $rozszerzenie</td><td><img src=pict/$rekord.$rozszerzenie width=500><br>";
				echo"Zmień na...<input type=file name=plik_nowy$rekord value='' ></td></tr>";
			}
			else {
				echo"<tr><td>Dodaj zdjęcie:</td><td><input type=file name=plik_n$rekord value=''></td></tr>";
			}
			echo"<tr><td><b>Kategoria</b></td><td colspan=2>";
			//lista z katerogiami
			$kat_all_q=mysql_query("select * from kat;");
			echo"<select name=\"k$rekord\">";
			while($rekords=mysql_fetch_assoc($kat_all_q)) {
				$katid=$rekords['kat_id'];
				$nazwa=$rekords['kat_name'];
				if($nazwa!='Informatyka') {
				
					echo"<option value='$katid' ";
					if($katid==$kategoria) { echo" selected=selected"; }
					echo">$nazwa</option>";
				}
			}
			echo"</select></TD></TR>";	
			
			for ($j=1;$j<=4;$j++) {
				$odp_t=mysql_fetch_assoc(mysql_query("select * from answers where question_id=$rekord and answer_id=$j;"));
				$odp=$odp_t['answer'];
				$tf=$odp_t['tf'];
				echo"<tr><td>Odpowiedź nr.$j</td><td><input type=\"text\" size=70 name=\"$rekord-$j\" value=\"$odp\"></td><td><input type=\"radio\" name=\"r$rekord\" value=\"$j\" ";
				if ($tf=="t") { echo'checked="on"'; }
				echo"></td></tr>";
			}  
			$c=1;
			echo('<tr><td colspan=3style="background-color:black"><hr size=5 color=red></td></tr>');
		}
	}
	if($c==1) {
	echo"<input type=hidden name=dzial value=$dzial>";
		echo'</table><div class=edytor><input type="submit" value="Popraw dane"><br><a href=baza_pytan.php?dzial='.$dzial.'><input type=button value=Powrót></a></div></form>'; 
		$c=0;
	}
}
//wyświetlenie bazy
else {
	echo'<form name="wszystkie_rekordy_bazy" method="get">';
		echo'<div class="opis_tabeli">
				<a href=index.php><input type=button value=Powrót></a><br>
				<input type=submit value="Edytuj wybrane" name=edit><br>
				<input type=submit value="Usuń" name=del>';
			echo"<input type=hidden name=dzial value=".$_GET['dzial'].">
		</div>";
	echo'<br><br><br><table align="center">';
	if(isset($_GET['dzial'])) {
		if($_GET['dzial']=='0') { echo'<br><br>nie możesz wybrać tej opcji!!';}
		$dzial=$_GET['dzial'];
		if($dzial=='Wszystkie') { 
			if($_GET['search']!='') { $q_p=mysql_query("select * from questions where question like '%".$_GET['search']."%' order by question_id"); }
			else { $q_p=mysql_query("select * from questions order by question_id;"); }
		}
		elseif($dzial==1) { 
			if($_GET['search']!='') { $q_p=mysql_query("select * from questions where kat_id between 3 and $ilosc_kat and question like '%".$_GET['search']."%' order by question_id"); }
			else { $q_p=mysql_query("select * from questions where kat_id between 3 and $ilosc_kat order by question_id;"); }
		}
		else { 
			if($_GET['search']!='') { $q_p=mysql_query("select * from questions where kat_id=$dzial and question like '%".$_GET['search']."%' order by question_id"); }
			else {$q_p=mysql_query("select * from questions where kat_id=$dzial order by question_id;"); }
		}
		$i=0;
		echo'<tr><td colspan=4>Wyszukaj po treści pytania:
				<form action=baza_pytan.php method=GET>
				<input type=text name=search size=20>
				<input type=submit value=Wyszukaj>
				</form>
			</td></tr>';
		echo'<tr bgcolor=#DAA520><td>ID</td><td>Treść pytania</td><td>kat</td><td>DEL<br>EDIT</td></tr>';
		while($pyt_t=mysql_fetch_assoc($q_p)) {
			$i++;
			$id_pyt=$pyt_t['question_id'];
			$q_pyt=$pyt_t['question'];
			$czy = strpos($q_pyt, "<img");
			if ($czy==TRUE) { $q_pyt.='150>'; }
			$k_pyt_id=$pyt_t['kat_id'];
			$k_pyt=nazwa($k_pyt_id);
			echo'<tr><td><b>'.$id_pyt.'</b></td><td>'.$q_pyt.'</td><td><b>'.$k_pyt.'</b></td><td><input type="checkbox" name='.$i.' value='.$id_pyt.'></td></tr>';
		}
		echo'<tr bgcolor=#DAA520><td>ID</td><td>Treść pytania</td><td>kat</td><td>DEL<br>EDIT</td></tr>';
		echo'</table></form><br><br><br>';
	}
}
?>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
