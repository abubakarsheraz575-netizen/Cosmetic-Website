<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class SafeSvgIcon
{
    private const ALLOWED_ELEMENTS = [
        'svg', 'g', 'path', 'circle', 'rect', 'line', 'polyline',
        'polygon', 'ellipse', 'title',
    ];

    private const ALLOWED_ATTRIBUTES = [
        'xmlns', 'viewBox', 'width', 'height', 'fill', 'stroke',
        'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'fill-rule',
        'clip-rule', 'opacity', 'x', 'y', 'x1', 'x2', 'y1', 'y2',
        'cx', 'cy', 'r', 'rx', 'ry', 'd', 'points', 'transform',
        'preserveAspectRatio',
    ];

    public static function sanitize(string $svg): string
    {
        if (stripos($svg, '<!DOCTYPE') !== false || stripos($svg, '<!ENTITY') !== false) {
            throw new \InvalidArgumentException('SVG logos must not contain document types or entities.');
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $document->loadXML($svg, LIBXML_NONET | LIBXML_NOBLANKS);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        if (! $loaded || ! $document->documentElement || strtolower($document->documentElement->localName) !== 'svg') {
            throw new \InvalidArgumentException('Upload a valid SVG logo.');
        }

        self::sanitizeNode($document->documentElement);
        $document->documentElement->setAttribute('xmlns', 'http://www.w3.org/2000/svg');

        $result = $document->saveXML($document->documentElement);
        if (! is_string($result) || $result === '') {
            throw new \RuntimeException('The SVG logo could not be sanitized.');
        }

        return $result;
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        for ($child = $node->firstChild; $child !== null;) {
            $next = $child->nextSibling;
            if ($child instanceof DOMElement) {
                if (! in_array(strtolower($child->localName), self::ALLOWED_ELEMENTS, true)) {
                    $node->removeChild($child);
                } else {
                    self::sanitizeNode($child);
                }
            } elseif ($child->nodeType !== XML_TEXT_NODE) {
                $node->removeChild($child);
            } elseif (strtolower($node->localName) !== 'title') {
                $node->removeChild($child);
            }
            $child = $next;
        }

        if (! $node instanceof DOMElement) {
            return;
        }

        for ($index = $node->attributes->length - 1; $index >= 0; $index--) {
            $attribute = $node->attributes->item($index);
            if (! $attribute) {
                continue;
            }
            $name = $attribute->nodeName;
            $value = $attribute->nodeValue ?? '';
            if (
                ! in_array($name, self::ALLOWED_ATTRIBUTES, true)
                || stripos($value, 'url(') !== false
                || stripos($value, 'javascript:') !== false
                || preg_match('/[<>"]/', $value)
            ) {
                $node->removeAttributeNode($attribute);
            }
        }
    }
}
