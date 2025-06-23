<?php
// Obtener el ID del dispositivo desde la URL
$id_dispositivo = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_dispositivo) {
    header("Location: index.php");
    exit();
}

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
$alertas = json_decode($response);

// Filtrar alertas por el ID del dispositivo
$alertas_dispositivo = array_filter($alertas, function($alerta) use ($id_dispositivo) {
    return $alerta->id_dispositivo == $id_dispositivo;
});
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Among SOS | Alertas del Dispositivo</title>
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
  <link rel="stylesheet" href="../../assets/fonts/tabler-icons.min.css" >
  <!-- [Feather Icons] https://feathericons.com -->
  <link rel="stylesheet" href="../../assets/fonts/feather.css" >
  <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
  <link rel="stylesheet" href="../../assets/fonts/fontawesome.css" >
  <!-- [Material Icons] https://fonts.google.com/icons -->
  <link rel="stylesheet" href="../../assets/fonts/material.css" >
  <!-- [Template CSS Files] -->
  <link rel="stylesheet" href="../../assets/css/style.css" id="main-style-link" >
  <link rel="stylesheet" href="../../assets/css/style-preset.css" >

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
                <li class="breadcrumb-item"><a href="index.php">Alertas</a></li>
                <li class="breadcrumb-item" aria-current="page">Dispositivo <?= $id_dispositivo ?></li>
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
                <li class="breadcrumb-item active">
                  <i class="ti ti-user-exclamation"></i>Alertas del Dispositivo <?= $id_dispositivo ?>
                  <span class="badge bg-primary ms-2"><?= count($alertas_dispositivo) ?> alertas</span>
                </li>
              </ol>
              
              <a href="index.php" class="btn btn-secondary mb-3">
                <i class="ti ti-arrow-left"></i> Volver
              </a>
              
              <table class="table">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">ID Alerta</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Hora</th>
                    <th scope="col">Tipo de alerta</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($alertas_dispositivo) > 0): ?>
                    <?php foreach($alertas_dispositivo as $alerta): ?>
                    <tr>
                      <td><?= $alerta->id_alerta ?></td>
                      <td><?= $alerta->fecha ?></td>
                      <td><?= $alerta->hora ?></td>
                      <td>
                        <span class="badge bg-<?= 
                          $alerta->tipo_alerta == 'Emergencia' ? 'danger' : 
                          ($alerta->tipo_alerta == 'Advertencia' ? 'warning' : 'info') 
                        ?>">
                          <?= $alerta->tipo_alerta ?>
                        </span>
                      </td>
                      <td>
                        <a href="eliminar.php?id=<?= $alerta->id_alerta ?>" class="btn btn-danger btn-sm">
                          <i class="ti ti-trash"></i> Eliminar
                        </a>
                      </td>
                    </tr>
                    <?php endforeach ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="5" class="text-center">No hay alertas para este dispositivo</td>
                    </tr>
                  <?php endif ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
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