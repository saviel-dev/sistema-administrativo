<?php
require_once "BaseModel.php";

class Usuario extends BaseModel
{


    public function existeDocumento($tipo, $numero)
    {
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE id_tipoDocumento = ? AND numeroDocumento_usuario = ?";
        $params = [(int)$tipo, (string)$numero];
        $types = "is"; // i = int, s = string
        $res = $this->consultaPreparadaUnica($sql, $params, $types);
        return $res['total'] > 0;
    }

    public function existeEmail($email)
    {
        $email = addslashes(trim($email));
        $sql = "SELECT COUNT(*) AS total FROM usuario WHERE email = '$email'";
        $res = $this->consultaUnica($sql);
        return $res['total'] > 0;
    }

    public function obtenerUsuarioPorDocumento($numeroDocumento)
    {
        $sql = "SELECT u.numeroDocumento_usuario, u.nombres_apellidos_usuario, u.password, u.id_usuario, 
                   r.nombre_rol, u.id_sede, s.nombre AS nombre_sede, u.id_rol
            FROM usuario u
            INNER JOIN rol r ON u.id_rol = r.id_rol
            INNER JOIN sede s ON u.id_sede = s.id_sede
            WHERE u.numeroDocumento_usuario = ? AND u.activo = 1
            LIMIT 1";

        $params = [(string) $numeroDocumento];
        $types = "s";

        return $this->consultaPreparadaUnica($sql, $params, $types);
    }

    public function actualizarCampoUsuario($campo, $valor, $idUsuario)
    {
        $permitidos = ['id_rol', 'id_sede', 'activo'];
        if (!in_array($campo, $permitidos)) return false;

        $sql = "UPDATE usuario SET {$campo} = ? WHERE id_usuario = ?";
        return $this->actualizarPreparado($sql, [$valor, $idUsuario], "ii");
    }

    public function obtenerUsuarioPorId($idUsuario)
    {
        $sql = "SELECT 
                u.id_usuario,
                u.fechaNacimiento_usuario,
                u.nombres_apellidos_usuario,
                u.numeroCelular,
                u.numeroDocumento_usuario,
                u.email,
                u.id_tipoDocumento,
                d.abreviatura_docIdentificacion AS tipoDocumento,
                u.id_sede,
                s.nombre AS nombre_sede,
                u.id_rol,
                r.nombre_rol
            FROM usuario u
            LEFT JOIN doc_identificacion d ON u.id_tipoDocumento = d.id_docidentificacion_sunat
            LEFT JOIN sede s ON u.id_sede = s.id_sede
            LEFT JOIN rol r ON u.id_rol = r.id_rol
            WHERE u.id_usuario = ?
            LIMIT 1";

        return $this->consultaPreparadaUnica($sql, [$idUsuario], "i");
    }

    public function modificarUsuario($data)
{
    $sql = "UPDATE usuario SET 
            nombres_apellidos_usuario = ?,
            fechaNacimiento_usuario = ?,
            email = ?, 
            numeroDocumento_usuario = ?, 
            numeroCelular = ?, 
            id_sede = ?, 
            id_rol = ?, 
            id_tipoDocumento = ?
        WHERE id_usuario = ?";

    $params = [
        (string)($data['nombres'] ?? ''),
        (string)($data['fechaNacimiento'] ?? ''), // puede ser '' si no cambias
        (string)($data['email'] ?? ''),
        (string)($data['numero'] ?? ''),
        (string)($data['celular'] ?? ''),
        (int)($data['id_sede'] ?? 0),
        (int)($data['id_rol'] ?? 0),
        (int)($data['id_tipoDocumento'] ?? 0),
        (int)($data['id_usuario'] ?? 0),
    ];

    // 5 strings + 4 ints = 9 letras
    $types = "sssssiiii";

    return $this->actualizarPreparado($sql, $params, $types);
}

    public function obtenerTodosLosUsuarios()
    {
        $sql = "SELECT 
                u.numeroDocumento_usuario,
                u.nombres_apellidos_usuario,
                u.password,
                u.id_usuario,
                r.nombre_rol,
                s.nombre AS nombre_sede,
                u.numeroCelular,
                u.id_rol,
                u.id_sede,
                u.id_tipoDocumento,
                d.abreviatura_docIdentificacion AS tipoDocumento,
                u.activo
            FROM usuario u
            LEFT JOIN rol r ON u.id_rol = r.id_rol
            LEFT JOIN sede s ON u.id_sede = s.id_sede
            LEFT JOIN doc_identificacion d ON u.id_tipoDocumento = d.id_docidentificacion_sunat";

        return $this->consultaPreparadaMultiple($sql, [], '');
    }

    public function registrar($data)
    {
        $query = "INSERT INTO usuario (
                id_tipoDocumento,
                numeroDocumento_usuario,
                fechaNacimiento_usuario,
                nombres_apellidos_usuario,
                numeroCelular,
                email,
                password,
                activo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            (int) $data['tipoDocumento'],
            (string) $data['numeroDocumento'],
            $data['fechaNacimiento'],
            strtoupper($data['nombres']),
            (string) $data['celular'],
            $data['email'],
            $data['password'], // ya viene hasheada
            0
        ];

        $types = "issssssi"; // tipos de datos: int, string...

        return $this->insertarPreparado($query, $params, $types);
    }
}
