<? 
session_start(); 
error_reporting(E_ALL);
include('access.php');
mysql_connect ($host, $login, $haslo) or 
die ("Nie można popołączyć się z MySQL");
mysql_query("SET NAMES 'utf8'");
mysql_select_db ($select) or 
die ("Nie można połączyć się z bazą danych");
if (($_SESSION['login']) && ($_SESSION['pass'])) {
	$query="select * from users where login='".$_SESSION['login']."';";
	$tab=mysql_fetch_assoc(mysql_query($query));
	$pass=$tab['pass'];
	if ($pass==$_SESSION['pass']) { 
		$access=1; 
	} 
	else { 
		$access=0; 
	}
}
else { $access=0;}
//udzielanie dostępu ze źródła zewnętrznego (weryfikacja linkiem z mejla)
if(isset($_GET['from']) && isset($_GET['code']) && isset($_GET['login'])) {
	if($_GET['from']=='verify') {
		$access=1;
	}
}
if (!$access) {
	$_SESSION['unauth']=1;
	header("Location: index.php"); 
}
//wysyłanie kodu i przygotowanie systemu do aktywacji
if($_GET['from']=='agreement') {
	$email=$_POST['email'];
	$login=$_SESSION['login'];
	$trade='off';
	if(isset($_POST['trade'])) { 
		if($_POST['trade']=='on') {
			$trade='on';
		}
	}
	$chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$act_code=false;
	for ($j=0;$j<25;$j++) { 
		$act_code.=substr($chars, rand(0, strlen($chars)-1), 1); 
	}	
	mysql_query("UPDATE users SET email='$email', activation='$act_code', trade='$trade' where login='$login';");
	mail("$email",
	"Aktywacja konta w systemie \"Egzamin Zawodowy\"",
	"Witaj!\r\n \r\n
	Email dotyczy aktywacji konta w aplikacji \"Egzamin Zawodowy\". \r\n
	Jeśli nie wiesz o co chodzi - zignoruj wiadomość. \r\n
	Twój kod aktywacyjny to: $act_code \r\n
	\r\n
	Skopiuj go w odpowiednie pole podczas procesu aktywacji lub kliknij w link: \r\n
	http://www.egzamin.pkr.cba.pl/activation.php?from=verify&login=$login&code=$act_code \r\n
	\r\n 
	Powodzenia w nauce do Egzaminu Zawodowego :)
	Wiadomość została wygenerowana automatycznie i nie należy na nią odpowiadać! \r\n
	\r\n
	\r\n
	Administratorzy",
	"From: Aktywacja@egzamin.pkr.cba.pl"); //wysłanie wiadomości do administratora
}

//aktywacja po wpisaniu kodu bądź odwolaniu z linka
elseif($_GET['from']=='verify') {
	$login=$_GET['login'];
	$code_t=mysql_fetch_assoc(mysql_query("SELECT activation from users where login='$login'"));
	$code_b=$code_t['activation'];
	$code_u=$_GET['code'];
	if($code_u!=$code_b) {
		echo"<div align=center width=800px><br><br><br><h3><font color=red>Błędny kod! Konto nie zostalo aktywowane</font></h3><br><br>
			Wprowadź kod ponownie:<br>
			<form name=code method=GET action=activation.php>
			<input type=hidden name=from value=verify>
			<input type=text size=15 name=code><input type=submit value=Aktywuj></form></div>
			";
	}
	else {
		mysql_query("UPDATE users SET activation='ok' where login='$login';");
		echo"<div align=center width=800px><br><br><br><h3><font color=green>Konto zostało aktywowane!</font></h3><br><br>
			Przenieś się do panelu użytkownika:<br>
			<a href=index.php><input type=button value=PANEL></a>
			";
	}
}
?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title></title>
<link rel="stylesheet" type="text/css" href="style.css">
<script type=text/javascript>
function check() {
	var email=document.getElementById('email');
	email=email.value;
   var TestEmail = /^(.[A-Za-z0-9\-]*\w)+@+([A-Za-z0-9\-]*\w)+(\.[A-Za-z]*\w)+$/;
   var Wynik = email.match(TestEmail);
	var processing=document.getElementById('processing');
	processing=processing.checked;
   if (Wynik == null)
   {
      alert("Proszę wpisać poprawny adres e-mail!");
      act.email.value='';
      return false;
   }
   else if (processing==false) {
		alert("Wymagana zgoda na przetwarzanie danych osobowych");
		return false;
   }
   else {
	 document.forms["act"].submit();
	}
}
</script>
</head>
<body>
<?
//podawanie adresu email i zatwierdzanie zgody na przetwarzanie oraz wysyłanie inf. handlowych
if($_GET['from']=='first_log') {
	$queryimie="select imie from users where login='".$_SESSION['login']."';";		//pobieranie imienia na potrzeby "Witaj $imie"
	$tabimie=mysql_fetch_assoc(mysql_query($queryimie));			
	$imie=$tabimie['imie'];
	$_GET=array();
	print("<br><div class=\"main-up\"><font class=\"witaj\">Witaj $imie</font>");
	echo'<br><br><br>';
	echo'<form name=act method=POST action=activation.php?from=agreement><table align=center width=850px border=0 bordercolor=black cellpadding=15px>
			<tr>
				<td><font size=5>Aby móc korzystać z aplikacji wymagane jest wypełnienie poniższych pól<br>
					<font color=red style=bold>UWAGA! Na podany e-mail zostanie wysłany kod aktywacyjny niezbędny do zakończenia aktywacji<br></font></font></td>
			</tr>
			<tr>
				<td><font size=4>E-mail:<br></font>
				<input type=text name=email id=email size=50></td>
			</tr>
			<tr>
				<td><font size=3px>Wyrażam zgodę na przetwarzanie moich danych osobowych  przez właściciela aplikacji zgodnie z ustawą z dn. 
					29 sierpnia 1997 r. o ochronie danych osobowych (Dz. U. z 2002 r. Nr 101, poz. 926 z późn. zm.) w 
					celach statystycznych oraz marketingowych obejmujące przesyłanie informacji drogą elektroniczną. Niniejsza 
					zgoda obejmuje przetwarzanie danych osobowych w przyszłości, o ile nie zmieni się cel 
					przetwarzania. Oświadczam, że zostałam(em) poinformowana(y) o przysługującym mi prawie dostępu 
					do treści moich danych osobowych oraz ich poprawiania, jak również prawie wniesienia w każdym 
					czasie sprzeciwu wobec ich przetwarzania.</font><br>
				</td>
				<td><input type=checkbox name=processing id=processing></td>
			</tr>
			<tr>
				<td><font size=3px>Wyrażam zgodę na przesyłanie informacji handlowych drogą elektroniczną przez właściciela aplikacji zgodnie z 
					ustawą z dn. 18 lipca 2002 roku o świadczeniu usług drogą elektroniczną (Dz. U. z 2002 r. Nr 144, 
					poz. 1204 z późn. zm.).</font>
				</td>
				<td><input type=checkbox name=trade id=trade></td>
			</tr>
			<tr><td colspan=2><input type=button value=Dalej onClick=check()></td></tr>
		</table></form>';
}
elseif($_GET['from']=='agreement') {
	$login=$_SESSION['login'];
	echo'<div align=center width=800px><font size=5>Kod aktywacyjny został przesłany na adres '.$email.'.<br>
		<font color=red><br><br>Wiadomość może znaleźć się w folderze SPAM! </font></font><br>
		<h3>Poniżej wpisz swój kod aktywacyjny:</h3>
		<form name=code method=GET action=activation.php>
		<input type=hidden name=from value=verify>
		<input type=hidden name=login value='.$login.'>
		<input type=text size=15 name=code><input type=submit value=Aktywuj></form></div>';
}
?>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
