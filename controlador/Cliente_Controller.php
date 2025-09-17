<?php
require_once __DIR__ . '/../modelo/Cliente_Model.php';

class ClienteController {

    private $cliente;

    public function __construct() {
        $this->cliente = new Cliente();
    }

    public function listar() {
        $data = $this->cliente->listar();
        echo json_encode(["success" => true, "data" => $data]);
        exit;
    }

    public function buscar() {
        $id = $_GET['id_cliente'] ?? 0;
        $data = $this->cliente->buscarPorId($id);
        echo json_encode(["success" => true, "data" => $data]);
    }

public function buscarPorDocumento() {
    $tipo = $_GET['id_doc_identificacion'] ?? 0;
    $nro = $_GET['nro_documento'] ?? '';
    $data = $this->cliente->buscarPorDocumento($tipo, $nro);

    if ($data) {
        echo json_encode(["success" => true, "data" => $data]);
    } else {
        echo json_encode(["success" => false, "message" => "Cliente no encontrado"]);
    }
}
    public function registrar() {
        $id = $this->cliente->registrar($_POST);
        echo json_encode(["success" => $id > 0, "id_cliente" => $id]);
    }

    public function modificar() {
        $id = $_POST['id_cliente'];
        unset($_POST['id_cliente']);
        $res = $this->cliente->modificar($id, $_POST);
        echo json_encode(["success" => $res > 0]);
    }

    public function cambiarEstado() {
        $id = $_POST['id_cliente'];
        $estado = $_POST['estadoCliente'];
        $res = $this->cliente->cambiarEstado($id, $estado);
        echo json_encode(["success" => $res > 0]);
    }

    public function consultarSunatRUC() {
    $ruc = $_GET['ruc'] ?? '';
    if (strlen($ruc) !== 11) {
        echo json_encode(["success" => false, "message" => "RUC inválido"]);
        return;
    }

    $token = 'apis-token-5172.lcqs8m8Qu9iS-EMU1tDiSO2TR14qkoBy';
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.apis.net.pe/v2/sunat/ruc?numero=' . $ruc,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Referer: http://apis.net.pe/api-ruc',
            'Authorization: Bearer ' . $token
        ),
    ));

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        echo json_encode(["success" => false, "message" => "Error en consulta CURL", "error" => $error]);
        return;
    }

    $empresa = json_decode($response, true);

    if (isset($empresa['numeroDocumento'])) {
        echo json_encode(["success" => true, "data" => $empresa]);
    } else {
        echo json_encode(["success" => false, "message" => "No se encontró información del RUC"]);
    }
}



public function consultarReniecDNI() {
    $dni = $_GET['dni'] ?? '';
    if (strlen($dni) !== 8) {
        echo json_encode(["success" => false, "message" => "DNI inválido"]);
        return;
    }

    $token = 'apis-token-5172.lcqs8m8Qu9iS-EMU1tDiSO2TR14qkoBy';

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.apis.net.pe/v2/reniec/dni?numero=' . $dni,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 2,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Referer: https://apis.net.pe/consulta-dni-api',
            'Authorization: Bearer ' . $token
        ),
    ));

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        echo json_encode(["success" => false, "message" => "Error en consulta CURL", "error" => $error]);
        return;
    }

    $persona = json_decode($response, true);

    if (isset($persona['numeroDocumento'])) {
        echo json_encode(["success" => true, "data" => $persona]);
    } else {
        echo json_encode(["success" => false, "message" => "No se encontró información del DNI"]);
    }
}

public function obtenerUltimoId() {
    return $this->cliente->obtenerSiguienteId();
}

}