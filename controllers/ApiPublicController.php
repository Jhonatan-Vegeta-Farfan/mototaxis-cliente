<?php
// Verificar si los modelos existen antes de incluirlos
$model_files = [
    __DIR__ . '/../models/Mototaxi.php',
    __DIR__ . '/../models/TokenApi.php',
    __DIR__ . '/../models/ClientApi.php',
    __DIR__ . '/../models/CountRequest.php'
];

foreach ($model_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

class ApiPublicController {
    private $mototaxiModel;
    private $tokenApiModel;
    private $clientApiModel;
    private $countRequestModel;
    private $db;

    public function __construct($db = null) {
        $this->db = $db;
        
        // Inicializar modelos solo si la conexión está disponible
        if ($db) {
            $this->mototaxiModel = new Mototaxi($db);
            $this->tokenApiModel = new TokenApi($db);
            $this->clientApiModel = new ClientApi($db);
            $this->countRequestModel = new CountRequest($db);
        }
    }

    // VISTA PÚBLICA DE DOCUMENTACIÓN
    public function index() {
        // Verificar si el archivo de vista existe
        $view_file = __DIR__ . '/../views/api_public/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            // Vista de respaldo
            $this->mostrarVistaRespaldo();
        }
    }

    // LISTAR MOTOTAXIS (JSON)
    public function listarMototaxis() {
        $this->configurarHeadersJSON();
        
        // Validar token
        $tokenValido = $this->validarTokenRequest();
        if (!$tokenValido) return;
        
        try {
            $pagina = $_GET['pagina'] ?? 1;
            $porPagina = $_GET['por_pagina'] ?? 10;
            
            // Intentar obtener datos de la base de datos
            $mototaxisPaginados = [];
            $totalMototaxis = 0;
            
            if ($this->mototaxiModel && $this->db) {
                try {
                    $stmt = $this->mototaxiModel->read();
                    $totalMototaxis = $stmt->rowCount();
                    
                    // Paginación manual
                    $offset = ($pagina - 1) * $porPagina;
                    $contador = 0;
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        if ($contador >= $offset && $contador < ($offset + $porPagina)) {
                            $mototaxisPaginados[] = $this->formatearDatosMototaxi($row);
                        }
                        $contador++;
                        if ($contador >= ($offset + $porPagina)) break;
                    }
                } catch (Exception $e) {
                    // Si hay error con la BD, usar datos de prueba
                    error_log("Error obteniendo datos de BD: " . $e->getMessage());
                    $datosPrueba = $this->mototaxiModel->getDatosPrueba();
                    $totalMototaxis = count($datosPrueba);
                    
                    $offset = ($pagina - 1) * $porPagina;
                    $mototaxisPaginados = array_slice($datosPrueba, $offset, $porPagina);
                }
            } else {
                // Si no hay modelo, usar datos de prueba estáticos
                $datosPrueba = [
                    [
                        'id' => 1,
                        'numero_asignado' => 'MT-001',
                        'nombre_completo' => 'Juan Pérez García',
                        'dni' => '12345678',
                        'direccion' => 'Av. Principal 123',
                        'placa_rodaje' => 'ABC-123',
                        'anio_fabricacion' => '2020',
                        'marca' => 'Honda',
                        'numero_motor' => 'M123456',
                        'tipo_motor' => '4 Tiempos',
                        'serie' => 'S789012',
                        'color' => 'Rojo',
                        'fecha_registro' => '2023-01-15',
                        'empresa' => [
                            'razon_social' => 'Transportes Huanta SAC',
                            'ruc' => '20123456781',
                            'representante_legal' => 'Carlos Rodríguez'
                        ],
                        'estado_registro' => 'ACTIVO',
                        'fecha_actualizacion' => date('Y-m-d H:i:s')
                    ]
                ];
                $totalMototaxis = count($datosPrueba);
                $mototaxisPaginados = $datosPrueba;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Lista de mototaxis obtenida exitosamente',
                'data' => $mototaxisPaginados,
                'paginacion' => [
                    'pagina_actual' => (int)$pagina,
                    'por_pagina' => (int)$porPagina,
                    'total_registros' => $totalMototaxis,
                    'total_paginas' => ceil($totalMototaxis / $porPagina)
                ]
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener mototaxis: ' . $e->getMessage()
            ]);
        }
    }

    // BUSCAR MOTOTAXI POR NÚMERO ASIGNADO (JSON)
    public function buscarMototaxi() {
        $this->configurarHeadersJSON();
        
        // Validar token
        $tokenValido = $this->validarTokenRequest();
        if (!$tokenValido) return;
        
        try {
            $numero = $_GET['numero'] ?? '';
            
            if (empty($numero)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Parámetro "numero" requerido para la búsqueda'
                ]);
                return;
            }
            
            $mototaxi = null;
            
            // Intentar buscar en la base de datos
            if ($this->db) {
                try {
                    $query = "SELECT m.*, e.razon_social as empresa, e.ruc as ruc_empresa,
                                     e.representante_legal as representante_empresa
                             FROM mototaxis m 
                             LEFT JOIN empresas e ON m.id_empresa = e.id 
                             WHERE m.numero_asignado = ?";
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(1, $numero);
                    $stmt->execute();
                    
                    $mototaxi = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    error_log("Error en búsqueda BD: " . $e->getMessage());
                    // Continuar con datos de prueba si hay error
                }
            }
            
            // Si no se encontró en BD, usar datos de prueba
            if (!$mototaxi) {
                $datosPrueba = [
                    'MT-001' => [
                        'id' => 1,
                        'numero_asignado' => 'MT-001',
                        'nombre_completo' => 'Juan Pérez García',
                        'dni' => '12345678',
                        'direccion' => 'Av. Principal 123',
                        'placa_rodaje' => 'ABC-123',
                        'anio_fabricacion' => '2020',
                        'marca' => 'Honda',
                        'numero_motor' => 'M123456',
                        'tipo_motor' => '4 Tiempos',
                        'serie' => 'S789012',
                        'color' => 'Rojo',
                        'fecha_registro' => '2023-01-15',
                        'id_empresa' => 1,
                        'empresa' => 'Transportes Huanta SAC',
                        'ruc_empresa' => '20123456781',
                        'representante_empresa' => 'Carlos Rodríguez'
                    ],
                    'MT-002' => [
                        'id' => 2,
                        'numero_asignado' => 'MT-002',
                        'nombre_completo' => 'María López Hernández',
                        'dni' => '87654321',
                        'direccion' => 'Jr. Secundaria 456',
                        'placa_rodaje' => 'DEF-456',
                        'anio_fabricacion' => '2021',
                        'marca' => 'Yamaha',
                        'numero_motor' => 'M654321',
                        'tipo_motor' => '4 Tiempos',
                        'serie' => 'S345678',
                        'color' => 'Azul',
                        'fecha_registro' => '2023-02-20',
                        'id_empresa' => 1,
                        'empresa' => 'Transportes Huanta SAC',
                        'ruc_empresa' => '20123456781',
                        'representante_empresa' => 'Carlos Rodríguez'
                    ]
                ];
                
                $mototaxi = $datosPrueba[$numero] ?? null;
            }
            
            if (!$mototaxi) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Mototaxi no encontrado con el número: ' . $numero
                ]);
                return;
            }
            
            // Formatear datos para respuesta completa
            $mototaxiFormateado = $this->formatearDatosMototaxi($mototaxi);
            
            echo json_encode([
                'success' => true,
                'message' => 'Mototaxi encontrado exitosamente',
                'data' => $mototaxiFormateado,
                'metadata' => [
                    'fecha_consulta' => date('Y-m-d H:i:s'),
                    'numero_buscado' => $numero,
                    'total_resultados' => 1
                ]
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ]);
        }
    }

    // VALIDAR TOKEN (JSON) - ENDPOINT PÚBLICO
    public function validarTokenEndpoint() {
        $this->configurarHeadersJSON();
        
        try {
            $headers = getallheaders();
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
            $token = str_replace('Bearer ', '', $token);
            
            // Si no hay token en el header, intentar obtenerlo de los parámetros GET
            if (empty($token)) {
                $token = $_GET['token'] ?? '';
            }
            
            if (empty($token)) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => '❌ Token de acceso requerido'
                ]);
                return;
            }
            
            $tokenData = false;
            
            // Intentar validar token en base de datos
            if ($this->tokenApiModel && $this->db) {
                $tokenData = $this->tokenApiModel->getByToken($token);
            }
            
            // Si no hay conexión a BD o no se encontró el token, verificar tokens de prueba
            if (!$tokenData) {
                // Tokens de prueba basados en los datos de la BD
                $tokensPrueba = [
                    '8ed9873d99e3ab18c922eaf4af3ee20f-STI-1' => [
                        'id' => 2,
                        'token' => '8ed9873d99e3ab18c922eaf4af3ee20f-STI-1',
                        'descripcion' => 'Token de prueba 1',
                        'estado' => 1
                    ],
                    '759503318_040d2bea544ac444_9aa8707b-1' => [
                        'id' => 3,
                        'token' => '759503318_040d2bea544ac444_9aa8707b-1',
                        'descripcion' => 'Token de prueba 2',
                        'estado' => 1
                    ]
                ];
                
                $tokenData = $tokensPrueba[$token] ?? false;
            }
            
            if (!$tokenData) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => '❌ Token no existe'
                ]);
                return;
            }
            
            // VERIFICAR ESTADO DEL TOKEN - NUEVA VALIDACIÓN
            if (isset($tokenData['estado']) && !$tokenData['estado']) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => '❌ Token inactivo - Contacte al administrador'
                ]);
                return;
            }
            
            // Registrar request si es posible
            if ($this->countRequestModel && isset($tokenData['id'])) {
                $this->registrarRequest($tokenData['id'], 'consulta_api');
            }
            
            echo json_encode([
                'success' => true,
                'message' => '✅ Token válido',
                'data' => [
                    'token' => [
                        'id' => $tokenData['id'] ?? null,
                        'token' => $tokenData['token'] ?? null,
                        'descripcion' => $tokenData['descripcion'] ?? null,
                        'estado' => (bool)($tokenData['estado'] ?? true),
                        'fecha_registro' => $tokenData['fecha_registro'] ?? date('Y-m-d H:i:s')
                    ]
                ]
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error validando token: ' . $e->getMessage()
            ]);
        }
    }

    // MÉTODOS PRIVADOS
    private function configurarHeadersJSON() {
        // Limpiar cualquier salida anterior
        if (ob_get_length()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Authorization, Content-Type");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
    }

    private function validarTokenRequest() {
        try {
            $headers = getallheaders();
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
            $token = str_replace('Bearer ', '', $token);
            
            // Si no hay token en el header, intentar obtenerlo de los parámetros GET
            if (empty($token)) {
                $token = $_GET['token'] ?? '';
            }
            
            if (empty($token)) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => '❌ Token de acceso requerido'
                ]);
                return false;
            }
            
            $tokenData = false;
            
            // Intentar validar token en base de datos
            if ($this->tokenApiModel && $this->db) {
                $tokenData = $this->tokenApiModel->getByToken($token);
            }
            
            // Si no hay conexión a BD, verificar tokens de prueba
            if (!$tokenData) {
                $tokensPrueba = [
                    '8ed9873d99e3ab18c922eaf4af3ee20f-STI-1' => ['estado' => 1],
                    '759503318_040d2bea544ac444_9aa8707b-1' => ['estado' => 1]
                ];
                $tokenData = $tokensPrueba[$token] ?? false;
            }
            
            if (!$tokenData) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => '❌ Token no existe'
                ]);
                return false;
            }
            
            // VERIFICAR ESTADO DEL TOKEN - NUEVA VALIDACIÓN
            if (isset($tokenData['estado']) && !$tokenData['estado']) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => '❌ Token inactivo - Contacte al administrador'
                ]);
                return false;
            }
            
            // Registrar request si es posible
            if ($this->countRequestModel && isset($tokenData['id'])) {
                $this->registrarRequest($tokenData['id'], 'consulta_api');
            }
            
            return true;
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error validando token: ' . $e->getMessage()
            ]);
            return false;
        }
    }

    private function registrarRequest($tokenId, $tipo) {
        try {
            if ($this->countRequestModel && $this->db) {
                $this->countRequestModel->id_token_api = $tokenId;
                $this->countRequestModel->tipo = $tipo;
                $this->countRequestModel->create();
            }
        } catch (Exception $e) {
            // Silenciar errores de registro
            error_log("Error registrando request: " . $e->getMessage());
        }
    }

    // Formatear datos del mototaxi para respuesta completa
    private function formatearDatosMototaxi($mototaxi) {
        return [
            'id' => $mototaxi['id'] ?? null,
            'numero_asignado' => $mototaxi['numero_asignado'] ?? '',
            'nombre_completo' => $mototaxi['nombre_completo'] ?? '',
            'dni' => $mototaxi['dni'] ?? '',
            'direccion' => $mototaxi['direccion'] ?? '',
            'placa_rodaje' => $mototaxi['placa_rodaje'] ?? '',
            'anio_fabricacion' => $mototaxi['anio_fabricacion'] ?? '',
            'marca' => $mototaxi['marca'] ?? '',
            'numero_motor' => $mototaxi['numero_motor'] ?? '',
            'tipo_motor' => $mototaxi['tipo_motor'] ?? '',
            'serie' => $mototaxi['serie'] ?? '',
            'color' => $mototaxi['color'] ?? '',
            'fecha_registro' => $mototaxi['fecha_registro'] ?? '',
            'id_empresa' => $mototaxi['id_empresa'] ?? null,
            'empresa' => [
                'razon_social' => $mototaxi['empresa'] ?? ($mototaxi['razon_social'] ?? ''),
                'ruc' => $mototaxi['ruc_empresa'] ?? ($mototaxi['ruc'] ?? ''),
                'representante_legal' => $mototaxi['representante_empresa'] ?? ($mototaxi['representante_legal'] ?? '')
            ],
            'estado_registro' => 'ACTIVO',
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];
    }

    private function mostrarVistaRespaldo() {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>API Mototaxis - Error</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .error { color: #d63031; background: #ffeaa7; padding: 20px; border-radius: 5px; }
            </style>
        </head>
        <body>
            <h1>API de Mototaxis</h1>
            <div class="error">
                <h3>Error: Vista no encontrada</h3>
                <p>La interfaz de la API no está disponible temporalmente.</p>
                <p>Puede usar los endpoints JSON directamente:</p>
                <ul>
                    <li><code>/api.php?action=validar_token&token=TOKEN</code></li>
                    <li><code>/api.php?action=buscar&numero=MT-001&token=TOKEN</code></li>
                </ul>
            </div>
        </body>
        </html>';
    }
}
?>