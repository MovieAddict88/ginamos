<?php
// test_view_reseller.php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set a non-existent reseller ID
$_GET['id'] = 99999;

// Simulate an admin session
session_start();
$_SESSION['loggedin'] = true;
$_SESSION['role'] = 'admin';

// Include the file that is causing the error
include 'view_reseller.php';
?>
