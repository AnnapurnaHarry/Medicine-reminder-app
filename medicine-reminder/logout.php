<?php
require_once __DIR__ . '/config/db.php';
session_start();
session_unset();
session_destroy();
header('Location: /medicine-reminder/register.php');
exit;
