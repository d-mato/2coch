<?php
$xml = <<<EOF
<packet>
	<thread res_from="-500" thread="1394233910" version="20090904" />
</packet>
EOF;

$ch = curl_init("http://msg.nicovideo.jp/3/api/");
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
#curl_setopt($ch,CURLOPT_);
#curl_setopt($ch,CURLOPT_);

$result = curl_exec($ch);
$xml = simplexml_load_string($result);

$comment_data = array();
$vpos_data = array();

foreach($xml->chat as $chat){
	$comment_data[] = [intval($chat['vpos']),strval($chat)];
	$vpos_data[] = intval($chat['vpos']);
}
array_multisort($vpos_data,$comment_data);

foreach($comment_data as $i=>$c){
	echo $c[0]."\t".$c[1]."\n";
	if($i==count($comment_data)-1)break;
	$wait = ($comment_data[$i+1][0]-$c[0])/100.0;
	sleep($wait);
}
