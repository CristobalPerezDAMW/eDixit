<?php
session_start();
session_destroy();
$params = session_get_cookie_params();
setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
$_SESSION = array();
header('Location: ./');
?>