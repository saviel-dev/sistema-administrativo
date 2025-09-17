<?php
require_once __DIR__ . '/../modelo/Usuario_Model.php';

class UsuarioController {

    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

       public function validarDocumento() {
    $tipo = $_GET['tipo'] ?? 0;
    $numero = trim($_GET['numero'] ?? '');

    $exists = $this->usuario->existeDocumento($tipo, $numero);

    echo json_encode(['exists' => $exists]);
}

public function cambiarEstado() {
    $idUsuario = $_POST['idUsuario'] ?? 0;
    $nuevoEstado = $_POST['nuevoEstado'] ?? 0;
    $ok = $this->usuario->actualizarCampoUsuario('activo', $nuevoEstado, $idUsuario);
    echo json_encode([
        "success" => $ok,
        "message" => $ok ? "Estado actualizado correctamente" : "Error al actualizar estado"
    ]);
}



public function cambiarSede() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $idUsuario = (int)($_POST['idUsuario'] ?? 0);
    $newSede   = (int)($_POST['newSede'] ?? 0);
    $ok = $this->usuario->actualizarCampoUsuario('id_sede', $newSede, $idUsuario);

    // 👇 si es el mismo usuario logueado, refresca variables de sesión
    if ($ok && !empty($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $idUsuario) {
        $_SESSION['id_sede'] = $newSede;

        // opcional: traer nombre de sede para mantener sincronizado
        // $nom = $this->usuario->obtenerNombreSedePorId($newSede); // implementa o reutiliza
        // $_SESSION['nombre_sede'] = $nom;
    }

    echo json_encode([
        "success" => $ok,
        "message" => $ok ? "Sede actualizada correctamente" : "Error al actualizar sede"
    ]);
}
public function modificar() {
    $idUsuario = $_POST['id_usuario'] ?? 0;
    if (!$idUsuario) {
        echo json_encode(["success" => false, "message" => "ID de usuario inválido"]);
        return;
    }

    // Campos
    $nombres   = trim($_POST['nombres_apellidos_usuario'] ?? '');
    $numero    = trim($_POST['numeroDocumento_usuario'] ?? '');
    $celular   = trim($_POST['numeroCelular'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $fechaNac  = trim($_POST['fechaNacimiento_usuario'] ?? ''); // <--- este es el name del form

    $idSede    = (int) ($_POST['id_sede'] ?? 0);
    $idRol     = (int) ($_POST['id_rol'] ?? 0);
    $idTipoDoc = (int) ($_POST['id_tipoDocumento'] ?? 0);

    if (!$nombres || !$numero || !$idSede || !$idRol || !$idTipoDoc) {
        echo json_encode(["success" => false, "message" => "Faltan campos requeridos"]);
        return;
    }

    $datos = [
        "id_usuario"       => $idUsuario,
        "nombres"          => $nombres,
        "fechaNacimiento"  => $fechaNac,   // <--- ahora sí
        "email"            => $email,      // <--- ahora sí
        "numero"           => $numero,
        "celular"          => $celular,
        "id_sede"          => $idSede,
        "id_rol"           => $idRol,
        "id_tipoDocumento" => $idTipoDoc,
    ];

    $ok = $this->usuario->modificarUsuario($datos);

    echo json_encode([
        "success" => $ok,
        "message" => $ok ? "Usuario modificado correctamente" : "No se realizaron cambios o falló la operación"
    ]);
}

public function obtenerUsuario() {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        echo json_encode([
            "success" => false,
            "message" => "ID de usuario inválido"
        ]);
        return;
    }

    $usuario = $this->usuario->obtenerUsuarioPorId($id);

    if (!$usuario) {
        echo json_encode([
            "success" => false,
            "message" => "Usuario no encontrado"
        ]);
        return;
    }

    echo json_encode([
        "success" => true,
        "data" => $usuario
    ]);
}

public function cambiarRol() {
    $idUsuario = $_POST['idUsuario'] ?? 0;
    $newRol = $_POST['newRol'] ?? 0;
    $ok = $this->usuario->actualizarCampoUsuario('id_rol', $newRol, $idUsuario);
    echo json_encode([
        "success" => $ok,
        "message" => $ok ? "Rol actualizado correctamente" : "Error al actualizar rol"
    ]);
}

public function login() {
    session_start();

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
        return;
    }

    $numeroDocumento = trim($_POST['numeroDocumento'] ?? '');
    $password = base64_decode($_POST['password'] ?? '');

    if (!$numeroDocumento || !$password) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos.']);
        return;
    }

    $usuario = $this->usuario->obtenerUsuarioPorDocumento($numeroDocumento);

    if (!$usuario || !password_verify($password, $usuario['password'])) {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas.']);
        return;
    }

    // Formato capitalizado primer nombre + apellido
    $nombres = explode(" ", trim($usuario['nombres_apellidos_usuario']));
    $nombreCorto = ucfirst(strtolower($nombres[0])) . ' ' . ucfirst(strtolower($nombres[1] ?? ''));

    // Guardar en sesión
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['numero_documento'] = $usuario['numeroDocumento_usuario'];
    $_SESSION['nombres_apellidos_usuario'] = $nombreCorto;
    $_SESSION['nombre_rol'] = $usuario['nombre_rol'];
    $_SESSION['id_sede']       = (int)$usuario['id_sede'];
    $_SESSION['nombre_sede'] = $usuario['nombre_sede'];
    $_SESSION['id_rol']       = (int)$usuario['id_rol'];

    echo json_encode(['success' => true]);
}

public function obtenerUsuarios() {
    header('Content-Type: application/json');

    $usuarios = $this->usuario->obtenerTodosLosUsuarios();

    echo json_encode([
        "success" => true,
        "data" => $usuarios  // 👈 Así lo requiere DataTables
    ]);
}

    public function registrar() {

        session_start(); // Muy importante

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode([
        "success" => false,
        "message" => "Token CSRF inválido. Refresca la página e intenta nuevamente."
    ]);
    return;
}

        // Validar datos básicos
        if (
            empty($_POST['email']) || 
            empty($_POST['password']) || 
            empty($_POST['nombres'])
        ) {
            echo json_encode([
                "success" => false,
                "message" => "Faltan campos obligatorios."
            ]);
            return;
        }

        $datos = $_POST;

        // Validar existencia previa de email
        if ($this->usuario->existeDocumento($datos['tipoDocumento'], $datos['numeroDocumento'])) {
    echo json_encode([
        "success" => false,
        "message" => "El usuario ya se encuentra registrado con ese documento."
    ]);
    return;
}

        // Hash de contraseña
        $passwordPlano = base64_decode($_POST['password']);
        $datos['password'] = password_hash($passwordPlano, PASSWORD_BCRYPT, ['cost' => 12]);
        // $datos['password'] = password_hash($datos['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        // Intentar registrar en base de datos
        $id = $this->usuario->registrar($datos);

        echo json_encode([
            "success" => $id > 0,
            "id_usuario" => $id,
            "message" => $id > 0 ? "Registro exitoso" : "Error al registrar usuario"
        ]);
    }

 
}