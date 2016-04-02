<?php
if(!isset($_GET['v']))header("Location: ./");
if(empty($_GET['v']))header("Location: ./");

$ERROR = false;
$MSG = "";
if(preg_match("/sm\d+/",$_GET['v'],$match)){
	$v = $match[0];
	require_once 'nicovideo.php';

	$nv = new Nicovideo();
	$comment_data = $nv->get_comment($v);
	if(!$comment_data){
		$MSG = "動画が見つかりませんでした。。。";
    $ERROR_TITLE = "動画が見つかりませんでした。。。";
		$ERROR = true;
	}
	$info = simplexml_load_string(file_get_contents("http://ext.nicovideo.jp/api/getthumbinfo/{$v}"));

}else{
	$ERROR = true;
  $ERROR_TITLE = "URLが変です。。。";
	$MSG = "URLが変です。。。<br>あと公式動画とかは対応してないです。。。";
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
<script src="js/jquery-2.1.3.min.js"></script>
<script src="js/script.js"></script>
<?php if($ERROR):?>
<meta name="robots" content="noindex">
<title><?=$ERROR_TITLE?></title>
<?php else:?>
<title><?=$info->thumb->title?>のコメント</title>
<?php endif;?>
</head>
<body>

<?php if($ERROR):?>
<span class="msg"><?=$MSG?></span>
<a href="./">トップへ戻る</a>

<?php else:?>


<div id="main">
<h1><?=$info->thumb->title?></h1>

<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-2218764024745094"
     data-ad-slot="7115113567"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>

<?php include '_form.html'; ?>

<dl>
	<dt>0：<font color=green>うｐ主</font>：<?=time2str($info->thumb->first_retrieve)?> ID:<?=$info->thumb->user_id?><dd><?=$info->thumb->description?><br><a href="http://www.nicovideo.jp/watch/<?=$v?>"><img src="<?=$info->thumb->thumbnail_url?>"></a></dd></dt>

	<?php foreach($comment_data as $c):?>
	<dt><?=$c->vpos_time?>：<font color=green>名無し</font>：<?=time2str($c->date)?> ID:<span class="id <?=$c->user_id?>"><?=$c->user_id?></span><dd><?=$c->msg?></dd></dt>
	<?php endforeach;?>
</dl>

<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-2218764024745094"
     data-ad-slot="7115113567"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>

</div>

<?php endif;?>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-64956232-2', 'auto');
  ga('send', 'pageview');

</script>
</body>
</html>
