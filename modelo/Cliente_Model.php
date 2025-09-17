<?php
require_once "BaseModel.php";

class Cliente extends BaseModel
{

    public function listar()
    {
        $sql = "SELECT 
                c.*, 
                d.abreviatura_docIdentificacion 
            FROM cliente c
            INNER JOIN doc_identificacion d 
                ON c.id_doc_identificacion = d.id_docidentificacion_sunat";
        return $this->consultar($sql);
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM cliente WHERE id_cliente = $id";
        return $this->consultaUnica($sql);
    }

public function buscarPorDocumento($tipoDoc, $numeroDoc)
{
    $tipoDoc = (int) $tipoDoc;
    $numeroDoc = trim($numeroDoc);

    $sql = "SELECT * FROM cliente 
            WHERE id_doc_identificacion = $tipoDoc 
            AND nro_documento LIKE '$numeroDoc%' 
            LIMIT 1";

    return $this->consultaUnica($sql); // asegúrate de que esta función devuelve un array o null
}

 public function registrar($data)
{
    // Seguridad: Sanitizar y definir valores por defecto
    $id_doc_identificacion = (int) ($data['id_doc_identificacion'] ?? 0);
    $nro_documento = trim($data['nro_documento'] ?? '');
    $razon_social = trim($data['razon_social'] ?? '');
    $nombre_comercial = trim($data['nombre_comercial'] ?? '');
    $representante = trim($data['representante'] ?? '');
    $direccion = trim($data['direccion'] ?? '');
    $codigo_ubigeo = trim($data['codigo_ubigeo'] ?? '');
    $telefono_fijo = trim($data['telefono_fijo'] ?? '');
    $celular_1 = trim($data['celular_1'] ?? '');
    $celular_2 = trim($data['celular_2'] ?? '');
    $email = trim($data['email'] ?? '');
    $ClasificacionCliente = (int) ($data['ClasificacionCliente'] ?? 0);
    $limite_credito = is_numeric($data['limite_credito']) ? $data['limite_credito'] : 0.00;
    $estadoCliente = (int) ($data['estadoCliente'] ?? 1);
    $notas = trim($data['notas'] ?? '');
    $estadoSunat = trim($data['estadoSunat'] ?? 'ACTIVO');
    $condicionSunat = trim($data['condicionSunat'] ?? 'HABIDO');

    // Armado de consulta
    $sql = "INSERT INTO cliente (
        id_doc_identificacion, nro_documento, razon_social, nombre_comercial,
        representante, direccion, codigo_ubigeo, telefono_fijo, celular_1,
        celular_2, email, ClasificacionCliente, limite_credito, estadoCliente,
        notas, estadoSunat, condicionSunat
    ) VALUES (
        $id_doc_identificacion, '$nro_documento', '$razon_social', '$nombre_comercial',
        '$representante', '$direccion', '$codigo_ubigeo', '$telefono_fijo', '$celular_1',
        '$celular_2', '$email', $ClasificacionCliente, $limite_credito, $estadoCliente,
        '$notas', '$estadoSunat', '$condicionSunat'
    )";

    return $this->insertar($sql);
}

    public function modificar($id, $data)
{
    // Escapar comillas para evitar errores de SQL (simple seguridad)
    foreach ($data as $k => $v) {
        $data[$k] = addslashes($v);
    }

    $sql = "UPDATE cliente SET
        nombre_comercial = '{$data['nombre_comercial']}',
        representante = '{$data['representante']}',
        direccion = '{$data['direccion']}',
        codigo_ubigeo = '{$data['codigo_ubigeo']}',
        telefono_fijo = '{$data['telefono_fijo']}',
        celular_1 = '{$data['celular_1']}',
        celular_2 = '{$data['celular_2']}',
        email = '{$data['email']}',
        ClasificacionCliente = {$data['ClasificacionCliente']},
        limite_credito = {$data['limite_credito']},
        notas = '{$data['notas']}',
        estadoSunat = '{$data['estadoSunat']}',
        condicionSunat = '{$data['condicionSunat']}'
        WHERE id_cliente = $id";

    return $this->ejecutar($sql);
}

    public function cambiarEstado($id, $estado)
    {
        $sql = "UPDATE cliente SET estadoCliente = $estado WHERE id_cliente = $id";
        return $this->ejecutar($sql);
    }

    public function obtenerSiguienteId()
{
    $sql = "SELECT MAX(id_cliente) + 1 AS siguiente FROM cliente";
    $result = $this->consultar($sql);
    return $result[0]['siguiente'] ?? 1;
}
}
