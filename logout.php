<?php
declare(strict_types=1);

/*
 * logout.php — ends the session and returns to the login page.
 */

session_start();
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
