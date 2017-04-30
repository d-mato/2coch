<?php
function check_forbidden($v) {
  $list = file(dirname(__FILE__).'/forbidden_list', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  return !in_array($v, $list);
}
