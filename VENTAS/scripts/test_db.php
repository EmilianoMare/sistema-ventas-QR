<?php
function tryConn($host,$db,$user,$pass,$label){
    try{
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "$label: OK\n";
    }catch(PDOException $e){
        echo "$label: ERROR: " . $e->getMessage() . "\n";
    }
}

tryConn('127.0.0.1','ventas_martin','root','', 'root-empty');
tryConn('127.0.0.1','ventas_martin','Administrador','Administrador', 'admin-admin');
