<?php  session_start();
include("access.php");
mysql_connect ($host,$login,$haslo) or 
die ("Nie można połączyć się z MySQL");
mysql_query("SET NAMES 'utf8'");
mysql_select_db ($select) or 
die ("Nie można połączyć się z bazą danych");
//sprawdzenie uprawnień
if (($_SESSION['login']) && ($_SESSION['pass'])) {
	$query="select * from users where login='".$_SESSION['login']."';";
	$tab=mysql_fetch_assoc(mysql_query($query));
	$pass=$tab['pass'];
	if ($pass==$_SESSION['pass'] && ($_SESSION['user_status']=='a' || $_SESSION['user_status']=='m' || $_SESSION['user_status']=='u')) { $access=1; }
	else { 	$access=0; }
} 
else { 
	$access=0;
}
if (!$access) {
	$_SESSION['unauth']=1;
	header("Location: index.php"); 
}	
//pobieranie czasu testu
$to_q="select value1 from settings where id_ustawienia='1';";
$to_t=mysql_fetch_assoc(mysql_query($to_q));
$timeout=$to_t['value1']; //w minutach!!
echo ('<form name="fczas"><input type="hidden" id="tft" value="'.$timeout.'"></form>');

//pobranie liczby pytań
$nr_of_q_t=mysql_fetch_assoc(mysql_query("select value1 from settings where id_ustawienia=4"));
$nr_of_q=$nr_of_q_t['value1'];

//sprawdzenie exp_time dla usera
$exp_date_t=mysql_fetch_assoc(mysql_query("select test_exp from users where login='".$_SESSION['login']."';"));
$exp_date_normal=$exp_date_t['test_exp'];
$date_y=substr($exp_date_normal,0,4);
$date_m=substr($exp_date_normal,5,2);
$date_d=substr($exp_date_normal,8,2);
$date_h=substr($exp_date_normal,11,2);
$date_i=substr($exp_date_normal,14,2);
$date_s=substr($exp_date_normal,17,2);
$exp_date_unix=(mktime($date_h, $date_i, $date_s, $date_m, $date_d, $date_y)*1);
$this_date_unix=(mktime($h=date('H'), $i=date('i'), $s=date('s'), $m=date('m'), $d=date('d'), $y=date('Y'))*1); //format uniksowy
$exp_check=$exp_date_unix-$this_date_unix;
if($exp_check<0) { mysql_query("update users set test_available='n', test_exp='0000-00-00 00:00:00' where login='".$_SESSION['login']."';");}
?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - TEST</title>
<link rel="stylesheet" type="text/css" href="style.css">
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
        var d = h / 24;               

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
 -->
</style>
<?php

if ($_POST) {
	$nrq=1;
	$nrqa="1-1";
	$nra=1;
	$dobre=0;	
	while ($_SESSION['wynikq'.$nrq]) {
		while ($_SESSION['wynika'.$nrqa]) { 
			$ch=0;
			if ($_SESSION['wyniktf'.$nrqa]=='t') { 
				if ($_POST['a'.$nrq]==$nra) { $ch=1; }
			}
			else { 
				if ($_POST['a'.$nrq]==$nra) { $ch=1;}
			}
			if ($ch==1 && $_SESSION['wyniktf'.$nrqa]=='t') { $dobre++; }
			$_SESSION['wynika'.$nrqa]=0; //zabezpieczenie przed wczytaniem pytań z poprzedniego układu jeśli bylo ich więcej (teraz mamy dwa, a wyświetla 5 bo zostało w tablicy) 
			$nra++;
			$nrqa="$nrq-$nra";
		}
		$_SESSION['wynikq'.$nrq]=0;
		$_SESSION['wyniktf'.$nrqa]=0;
		$nrq++;
		$nra=1;
		$nrqa="$nrq-$nra";
	} 
	$nrq--;
	$procent=($dobre/$nrq)*100;
	$max_test_t=mysql_fetch_assoc(mysql_query("select test_nr from result where login='".$_SESSION['login']."' order by test_nr desc limit 1;"));
	$akt_test=$max_test_t['test_nr']; 
	if($_SESSION['user-status']!='a') {
		mysql_query("Update result SET result='".$procent."' where login='".$_SESSION['login']."' and test_nr='".$akt_test."'");
	}
	print ('<script type="text/javascript">alert(\'Liczba dobrych odpowiedzi: '.$dobre.' na '.$nrq.' co daje '.$procent.' % \');</script>');
	print ("<center>Liczba dobrych odpowiedzi: $dobre na $nrq co daje $procent %<br><a href=index.php><input type=button value=Powrót></a></center>");
}
else {	//wyświetlenie testu
	$yn_t=mysql_fetch_assoc(mysql_query("select * from users where login='".$_SESSION['login']."';"));
	$yn=$yn_t['test_available'];	//sprawdzenie czy użytkownik może wykonac test
	if ($yn=='y') {
		echo('<script type="text/javascript">
			alert(\'Jesteś zalogowany jako: '.$_SESSION['login'].'. 
			Pamitaj, że ten test możesz wykonać tylko jednokrotnie! 
			Od chwili zamknięcia komunikatu, na wykonanie testu masz '.$timeout.' minut. 
			Powodzenia!\')</script>');
		if($_SESSION['user_status']!='a') {
			mysql_query("UPDATE users set test_available='n' where login='".$_SESSION['login']."';");	//na samym początku blokada wykonania testu po raz kolejny. (np. odświeżenie strony)
		}
		$nr_testu_t=mysql_fetch_assoc(mysql_query("select test_nr from result where login='".$_SESSION['login']."' order by test_nr DESC LIMIT 1"));
		$nr_testu=(($nr_testu_t['test_nr'])*1)+1;
		if($_SESSION['user-status']!='a') {
			mysql_query("insert into result (login, test_nr, result) values ('".$_SESSION['login']."', '".$nr_testu."','0');");
		}	
		$query_question="select * from questions order by rand() limit $nr_of_q;";	//losowanie pytań
		$tab_all_question=mysql_query($query_question);
		print ('<form name="test" action="test.php" method="POST">');
		print ('<br><br><table border="0" align="center">');
		$nrq=0;
		while ($all_question=mysql_fetch_assoc($tab_all_question)) {
			$nrq++;
			$_SESSION['wynikq'.$nrq]=$all_question['question'];
			$czy = strpos($_SESSION['wynikq'.$nrq], "<img"); //sprawdzenie czy istnieje zdjęcie
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
		print ('<tr><td><div class="licznik">Pozostały czas:<Input type="text" name="pole" id="pozostalo"></td></tr></div>');
		print ('<tr><td><input type="submit" value="zakończ test"></td></tr>');
		print ('</table></form>');
	}
	else { echo"Nie możesz wykonać testu!"; }	//gdy nie moze wykonać testu
?>
</form>
<?php
}
?>
</div>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
