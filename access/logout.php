<?php
session_start();
session_unset();  
session_destroy();  

header("Location: ../access/login.php");  
exit;