<?php
session_start();

// Obtener lista de dispositivos
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://spring.informaticapp.com:1314/dispositivos',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
));
$response = curl_exec($curl);
curl_close($curl);
$dispositivos = json_decode($response);

// Obtener todas las alertas y guardar el último ID en sesión
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
curl_close($curl);
$todasAlertas = json_decode($response);

// Guardar el último ID de alerta en sesión para comparar después
$ultimoIdAlerta = 0;
if (!empty($todasAlertas)) {
    $ultimaAlerta = end($todasAlertas);
    $ultimoIdAlerta = $ultimaAlerta->id_alerta;
    $_SESSION['ultimo_id_alerta'] = $ultimoIdAlerta;
}

// Crear un array para contar alertas por dispositivo
$contadorAlertas = array();
foreach ($todasAlertas as $alerta) {
    $idDispositivo = $alerta->id_dispositivo;
    if (!isset($contadorAlertas[$idDispositivo])) {
        $contadorAlertas[$idDispositivo] = 0;
    }
    $contadorAlertas[$idDispositivo]++;
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->
<head>
  <title>Among SOS | Alertas</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Mantis is made using Bootstrap 5 design framework. Download the free admin template & use it for your project.">
  <meta name="keywords" content="Mantis, Dashboard UI Kit, Bootstrap 5, Admin Template, Admin Dashboard, CRM, CMS, Bootstrap Admin Template">
  <meta name="author" content="CodedThemes">

  <!-- [Favicon] icon -->
  <link rel="icon" href="../../assets/images/logo.svg" type="image/x-icon">
  <!-- [Google Font] Family -->
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
  <!-- Toastr CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
                <li class="breadcrumb-item"><a href="javascript: void(0)">Monitoreo</a></li>
                <li class="breadcrumb-item" aria-current="page">Alertas</li>
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
                <li class="breadcrumb-item active"><i class="ti ti-user-exclamation"></i>Lista de alertas</li>
              </ol>
              
              <table class="table">
                  <thead class="thead-light">
                      <tr>
                          <th scope="col">Dispositivo IoT</th>
                          <th score="col">Casos actuales</th>
                          <th scope="col" colspan="2">Operaciones</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach($dispositivos as $dispositivo) : 
                          $idDispositivo = $dispositivo->id_dispositivo;
                          $totalAlertas = isset($contadorAlertas[$idDispositivo]) ? $contadorAlertas[$idDispositivo] : 0;
                      ?>
                      <tr>
                          <td><?= $idDispositivo ?></td>
                          <td>
                              <span class="badge bg-<?= $totalAlertas > 0 ? 'danger' : 'success' ?>">
                                  <?= $totalAlertas ?>
                              </span>
                          </td>
                          <td>
                              <a href="visualizar.php?id=<?= $idDispositivo ?>" class="btn btn-warning">
                                  <i class="ti ti-eye"></i> Visualizar
                              </a>
                          </td>
                          <td>
                              <a href="eliminar.php?id=<?= $idDispositivo ?>" class="btn btn-danger">
                                  <i class="ti ti-trash"></i> Limpiar
                              </a>
                          </td>
                      </tr>
                      <?php endforeach ?>
                  </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- [ Main Content ] end -->

  <!-- Audio para notificaciones (oculto) -->
  <audio id="notificationSound" preload="auto">
    <source src="../../assets/sounds/notification.mp3" type="audio/mpeg">
  </audio>

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
  <!-- jQuery y Toastr -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

  <script>
  // Configuración de toastr
  toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
  };

  // Función para verificar nuevas alertas
  function checkForNewAlerts() {
    $.ajax({
      url: 'check_alerts.php',
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.newAlerts && response.newAlerts.length > 0) {
          // Reproducir sonido de notificación
          document.getElementById('notificationSound').play();
          
          // Mostrar notificación para cada nueva alerta
          response.newAlerts.forEach(function(alerta) {
            toastr.warning(
              'Nueva alerta en dispositivo: ' + alerta.id_dispositivo + 
              '<br>Tipo: ' + alerta.tipo_alerta +
              '<br>Fecha: ' + alerta.fecha + ' ' + alerta.hora,
              '¡Alerta detectada!',
              {timeOut: 10000}
            );
          });
          
          // Actualizar la página después de 2 segundos
          setTimeout(function() {
            location.reload();
          }, 2000);
        }
      },
      complete: function() {
        // Verificar nuevamente después de 5 segundos
        setTimeout(checkForNewAlerts, 5000);
      }
    });
  }

  // Iniciar la verificación cuando la página esté lista
  $(document).ready(function() {
    checkForNewAlerts();
  });
  </script>

  <script>layout_change('light');</script>
  <script>change_box_container('false');</script>
  <script>layout_rtl_change('false');</script>
  <script>preset_change("preset-1");</script>
  <script>font_change("Public-Sans");</script>
</body>
</html>