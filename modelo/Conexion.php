<?php
class Conexion {
    private static $tipo = 1;

    public static function obtenerDatosConexion() {
        if (self::$tipo === 1) {
            return [
                'servidor' => 'localhost',
                'usuario'  => 'root',
                'password' => '',
                'basedatos'=> 'BDrepuestosNazca'
            ];
        } else {
            return [
                'servidor' => 'localhost',
                'usuario'  => 'powermot_pierre',
                'password' => 'Pierre1945',
                'basedatos'=> 'powermot_peru'
            ];
        }
    }

    public static function conectar() {
        $cfg = self::obtenerDatosConexion();
        $cn = @mysqli_connect($cfg['servidor'], $cfg['usuario'], $cfg['password'], $cfg['basedatos']);

        if (!$cn) {
            self::logError(new Exception("Error al conectar a la base de datos: " . mysqli_connect_error()));
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        mysqli_set_charset($cn, "utf8");
        return $cn;
    }

    public static function logError($e) {
        $mensaje = "Fecha: " . date("Y-m-d H:i:s") . "\n" .
                   "Archivo: " . $e->getFile() . "\n" .
                   "Línea: " . $e->getLine() . "\n" .
                   "Mensaje: " . $e->getMessage() . "\n\n";
        error_log($mensaje, 3, __DIR__ . "/../logs/errores.log");
    }
}