<?php
session_start();
$pass_hash = trim(file_get_contents(dirname(__FILE__).'/../secret/password'));

function check_login() {
  if (!isset($_SESSION['login'])) {
    http_response_code(401); exit;
  }
}

function get_contacts() {
  check_login();
  $data = [];
  $fp = fopen(dirname(__FILE__).'/../secret/contacts.csv', 'r');
  while (($row = fgetcsv($fp)) !== false) {
    $data[] = [
      'date' => date('m/d H:i:s', intval($row[0])),
      'comment' => $row[1],
      'ip_addr' => $row[2],
      'referer' => $row[3],
    ];
  }
  fclose($fp);
  header("Content-Type: application/json; charset=utf-8");
  exit(json_encode(array_reverse($data)));
}

function login() {
  global $pass_hash;
  if (password_verify($_POST['pass'], $pass_hash)) {
    $_SESSION['login'] = 1;
    exit;
  } else {
    $fp = fopen(dirname(__FILE__).'/../secret/fail.log', 'a');
    fputcsv($fp, [time(), $_POST['pass'], $_SERVER['REMOTE_ADDR']]);
    fclose($fp);
    http_response_code(403); exit;
  }
}

function logout() {
  unset($_SESSION['login']);
  exit;
}

if (isset($_GET['contacts']))
  get_contacts();

else if (isset($_GET['login']))
  login();

else if (isset($_GET['logout']))
  logout();
