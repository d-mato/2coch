<?php
require 'access-control.php';

if(!isset($_GET['v']))header("Location: ./");
if(empty($_GET['v']))header("Location: ./");

$error = [];
if(preg_match("/sm\d+/",$_GET['v'],$match)){
  $v = $match[0];

  require_once 'check_forbidden.php';
  if (!check_forbidden($v)) {
    $error['title'] = '禁止されています';
    $error['message'] = 'このページへのアクセスは禁止されています';

  } else {
    require_once 'nicovideo.php';

    $nv = new Nicovideo();
    $comment_data = $nv->get_comment($v);
    if(!$comment_data){
      $error['title'] = "動画が見つかりませんでした。。。";
      $error['message'] = "動画が見つかりませんでした。。。";
    }

    $info = getVideoInfo($v);

    if ($info['is_deleted']) {
      $error['title'] = "削除済み動画。。。";
      $error['message'] = "動画は削除されました。。。";
    }
  }

}else{
  $error['title'] = "URLが変です。。。";
  $error['message'] = "URLが変です。。。<br>あと公式動画とかは対応してないです。。。";
}

function time2str($time){
  if(preg_match("/^\d+$/",$time)){
    $time = intval($time);
  }else{
    $time = strtotime($time);
  }
  $youbi = ['日','月','火','水','木','金','土'];
  $date_str = date("Y/m/d",$time)."(".$youbi[intval(date("w",$time))].") ".date("H:i:s",$time);
  return $date_str;
}

?>

<!doctype html>
<html lang="ja">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta charset="utf-8">
<link rel="stylesheet" href="css/read.css">
<script src="js/jquery-3.0.0.min.js"></script>

<?php if(count($error)):?>
<meta name="robots" content="noindex">
<title><?=$error['title']?></title>
<?php else:?>
<title><?=$info['title']?> へのコメント</title>
<?php endif;?>
</head>
<body>

<?php if(count($error)):?>
<span class="msg"><?=$error['message']?></span>
<a href="/">トップへ戻る</a>

<?php else:?>


<div id="main">
<h1><?=$info['title']?></h1>

<?php include '_form.html'; ?>

<nav><a href="/">トップへ戻る</a></nav>

<dl>
  <dt>0：<span class="name"><?=$info['user_nickname']?></span><span class="date"><?=time2str($info['first_retrieve'])?></span> ID:<?=$info['user_id']?></dt>
  <dd>
    <p><?=$info['description']?></p>
    <a href="http://www.nicovideo.jp/watch/<?=$v?>" target="_blank"><img src="<?=$info['thumbnail_url']?>">動画閲覧ページへ飛びます</a>
  </dd>

  <?php foreach($comment_data as $i => $c):?>
    <dt><?=$c->vpos_time?>：<span class="name">名無し</span><span class="date"><?=time2str($c->date)?></span> ID:<span class="id <?=$c->user_id?>"><?=$c->user_id?></span></dt>
    <?php if(!preg_match("/アドセンス|ｱﾄﾞｾﾝｽ|クリック|ｸﾘｯｸ/", $c->msg)): ?>
      <dd><?=$c->msg?></dd>
    <?php endif; ?>
    <?php if ($i % 100 == 0): ?><?php include '_ads.html'; ?><?php endif; ?>
  <?php endforeach;?>
</dl>

</div>

<?php endif;?>
<?php include '_analytics.html'; ?>
<?php include '_contact.html'; ?>

<script src="js/script.js"></script>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
(adsbygoogle = window.adsbygoogle || []).push({
  google_ad_client: "ca-pub-2218764024745094",
    enable_page_level_ads: true
});
</script>
</body>
</html>
<?php
$fp = fopen(dirname(__FILE__).'/secret/access.log', 'a');
if (count($error))
  $title = $error['title'];
else
  $title = $info['title'];
$data = [time(), $_SERVER['REQUEST_URI'], $title, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']];
fputcsv($fp, $data);
fclose($fp);
?>
