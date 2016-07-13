<?php
$fp = fopen(dirname(__FILE__).'/secret/accounts.csv', 'r');
$Accounts = [];
while(($csv = fgetcsv($fp)) !== false) {
  $Accounts[] = ['mail' => trim($csv[0]), 'password' => trim($csv[1])];
}
fclose($fp);

function err($msg) {
  $fp = fopen(dirname(__FILE__).'/secret/error.log', 'a');
  fputcsv($fp, [time(), $msg]);
  fclose($fp);
}

class Nicovideo{
  var $ch,$info;

  function change_account() {
    global $Accounts;
    if (isset($this->account)) {
      unset($Accounts[$this->account['id']]);
    }
    $id = array_rand($Accounts);
    $this->account = $Accounts[$id];
    $this->account['id'] = $id;
  }

  function __construct(){
    $this->change_account();
    $cookie_file = dirname(__FILE__)."/secret/cookie_{$this->account['id']}";
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie_file);
    curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_file);

    //CA証明書の検証をしない
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36");

    // リダイレクト設定
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // 最大何回リダイレクトをたどるか
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    // リダイレクトの際にヘッダのRefererを自動的に追加させる
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    $this->ch = $ch;
  }

  function login(){
    $mail = $this->account['mail'];
    $password = $this->account['password'];

    $ch = $this->ch;
    /* ログイン */
    curl_setopt($ch,CURLOPT_URL,"https://secure.nicovideo.jp/secure/login");
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,"mail_tel={$mail}&password={$password}");

    $res = curl_exec($ch);
    err("login: {mail: `$mail`, password: `$password`}");
    $fp = fopen(dirname(__FILE__).'/secret/login_history.log', 'a');
    fputcsv($fp, [time(), $mail]);
    fclose($fp);
  }

  /* $v:sm12345 */
  function load_ms_info($v){
    $ch = $this->ch;
    curl_setopt($ch,CURLOPT_URL,"http://flapi.nicovideo.jp/api/getflv/{$v}");
    $result = urldecode(curl_exec($ch));

    /* cookieが無効の場合一度ログイン処理を行う */
    if(preg_match("/closed=1/",$result)){
      err('load_ms_info: closed=1, retry');
      $this->change_account();
      $this->login();
      curl_setopt($ch,CURLOPT_URL,"http://flapi.nicovideo.jp/api/getflv/{$v}");
      $result = urldecode(curl_exec($ch));
    }

    if(!preg_match("/thread_id=(\d+).+&ms=(.+?)&/",$result,$match))return false;
    return $this->info = ['thread_id'=>$match[1],'ms'=>$match[2]];
  }

  /* $v:sm12345 */
  /* DBを参照し、キャッシュが存在すればそれを返す
   * 無ければコメント取得処理を行う
   * 投稿から時間が経っていない動画はキャッシュ化しない
   * キャッシュ化から時間がたったものは再取得する
   */
  function get_comment($v){
    if (!$this->load_ms_info($v)) {
      err('load_ms_info: false');
      return false;
    }

    $ch = $this->ch;
    $url = $this->info['ms']."thread?version=20090904&thread={$this->info['thread_id']}&res_from=-1000";
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, false);
    // curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);

    $result = curl_exec($ch);
    // echo "<textarea>".$result."</textarea>";
    if (!preg_match("/^</", $result)) {
      err('comment api: invalid data');
      return false;
    }
    $xml = @simplexml_load_string($result);

    $comment_data = array();
    $vpos_data = array();

    foreach($xml->chat as $chat){
      if(strval($chat)=="")continue;
      /* 「うぽつ」「888...」「www...」が含まれるコメントを除去 */
      /* 「おつ」「乙」が含まれるコメントを除去 */
      if(preg_match("/うぽつ/u",strval($chat)))continue;
      if(preg_match("/^[8８]*$/u",strval($chat)))continue;
      if(preg_match("/^[wｗ]*$/u",strval($chat)))continue;
      if(preg_match("/^[乙|(おつ)|(お疲れ)]/u",strval($chat)))continue;

      $comment_data[] = new Comment(intval($chat['vpos']),intval($chat['date']),strval($chat),strval($chat['user_id']));
      $vpos_data[] = intval($chat['vpos']);
    }
    array_multisort($vpos_data,$comment_data);
    return $comment_data;
  }
}

class Comment{
  var $vpos,$data,$msg,$user_id,$vpos_time;
  function __construct($vpos,$date,$msg,$user_id){
    $this->vpos = $vpos;
    $this->date = $date;
    $this->msg = $msg;
    $this->user_id = $user_id;

    $this->vpos_time = $this->vpos2time($vpos);
  }
  function vpos2time($vpos){
    $vpos = intval($vpos);
    $vpos_time = floor($vpos/100/60).":".sprintf("%02d",($vpos/100)%60);
    return $vpos_time;
  }
}

/*
 * 動画情報を取得する
 * キャッシュ(DB)を参照し、保存済みであればそれを読み出し、
 * キャッシュが無ければアクセスして保存する
 */
function getVideoInfo($v) {
  $pdo = new PDO('sqlite:'.dirname(__FILE__).'/db/2coch.db');
  $pdo->query('create table if not exists video_info (_id integer primary key autoincrement, video_id text unique, title text, thumbnail_url text, first_retrieve text, description text, length text, tags text, user_id int, user_nickname text, user_icon_url text, is_deleted int default 0);');

  $sth = $pdo->prepare("select * from video_info where video_id = :video_id");
  $sth->execute([':video_id' => $v]);
  $info = $sth->fetch();
  if ($info) {
    // echo "<!--cached!-->\n";
    return $info;
  } else {
    // echo "<!--no cache ..-->";
    $xml = simplexml_load_string(file_get_contents("http://ext.nicovideo.jp/api/getthumbinfo/{$v}"));
    $thumb = json_decode(json_encode($xml->thumb), true);

    $info = ['video_id' => $v];
    if ($xml->error) {
      $info['is_deleted'] = 1;
    } else {
      $info['is_deleted'] = 0;
      $info['title'] = $thumb['title'];
      $info['thumbnail_url'] = $thumb['thumbnail_url'];
      $info['first_retrieve'] = $thumb['first_retrieve'];
      $info['description'] = $thumb['description'];
      $info['length'] = $thumb['length'];
      $info['user_id'] = $thumb['user_id'];
      $info['user_nickname'] = $thumb['user_nickname'];
      $info['user_icon_url'] = $thumb['user_icon_url'];
      $info['tags'] = implode(' ', $thumb['tags']['tag']);
    }

    $insert_data = [];
    foreach ($info as $key => $value) {
      $insert_data[":$key"] = $value;
    }

    $sql = 'insert into video_info (video_id, title, thumbnail_url, first_retrieve, description, length, tags, user_id, user_nickname, user_icon_url, is_deleted) values (:video_id, :title, :thumbnail_url, :first_retrieve, :description, :length, :tags, :user_id, :user_nickname, :user_icon_url, :is_deleted)';
    $sth = $pdo->prepare($sql);
    if (!$sth->execute($insert_data)) {
      echo "<!--";
      var_dump($sth->errorInfo());
      echo "-->";
    }
    return $info;
  }
}
