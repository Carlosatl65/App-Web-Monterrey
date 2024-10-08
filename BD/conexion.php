<?php
    class Conexion{
        public static function Conectar(){
            define('servidor','localhost'); //servidor
            define('nombre_bd','monterre_c_t_monterrey'); //nombre de la base de datos
            define('usuario','root'); //usuario de db
            define('password',"xGiKzc/IUzc392_U"); //contraseña de db
            $opciones = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
            try{
                $conexion = new PDO("mysql:host=".servidor."; dbname=".nombre_bd, usuario, password, $opciones);
                return $conexion;
            }catch(Exception $e){
                die("El error de conexión es: ".$e->getMessage());
            }
        }
    }
?>