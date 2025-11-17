<?php
class ExternalApiConsumer {
    private $db;
    private $api_base_url;
    private $api_endpoint;

    public function __construct($db) {
        $this->db = $db;
        $this->api_base_url = 'https://mototaxis-huanta.dpweb2024.com/';
        $this->api_endpoint = 'https://mototaxis-huanta.dpweb2024.com/api.php';
    }

    // Método para buscar mototaxi en la API externa
    public function buscarMototaxiExterno($numero_asignado) {
        try {
            // Primero intentar con el endpoint de búsqueda específica
            $params = [
                'numero' => $numero_asignado
            ];

            $url = $this->api_endpoint . '?' . http_build_query($params);
            
            error_log("Buscando en API externa: " . $url);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'MotoTaxis-Cliente-API/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                error_log("Error cURL al consumir API externa: " . $error);
                return false;
            }

            error_log("Respuesta API externa - HTTP Code: " . $http_code);
            error_log("Respuesta API externa: " . $response);

            if ($http_code === 200 && !empty($response)) {
                $data = json_decode($response, true);
                
                if (isset($data['success']) && $data['success'] && isset($data['data'])) {
                    error_log("Mototaxi encontrado en API externa: " . $numero_asignado);
                    return $this->formatearDatosExternos($data['data']);
                } else {
                    error_log("API externa no encontró el mototaxi: " . $numero_asignado);
                    error_log("Respuesta completa: " . print_r($data, true));
                }
            } else {
                error_log("Error HTTP o respuesta vacía de API externa");
            }

            return false;

        } catch (Exception $e) {
            error_log("Error en ExternalApiConsumer::buscarMototaxiExterno: " . $e->getMessage());
            return false;
        }
    }

    // Método para listar mototaxis de la API externa
    public function listarMototaxisExternos($pagina = 1, $porPagina = 10) {
        try {
            // Intentar obtener todos los datos primero
            $url = $this->api_endpoint;
            
            error_log("Listando desde API externa: " . $url);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'MotoTaxis-Cliente-API/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                error_log("Error cURL al listar desde API externa: " . $error);
                return false;
            }

            if ($http_code === 200 && !empty($response)) {
                $data = json_decode($response, true);
                
                if (isset($data['success']) && $data['success'] && isset($data['data'])) {
                    $mototaxis_formateados = [];
                    foreach ($data['data'] as $mototaxi) {
                        $mototaxis_formateados[] = $this->formatearDatosExternos($mototaxi);
                    }
                    
                    // Aplicar paginación manual
                    $total = count($mototaxis_formateados);
                    $offset = ($pagina - 1) * $porPagina;
                    $datos_paginados = array_slice($mototaxis_formateados, $offset, $porPagina);
                    
                    return [
                        'data' => $datos_paginados,
                        'paginacion' => [
                            'pagina_actual' => $pagina,
                            'por_pagina' => $porPagina,
                            'total_registros' => $total,
                            'total_paginas' => ceil($total / $porPagina)
                        ]
                    ];
                }
            }

            return false;

        } catch (Exception $e) {
            error_log("Error en ExternalApiConsumer::listarMototaxisExternos: " . $e->getMessage());
            return false;
        }
    }

    // Método para obtener datos directos de la API externa
    public function obtenerDatosDirectosAPI() {
        try {
            $url = $this->api_endpoint;
            
            error_log("Obteniendo datos directos de: " . $url);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'MotoTaxis-Cliente-API/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                error_log("Error cURL al obtener datos directos: " . $error);
                return false;
            }

            if ($http_code === 200 && !empty($response)) {
                $data = json_decode($response, true);
                error_log("Datos directos obtenidos: " . print_r($data, true));
                return $data;
            }

            return false;

        } catch (Exception $e) {
            error_log("Error en ExternalApiConsumer::obtenerDatosDirectosAPI: " . $e->getMessage());
            return false;
        }
    }

    // Formatear datos de la API externa al formato interno
    private function formatearDatosExternos($datosExternos) {
        // Si los datos ya vienen en el formato esperado, devolverlos directamente
        if (isset($datosExternos['numero_asignado'])) {
            return [
                'id' => $datosExternos['id'] ?? null,
                'numero_asignado' => $datosExternos['numero_asignado'] ?? '',
                'nombre_completo' => $datosExternos['nombre_completo'] ?? '',
                'dni' => $datosExternos['dni'] ?? '',
                'direccion' => $datosExternos['direccion'] ?? '',
                'placa_rodaje' => $datosExternos['placa_rodaje'] ?? '',
                'anio_fabricacion' => $datosExternos['anio_fabricacion'] ?? '',
                'marca' => $datosExternos['marca'] ?? '',
                'numero_motor' => $datosExternos['numero_motor'] ?? '',
                'tipo_motor' => $datosExternos['tipo_motor'] ?? '',
                'serie' => $datosExternos['serie'] ?? '',
                'color' => $datosExternos['color'] ?? '',
                'fecha_registro' => $datosExternos['fecha_registro'] ?? '',
                'id_empresa' => $datosExternos['id_empresa'] ?? null,
                'empresa' => [
                    'razon_social' => $datosExternos['empresa']['razon_social'] ?? ($datosExternos['razon_social'] ?? ''),
                    'ruc' => $datosExternos['empresa']['ruc'] ?? ($datosExternos['ruc'] ?? ''),
                    'representante_legal' => $datosExternos['empresa']['representante_legal'] ?? ($datosExternos['representante_legal'] ?? '')
                ],
                'estado_registro' => $datosExternos['estado_registro'] ?? 'ACTIVO',
                'fecha_actualizacion' => date('Y-m-d H:i:s'),
                'fuente' => 'API_EXTERNA'
            ];
        }
        
        // Si los datos vienen en formato diferente, adaptarlos
        return [
            'id' => $datosExternos['id'] ?? null,
            'numero_asignado' => $datosExternos['numero'] ?? $datosExternos['numero_asignado'] ?? '',
            'nombre_completo' => $datosExternos['nombre'] ?? $datosExternos['nombre_completo'] ?? '',
            'dni' => $datosExternos['dni'] ?? '',
            'direccion' => $datosExternos['direccion'] ?? $datosExternos['dirreccion'] ?? '',
            'placa_rodaje' => $datosExternos['placa'] ?? $datosExternos['placa_rodaje'] ?? '',
            'anio_fabricacion' => $datosExternos['anio'] ?? $datosExternos['anio_fabricacion'] ?? '',
            'marca' => $datosExternos['marca'] ?? '',
            'numero_motor' => $datosExternos['motor'] ?? $datosExternos['numero_motor'] ?? '',
            'tipo_motor' => $datosExternos['tipo_motor'] ?? '',
            'serie' => $datosExternos['serie'] ?? '',
            'color' => $datosExternos['color'] ?? '',
            'fecha_registro' => $datosExternos['fecha'] ?? $datosExternos['fecha_registro'] ?? '',
            'id_empresa' => $datosExternos['id_empresa'] ?? null,
            'empresa' => [
                'razon_social' => $datosExternos['empresa'] ?? $datosExternos['razon_social'] ?? '',
                'ruc' => $datosExternos['ruc_empresa'] ?? $datosExternos['ruc'] ?? '',
                'representante_legal' => $datosExternos['representante'] ?? $datosExternos['representante_legal'] ?? ''
            ],
            'estado_registro' => $datosExternos['estado'] ?? $datosExternos['estado_registro'] ?? 'ACTIVO',
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
            'fuente' => 'API_EXTERNA'
        ];
    }

    // Verificar si la API externa está disponible
    public function verificarDisponibilidadAPI() {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->api_endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            error_log("Verificación API externa - HTTP Code: " . $http_code);
            return $http_code === 200;
        } catch (Exception $e) {
            error_log("Error verificando disponibilidad API: " . $e->getMessage());
            return false;
        }
    }

    // Probar conexión directa y obtener datos de muestra
    public function probarConexionAPI() {
        try {
            $datos = $this->obtenerDatosDirectosAPI();
            if ($datos) {
                return [
                    'conexion_exitosa' => true,
                    'total_registros' => isset($datos['data']) ? count($datos['data']) : 0,
                    'datos_muestra' => isset($datos['data'][0]) ? $datos['data'][0] : null,
                    'respuesta_completa' => $datos
                ];
            }
            return ['conexion_exitosa' => false];
        } catch (Exception $e) {
            error_log("Error probando conexión API: " . $e->getMessage());
            return ['conexion_exitosa' => false, 'error' => $e->getMessage()];
        }
    }
}
?>