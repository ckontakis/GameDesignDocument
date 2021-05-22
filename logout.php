<?php

session_start();

unset($_SESSION['logged_in']);
unset($_SESSION['id']);

header('location:index.php');