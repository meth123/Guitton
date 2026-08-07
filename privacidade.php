<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/partials/public-head.php';

render_public_head([
    'title' => 'Política de Privacidade e Cookies | Guitton',
    'description' => 'Saiba como a Guitton trata dados pessoais e utiliza cookies em seu site.',
    'canonical' => '/privacidade.php',
]);
$activePage = '';
?>
<body>
<?php require __DIR__ . '/partials/public-header.php'; ?>
<main class="privacy-page">
  <header class="privacy-hero">
    <div class="privacy-shell"><p class="eyebrow">PRIVACIDADE E TRANSPARÊNCIA</p><h1>Política de Privacidade e Cookies</h1><p>Última atualização: 7 de agosto de 2026.</p></div>
  </header>
  <article class="privacy-shell privacy-content">
    <p>Esta política explica como a Guitton coleta, utiliza, armazena e protege dados pessoais quando você acessa este site, entra em contato ou se inscreve para receber novas publicações.</p>

    <h2>1. Quem trata seus dados</h2>
    <p>A Guitton atua como controladora dos dados tratados neste site. Solicitações relacionadas à privacidade podem ser enviadas para <a href="mailto:crisguitton@aasp.org.br">crisguitton@aasp.org.br</a> ou dirigidas ao endereço Alameda Armênio Mendes, 66, Aparecida, Santos/SP.</p>

    <h2>2. Dados que podemos tratar</h2>
    <ul>
      <li><strong>Newsletter:</strong> endereço de e-mail, data do consentimento e estado da inscrição.</li>
      <li><strong>Contato e agendamento:</strong> dados que você informar voluntariamente ao usar WhatsApp, e-mail ou Calendly.</li>
      <li><strong>Segurança e funcionamento:</strong> endereço IP, data e hora de acesso, registros técnicos e cookie de sessão.</li>
      <li><strong>Preferência de cookies:</strong> sua escolha entre permitir ou recusar serviços opcionais, armazenada localmente no navegador.</li>
    </ul>

    <h2>3. Finalidades e bases legais</h2>
    <p>Usamos os dados para entregar as funcionalidades solicitadas, responder contatos, administrar a newsletter, proteger o site contra abuso e cumprir obrigações legais. O envio da newsletter e o carregamento de serviços opcionais são baseados em consentimento, que pode ser revogado a qualquer momento. Medidas de segurança e registros técnicos podem ser tratados para atender interesses legítimos e proteger o funcionamento do site.</p>

    <h2>4. Newsletter</h2>
    <p>Ao marcar a opção de consentimento e cadastrar seu e-mail, você autoriza o envio de avisos sobre novas publicações. Cada mensagem contém um link para cancelar a inscrição. Após o cancelamento, podemos manter um registro mínimo pelo período necessário para comprovar a solicitação e impedir novos envios, sem prejuízo do direito de pedir a eliminação quando aplicável.</p>

    <h2>5. Cookies e tecnologias semelhantes</h2>
    <p>O site utiliza um cookie de sessão estritamente necessário para segurança, autenticação administrativa e proteção dos formulários. Ele não é usado para publicidade. Sua preferência sobre serviços opcionais é guardada no armazenamento local do navegador.</p>
    <p>Google Maps e Calendly podem usar cookies ou tecnologias próprias quando autorizados e carregados. Esses serviços são opcionais: recusá-los não impede a leitura do site, do blog ou o contato por telefone e e-mail. Você pode alterar sua decisão a qualquer momento no botão <button class="privacy-inline-button" type="button" data-cookie-settings>Preferências de cookies</button>.</p>

    <h2>6. Compartilhamento e serviços de terceiros</h2>
    <p>Os dados podem ser processados por fornecedores necessários à hospedagem e ao envio de e-mails. Se você autorizar ou acessar recursos externos, também poderá interagir com Google Maps, Calendly e WhatsApp, sujeitos às políticas desses fornecedores. Alguns serviços podem processar dados fora do Brasil, utilizando mecanismos de proteção previstos na legislação aplicável.</p>

    <h2>7. Segurança e retenção</h2>
    <p>Adotamos medidas técnicas e administrativas razoáveis para proteger os dados contra acesso indevido, alteração, perda ou divulgação. Os dados são mantidos somente pelo período necessário às finalidades descritas, ao cumprimento de obrigações legais e ao exercício regular de direitos.</p>

    <h2>8. Seus direitos</h2>
    <p>Nos termos da LGPD, você pode solicitar confirmação e acesso ao tratamento, correção, anonimização, bloqueio ou eliminação quando aplicável, informação sobre compartilhamentos, portabilidade nos termos regulamentares e revogação do consentimento. Para exercer esses direitos, escreva para <a href="mailto:crisguitton@aasp.org.br">crisguitton@aasp.org.br</a>. Poderemos pedir informações para confirmar sua identidade e proteger seus dados.</p>

    <h2>9. Alterações desta política</h2>
    <p>Esta política poderá ser atualizada para refletir mudanças no site, nos serviços utilizados ou na legislação. A versão vigente e a data da atualização permanecerão disponíveis nesta página.</p>
  </article>
</main>
<?php require __DIR__ . '/partials/public-footer.php'; ?>
