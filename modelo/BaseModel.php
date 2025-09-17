<?php
require_once "Conexion.php";

class BaseModel {
    protected $cn;

    public function __construct() {
        $this->cn = Conexion::conectar();
    }

    protected function consultar($sql) {
        try {
            $rs = mysqli_query($this->cn, $sql);
            if (!$rs) throw new Exception(mysqli_error($this->cn));

            $resultados = [];
            while ($fila = mysqli_fetch_assoc($rs)) {
                $resultados[] = $fila;
            }
            return $resultados;
        } catch (Exception $e) {
            Conexion::logError($e);
            throw $e;
        }
    }

    protected function consultaUnica($sql) {
        $lista = $this->consultar($sql);
        return $lista[0] ?? null;
    }

    protected function insertar($sql) {
        try {
            if (!mysqli_query($this->cn, $sql)) {
                throw new Exception(mysqli_error($this->cn));
            }
            return mysqli_insert_id($this->cn);
        } catch (Exception $e) {
            Conexion::logError($e);
            throw $e;
        }
    }

    protected function ejecutar($sql) {
        try {
            if (!mysqli_query($this->cn, $sql)) {
                throw new Exception(mysqli_error($this->cn));
            }
            return mysqli_affected_rows($this->cn);
        } catch (Exception $e) {
            Conexion::logError($e);
            throw $e;
        }
    }

    protected function insertarPreparado($query, $params, $types) {
    try {
        $stmt = mysqli_prepare($this->cn, $query);
        if (!$stmt) throw new Exception(mysqli_error($this->cn));

        mysqli_stmt_bind_param($stmt, $types, ...$params);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        $insertId = mysqli_insert_id($this->cn);
        mysqli_stmt_close($stmt);
        return $insertId;
    } catch (Exception $e) {
        Conexion::logError($e);
        throw $e;
    }
}


protected function consultaPreparadaUnica($query, $params, $types) {
    try {
        $stmt = mysqli_prepare($this->cn, $query);
        if (!$stmt) throw new Exception(mysqli_error($this->cn));

        mysqli_stmt_bind_param($stmt, $types, ...$params);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        $resultado = mysqli_stmt_get_result($stmt);
        $fila = mysqli_fetch_assoc($resultado);

        mysqli_stmt_close($stmt);
        return $fila;
    } catch (Exception $e) {
        Conexion::logError($e);
        throw $e;
    }
}

protected function consultaPreparadaMultiple($query, $params = [], $types = '') {
    try {
        $stmt = mysqli_prepare($this->cn, $query);
        if (!$stmt) throw new Exception(mysqli_error($this->cn));

        if (!empty($params) && !empty($types)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        $resultado = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $filas[] = $fila;
        }

        mysqli_stmt_close($stmt);
        return $filas;
    } catch (Exception $e) {
        Conexion::logError($e);
        throw $e;
    }
}

protected function actualizarPreparado($query, $params, $types) {
    try {
        $stmt = mysqli_prepare($this->cn, $query);
        if (!$stmt) throw new Exception(mysqli_error($this->cn));

        mysqli_stmt_bind_param($stmt, $types, ...$params);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    } catch (Exception $e) {
        Conexion::logError($e);
        throw $e;
    }
}


}