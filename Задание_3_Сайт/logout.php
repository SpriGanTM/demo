<?php
require_once 'functions.php';
$_SESSION=[]; session_destroy(); session_start(); flash('Вы вышли из панели управления.'); redirect('index.php');
