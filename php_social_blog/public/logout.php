<?php
require_once __DIR__ . '/../includes/bootstrap.php';
logoutUser();
redirect('/public/login.php');
