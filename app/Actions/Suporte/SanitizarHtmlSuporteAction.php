<?php

namespace App\Actions\Suporte;

use App\Models\SuporteArquivo;
use DOMDocument;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SanitizarHtmlSuporteAction
{
    /**
     * @param  Collection<int, SuporteArquivo>  $arquivos
     * @return array{html: string, imagens: array<int, string>}
     */
    public static function handle(string $html, Collection $arquivos, string $campo): array
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Allowed', 'p,br,h2,h3,h4,strong,em,ul,ol,li,blockquote,a[href|title],figure[class],figcaption,table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan],img[src|alt|width|height]');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $definition = $config->getHTMLDefinition(true);
        $definition->addElement('figure', 'Block', 'Flow', 'Common', ['class' => 'Text']);
        $definition->addElement('figcaption', 'Block', 'Flow', 'Common');
        $html = (new HTMLPurifier($config))->purify($html);

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $imagens = [];
        foreach ($document->getElementsByTagName('img') as $imagem) {
            $src = $imagem->getAttribute('src');
            $path = parse_url($src, PHP_URL_PATH);
            $arquivo = $arquivos->first(fn (SuporteArquivo $item) => $item->tipo === 'imagem'
                && $path === route('suporte-arquivo-exibir', $item->uid, false));
            if (! $arquivo || (parse_url($src, PHP_URL_HOST) && parse_url($src, PHP_URL_HOST) !== parse_url(config('app.url'), PHP_URL_HOST))) {
                throw ValidationException::withMessages([$campo => 'Use apenas imagens enviadas pelo editor deste formulário.']);
            }
            $imagem->setAttribute('src', route('suporte-arquivo-exibir', $arquivo->uid, false));
            $imagens[] = $arquivo->uid;
        }

        $body = $document->getElementsByTagName('body')->item(0);
        $texto = html_entity_decode($body->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (trim(str_replace("\u{00A0}", ' ', $texto)) === '' && $imagens === []) {
            throw ValidationException::withMessages([$campo => 'Informe uma descrição com texto ou imagem.']);
        }
        $resultado = '';
        foreach ($body->childNodes as $node) {
            $resultado .= $document->saveHTML($node);
        }

        return ['html' => $resultado, 'imagens' => array_values(array_unique($imagens))];
    }
}
