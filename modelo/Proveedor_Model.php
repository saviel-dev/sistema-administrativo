<?php
require_once "BaseModel.php";

class Proveedor extends BaseModel
{
    /* ======= VALIDACIONES ======= */

    public function existeDocumento($idTipoDoc, $numero)
    {
        $sql = "SELECT COUNT(*) AS total 
                  FROM proveedores 
                 WHERE id_doc_identificacion = ? 
                   AND numero_documento = ? 
                 LIMIT 1";
        $res = $this->consultaPreparadaUnica($sql, [(int)$idTipoDoc, (string)$numero], "is");
        return ($res['total'] ?? 0) > 0;
    }

    /* ======= CRUD ======= */

    public function registrar($d)
    {
        $q = "INSERT INTO proveedores (
                id_doc_identificacion,
                numero_documento,
                razon_social,
                nombre_comercial,
                representante,
                direccion,
                id_ubigeo_inei,
                pais,
                telefono_fijo,
                celular,
                email,
                web,
                limite_credito,
                estado_proveedor,
                notas
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $params = [
            (int)($d['id_doc_identificacion']),
            (string)$d['numero_documento'],
            (string)$d['razon_social'],
            (string)($d['nombre_comercial'] ?? ''),
            (string)($d['representante'] ?? ''),
            (string)($d['direccion'] ?? ''),
            (string)($d['id_ubigeo_inei'] ?? ''),
            (string)($d['pais'] ?? ''),
            (string)($d['telefono_fijo'] ?? ''),
            (string)($d['celular'] ?? ''),
            (string)($d['email'] ?? ''),
            (string)($d['web'] ?? ''),
            (float)($d['limite_credito'] ?? 0),
            (string)($d['estado_proveedor'] ?? 'ACTIVO'),
            (string)($d['notas'] ?? '')
        ];

        // 1 int, 11 strings, 1 double, 2 strings = 15
        $types = "isssssssssss" . "d" . "ss"; // => "isssssssssssdss"

        return $this->insertarPreparado($q, $params, $types);
    }

    public function modificar($d)
    {
        $q = "UPDATE proveedores SET
                id_doc_identificacion = ?,
                numero_documento      = ?,
                razon_social          = ?,
                nombre_comercial      = ?,
                representante         = ?,
                direccion             = ?,
                id_ubigeo_inei        = ?,
                pais                  = ?,
                telefono_fijo         = ?,
                celular               = ?,
                email                 = ?,
                web                   = ?,
                limite_credito        = ?,
                estado_proveedor      = ?,
                notas                 = ?
              WHERE id_proveedor = ?";

        $params = [
            (int)($d['id_doc_identificacion']),
            (string)$d['numero_documento'],
            (string)$d['razon_social'],
            (string)($d['nombre_comercial'] ?? ''),
            (string)($d['representante'] ?? ''),
            (string)($d['direccion'] ?? ''),
            (string)($d['id_ubigeo_inei'] ?? ''),
            (string)($d['pais'] ?? ''),
            (string)($d['telefono_fijo'] ?? ''),
            (string)($d['celular'] ?? ''),
            (string)($d['email'] ?? ''),
            (string)($d['web'] ?? ''),
            (float)($d['limite_credito'] ?? 0),
            (string)($d['estado_proveedor'] ?? 'ACTIVO'),
            (string)($d['notas'] ?? ''),
            (int)($d['id_proveedor'])
        ];

        // (previos 15 tipos) + i (id_proveedor)
        $types = "isssssssssssdss" . "i"; // => "isssssssssssdssi"

        return $this->actualizarPreparado($q, $params, $types);
    }

    /* ======= OBTENER / LISTAR ======= */

    public function obtenerPorId($id)
    {
        $q = "SELECT 
                p.id_proveedor,
                p.FechaRegistro_proveedor,
                p.id_doc_identificacion,
                d.abreviatura_docIdentificacion AS tipoDocumento,
                p.numero_documento,
                p.razon_social,
                p.nombre_comercial,
                p.representante,
                p.direccion,
                p.id_ubigeo_inei,
                p.pais,
                p.telefono_fijo,
                p.celular,
                p.email,
                p.web,
                p.limite_credito,
                p.estado_proveedor,
                p.notas
              FROM proveedores p
              LEFT JOIN doc_identificacion d 
                ON p.id_doc_identificacion = d.id_docidentificacion_sunat
             WHERE p.id_proveedor = ?
             LIMIT 1";

        return $this->consultaPreparadaUnica($q, [(int)$id], "i");
    }

    public function listarTodos()
    {
        $q = "SELECT 
                p.id_proveedor,
                p.FechaRegistro_proveedor,
                p.id_doc_identificacion,
                d.abreviatura_docIdentificacion AS tipoDocumento,
                p.numero_documento,
                p.razon_social,
                p.nombre_comercial,
                p.representante,
                p.pais,
                p.telefono_fijo,
                p.celular,
                p.email,
                p.limite_credito,
                p.estado_proveedor
              FROM proveedores p
              LEFT JOIN doc_identificacion d 
                ON p.id_doc_identificacion = d.id_docidentificacion_sunat
              ORDER BY p.id_proveedor DESC";

        return $this->consultaPreparadaMultiple($q);
    }

    /* ======= ACTUALIZACIONES DE UN CAMPO ======= */

    public function actualizarCampoProveedor($campo, $valor, $id)
    {
        // Whitelist de campos modificables de a uno
        $permitidos = [
            'estado_proveedor' => 's',
            'limite_credito'   => 'd',
            'pais'             => 's',
            'telefono_fijo'    => 's',
            'celular'          => 's',
            'email'            => 's',
            'web'              => 's',
        ];
        if (!array_key_exists($campo, $permitidos)) return false;

        $q = "UPDATE proveedores SET {$campo} = ? WHERE id_proveedor = ?";
        $tipo = $permitidos[$campo]; // 's' o 'd'

        if ($tipo === 'd') {
            return $this->actualizarPreparado($q, [(float)$valor, (int)$id], "di");
        }
        return $this->actualizarPreparado($q, [(string)$valor, (int)$id], "si");
    }
}