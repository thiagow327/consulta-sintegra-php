<?php

// URL do serviço que gera o Captcha
$url = "http://www.sintegra.fazenda.pr.gov.br/sintegra/captcha";

// Inicia a sessão cURL
$ch = curl_init();

// Configurações da requisição cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$resultado = curl_exec($ch);

// Codifica o resultado do Captcha em base64
$base64 = base64_encode($resultado);

// Fecha a sessão cURL
curl_close($ch);
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNPJ</title>

    <!-- Adiciona os links para os arquivos CSS do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container">
        <div class="card p-4">
            <h1 class="mb-2">Captcha</h1>

            <form id="form-captcha" method="post" action="consultaCNPJ.html">
                <img src='data:image/jpeg;base64, <?= $base64 ?>' class="mb-3" />
                <div class="mb-3">
                    <label for="captcha" class="form_label">Digite os dados da imagem acima:</label>
                    <br>
                    <input type="text" class="form_control" name="captcha" value="">
                </div>

                <input type="hidden" name="cnpj" value="<?= isset($_POST['cnpj']) ? htmlspecialchars($_POST['cnpj']) : '' ?>">

                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>
    </div>

</body>

</html>