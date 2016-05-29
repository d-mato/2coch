<?php
/*
 * google以外のbotによるアクセスを規制する
 */

$deny_list = ['bingbot'];
  
foreach ($deny_list as $deny) {
  if (strpos($_SERVER['HTTP_USER_AGENT'], $deny) !== false)
    die();
}
