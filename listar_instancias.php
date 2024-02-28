<?php
include 'db/config.php';

try {
    $sql = 'SELECT * FROM config_api';
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        foreach ($results as $result) {
            $instancia_ID = $result['id'];
            $url_api = $result['url_api'];
            $api_key = $result['api_key'];
        }
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

if (isset($_GET['encerrar'])) {
    $instancia = $_GET['encerrar'];

    // Parametros do End-Point para encerrar a sessão da instancia
    $parametros = "/session/terminate/";

    // Montando url de ação
    $url = $url_api . $parametros . $instancia;

    $headers = [
        'accept: application/json',
        'x-api-key: ' . $api_key
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "<script>javascript:alert('Sessão da Instancia finalizada com sucesso!');javascript:window.location='listar_instancias.php';</script>";
}


if (isset($_GET['apagar'])) {
    $instancia = $_GET['apagar'];
    try {
        $stmt = $conn->prepare('DELETE FROM instancias WHERE nome = :nome');
        $stmt->bindParam(':nome', $instancia);
        $stmt->execute();

        // Parametros do End-Point para encerrar a sessão da instancia
        $parametros = "/session/terminate/";

        // Montando url de ação
        $url = $url_api . $parametros . $instancia;

        $headers = [
            'accept: application/json',
            'x-api-key: ' . $api_key
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($stmt->rowCount() != '1') {
            echo "<script>javascript:alert('Ops, aconteceu algum erro, tente novamente!');javascript:window.location='listar_instancias.php';</script>";
        } else {
            echo "<script>javascript:alert('Instancia removida com sucesso!');javascript:window.location='listar_instancias.php';</script>";
        }
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Manager - LF Promotora</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/fav.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/29.0.0/classic/ckeditor.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="assets/js/config.js"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->

            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="index.php" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <img src="assets/img/icone1.png" height="40px" width="40px" margin-left="10px">
                        </span>
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">Verifica WPP</span>
                    </a>

                    <a href="javascript:void(0);"
                        class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                <ul class="menu-inner py-1">
                    <!-- Dashboard -->
                    <li class="menu-item">
                        <a href="index.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="Analytics">Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="instancia.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-message-alt-add"></i>
                            <div data-i18n="Analytics">Criar Instância</div>
                        </a>
                    </li>
                    <li class="menu-item active open">
                        <a href="listar_instancias.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-list-ul"></i>
                            <div data-i18n="Analytics">Listar Instâncias</div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="verificador.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bxl-whatsapp"></i>
                            <div data-i18n="Analytics">Verificador WhatsApp</div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class='menu-icon tf-icons bx bx-cog'></i>
                            <div data-i18n="Configurações">Configurações</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item">
                                <a href="config_api.php" class="menu-link">
                                    <div data-i18n="WhatsApp Api">WhatsApp Api</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="card">
                            <h5 class="card-header">Listando Instancias</h5>
                            <div class="table">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome da Instância</th>
                                            <th>Cliente</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php

                                        try {
                                            $sql = 'SELECT * FROM instancias';
                                            $stmt = $conn->prepare($sql);
                                            $stmt->execute();
                                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            foreach ($results as $result) {

                                                // Parametros do End-Point
                                                $parametros = "/session/status/";
                                                $getInfo = "/client/getClassInfo/";

                                                // Montando url de ação status
                                                $url = $url_api . $parametros . $result['nome'];
                                                // Montando URL de ação informações
                                                $url_info = $url_api . $getInfo . $result['nome'];

                                                $headers = [
                                                    'accept: application/json',
                                                    'x-api-key: ' . $api_key
                                                ];

                                                $ch = curl_init($url);
                                                $ch2 = curl_init($url_info);
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                                curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);

                                                $response = curl_exec($ch);
                                                $response_info = curl_exec($ch2);
                                                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                                $httpCode_info = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                                                curl_close($ch);
                                                curl_close($ch2);
                                                $responseArray = json_decode($response, true);
                                                $responseArray_info = json_decode($response_info, true);

                                                if (isset($responseArray_info['sessionInfo']) && isset($responseArray_info['sessionInfo']['me'])) {
                                                    $user = $responseArray_info['sessionInfo']['me']['user'];
                                                    $name = $responseArray_info['sessionInfo']['pushname'];
                                                } else {
                                                    $user = "Disconnected";
                                                    $name = "Disconnected";
                                                }

                                                if ($responseArray === null) {
                                                    echo '<hr><div style="padding-left:20px; color:red;"><b>API OFFLINE, INICIE A API, PARA EXIBIR A LISTA DE INSTANCIAS</b></div><hr>';
                                                    exit;
                                                }

                                                if ($responseArray['state'] === 'CONNECTED') {
                                                    $status_session = '<span class="badge rounded-pill bg-success">Instancia Conectada</span>';
                                                } else {
                                                    $status_session = '<span class="badge rounded-pill bg-danger">Instancia Desconectada</span>';
                                                }

                                                echo '<tr>';
                                                echo '<td><i class="fab fa-angular fa-lg text-danger me-3"></i> <strong>' . $result['nome'] . '</strong></td>';
                                                echo '<td>' . $name . ' / ' . $user . '</td>';
                                                echo '<td>' . $status_session . '</td>';
                                                echo '<td>';
                                                echo '<div class="dropdown">';
                                                echo '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">';
                                                echo '<i class="bx bx-dots-vertical-rounded"></i>';
                                                echo '</button>';
                                                echo '<div class="dropdown-menu">';
                                                echo '<a class="dropdown-item" href="exibir_qrcode.php?instancia=' . $result['nome'] . '"><i class="bx bx-qr-scan"></i> Gerar QRCODE</a>';
                                                echo '<a class="dropdown-item" href="listar_instancias.php?encerrar=' . $result['nome'] . '"><i class="bx bx-power-off"></i> Encerrar Sessão</a>';
                                                echo '<a class="dropdown-item" href="listar_instancias.php?apagar=' . $result['nome'] . '"><i class="bx bx-trash me-1"></i> Deletar</a>';
                                                echo '</div>';
                                                echo '</div>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        } catch (PDOException $e) {
                                            echo 'Error: ' . $e->getMessage();
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- / Content -->
                <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="assets/js/pages-account-settings-account.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>