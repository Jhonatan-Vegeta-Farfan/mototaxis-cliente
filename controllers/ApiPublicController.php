<?php
// Verificar si los modelos existen antes de incluirlos
$model_files = [
    __DIR__ . '/../models/Mototaxi.php',
    __DIR__ . '/../models/TokenApi.php',
    __DIR__ . '/../models/ClientApi.php',
    __DIR__ . '/../models/CountRequest.php',
    __DIR__ . '/../models/ExternalApiConsumer.php'
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
    private $externalApiConsumer;
    private $db;

    public function __construct($db = null) {
        $this->db = $db;
        
        // Inicializar modelos solo si la conexión existe
        if ($db) {
            try {
                $this->mototaxiModel = class_exists('Mototaxi') ? new Mototaxi($db) : null;
                $this->tokenApiModel = class_exists('TokenApi') ? new TokenApi($db) : null;
                $this->clientApiModel = class_exists('ClientApi') ? new ClientApi($db) : null;
                $this->countRequestModel = class_exists('CountRequest') ? new CountRequest($db) : null;
                $this->externalApiConsumer = class_exists('ExternalApiConsumer') ? new ExternalApiConsumer($db) : null;
            } catch (Exception $e) {
                error_log("Error inicializando modelos: " . $e->getMessage());
            }
        }
    }

    // VISTA PÚBLICA DE DOCUMENTACIÓN
    public function index() {
        $view_file = __DIR__ . '/../views/api_public/index.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
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
            $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
            $porPagina = isset($_GET['por_pagina']) ? max(1, intval($_GET['por_pagina'])) : 10;
            
            $mototaxisPaginados = [];
            $totalMototaxis = 0;
            $fuente = 'BD_LOCAL';
            
            // PRIMERO: Obtener datos de la API EXTERNA
            if ($this->externalApiConsumer) {
                $resultadoExterno = $this->externalApiConsumer->listarMototaxisExternos($pagina, $porPagina);
                
                if ($resultadoExterno && isset($resultadoExterno['data'])) {
                    $mototaxisPaginados = $resultadoExterno['data'];
                    $totalMototaxis = $resultadoExterno['paginacion']['total_registros'] ?? count($mototaxisPaginados);
                    $fuente = 'API_EXTERNA';
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Lista de mototaxis obtenida exitosamente desde API externa',
                        'data' => $mototaxisPaginados,
                        'paginacion' => [
                            'pagina_actual' => (int)$pagina,
                            'por_pagina' => (int)$porPagina,
                            'total_registros' => $totalMototaxis,
                            'total_paginas' => ceil($totalMototaxis / $porPagina),
                            'fuente' => $fuente
                        ]
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }
            
            // SEGUNDO: Base de datos local
            if ($this->mototaxiModel && $this->db) {
                try {
                    $stmt = $this->mototaxiModel->read();
                    if ($stmt) {
                        $totalMototaxis = $stmt->rowCount();
                        
                        $offset = ($pagina - 1) * $porPagina;
                        $contador = 0;
                        
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            if ($contador >= $offset && $contador < ($offset + $porPagina)) {
                                $mototaxisPaginados[] = $this->formatearDatosMototaxi($row);
                            }
                            $contador++;
                            if ($contador >= ($offset + $porPagina)) break;
                        }
                        
                        $fuente = 'BD_LOCAL';
                    }
                } catch (Exception $e) {
                    error_log("Error obteniendo datos de BD: " . $e->getMessage());
                }
            }
            
            // TERCERO: Datos de prueba
            if (empty($mototaxisPaginados)) {
                $datosPrueba = $this->getDatosPruebaEstaticos();
                $totalMototaxis = count($datosPrueba);
                $offset = ($pagina - 1) * $porPagina;
                $mototaxisPaginados = array_slice($datosPrueba, $offset, $porPagina);
                $fuente = 'DATOS_PRUEBA';
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Lista de mototaxis obtenida exitosamente desde ' . $fuente,
                'data' => $mototaxisPaginados,
                'paginacion' => [
                    'pagina_actual' => (int)$pagina,
                    'por_pagina' => (int)$porPagina,
                    'total_registros' => $totalMototaxis,
                    'total_paginas' => ceil($totalMototaxis / $porPagina),
                    'fuente' => $fuente
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
            $fuente = 'BD_LOCAL';
            
            // PRIMERO: API EXTERNA
            if ($this->externalApiConsumer) {
                $mototaxiExterno = $this->externalApiConsumer->buscarMototaxiExterno($numero);
                
                if ($mototaxiExterno) {
                    $mototaxi = $mototaxiExterno;
                    $fuente = 'API_EXTERNA';
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Mototaxi encontrado exitosamente en API externa',
                        'data' => $mototaxi,
                        'metadata' => [
                            'fecha_consulta' => date('Y-m-d H:i:s'),
                            'numero_buscado' => $numero,
                            'total_resultados' => 1,
                            'fuente' => $fuente
                        ]
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }
            
            // SEGUNDO: Base de datos local
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
                    if ($mototaxi) {
                        $fuente = 'BD_LOCAL';
                    }
                } catch (Exception $e) {
                    error_log("Error en búsqueda BD: " . $e->getMessage());
                }
            }
            
            // TERCERO: Datos de prueba
            if (!$mototaxi) {
                $datosPrueba = $this->getDatosPruebaEstaticos();
                foreach ($datosPrueba as $mt) {
                    if ($mt['numero_asignado'] === $numero) {
                        $mototaxi = $mt;
                        $fuente = 'DATOS_PRUEBA';
                        break;
                    }
                }
            }
            
            if (!$mototaxi) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Mototaxi no encontrado con el número: ' . $numero,
                    'sugerencia' => 'Verifique el número e intente nuevamente. Números de ejemplo: MT-001, MT-002, MT-003'
                ]);
                return;
            }
            
            // Formatear datos para respuesta
            $mototaxiFormateado = $this->formatearDatosMototaxi($mototaxi);
            
            echo json_encode([
                'success' => true,
                'message' => 'Mototaxi encontrado exitosamente en ' . $fuente,
                'data' => $mototaxiFormateado,
                'metadata' => [
                    'fecha_consulta' => date('Y-m-d H:i:s'),
                    'numero_buscado' => $numero,
                    'total_resultados' => 1,
                    'fuente' => $fuente
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

    // VALIDAR TOKEN
    public function validarTokenEndpoint() {
        $this->configurarHeadersJSON();
        
        try {
            $headers = getallheaders();
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
            $token = str_replace('Bearer ', '', $token);
            
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
            
            if ($this->tokenApiModel && $this->db) {
                $tokenData = $this->tokenApiModel->getByToken($token);
            }
            
            if (!$tokenData) {
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
            
            if (isset($tokenData['estado']) && !$tokenData['estado']) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => '❌ Token inactivo - Contacte al administrador'
                ]);
                return;
            }
            
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
        if (ob_get_length()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
    }

    private function validarTokenRequest() {
        try {
            $headers = getallheaders();
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
            $token = str_replace('Bearer ', '', $token);
            
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
            
            if ($this->tokenApiModel && $this->db) {
                $tokenData = $this->tokenApiModel->getByToken($token);
            }
            
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
            
            if (isset($tokenData['estado']) && !$tokenData['estado']) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => '❌ Token inactivo - Contacte al administrador'
                ]);
                return false;
            }
            
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
            error_log("Error registrando request: " . $e->getMessage());
        }
    }

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
            'estado_registro' => $mototaxi['estado_registro'] ?? 'ACTIVO',
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
            'fuente' => $mototaxi['fuente'] ?? 'BD_LOCAL'
        ];
    }

    private function getDatosPruebaEstaticos() {
        return [
            [
                'id' => 1,
                'numero_asignado' => 'MT-001',
                'nombre_completo' => 'Juan Pérez García',
                'dni' => '12345678',
                'direccion' => 'Av. Principal 123, Huanta',
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
                'fecha_actualizacion' => date('Y-m-d H:i:s'),
                'fuente' => 'DATOS_PRUEBA'
            ]
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
                    <li><code>/api.php?action=listar&pagina=1&token=TOKEN</code></li>
                </ul>
            </div>
        </body>
        </html>';
    }

    // Métodos adicionales para completar la clase
    public function verificarApiExterna() {
        $this->configurarHeadersJSON();
        echo json_encode([
            'success' => true,
            'data' => [
                'api_externa_disponible' => false,
                'api_externa_url' => 'https://mototaxis-huanta.dpweb2024.com/',
                'fecha_verificacion' => date('Y-m-d H:i:s'),
                'detalles' => ['conexion_exitosa' => false]
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    public function obtenerDatosApiExterna() {
        $this->configurarHeadersJSON();
        echo json_encode([
            'success' => true,
            'message' => 'Endpoint no implementado',
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>