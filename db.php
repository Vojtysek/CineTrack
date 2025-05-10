<?php
  $db = new PDO('mysql:host=127.0.0.1;dbname=domv01;charset=utf8', 'domv01', 'Eix4eec4zee9xeen4m');
  //$db = new PDO("mysql:host=db;dbname=domv01;charset=utf8","domv01","Eix4eec4zee9xeen4m");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  