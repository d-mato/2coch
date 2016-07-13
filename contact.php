<?php
header('Content-Type: application/json');
if (!isset($_POST['msg']) || trim($_POST['msg']) === '' || !preg_match("/[(ぁ-ん)(ァ-ヴ)]/u", $_POST['msg'])) {
  http_response_code(400);
  exit(json_encode(['success' => false]));
}

$msg = str_replace(["\r\n", "\n", "\r"], "<br>", $_POST['msg']);
$fp = fopen(dirname(__FILE__).'/secret/contacts.csv', 'a');
$data = [time(),$msg, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $_SERVER["HTTP_REFERER"]];
fputcsv($fp, $data);
fclose($fp);

exit(json_encode(['success' => true]));
