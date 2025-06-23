<?php
session_start();

// Configurar cabeceras para respuesta JSON
header('Content-Type: application/json');

// Obtener el último ID de alerta registrado en sesión
$ultimoIdConocido = isset($_SESSION['ultimo_id_alerta']) ? $_SESSION['ultimo_id_alerta'] : 0;

try {
    // Consultar las alertas desde la API
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://spring.informaticapp.com:1314/alertas',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    
    if (curl_errno($curl)) {
        throw new Exception('Error al consultar alertas: ' . curl_error($curl));
    }
    
    curl_close($curl);
    $alertas = json_decode($response);

    // Verificar si la respuesta es válida
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar la respuesta JSON');
    }

    // Filtrar alertas nuevas
    $nuevasAlertas = array();
    if (is_array($alertas) && !empty($alertas)) {
        foreach ($alertas as $alerta) {
            if (isset($alerta->id_alerta) && $alerta->id_alerta > $ultimoIdConocido) {
                $nuevasAlertas[] = $alerta;
            }
        }
        
        // Actualizar el último ID conocido si hay nuevas alertas
        if (!empty($nuevasAlertas)) {
            $ultimaAlerta = end($alertas);
            $_SESSION['ultimo_id_alerta'] = $ultimaAlerta->id_alerta;
        }
    }

    // Devolver respuesta JSON
    echo json_encode(array(
        'success' => true,
        'newAlerts' => $nuevasAlertas,
        'totalAlerts' => is_array($alertas) ? count($alertas) : 0
    ));

} catch (Exception $e) {
    // Manejar errores
    echo json_encode(array(
        'success' => false,
        'error' => $e->getMessage()
    ));
}
?>