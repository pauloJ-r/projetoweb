<?php 

session_start();

setcookie('token');

header("Location: index.php");