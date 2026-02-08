<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        try {
            return self::purifier()->purify($html);
        } catch (\Throwable) {
            return self::cleanWithDom($html);
        }
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier instanceof HTMLPurifier) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $cachePath = storage_path('app/purifier');
        if (!is_dir($cachePath)) {
            @mkdir($cachePath, 0755, true);
        }

        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set(
            'HTML.Allowed',
            'p,br,b,strong,i,em,u,ul,ol,li,blockquote,sub,sup,span,div,a[href|title|target|rel],img[src|alt|title|width|height]'
        );
        $config->set('HTML.ForbiddenElements', ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input']);
        $config->set('Attr.EnableID', false);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
            'data' => false,
            'javascript' => false,
        ]);
        $config->set('URI.DisableJavaScript', true);

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }

    private static function cleanWithDom(string $html): string
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div id="__sanitize_root__">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $allowedTags = ['p', 'br', 'b', 'strong', 'i', 'em', 'u', 'ul', 'ol', 'li', 'blockquote', 'sub', 'sup', 'span', 'div', 'a', 'img'];
        $allowedAttrs = [
            'a' => ['href', 'title', 'target', 'rel'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
        ];

        $root = $doc->getElementById('__sanitize_root__');
        if (!$root) {
            return '';
        }

        $nodes = iterator_to_array($root->getElementsByTagName('*'));

        foreach ($nodes as $node) {
            $tagName = strtolower($node->nodeName);

            if (!in_array($tagName, $allowedTags, true)) {
                $node->parentNode?->removeChild($node);
                continue;
            }

            if ($node->hasAttributes()) {
                $toRemove = [];
                foreach ($node->attributes as $attr) {
                    $attrName = strtolower($attr->name);
                    $attrValue = trim((string) $attr->value);

                    $isAllowed = in_array($attrName, $allowedAttrs[$tagName] ?? [], true);
                    $isDangerous = str_starts_with($attrName, 'on')
                        || $attrName === 'style'
                        || str_starts_with(strtolower($attrValue), 'javascript:')
                        || str_starts_with(strtolower($attrValue), 'data:');

                    if (!$isAllowed || $isDangerous) {
                        $toRemove[] = $attrName;
                    }
                }

                foreach ($toRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $doc->saveHTML($child);
        }

        return $clean;
    }
}
