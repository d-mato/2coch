<?php
if(!isset($_GET['v']))header("Location: ./");
if(empty($_GET['v']))header("Location: ./");

$error = [];
if(preg_match("/sm\d+/",$_GET['v'],$match)){
  $v = $match[0];
  require_once 'nicovideo.php';

  $nv = new Nicovideo();
  $comment_data = $nv->get_comment($v);
  if(!$comment_data){
    $error['message'] = "動画が見つかりませんでした。。。";
    $error['title'] = "動画が見つかりませんでした。。。";
  }
  $info = simplexml_load_string(file_get_contents("http://ext.nicovideo.jp/api/getthumbinfo/{$v}"));

  if ($info->error) {
    $error['title'] = "削除済み動画。。。";
    $error['message'] = "動画は削除されました。。。";
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
<style>
body {
  background-color:#efefef;
}
h1 {
  color:red;
  font-size:larger;
  margin:-.5em 0 0;
}
#main {
  width:100%;
  max-width:800px;
}
dd {
  margin-bottom:20px;
}
span.id {
  text-decoration:underline;
  cursor:pointer;
}
span.id:hover {
  color:#888;
}

.green {
  color:green;
}
.red{
  color:red;
  font-weight:bold;
}
</style>

<?php if(count($error)):?>
<meta name="robots" content="noindex">
<title><?=$error['title']?></title>
<?php else:?>
<title><?=$info->thumb->title?>のコメント</title>
<?php endif;?>
</head>
<body>

<?php if(count($error)):?>
<span class="msg"><?=$error['message']?></span>
<a href="./">トップへ戻る</a>

<?php else:?>


<div id="main">
<h1><?=$info->thumb->title?></h1>

<?php include '_ads.html'; ?>

<?php include '_form.html'; ?>

<dl>
  <dt>0：<font color=green><?=$info->thumb->user_nickname?></font>：<?=time2str($info->thumb->first_retrieve)?> ID:<?=$info->thumb->user_id?><dd><?=$info->thumb->description?><br><a href="http://www.nicovideo.jp/watch/<?=$v?>"><img src="<?=$info->thumb->thumbnail_url?>"></a></dd></dt>

  <?php foreach($comment_data as $c):?>
  <dt><?=$c->vpos_time?>：<font color=green>名無し</font>：<?=time2str($c->date)?> ID:<span class="id <?=$c->user_id?>"><?=$c->user_id?></span><dd><?=$c->msg?></dd></dt>
  <?php endforeach;?>
</dl>

<?php include '_ads.html'; ?>

</div>

<?php endif;?>
<?php include '_analytics.html'; ?>

<script src="js/jquery-2.1.3.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
