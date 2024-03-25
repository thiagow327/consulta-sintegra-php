# consulta-sintegra
<b>Descrição</b>
<br>
Este projeto em PHP foi desenvolvido para realizar consultas de informações relacionadas a CNPJ's no site do Sintegra. O script faz a simulação de interações com o site para obter dados como Inscrição Estadual, Nome Empresarial, Endereço, Telefone, E-mail, Atividade Econômica, entre outros.

<b>Requisitos</b>
<br>
Certifique-se de atender aos seguintes requisitos antes de utilizar este script:
- PHP 7.0 ou superior.
- Habilitar a extensão cURL no PHP.

<b>Limitações e Desafios</b>
- Gestão de Captchas: A inclusão de captchas diretamente no terminal não é suportada, e a solução proposta envolve a obtenção manual do código captcha por meio de um formulário web.
- Cookies e Segurança: A interação com o site enfrenta desafios relacionados à gestão de cookies e possíveis medidas de segurança implementadas pelo Sintegra. A abordagem atual SIMULA interações web, mas pode encontrar limitações dependendo das políticas de segurança do site.

<b>Execução</b>
<br>
Para realizar uma consulta, siga os passos abaixo:
- Execute index.php em seu navegador.
- Digite os dados do Captcha, e clique em 'Enviar'.
- Você será direcionado para a pagina 'consultaCNPJ.html'
- Digite o numero do CNPJ e clique em 'Consultar'.
Exemplos para teste: 00080160000198, 00063744000155
- O resultado da consulta será exibido no formato de array associativo.

<b>Nota</b>: Este projeto é parte de um teste técnico de trabalho e destina-se exclusivamente a fins de avaliação. Recomenda-se o uso estrito para este propósito. Caso as limitações apresentadas tornem-se significativas, é aconselhável explorar alternativas, como a busca por APIs oficiais ou fontes de dados alternativas.
