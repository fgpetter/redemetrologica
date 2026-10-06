<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'pep' => [
        'api_key' => env('PEP_API_KEY'),
    ],

    'correios' => [
        'base_url' => env('CORREIOS_BASE_URL', 'https://api.correios.com.br'),
        'contrato' => env('CORREIOS_CONTRATO'),
        'cartao_postagem' => env('CORREIOS_CARTAO_POSTAGEM'),
        'api_username' => env('API_USERNAME'),
        'api_key' => env('API_KEY'),
        'codigo_servico_sedex_12' => env('CORREIOS_CODIGO_SERVICO_SEDEX_12', '03140'),
        'codigo_servico_sedex' => env('CORREIOS_CODIGO_SERVICO_SEDEX', '03220'),
        'tipo_rotulo' => env('CORREIOS_TIPO_ROTULO', 'P'),
        'tipo_dace' => env('CORREIOS_TIPO_DACE', 'C'),
        'formato_rotulo' => env('CORREIOS_FORMATO_ROTULO', 'ET'),
        'imprime_remetente' => env('CORREIOS_IMPRIME_REMETENTE', 'S'),
        'layout_impressao' => env('CORREIOS_LAYOUT_IMPRESSAO', 'PADRAO'),
        'timeout' => (int) env('CORREIOS_TIMEOUT', 15),
        'admin_email' => env('CORREIOS_ADMIN_EMAIL', 'sistema@redemetrologica.com.br'),
        'documentos_email' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env(
                'CORREIOS_DOCUMENTOS_EMAIL',
                'tecnico@redemetrologica.com.br,interlab@redemetrologica.com.br',
            )),
        ))),
        'remetente' => [
            'nome' => env('CORREIOS_REMETENTE_NOME'),
            'cpf_cnpj' => env('CORREIOS_REMETENTE_CNPJ'),
            'cep' => env('CORREIOS_REMETENTE_CEP'),
            'logradouro' => env('CORREIOS_REMETENTE_LOGRADOURO'),
            'numero' => env('CORREIOS_REMETENTE_NUMERO'),
            'complemento' => env('CORREIOS_REMETENTE_COMPLEMENTO'),
            'bairro' => env('CORREIOS_REMETENTE_BAIRRO'),
            'cidade' => env('CORREIOS_REMETENTE_CIDADE'),
            'uf' => env('CORREIOS_REMETENTE_UF'),
        ],
    ],

];
