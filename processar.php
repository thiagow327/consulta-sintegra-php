<?php

// Definição de constantes
define('SINTEGRA_URL', 'http://www.sintegra.fazenda.pr.gov.br/sintegra/');
define('TEMPLATES_DIR', __DIR__ . '/templates/');


/**
 * Função para executar a consulta
 */
function executar($captcha, $cnpj)
{
    $resultado = [];

    // Busca o HTML da página de consulta por CNPJ
    $pagina_consulta_por_CNPJ = buscarHTMLPorCNPJ($cnpj, $captcha);

    // Verifica se a busca foi bem-sucedida
    if ($pagina_consulta_por_CNPJ === false) {
        $resultado[] = [
            'status' => false,
            'message' => 'Não foi possível identificar o conteúdo da pesquisa referente ao CPNJ: ' . $cnpj,
            'body' => ['cnpj' => $cnpj]
        ];
        return $resultado;
    }

    // Extrai dados do HTML obtido
    $dados_extraidos = extrairDadosDoHTML($pagina_consulta_por_CNPJ);

    // Adiciona os dados ao resultado
    $resultado[] = [
        'status' => true,
        'body' => [
            'cnpj' => $cnpj,
            'inscricoes' => [$dados_extraidos]
        ]
    ];

    // Busca e adiciona mais inscrições estaduais, se houverem
    adicionarInscricoesEstaduais($cnpj, $resultado);

    return $resultado;
}


/**
 * Função para extrair dados do HTML
 */
function extrairDadosDoHTML($html)
{
    $dados_extraidos = [];

    // Expressões regulares para extrair os dados
    $regex = [
        'inscricaoEstadual' => '/<td class="form_label" width="120">Inscri&ccedil;&atilde;o Estadual:<\/td>\s*<td class="form_conteudo"\s*>(.*?)<\/td>/s',
        'nomeEmpresarial' => '/<td class="form_label">Nome Empresarial:<\/td>\s*<td class="form_conteudo" colspan="3"\s*>(.*?)<\/td>/s',
        'logradouro' => '/<td class="form_label">Logradouro:<\/td>\s*<td class="form_conteudo" colspan="5">(.*?)<\/td>/s',
        'numero' => '/<td class="form_label" width="80">N&uacute;mero:<\/td>\s*<td class="form_conteudo">(.*?)<\/td>/s',
        'complemento' => '/<td class="form_label" width="100">Complemento:<\/td>\s*<td class="form_conteudo" colspan="3">(.*?)<\/td>/s',
        'bairro' => '/<td class="form_label">Bairro:<\/td>\s*<td class="form_conteudo" colspan="5">(.*?)<\/td>/s',
        'municipio' => '/<td class="form_label">Munic&iacute;pio:<\/td>\s*<td class="form_conteudo" colspan="3">(.*?)<\/td>/s',
        'uf' => '/<td class="form_label" width="50">UF:<\/td>\s*<td class="form_conteudo">(.*?)<\/td>/s',
        'cep' => '/<td class="form_label">CEP:<\/td>\s*<td class="form_conteudo">(.*?)<\/td>/s',
        'telefone' => '/<td class="form_label">Telefone:<\/td>\s*<td class="form_conteudo" colspan="3">(.*?)<\/td>/s',
        'email' => '/<td class="form_label">E-mail:<\/td>\s*<td class="form_conteudo" colspan="5">(.*?)<\/td>/s',
        'atividade_principal' => '/<td class="form_label" width="210">Atividade Econ&ocirc;mica Principal:<\/td>\s*<td class="form_conteudo">(.*?)<\/td>/s',
        'inicio_atividades' => '/<td class="form_label">In&iacute;cio das Atividades:<\/td>\s*<td class="form_conteudo">(.*?)<\/td>/s',
        'situacao_atual' => '/<td class="form_label">Situa&ccedil;&atilde;o Atual:<\/td>\s*<td class="form_conteudo">(.*?)<\/td>/s',
        'situacao_cadastral' => '/<td class="form_label">Situa&ccedil;&atilde;o Cadastral:<\/td>\s*<td class="form_conteudo">(.*?)<\/td>/s',
        'regime_tributario' => '/<td class="form_label">Regime Tribut&aacute;rio:<\/td>\s*<td class="form_conteudo">(.*?)<\/td>/s',
    ];

    // Itera sobre as expressões regulares e extrai os dados correspondentes
    foreach ($regex as $chave => $padrao) {
        if (preg_match($padrao, $html, $matches)) {
            $dados_extraidos[$chave] = $matches[1] !== '' ? html_entity_decode($matches[1]) : 'VAZIO';
        } else {
            $dados_extraidos[$chave] = 'DADO NAO ENCONTRADO';
        }
    }

    // Retorna os dados extraídos
    return $dados_extraidos;





    // // Implemente a lógica para extrair os dados reais do HTML aqui
    // return ['id' => 0]; // Por enquanto, retorna apenas um exemplo
}


/**
 * Função para buscar inscrições estaduais por CNPJ e adicionar ao resultado
 * @warning: O segundo parâmetro, $resultado, está vindo como referência,
 *           ou seja, qualquer alteração em seu conteúdo, afetará a variável externa.
 */
function adicionarInscricoesEstaduais($cnpj, &$resultado)
{
    $contador = 1;

    // Lógica para buscar e adicionar inscrições estaduais ao resultado
    while ($nova_pagina_inscricao_estadual = buscarInscricoesEstaduaisPorCNPJEOffset($cnpj, $contador)) {

        $dados_extraidos = extrairDadosDoHTML($nova_pagina_inscricao_estadual);
        $dados_extraidos['id'] = $contador; // Temporário

        end($resultado);
        $ultima_chave = key($resultado);

        $resultado[$ultima_chave]['body']['inscricoes'][] = $dados_extraidos;

        $contador++;
    }
}


/**
 * Função para verificar se existem mais inscrições para este CNPJ
 */
function existemMaisInscricoesParaEsseCPNJ($html)
{
    $texto_alvo = "Outra Inscrição Estadual";
    $regex = '/<button.*?>\s*' . preg_quote($texto_alvo, '/') . '\s*<\/button>/';

    return (preg_match($regex, $html));
}


/**
 * Função para buscar HTML por CNPJ
 */
function buscarHTMLPorCNPJ($cnpj, $captcha)
{
    $arquivo = TEMPLATES_DIR . "$cnpj/index.html";
    if (file_exists($arquivo)) {
        return file_get_contents($arquivo);
    }

    return false;

    /**
     * O trecho de código a seguir deveria ser executado, mas, por algum motivo, mesmo após
     * a geração do Captcha, estou recebendo a seguinte mensagem de erro:
     * "Sua solicitação não pode ser atendida - O NÚMERO DE CONTROLE DIGITADO 
     * {%s} NÃO CORRESPONDE AO NÚMERO APRESENTADO NA IMAGEM".
     * 
     * Acredito que isso possa ser devido a um problema de Cookie. Não vejo como uma requisição
     * GET (http://www.sintegra.fazenda.pr.gov.br/sintegra/captcha) poderia garantir a segurança
     * para uma requisição POST subsequente vinda do mesmo cliente. 
     * Durante a primeira requisição, que é a geração do Captcha, um cabeçalho
     * chamado "Set-Cookie" é devolvido, configurando um Cookie com o seguinte valor, exemplo: 
     * CAKEPHP=8ecfd5653e205d78790d921c2e73e365; path=/sintegra.
     * Ao realizar o POST com o envio dos dados, este Cookie é alterado quando visualizo a resposta.
     * Eu suspeito que esteja sendo esse o ponto do problema, mas, sinceramente, não tenho certeza.
     */
    $post = [
        'empresa' => 'Consultar Empresa',
        '_method' => 'POST',
        'data' => [
            'Sintegra1' => [
                'CodImage' => $captcha,
                'Cnpj' => $cnpj,
            ]
        ]
    ];

    $ch = curl_init(SINTEGRA_URL);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array('Content-Type: application/x-www-form-urlencoded')
    );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);

    curl_close($ch);

    return $response;
}


/**
 * Função para buscar inscrições estaduais por CNPJ e offset
 */
function buscarInscricoesEstaduaisPorCNPJEOffset($cnpj, $offset)
{
    $arquivo = TEMPLATES_DIR . "$cnpj/inscricoes/$offset.html";

    if (file_exists($arquivo)) {
        return file_get_contents($arquivo);
    }

    return false;
}


/**
 * Processo Spider
 */
$dados = $_POST;
if (empty($dados) || empty($dados['cnpj'])) {
    echo 'CNPJ não fornecido corretamente.';
    exit;
}

try {
    // Executa a consulta apenas para o CNPJ informado
    $resultado = executar($dados['captcha'], $dados['cnpj']);

    // Exibe o resultado
    echo '<pre>';
    print_r($resultado);
    echo '</pre>';
} catch (Exception $exc) {
    echo $exc->getMessage() . '<br>';
    echo $exc->getFile() . '<br>';
    echo $exc->getTraceAsString();
}
