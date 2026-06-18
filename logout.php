<?php
require_once 'includes/db.php';
logout();
header('Location: login.php?loggedout=1');
exit;
