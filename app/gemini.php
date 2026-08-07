<?php
declare(strict_types=1);

function gemini_writing_prompt(string $action, string $title, string $summary, string $content): string
{
    $instructions = [
        'create' => 'Crie a publicação completa do zero a partir do título e do resumo.',
        'improve' => 'Reescreva e melhore o texto atual, preservando os fatos, a intenção e a voz autoral.',
        'continue' => 'Continue o texto atual de forma natural e sem repetir o que já foi dito. Retorne o texto completo, incluindo a parte atual.',
    ];

    if (!isset($instructions[$action])) {
        throw new InvalidArgumentException('Ação de escrita inválida.');
    }

    return <<<PROMPT
Você é o assistente editorial do blog da Guitton, uma empresa brasileira de consultoria e administração imobiliária.

{$instructions[$action]}

Escreva em português do Brasil, com tom humano, claro, confiável e profissional. Produza conteúdo útil, específico e fácil de ler. Não invente dados, leis, números, casos ou depoimentos. Quando o tema envolver legislação, evite afirmações categóricas e recomende orientação profissional quando apropriado. Não inclua título principal, resumo, introduções sobre a tarefa ou comentários fora do artigo.

O campo content_html deve conter somente HTML semântico usando exclusivamente: <p>, <h2>, <h3>, <strong>, <em>, <ul>, <ol>, <li>, <blockquote> e <br>. Não use Markdown, links, atributos HTML ou imagens. Estruture o artigo com parágrafos curtos e subtítulos úteis.

Título: {$title}
Resumo: {$summary}
Texto atual:
{$content}
PROMPT;
}

function generate_post_with_gemini(string $action, string $title, string $summary, string $content): array
{
    if (GEMINI_API_KEY === '') {
        throw new RuntimeException('A chave do Gemini ainda não foi configurada no arquivo .env.');
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('A extensão cURL do PHP precisa estar habilitada no servidor.');
    }

    $payload = [
        'contents' => [[
            'role' => 'user',
            'parts' => [['text' => gemini_writing_prompt($action, $title, $summary, $content)]],
        ]],
        'generationConfig' => [
            'temperature' => 0.65,
            'maxOutputTokens' => 4096,
            'responseMimeType' => 'application/json',
            'responseJsonSchema' => [
                'type' => 'object',
                'properties' => [
                    'content_html' => [
                        'type' => 'string',
                        'description' => 'O artigo completo em HTML semântico permitido.',
                    ],
                ],
                'required' => ['content_html'],
                'additionalProperties' => false,
            ],
        ],
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
        . rawurlencode(GEMINI_MODEL) . ':generateContent';
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 90,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . GEMINI_API_KEY,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    ]);

    $raw = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($raw === false) {
        throw new RuntimeException('Não foi possível conectar ao Gemini. ' . $curlError);
    }

    $response = json_decode($raw, true);
    if ($status < 200 || $status >= 300) {
        $apiMessage = is_array($response) ? (string) ($response['error']['message'] ?? '') : '';
        throw new RuntimeException($apiMessage !== '' ? 'Gemini: ' . $apiMessage : 'O Gemini respondeu com erro HTTP ' . $status . '.');
    }

    $text = (string) ($response['candidates'][0]['content']['parts'][0]['text'] ?? '');
    $result = json_decode($text, true);
    if (!is_array($result) || trim((string) ($result['content_html'] ?? '')) === '') {
        throw new RuntimeException('O Gemini não devolveu um texto válido. Tente novamente.');
    }

    $html = sanitize_post_html((string) $result['content_html']);
    if (mb_strlen(plain_text_from_html($html), 'UTF-8') < 80) {
        throw new RuntimeException('O texto gerado ficou incompleto. Tente novamente.');
    }

    return ['content_html' => $html];
}
