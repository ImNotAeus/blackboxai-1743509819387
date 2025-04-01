<?php
require_once 'functions.inc.php';

session_start();

// Destroy all session data
$_SESSION = array();
session_destroy();

// Redirect to login page
redirect('../pages/login.php');
?>