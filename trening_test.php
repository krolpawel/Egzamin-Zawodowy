<?php  session_start();
include("access.php");
mysql_connect ($host,$login,$haslo) or 
die ("Nie można połączyć się z MySQL");
mysql_query("SET NAMES 'utf8'");
mysql_select_db ($select) or 
die ("Nie można połączyć się z bazą danych");
	if (isset($_SESSION['login']) && isset($_SESSION['pass'])) {
		$query="select * from users where login='".$_SESSION['login']."';";
		$tab=mysql_fetch_assoc(mysql_query($query));
		$pass=$tab['pass'];
		if ($pass==$_SESSION['pass']) { 
			$_SESSION['logged']=1;
		} 
		else { $_SESSION['login']="unidentified"; $_SESSION['logged']=0; }
	}
//Sprawdzenie czy do strony może mieć dostęp osoba niezalogowana
$ann_acc=mysql_fetch_assoc(mysql_query("Select value1 from settings where id_ustawienia=2;"));
if($ann_acc['value1']==1 && $_SESSION['logged']!=1) {
	$_SESSION['unauth']=1;
	header('Location: index.php');
}
//pobieranie czasu testu zapisanego w bazie
$to_q="select value1 from settings where id_ustawienia='6';";	
$to_t=mysql_fetch_assoc(mysql_query($to_q));
$timeout=$to_t['value1']; //w minutach
echo ('<form name="fczas"><input type="hidden" id="tft" value="'.$timeout.'"></form>');	// formularz potrzebny do późniejszego porania danych przez skrypt JS

//pobranie liczby pytań
$nr_of_q_t=mysql_fetch_assoc(mysql_query("select value1 from settings where id_ustawienia=5"));
$nr_of_q=$nr_of_q_t['value1'];

?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - Test Treningowy</title>
<link rel="stylesheet" type="text/css" href="style.css">
<!-- skrypt przeliczający i wyświetlający odliczanie i poprawnie odliczający. w momencie TimeOutu następuje automatyczne wysłanie formularza -->
<script type="text/javascript">
<!--
var zmienna= new Date();
var timeout=(fczas.tft.value)*60000;
var dataWydarzenia = zmienna.getTime() + timeout;

function odliczanie()
{
var pozostalo=document.getElementById("pozostalo");
var aktualnyCzas = new Date(); 
var pozostalyCzas = dataWydarzenia - aktualnyCzas.getTime(); 
if (pozostalyCzas > 0)  
    {                        
        var s = pozostalyCzas / 1000;  
        var min = s / 60;              
        var h = min / 60;                          

        var poz_s = Math.floor(s  % 60);         
        var poz_min = Math.floor(min % 60);   
        var poz_godz = Math.floor(h);          
        
        if (poz_min < 10)					
          poz_min = "0" + poz_min;
        if (poz_s < 10)							
          poz_s = "0" + poz_s;
        if (poz_godz < 10)					
          poz_godz = "0" + poz_godz;
        test.pozostalo.value=(poz_godz + ':' + poz_min + ':' + poz_s);
		  
		setTimeout("odliczanie()",1000);  
    }
		else			
		{
		test.pozostalo.value=("Koniec czasu");
		clearTimeout; 
		document.test.submit();
		}
}
-->
</script>
</head>
<body onLoad="odliczanie()">
<div>
<style type="text/css">
<!-- 
td { text-align:left; }
td.center { text-align:center; }
td.right { text-align:right; }
 -->
</style>
<?php
//wyniki po przesłaniu testu
if ($_POST) {
	print ('<table border="0" align="center">');
	$nrq=1;	//nr pytania
	$nrqa="1-1";	//nr pytanie-odpowiedź
	$nra=1;	//nr odpowiedzi
	$dobre=0;		
	while ($_SESSION['wynikq'.$nrq]) {	
		print('<tr><td height="80px"><font size="5">'.$nrq.". ".$_SESSION['wynikq'.$nrq].'</font></td></tr>');	//wyświetlanie pytań
		while ($_SESSION['wynika'.$nrqa]) { //wyświetlanie odpowiedzi
			$ch=0;
			if ($_SESSION['wyniktf'.$nrqa]=='t') { 
				print('<tr><td><font color="green"><input type="radio" ');	//gdy prawdziwa
				if ($_POST['a'.$nrq]==$nra) { print ('checked="on"'); $ch=1; }	//gdy zaznaczona
				print (' name="a'.$nrq.'" value="'.$nrqa.'"">'.$nrqa." ".$_SESSION['wynika'.$nrqa].'</font></td></tr>'); 
			}
			else { 
				print('<tr><td><font color="red"><input type="radio" ');	//gdy fałszywa
				if ($_POST['a'.$nrq]==$nra) { print ('checked="on"'); $ch=1;}	//gdy zaznaczona
				print (' name="a'.$nrq.'" value="'.$nra.'" ">'.$nrqa." ".$_SESSION['wynika'.$nrqa].'</font></td></tr>'); 
			}
			if ($ch==1 && $_SESSION['wyniktf'.$nrqa]=='t') { $dobre++; }	//gdy zaznaczona i prawidłowa
			$_SESSION['wynika'.$nrqa]=0; //zabezpieczenie przed wczytaniem pytań z poprzedniego układu jeśli bylo ich więcej (teraz mamy dwa, a wyświetla 5 bo zostało w tablicy) 
			$nra++;
			$nrqa="$nrq-$nra";
		}
		$_SESSION['wynikq'.$nrq]=0;	//zabezpieczenie przed wywołaniem tej zmiennej w późniejszym czasie
		$_SESSION['wyniktf'.$nrqa]=0;	//		--||--
		$nrq++;
		$nra=1;
		$nrqa="$nrq-$nra";
	} 
	print ('</table>');
	$nrq--;
	$procent=($dobre/$nrq)*100;	//wyliczenie procentów
	if ($_SESSION['logged']==1) {	//sprawdza czy zalogowany		
		$max_pod_q=mysql_query("select podejscie from trening_result where login='".$_SESSION['login']."' AND kat_id='".$_SESSION['kat']."' order by podejscie DESC limit 1;"); //pobranie ostatniego podejścia
		while ($max_pod_t=mysql_fetch_assoc($max_pod_q)) { $max_pod=$max_pod_t['podejscie']; }
		$act_pod=($max_pod*1)+1;	//ustalenie numeru aktualnego podejścia
		mysql_query("insert into trening_result (login, kat_id, podejscie, wynik) value ('".$_SESSION['login']."', '".$_SESSION['kat']."', '".$act_pod."', '".$procent."');"); //wprowadzenie wyniku do bazy
	}
	print ('<script type="text/javascript">alert(\'Liczba dobrych odpowiedzi: '.$dobre.' na '.$nrq.' co daje '.$procent.' % \');</script>');
	print ("<center>Liczba dobrych odpowiedzi: $dobre na $nrq co daje $procent %<br><a href=index.php><input type=button value=Powrót></a></center>");
}
//Wyświetlenie testu
elseif (isset($_GET['zakres_wybrany']) || isset($_GET['zakres_wszystko'])) {
	$_SESSION['kat']=$_GET['o'];
	//czy zalogowany + komunikat JS
	if ($_SESSION['logged']) { echo('<script type="text/javascript">alert(\'Jesteś zalogowany jako: '.$_SESSION['login'].'. Postępy zostaną zapisane. Powodzenia!\')</script>');}
	else { echo('<script type="text/javascript">alert(\'Nie jesteś zalogowany. Postępy nie zostaną zapisane!\');</script>'); }
	//ilość kategorii
	$ilosc_kat_q=mysql_fetch_assoc(mysql_query("SELECT kat_id FROM kat ORDER BY kat_id DESC LIMIT 1;"));
	$ilosc_kat=$ilosc_kat_q['kat_id'];
	//druk form i table; prepare to while
	print ('<form name="test" action="trening_test.php" method="POST">');
	print ('<table border="0" align="center">');
	$nrq=0;
	//gdy wykonywany jest pełny test z kategorii
	if (isset($_GET['zakres_wszystko'])) {	//gdy wybrano test z całej kategorii
	//zapytanie o pytania z danego działu
	if ($_GET['o']!='1') { $query_question="select * from questions where kat_id='".$_SESSION['kat']."' order by rand() limit $nr_of_q"; }
	elseif ($_GET['o']=='1') { $query_question="select * from questions where kat_id between 3 and $ilosc_kat order by rand() limit $nr_of_q;"; }	//losuje pytania W OKREŚLONEJ TU ILOŚCI!!!!
	$tab_all_question=mysql_query($query_question);
		while ($all_question=mysql_fetch_assoc($tab_all_question)) {
			$nrq++;
			$_SESSION['wynikq'.$nrq]=$all_question['question'];
			$czy = strpos($_SESSION['wynikq'.$nrq], "<img"); 	//sprawdza czy jest zdjęcie
			if ($czy==TRUE) { $_SESSION['wynikq'.$nrq].='500>'; }		//wielkość zdjęcia w teście
			print('<tr><td height="80px"><font size="5">'.$nrq.". ".$_SESSION['wynikq'.$nrq].'</font></td></tr>');
			$idq=$all_question['question_id'];
			$query_answer="select * from answers where question_id='".$idq."'  order by rand() limit 4;";	//losuje kolejność odpowiedzi
			$tab_all_answers=mysql_query($query_answer);
			$nra=0;
				while ($all_answers=mysql_fetch_assoc($tab_all_answers)) { 
					$nra++;
					$nrqa="$nrq-$nra";
					$_SESSION['wynika'.$nrqa]=$all_answers['answer'];
					$_SESSION['wyniktf'.$nrqa]=$all_answers['tf'];
					print('<tr><td><input type="radio" name="a'.$nrq.'" value="'.$nra.'">'.$nrqa." ".$_SESSION['wynika'.$nrqa].'</td></tr>');
				}
			}
		print ('<tr><td><div class="licznik">Pozostały czas:<Input type="text" name="pole" id="pozostalo"></td></tr></div>');
		print ('<tr><td><input type="submit" value="Zakończ i Sprawdź!"></td></tr>');
	}
	//gdy ćwiczony jest dany zakres
	elseif (isset($_GET['zakres_wybrany'])) {
	//zapytanie o pytania z danego działu
		if ($_GET['o']!='1') { $query_question="select * from questions where kat_id='".$_SESSION['kat']."' order by rand()"; }
		elseif ($_GET['o']=='1') { $query_question="select * from questions where kat_id between 3 and $ilosc_kat order by rand()"; }	//losuje pytania W OKREŚLONEJ TU ILOŚCI!!!!
		$tab_all_question=mysql_query($query_question);
		$spr_zakresu=0;
		$od=$_GET['od'];	
		$do=$_GET['do'];
		while ($all_question=mysql_fetch_assoc($tab_all_question)) {
			$spr_zakresu++;
			if ($spr_zakresu>=$od && $spr_zakresu<=$do) {	//czy pętla jest  zakresie z którego pytania mają zostać wyświetlone
				$nrq++;
				$_SESSION['wynikq'.$nrq]=$all_question['question'];
				$czy = strpos($_SESSION['wynikq'.$nrq], "<img");
				if ($czy==TRUE) { $_SESSION['wynikq'.$nrq].='500>'; }
				print('<tr><td height="80px"><font size="5">'.$nrq.". ".$_SESSION['wynikq'.$nrq].'</font></td></tr>');
				$idq=$all_question['question_id'];
				$query_answer="select * from answers where question_id='".$idq."'  order by rand() limit 4;";
				$tab_all_answers=mysql_query($query_answer);
				$nra=0;
				while ($all_answers=mysql_fetch_assoc($tab_all_answers)) { 
					$nra++;
					$nrqa="$nrq-$nra";
					$_SESSION['wynika'.$nrqa]=$all_answers['answer'];
					$_SESSION['wyniktf'.$nrqa]=$all_answers['tf'];
					print('<tr><td><input type="radio" name="a'.$nrq.'" value="'.$nra.'">'.$nrqa." ".$_SESSION['wynika'.$nrqa].'</td></tr>');
				}
			}
		}
		print ('<tr><td><input type="submit" value="Pokaż prawidłowe odpowiedzi"></td></tr>');
	}
	echo'<tr><td><a href=trening.php?rezygnacja=on><input type=button value=Zrezygnuj></a></td></tr>';
	print ('</table></form>');
}
//Wybór zakresu pytań w dziale / test z całego działu
elseif ($_GET['o']) {
	$kat_id=$_GET['o'];
	$kat_query=mysql_query("Select kat_name from kat where kat_id='$kat_id';");
	while($rekord=mysql_fetch_assoc($kat_query)) {
		$kategoria=$rekord['kat_name'];
		$kategoria=str_replace('_',' ',$kategoria);
		$kategoria=ucwords($kategoria);
	}
	$ilosc_pytan_tab=mysql_fetch_assoc(mysql_query("select question_id from questions order by question_id DESC LIMIT 1"));
	$ilosc_pytan=$ilosc_pytan_tab['question_id'];
	echo"<table align=center border=1><form name=zakres method=GET><input type=hidden name=o value='".$kat_id."'><tr><td class=center>Dział:</td><td colspan=3 class=center><b>$kategoria</b></td></tr>";
	echo"<tr><td class=center>Wybierz zakres pytań <br>z tej kategorii:</td><td>Od <input type=text size=1 name=od></td><td>Do <input type=text size=1 name=do><br>(max. $ilosc_pytan)</td><td><input type=submit name=zakres_wybrany value='Wybierz'></td></tr>";
	echo"<tr><td colspan=2 class=center>lub wykonaj test z całego działu</td><td colspan=2 class=center><input type=submit name=zakres_wszystko value='Wykonaj'></td></tr>";
	echo'<tr><td colspan=4 class=center><font color=red><b>UWAGA! <br>Postępy zostaną zapisane tylko, jeśli wykonujesz <br>test z całego działu i jesteś zalogowany/a.</b></font></td></tr>';
	echo'<tr><td colspan=4 class=center><a href=trening.php><input type=button value=Powrót></a></td></tr>';
	echo"</table>";
}
?>
</div>
<br>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>

</html>
