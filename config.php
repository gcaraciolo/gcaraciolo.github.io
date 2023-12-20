<?php

use Illuminate\Support\Str;

return [
    'baseUrl' => '',
    'production' => false,
    'siteName' => 'Guilherme Caraciolo on Laravel',
    'siteDescription' => 'Reasoning about Laravel, PHP, Vue.js, JavaScript and sharing all along. Guilherme Caraciolo.',
    'siteAuthor' => 'Guilherme Caraciolo',

    // collections
    'collections' => [
        'posts' => [
            'author' => 'Guilherme Caraciolo', // Default author, if not provided in a post
            'sort' => '-date',
            'path' => fn ($page) => is_null($page->language) ? 'blog/pt/' . $page->getFilename() : 'blog/' . $page->language . '/' . $page->getFilename(),
        ],
    ],

    // helpers
    'getDate' => function ($page) {
        return Datetime::createFromFormat('U', $page->date);
    },
    'getExcerpt' => function ($page, $length = 255) {
        if ($page->excerpt) {
            return $page->excerpt;
        }

        $content = preg_split('/<!-- more -->/m', $page->getContent(), 2);
        $cleaned = trim(
            strip_tags(
                preg_replace(['/<pre>[\w\W]*?<\/pre>/', '/<h\d>[\w\W]*?<\/h\d>/'], '', $content[0]),
                '<code>'
            )
        );

        if (count($content) > 1) {
            return $cleaned;
        }

        $truncated = substr($cleaned, 0, $length);

        if (substr_count($truncated, '<code>') > substr_count($truncated, '</code>')) {
            $truncated .= '</code>';
        }

        return strlen($cleaned) > $length
            ? preg_replace('/\s+?(\S+)?$/', '', $truncated) . '...'
            : $cleaned;
    },
    'isActive' => function ($page, $path) {
        return Str::endsWith(trimPath($page->getPath()), trimPath($path));
    },
];
