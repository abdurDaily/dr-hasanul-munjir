<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitises rich-text editor output (blog posts, treatment descriptions, legal pages)
 * before it is stored, so it can be rendered unescaped safely.
 */
class HtmlCleaner
{
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig)
            ->allowElement('p')->allowElement('br')->allowElement('strong')->allowElement('b')
            ->allowElement('em')->allowElement('i')->allowElement('u')->allowElement('s')
            ->allowElement('h2')->allowElement('h3')->allowElement('h4')
            ->allowElement('ul')->allowElement('ol')->allowElement('li')
            ->allowElement('blockquote')->allowElement('pre')->allowElement('code')
            ->allowElement('a', ['href', 'title', 'target', 'rel'])
            ->allowElement('img', ['src', 'alt', 'width', 'height'])
            ->allowElement('figure')->allowElement('figcaption')
            ->allowElement('table')->allowElement('thead')->allowElement('tbody')->allowElement('tr')
            ->allowElement('th')->allowElement('td')
            ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
            ->allowMediaSchemes(['https', 'http'])
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->forceAttribute('a', 'rel', 'noopener')
            ->withMaxInputLength(200000);

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function clean(?string $html): ?string
    {
        if ($html === null || trim(strip_tags($html, '<img>')) === '') {
            return null;
        }

        return $this->sanitizer->sanitize($html);
    }
}
