<?php
/*
ニコニコ動画のコメントを2chっぽく表示するだけの無意味なサイト
*/

$rss_file_list = [
  'all',
  'g_ent2',
  'g_life2',
  'g_politics',
  'g_tech',
  'g_culture2',
  'g_other'
];
$xml_list = [];
foreach($rss_file_list as $rss_file){
  $xml = simplexml_load_file(dirname(__FILE__).'/rss/'.$rss_file.'.xml');
  so_filter($xml);
  $xml_list[] = $xml;
}

/* 公式動画はコメント取得方法が少し違うのでso****のrssを除去しておく。そのためのフィルタ関数。*/
function so_filter(&$xml){
  for($i=0;$i<count($xml->channel->item);$i++){
    if(preg_match("/so\d+/",$xml->channel->item[$i]->link)){
      unset($xml->channel->item[$i]);
      /* foreach内でunsetすると添字がズレるのでデクリメント */
      $i--;
    }
  }
}
?>
<!doctype html>
<html lang="ja">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta charset="utf-8">
<link rel="stylesheet" href="css/index.css">
<script src="./js/jquery-3.0.0.min.js"></script>

<title>2ch風ニコ動コメントビューワ 2coch</title>
</head>
<body>
<div id="wrapper">

  <div class="container">
    <h1>2ch風ニコ動コメントビューワ nicovideo 2chlike comment viewer</h1>
    <p>ニコ動のコメントを２ちゃんねるっぽく表示するだけの無意味なサイト</p>
    <p>フォームに動画URLを入力するとコメントをスレッドっぽく表示できる。</p>

    <?php include '_form.html'; ?>

  </div>

<?php foreach($xml_list as $xml):?>
  <div class="container" id="thread">
    <h3><?=$xml->channel->title?></h3>
  <?php foreach($xml->channel->item as $i=>$item):?>
    <a href="read.php?v=<?=preg_replace('/^.*\//','',$item->link)?>"><?=$item->title?></a>
  <?php endforeach;?>
  </div>
<?php endforeach;?>
</div>

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
