<?php
// Obtener el ID del dispositivo desde la URL
$id_dispositivo = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_dispositivo <= 0) {
    header('Location: index.php');
    exit;
}

// Función para manejar las respuestas de la API
function callAPI($url, $method = 'GET', $data = null) {
    $curl = curl_init();
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
    ];
    
    if ($method !== 'GET' && $data) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
        $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
    }
    
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    // Verificar si la respuesta es JSON válido
    $decoded = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'Invalid JSON response: ' . json_last_error_msg(),
            'raw_response' => $response,
            'http_code' => $httpCode
        ];
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => $decoded,
        'http_code' => $httpCode,
        'error' => $httpCode >= 300 ? 'HTTP Error: ' . $httpCode : null
    ];
}

// Obtener información del dispositivo
$dispositivoResponse = callAPI('http://spring.informaticapp.com:1314/dispositivos/' . $id_dispositivo);
if (!$dispositivoResponse['success']) {
    die("Error al obtener información del dispositivo: " . ($dispositivoResponse['error'] ?? 'Código HTTP ' . $dispositivoResponse['http_code']));
}
$dispositivo = $dispositivoResponse['data'];

// Obtener todas las cámaras disponibles
$camarasResponse = callAPI('http://spring.informaticapp.com:1314/camaras');
if (!$camarasResponse['success']) {
    die("Error al obtener la lista de cámaras: " . ($camarasResponse['error'] ?? 'Código HTTP ' . $camarasResponse['http_code']));
}
$camaras = $camarasResponse['data'];

// Obtener cámaras ya asignadas a este dispositivo
$camarasAsignadasResponse = callAPI('http://spring.informaticapp.com:1314/dispositivo-camara/' . $id_dispositivo);
$camaras_asignadas_ids = [];

if ($camarasAsignadasResponse['success']) {
    foreach ($camarasAsignadasResponse['data'] as $ca) {
        if (is_object($ca) && property_exists($ca, 'id_camara')) {
            $camaras_asignadas_ids[] = $ca->id_camara;
        }
    }
}

// Procesar el formulario cuando se envía
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $camaras_seleccionadas = isset($_POST['camaras']) ? $_POST['camaras'] : [];
    $errors = [];
    
    // 1. Eliminar asignaciones que ya no están seleccionadas
    foreach ($camaras_asignadas_ids as $id_camara) {
        if (!in_array($id_camara, $camaras_seleccionadas)) {
            $deleteResponse = callAPI(
                'http://spring.informaticapp.com:1314/dispositivo-camara',
                'DELETE',
                ['id_dispositivo' => $id_dispositivo, 'id_camara' => $id_camara]
            );
            
            if (!$deleteResponse['success'] && $deleteResponse['http_code'] != 404) {
                $errors[] = "Error al eliminar asignación (Cámara ID: $id_camara): " . 
                           ($deleteResponse['error'] ?? 'Código HTTP ' . $deleteResponse['http_code']);
            }
        }
    }
    
    // 2. Agregar nuevas asignaciones que no existían antes
    foreach ($camaras_seleccionadas as $id_camara) {
        if (!in_array($id_camara, $camaras_asignadas_ids)) {
            $postResponse = callAPI(
                'http://spring.informaticapp.com:1314/dispositivo-camara',
                'POST',
                ['id_dispositivo' => $id_dispositivo, 'id_camara' => $id_camara]
            );
            
            // Ignorar error 409 (Conflicto) que indica que la asignación ya existe
            if (!$postResponse['success'] && $postResponse['http_code'] != 409) {
                $errors[] = "Error al crear asignación (Cámara ID: $id_camara): " . 
                           ($postResponse['error'] ?? 'Código HTTP ' . $postResponse['http_code']);
            }
        }
    }
    
    if (empty($errors)) {
        // Redirigir de vuelta a la lista de dispositivos
        header('Location: index.php?success=1');
        exit;
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->
<head>
  <title>Among SOS | Asignar Cámaras</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Mantis is made using Bootstrap 5 design framework. Download the free admin template & use it for your project.">
  <meta name="keywords" content="Mantis, Dashboard UI Kit, Bootstrap 5, Admin Template, Admin Dashboard, CRM, CMS, Bootstrap Admin Template">
  <meta name="author" content="CodedThemes">

  <!-- [Favicon] icon -->
  <link rel="icon" href="../../assets/images/logo.svg" type="image/x-icon"> <!-- [Google Font] Family -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
  <!-- [Tabler Icons] https://tablericons.com -->
  <link rel="stylesheet" href="../../assets/fonts/tabler-icons.min.css">
  <!-- [Feather Icons] https://feathericons.com -->
  <link rel="stylesheet" href="../../assets/fonts/feather.css">
  <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
  <link rel="stylesheet" href="../../assets/fonts/fontawesome.css">
  <!-- [Material Icons] https://fonts.google.com/icons -->
  <link rel="stylesheet" href="../../assets/fonts/material.css">
  <!-- [Template CSS Files] -->
  <link rel="stylesheet" href="../../assets/css/style.css" id="main-style-link">
  <link rel="stylesheet" href="../../assets/css/style-preset.css">
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">

  <?php include '../../includes/sidebar.php'; ?>

  <!-- [ Main Content ] start -->
  <div class="pc-container">
    <div class="pc-content">
      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="m-b-10">Home</h5>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="javascript: void(0)">Mantenimiento</a></li>
                <li class="breadcrumb-item"><a href="index.php">Dispositivos</a></li>
                <li class="breadcrumb-item" aria-current="page">Asignar Cámaras</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->
      <!-- [ Main Content ] start -->
      <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-md-6 col-xl-12">
          <div class="card">
            <div class="card-body">
              <!-- Breadcrumb -->
              <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active"><i class="ti ti-device-computer-camera"></i> Asignar cámaras al dispositivo: <?= htmlspecialchars($dispositivo->nombre) ?></li>
              </ol>

              <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success" role="alert">
                  ¡Las asignaciones se guardaron correctamente!
                </div>
              <?php endif; ?>

              <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                  <strong>Errores encontrados:</strong><br>
                  <?= $error_message ?>
                </div>
              <?php endif; ?>

              <form method="POST" action="">
                <input type="hidden" name="id_dispositivo" value="<?= $id_dispositivo ?>">
                
                <div class="mb-3">
                  <label class="form-label">Dispositivo:</label>
                  <input type="text" class="form-control" value="<?= htmlspecialchars($dispositivo->nombre) ?> - <?= htmlspecialchars($dispositivo->ubicacion) ?>" readonly>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Cámaras disponibles:</label>
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>Seleccionar</th>
                          <th>ID</th>
                          <th>Código</th>
                          <th>Ubicación</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (is_array($camaras) || is_object($camaras)): ?>
                          <?php foreach ($camaras as $camara): ?>
                            <?php if (is_object($camara) && property_exists($camara, 'id_camara')): ?>
                            <tr>
                              <td>
                                <div class="form-check">
                                  <input class="form-check-input" type="checkbox" name="camaras[]" 
                                         value="<?= $camara->id_camara ?>" 
                                         <?= in_array($camara->id_camara, $camaras_asignadas_ids) ? 'checked' : '' ?>>
                                </div>
                              </td>
                              <td><?= $camara->id_camara ?></td>
                              <td><?= htmlspecialchars($camara->codigo ?? '') ?></td>
                              <td><?= htmlspecialchars($camara->ubicacion ?? '') ?></td>
                            </tr>
                            <?php endif; ?>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="4" class="text-center">No se encontraron cámaras disponibles</td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                  <a href="index.php" class="btn btn-secondary me-md-2">Cancelar</a>
                  <button type="submit" class="btn btn-primary">Guardar asignaciones</button>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>
  <!-- [ Main Content ] end -->

  <?php include '../../includes/footer.php'; ?>

  <!-- [Page Specific JS] start -->
  <script src="../../assets/js/plugins/apexcharts.min.js"></script>
  <script src="../../assets/js/pages/dashboard-default.js"></script>
  <!-- [Page Specific JS] end -->
  <!-- Required Js -->
  <script src="../../assets/js/plugins/popper.min.js"></script>
  <script src="../../assets/js/plugins/simplebar.min.js"></script>
  <script src="../../assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../assets/js/fonts/custom-font.js"></script>
  <script src="../../assets/js/pcoded.js"></script>
  <script src="../../assets/js/plugins/feather.min.js"></script>

  <script>layout_change('light');</script>
  <script>change_box_container('false');</script>
  <script>layout_rtl_change('false');</script>
  <script>preset_change("preset-1");</script>
  <script>font_change("Public-Sans");</script>
</body>
<!-- [Body] end -->
</html>