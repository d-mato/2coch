<?php
/* rssをget */
$rss_list = [
	'http://www.nicovideo.jp/ranking/fav/daily/all?rss=2.0&lang=ja-jp',
	'http://www.nicovideo.jp/ranking/fav/daily/g_ent2?rss=2.0&lang=ja-jp',
	'http://www.nicovideo.jp/ranking/fav/daily/g_life2?rss=2.0&lang=ja-jp',
	'http://www.nicovideo.jp/ranking/fav/daily/g_politics?rss=2.0&lang=ja-jp',
	'http://www.nicovideo.jp/ranking/fav/daily/g_tech?rss=2.0&lang=ja-jp',
	'http://www.nicovideo.jp/ranking/fav/daily/g_culture2?rss=2.0&lang=ja-jp',
	'http://www.nicovideo.jp/ranking/fav/daily/g_other?rss=2.0&lang=ja-jp'
];

foreach($rss_list as $rss){
	preg_match("/daily\/(.*)\?/",$rss,$match);
	$file = dirname(__FILE__).'/rss/'.$match[1].'.xml';
	file_put_contents($file,file_get_contents($rss));
	sleep(2);
}
