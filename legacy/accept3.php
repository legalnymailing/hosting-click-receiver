<?php
// Skrypt przekierowujący akceptacje otrzymania mailingu - po to, aby każdy
// mailing mógł mieć link prowadzący bezpośrednio do docelowej domeny, a nie
// do akomail.tomaszklim.pl
//
// (C) Tomasz Klim, kwiecień 2013

$inquiry = @$_GET["inquiry"];
$mailing_id = @$_GET["mid"];

if (!empty($inquiry) && !empty($mailing_id) && is_numeric($mailing_id) && preg_match("#^[0-9]+/[0-9]+/[0-9a-zA-Z]+$#", $inquiry))
{
	$url = "http://akomail-03.fromhomepl.tomaszklim.pl/inquiry/accept/$inquiry?mid=$mailing_id";

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	$data = curl_exec($curl);
	curl_close($curl);

	$log = (strpos($data, "OKOKOK") === false ? "failed" : "succeed");
	file_put_contents("akomail_inquiry_$log.log", "$url\n", FILE_APPEND);
}
/*
else if (!empty($inquiry) || !empty($mailing_id))
{
	file_put_contents("akomail_invalid_url.log", print_r($_GET, true), FILE_APPEND);
}
*/

header("Location: /");
die();
