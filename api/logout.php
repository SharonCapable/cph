<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::logout();
setFlash('success', 'You have been logged out successfully');
redirect('/public/login.php');
