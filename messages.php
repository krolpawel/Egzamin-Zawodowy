<? session_start(); 
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
	if ($pass==$_SESSION['pass']) { 
		$log=1;
	} 
}
if (!$log) { 
	$_SESSION['unauth']=1;
	header("Location: index.php"); 
}
//zerowanie
$host=''; $login=''; $haslo=''; $select=''; $log='';

//dane o senderze
if($_SESSION['user_status']=='a') {
	$sender_tab=mysql_fetch_assoc(mysql_query("select imie, nazwisko from users where login='".$_SESSION['login']."'"));
	$sender=$sender_tab['imie']." ".$sender_tab['nazwisko'];
}
else { $sender=$_SESSION['login']; }

//id wiadomości
$message_id_tab=mysql_fetch_assoc(mysql_query("Select value1 from settings where id_ustawienia='3'"));
$message_id=$message_id_tab['value1']+1;

$data=date('Y-m-d');

//oznaczenie jako nieprzeczytana
if(isset($_GET['new_agin'])) {
	mysql_query("update messages set new=1 where login='".$_SESSION['login']."' and message_id='".$_GET['new_agin']."'");
	header('Location:messages.php?inbox');
}
//wysyłanie widomośc od admina
elseif(isset($_POST['content'])&&$_SESSION['user_status']=='a') {
	$_POST['from-adm']=false;
	$_POST['button']=false;
	$content=htmlspecialchars($_POST['content']);
	$_POST['content']=false;
	$subject=$_POST['subject'];
	$_POST['subject']=false;
	$_POST['send']=false;
	foreach($_POST as $login) {
		if($login!='') {
			mysql_query("insert into messages values ('$login', '$message_id', '$sender', '$subject', '$content', '$data', '1')");
		}
	}
	mysql_query("update settings set value1='".$message_id."' where id_ustawienia=3;");
	header('Location:administration.php');
}
//wysłanie wiadomości od usera/moderatora
elseif(isset($_POST['content'])&&($_SESSION['user_status']=='m' || $_SESSION['user_status']=='u')) {
	if($_POST['receiver']=='0') { echo"Nie wprowadzono odbiorcy!!! <a href='javascript:history.back()'><input type=button value='Powrót'></a>"; }
	else {
		$receiver=$_POST['receiver'];
		$subject=$_POST['subject'];
		$content=$_POST['content'];
		mysql_query("insert into messages values('$receiver', '$message_id', '$sender', '$subject', '$content', '$data', '1')");
		mysql_query("update settings set value1='".$message_id."' where id_ustawienia=3;");
		header('Location:main.php');
	}
}
//usuwanie wiadomości
elseif(isset($_GET['del'])) {
	mysql_query("DELETE from messages WHERE login='".$_SESSION['login']."' and message_id='".$_GET['del']."'");
	header('Location:messages.php?inbox='.$_SESSION['login']);
}
?>

<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - Wiadomości</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<?
//nowa wiadomość od administratora
if ($_GET['new']=='from-adm') { 
	$_GET['new']=false;
	$_GET['test_nr']=false; //potrzebne przy przekierowaniu z widoku zarządzania GRUPĄ/KLASĄ
	echo"<table border=1 align=center><form name=from-adm method=POST action=messages.php>
			<tr><td colspan=2>Nowa wiadomość</td></tr>
			<tr><td>Nadawca</td><td>$sender</td></tr>
			<tr><td>Temat</td><td><input type=text size=100% name=subject></td></tr>
			<tr><td>Odbiorcy:</td><td>Treść:</td></tr>
			<tr><td>";
	$i=1;
	foreach($_GET as $login) {
	if($login!='' && $login!='0') {
		$user=mysql_fetch_assoc(mysql_query("select imie,nazwisko from users where login='$login'"));
		echo"<input type=hidden name='$i' value='$login' checked=checked>";
		$i++;
		echo($user['nazwisko'].' '.$user['imie'].' ('.$login.')<br>');
		}
	}
	echo"</td><td><textarea cols=60 rows=10 name=content></textarea></td></tr>";
	echo"<tr><td><a href='javascript:history.back()'><input type=button name=button value='Powrót'></a></td>
			<td><input type=submit name=send value='Wyślij'></td></tr>";
	echo"</form></table>";
	$_GET=false;
}
//skrzynka odbiorcza
elseif(isset($_GET['inbox'])) {
		$messages_q=mysql_query("select * from messages where login='".$_SESSION['login']."' order by data desc");
		echo"<table border=1 align=center cellpadding=5 width=800><tr><td><b>NADAWCA</b></td><td width=70%><b>TEMAT</b></td><td><b>DATA</b></td></tr>";
		while($rekord=mysql_fetch_assoc($messages_q)) {
			$m_id=$rekord['message_id'];
			if($_SESSION['user_status']=='a') { 
				$dane_sendera=mysql_fetch_assoc(mysql_query("select imie,nazwisko from users where login='".$rekord['sender']."'"));
				$rekord['sender'].="<br>(".$dane_sendera['nazwisko']." ".$dane_sendera['imie'].")";
			}
			if ($rekord['new']=='1') { 
				echo('<tr>
						<td class=dobra><a href=messages.php?message='.$m_id.' style="color:green" class=all-new>'.$rekord['sender'].'</a></td>
						<td class=dobra><a href=messages.php?message='.$m_id.' style="color:green" class=all-new>'.$rekord['subject'].'</a></td>
						<td class=dobra><a href=messages.php?message='.$m_id.' style="color:green" class=all-new>'.$rekord['data'].'</a></td>
					</tr>');
			}
			else {
				echo('<tr>
						<td><a href=messages.php?message='.$m_id.' style="color:black" class=all-old>'.$rekord['sender'].'</a></td>
						<td><a href=messages.php?message='.$m_id.' style="color:black" class=all-old>'.$rekord['subject'].'</a></td>
						<td><a href=messages.php?message='.$m_id.' style="color:black" class=all-old>'.$rekord['data'].'</a></td>
					</tr>');
			}
		}
		echo"<tr><td colspan=3><a href=index.php><input type=button value=Powrót></a></td></tr></table>";
}
//widok pojedyńczej wiadomości
elseif(isset($_GET['message'])) {
	$message=mysql_fetch_assoc(mysql_query("select * from messages where login='".$_SESSION['login']."' and message_id='".$_GET['message']."'"));
	if($_SESSION['user_status']=='a') { 
		$dane_sendera=mysql_fetch_assoc(mysql_query("select imie,nazwisko from users where login='".$message['sender']."'"));
		$sender_full=$message['sender']."<br>(".$dane_sendera['nazwisko']." ".$dane_sendera['imie'].")";
	}
	else{ $sender_full=$message['sender']; }
	$message['content']=preg_replace('(\n)', '<br>', $message['content']); 
	echo'<table align=center border=1 cellpadding=5 width=800px>
			<tr><td width=15%>NADAWCA:</td><td class=lewo><b>'.$sender_full.'</b></td></tr>
			<tr><td>TEMAT:</td><td class=lewo><b>'.$message['subject'].'</b></td></tr>
			<tr><td>DATA:</td><td class=lewo>'.$message['data'].'</td></tr>
			<tr><td>TREŚĆ:</td><td class=lewo>'.$message['content'].'</td></tr>
			<tr><td colspan=2>
				<a href="javascript:history.back()"><input type=button value="Powrót"></a>
				<a href=messages.php?new_agin='.$message['message_id'].'><input type=button value="Oznacz jako nieprzeczytaną"></a>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href=messages.php?del='.$message['message_id'].'><input type=button value="Usuń"></a>
			</td></tr></table>';
	mysql_query("update messages set new=0 where login='".$_SESSION['login']."' and message_id='".$message['message_id']."'");
	if($_SESSION['user_status']=='a') {
			echo'<table align=center border=1 cellpadding=5 width=800px><form name=re action=messages.php method=POST>
					<tr><td width=15%>TEMAT:</td><td class=lewo><input type=text size=100 name=subject value="Re: '.$message['subject'].'"></td></tr>
					<tr><td>TREŚĆ:</td><td class=lewo><textarea cols=78 rows=10 name=content></textarea></td></tr>
					<input type=hidden name=receiver value="'.$message['sender'].'">
					<tr><td colspan=2><input type=submit value="Odpowiedz"></td></tr>
				</form></table>';
	}
}
//nowa wiadomość od usera/moderatora
elseif($_GET['new']=='from-usr') {
	$tekst='Witamy w sekcji kontaktu z administracją!<br>
			Tutaj możesz zgłosić błędy, swoją opinię lub sugestie dotyczące aplikacji.<br>
			Prosimy nie używać tego narzędzia w sprawach innych niż związane z tą aplikacją.<br>
			Wiadomości NIE SĄ anonimowe.<br><br>
			Jako odbiorcę wybierz nauczyciela/administratora, który prowadziz Tobą zajęcia.';
	echo"<div align=center width=400><font size=4>$tekst</font></div><br><br><br>";
	echo"<table align=center width=800px border=1 cellpadding=5><form name=new-from-usr action=messages.php method=POST>
		<tr><td>ODBIORCA:</td><td>
		<select name=receiver>
			<option value=0>Wybierz odbiorcę...</option>";
			$admini_q=mysql_query("Select login,imie,nazwisko from users where uprawnienia='a'");
			while($rekord=mysql_fetch_assoc($admini_q)) {
				echo"<option value='".$rekord['login']."'>".$rekord['imie']." ".$rekord['nazwisko']."</option>";
			}
	echo'</select></td></tr>';
	echo"<tr><td>TEMAT:</td><td><input type=text size=100% name=subject></td></tr>
		<tr><td>TREŚĆ:</td><td><textarea name=content cols=78 rows=10></textarea></td></tr>
		<tr><td colspan=2>
			<a href=index.php><input type=button value='Powrót'></a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type=submit name=send value='Wyślij'></td>
		</tr></form></table>";
}
?>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
