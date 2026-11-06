<?php

namespace App\Support;

class TransportDescriptionHtmlSanitizer
{
    public const FIELDS = [
        'long_description',
        'long_description_fr',
        'inclusions',
        'inclusions_fr',
        'exclusions',
        'exclusions_fr',
        'pickup_instructions',
        'pickup_instructions_fr',
    ];

    private \HTMLPurifier $purifier;

    public function __construct()
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set(
            'HTML.Allowed',
            'p,br,strong,b,em,i,u,s,strike,del,h1,h2,h3,ol,ul,li,blockquote,a[href|title]'
        );
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
            'tel' => true,
        ]);
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Cache.SerializerPath', storage_path('framework/cache/data'));

        $this->purifier = new \HTMLPurifier($config);
    }

    public function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $sanitized = $this->purifier->purify($html);
        $text = html_entity_decode(strip_tags($sanitized), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($text) === '' ? '' : $sanitized;
    }
}
