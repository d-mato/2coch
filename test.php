<?php
require_once 'nicovideo.php';
$nv = new Nicovideo();

$nv->login();
$nv->get_comment("sm23176585");
