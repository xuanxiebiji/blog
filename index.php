<?php
//开启session
session_start();
include 'bootstrap/Psr4AutoLoad.php';
include 'bootstrap/Start.php';
include 'bootstrap/alias.php';
$config = include 'config/config.php';

Start::router();















