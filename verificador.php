<?php
include 'db/config.php';

// Array para armazenar os resultados da verificação
$resultados_verificacao = array();

try {
    $sql = 'SELECT * FROM config_api';
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        foreach ($results as $result) {
            $url_api = $result['url_api'];
            $api_key = $result['api_key'];
        }
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

try {

    // Consulta SQL para recuperar as instâncias
    $sql = 'SELECT id, nome FROM instancias';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $instancias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

if (isset($_POST['submit'])) {
    // Verifica se o arquivo foi enviado
    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
        // Abre o arquivo em modo de leitura
        $handle = fopen($_FILES['file']['tmp_name'], "r");

        // Verifica se o arquivo foi aberto com sucesso
        if ($handle !== FALSE) {

            // Inicializa a sessão cURL fora do bloco condicional
            $ch = curl_init();

            $instancia_nome = $_POST['instancia'];
            $instancia = '/client/isRegisteredUser/' . $instancia_nome;

            // URL da API
            $url = $url_api . $instancia;

            // Dados do cabeçalho (headers)
            $headers = array(
                'accept: application/json',
                'x-api-key: ' . $api_key,
                'Content-Type: application/json'  
            );

            // Loop para ler cada linha do arquivo CSV
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                // O número de telefone está na primeira coluna do CSV
                $phoneNumber = $data[0];

                // Dados do corpo da requisição (body)
                $data_array = array(
                    'number' => $phoneNumber
                );

                // Converte os dados em JSON
                $data_json = json_encode($data_array);

                // Define as opções da requisição
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                // Executa a requisição e obtém a resposta
                $response = curl_exec($ch);

                // Verifica por erros
                if (curl_errno($ch)) {
                    echo 'Erro na requisição cURL: ' . curl_error($ch);
                }

                // Trata a resposta
                $result = json_decode($response, true);

                // Armazena o resultado da verificação
                $resultado_verificacao = array(
                    'numero' => $phoneNumber,
                    'whatsapp' => ($result['success'] && $result['result']) ? 'Sim' : 'Não'
                );

                // Adiciona o resultado ao array de resultados
                $resultados_verificacao[] = $resultado_verificacao;

                // Add delay para enviar próxima mensagem
                usleep(500);
            }

            // Fecha a sessão cURL
            curl_close($ch);

            // Fecha o arquivo CSV
            fclose($handle);

            // Define o cabeçalho para download do arquivo CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="resultados_verificacao.csv"');

            // Abre o arquivo de saída para escrita
            $output = fopen('php://output', 'w');

            // Escreve os resultados no arquivo CSV
            fputcsv($output, array('Número', 'WhatsApp'));

            foreach ($resultados_verificacao as $resultado) {
                fputcsv($output, $resultado);
            }

            // Fecha o arquivo de saída
            fclose($output);

            // Interrompe o script para evitar a exibição do HTML abaixo
            exit();
        } else {
            echo "Erro ao abrir o arquivo CSV.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Verificar WhatsApp em Massa</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

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
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .containerbox {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .emoji {
            font-size: 30px;
            position: relative;
            cursor: pointer;
            margin-left: 10px;
        }

        .emoji>span {
            padding: 10px;
            border: 1px solid transparent;
            transition: 100ms linear;
        }

        .emoji span:hover {
            background-color: #fff;
            border-radius: 4px;
            border: 1px solid #e7e7e7;
            box-shadow: 0 7px 14px 0 rgb(0 0 0 / 12%);
        }

        #emoji-picker {
            padding: 6px;
            font-size: 20px;
            z-index: 1;
            position: absolute;
            display: none;
            width: 189px;
            border-radius: 4px;
            top: 53px;
            right: 0;
            background: #fff;
            border: 1px solid #e7e7e7;
            box-shadow: 0 7px 14px 0 rgb(0 0 0 / 12%);
        }

        #emoji-picker span {
            cursor: pointer;
            width: 35px;
            height: 35px;
            display: inline-block;
            text-align: center;
            padding-top: 4px;
        }

        #emoji-picker span:hover {
            background-color: #e7e7e7;
            border-radius: 4px;
        }

        .emoji-arrow {
            position: absolute;
            width: 0;
            height: 0;
            top: 0;
            right: 18px;
            box-sizing: border-box;
            border-color: transparent transparent #fff #fff;
            border-style: solid;
            border-width: 4px;
            transform-origin: 0 0 0;
            transform: rotate(135deg);
        }


        .creator {
            position: fixed;
            right: 5px;
            top: 5px;
            font-size: 13px;
            font-family: sans-serif;
            text-decoration: none;
            color: #111;
        }

        .creator:hover {
            color: deeppink;
        }

        .creator i {
            font-size: 12px;
            color: #111;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        
    </style>
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
                            <img src="assets/img/icone1.png" height="45px" width="45px" margin-left="10px">
                        </span>
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">Verifica WPP</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
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
                    <li class="menu-item">
                        <a href="listar_instancias.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-list-ul"></i>
                            <div data-i18n="Analytics">Listar Instâncias</div>
                        </a>
                    </li>

                    <li class="menu-item active open">
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
                    <li class="menu-item">
                        <a href="<?php echo $url_api ?>/api-docs" class="menu-link" target="_blank">
                            <i class="menu-icon tf-icons bx bxl-whatsapp"></i>
                            <div data-i18n="Analytics">Documentação API</div>
                        </a>
                    </li>
                    <?php

                    // Parametros do End-Point
                    $parametros = "/session/status/verificandoStatus";

                    // Montando url de ação
                    $url = $url_api . $parametros;

                    $headers = [
                        'accept: image/png',
                        'x-api-key: ' . $api_key
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($httpCode == 200) {
                        echo '<div style="padding:30%"><span class="badge rounded-pill bg-success">API ON-LINE!</span></div>';
                    } else {
                        echo '<div style="padding:30%"><span class="badge rounded-pill bg-danger">API OFF-LINE!</span></div>';
                    }

                    ?>
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
                        <div class="container">
                            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Importar /</span> Verificar em massa</h4>
                            <form method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="instancia" class="form-label">Instância</label>
                                    <select class="form-select" id="instancia" name="instancia" required>
                                        <option value="">Selecione uma instância</option>
                                        <?php foreach ($instancias as $instancia) { ?>
                                            <option value="<?php echo $instancia['nome']; ?>"><?php echo $instancia['nome']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div><br>
                                <div class="form-group">
                                    <label for="file" class="form-label">Arquivo CSV</label>
                                    <input type="file" class="form-control" id="file" name="file" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="submit" class="btn btn-primary" style="margin-top: 20px;">Verificar</button>
                                </div>
                            </form>
                            <br>
                        </div>
                    </div>
                    <!-- / Content -->
                </div>
                <!-- / Content wrapper -->

                <!-- Footer -->
                <footer class="footer footer-fixed">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center">
                                    <div class="text-center">
                                        <span class="text-muted">2024 &copy; Mateus Tosta</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- Footer -->
            </div>
            <!-- Layout container -->
        </div>
        <!-- / Layout wrapper -->
        <!-- Core scripts -->
        <script src="assets/vendor/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/vendor/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
        <script src="assets/vendor/libs/slim-select/dist/slimselect.min.js"></script>
        <!-- Libs -->
        <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
        <!-- Demo -->
        <script src="assets/js/demo.js"></script>
</body>

</html>
