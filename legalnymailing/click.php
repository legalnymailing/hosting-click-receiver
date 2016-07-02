<?php
// Skrypt odbierajacy klikniecia w linki w mailingach i przekierowujacy je do serwera AkoMail
// (C) Tomasz Klim, kwiecien 2013, listopad 2014


// na tych instancjach mozliwe jest unsubscribe za pomoca linka (platne dodatkowo per NIP)
$instances_with_automatic_unsubscribe_links = array(
	"akomail-01.tomaszklim.pl",
	"akomail-02.tomaszklim.pl",
	"akomail-03.tomaszklim.pl",
	"akomail-04.tomaszklim.pl",
);


// TODO: dorobienie obslugi parametru "lang" (pl/en) - wtedy trzeba zaczac uwazac na
//       standard kodowania znakow, w jakim zapisuje sie ten plik (wlasnie dlatego
//       na razie nie ma tu w ogole polskich znakow)

// ten komunikat docelowo powinien byc mozliwie jak najladniejszy i ma sugerowac,
// ze uzytkownik wlasnie ostatecznie zakonczyl wspolprace
$unsub_success = "You have been successfully unsubscribed. We're sorry you're leaving us... Please note that in the near future you may receive a few additional emails, that are already queued at our servers.";

// ten komunikat tez powinien byc mozliwie jak najladniejszy, trzeba by tez w nim docelowo
// polozyc nacisk na to, ze najprawdopodobniej uzytkownik jest juz wypisany, a dopiero
// mniejsza czcionka sugestia, ze jesli nadal otrzymuje maile, to ma odpowiedziec mailem
// z odpowiednim tematem.
$unsub_error = "Unknown error has happened, or you are already unsubscribed. If not, please just reply with subject UNSUBSCRIBE to unsubscribe.";

// ten komunikat nie musi byc ladny, bo nikomu nie powinien sie pokazywac - no chyba, ze
// osoba zakladajaca szablon bedzie probowala byc sprytna i dodac link bez placenia.
$unsub_disabled = "Sorry, automatic unsubscribe has not been enabled for this campaign. Please reply with subject UNSUBSCRIBE to unsubscribe.";

// ten komunikat powinien byc dosc enigmatyczny, bo uzytkownik wie tyle o tym, gdzie sie
// zapisal, ile dowiedzial sie z maila i komunikat nie powinien mu ujawniac niczego wiecej
// (jesli nastapi przekierowanie, komunikat nie jest wyswietlany)
$accept_success = "Success.";


function execute_http($url)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 20);
	$data = curl_exec($curl);
	curl_close($curl);
	return strpos($data, "OKOKOK") === false ? "failed" : "succeed";
}

function log_entry($file, $url)
{
	$entry = "$url\n";
	$entry .= "Local-Time: " . date("Y-m-d H:i:s") . "\n";
	$entry .= "Request-Time: " . @$_SERVER["REQUEST_TIME"] . "\n";
	$entry .= "Referer: " . @$_SERVER["HTTP_REFERER"] . "\n";
	$entry .= "Remote-IP: " . @$_SERVER["REMOTE_ADDR"] . " (" . @$_SERVER["REMOTE_HOST"] . ")\n";
	$entry .= "User-Agent: " . @$_SERVER["HTTP_USER_AGENT"] . "\n";
	$entry .= "Accept: " . @$_SERVER["HTTP_ACCEPT"] . "\n";
	$entry .= "Accept-Encoding: " . @$_SERVER["HTTP_ACCEPT_ENCODING"] . "\n";
	$entry .= "Accept-Language: " . @$_SERVER["HTTP_ACCEPT_LANGUAGE"] . "\n";
	$entry .= "Cookie: " . @$_SERVER["HTTP_COOKIE"] . "\n";
	$entry .= "\n";
	file_put_contents($file, $entry, FILE_APPEND);
}


$action = @$_GET["action"];
$instance = @$_GET["instance"];
$inquiry = @$_GET["inquiry"];
$mail_desc = @$_GET["mail"];
$mailing_id = @$_GET["mid"];
$autore_id = @$_GET["id"];
$autore_type = @$_GET["type"];
$redir_url = @$_GET["redirect"];
$redir_auth = @$_GET["auth"];


if (empty($action) || ($action != "accept" && $action != "unsubscribe"))
	die("invalid action");

if (empty($instance) || !preg_match("#^([0-9a-z-]+)\.(tomaszklim.pl|legalnymailing.pl)$#", $instance, $ret))
	die("invalid instance");

if ($action == "unsubscribe" && !in_array($instance, $instances_with_automatic_unsubscribe_links, true))
	die($unsub_disabled);

$instance_name = $ret[1];
$domain = $ret[2];
if ($domain == "tomaszklim.pl")
	$instance_name .= ".fromhomepl";

$baseurl = "http://$instance_name.$domain";


// tutaj ustalilismy, do jakiego vhosta bedziemy przekazywac requesta i zaczynamy
// logike zwiazana z rodzajem tego requesta


if ($action == "accept" && !empty($inquiry) && !empty($mailing_id) && is_numeric($mailing_id) && preg_match("#^[0-9]+/[0-9]+/[0-9a-zA-Z]+$#", $inquiry))
{
	$url = "$baseurl/inquiry/accept/$inquiry?mid=$mailing_id";

	$status = execute_http($url);
	log_entry("akomail_accept_inquiry_$status.log", $url);

	// olewamy ewentualne bledy przy zapisywaniu sie i jesli sie da, to przekierowujemy
	if (!empty($redir_url) && !empty($redir_auth)) {
		$verify_auth = substr(sha1("4DAZNzAjqoS6LJXexiXTyF6hrFElJJJA:$redir_url"), 0, 6);
		if ($verify_auth == $redir_auth) {
			header("Location: http://$redir_url");
			die();
		}
	}

	echo $accept_success;
}
else if ($action == "unsubscribe" && !empty($inquiry) && !empty($mailing_id) && is_numeric($mailing_id) && preg_match("#^[0-9]+/[0-9]+/[0-9a-zA-Z]+$#", $inquiry))
{
	$url = "$baseurl/inquiry/signoff/$inquiry?mid=$mailing_id&ip=".$_SERVER["REMOTE_ADDR"];

	$status = execute_http($url);
	log_entry("akomail_unsubscribe_inquiry_$status.log", $url);

	echo $status == "succeed" ? $unsub_success : $unsub_error;
}
else if ($action == "unsubscribe" && !empty($mail_desc) && preg_match("#^[0-9]+/[0-9a-zA-Z]+$#", $mail_desc))
{
	$url = "$baseurl/newsletter/unsubscribe/$mail_desc?ip=".$_SERVER["REMOTE_ADDR"];

	if (!empty($mailing_id) && is_numeric($mailing_id))
		$url .= "&mid=$mailing_id";
	else if (!empty($autore_type) && !empty($autore_id) && is_numeric($autore_id))
		$url .= "&id=$autore_id&type=$autore_type";

	$status = execute_http($url);
	log_entry("akomail_unsubscribe_mail_$status.log", $url);

	echo $status == "succeed" ? $unsub_success : $unsub_error;
}
else
{
	file_put_contents("akomail_invalid_url.log", date("Y-m-d H:i:s")."\n".print_r($_GET, true).print_r($_SERVER, true)."\n\n", FILE_APPEND);
	echo "invalid email data";
}
