<?php
$snippets = array();

$tmp = [
    'lastModified' => [
        'file' => 'lastmodified',
        'description' => 'Appends a filemtime() timestamp to a file path for cache-busting (with path-traversal protection).',
    ],
];

foreach ($tmp as $k => $v) {
    /** @var modSnippet $snippet */
    $snippet = $modx->newObject('modSnippet');
    /** @noinspection PhpUndefinedVariableInspection */
    $snippet->fromArray([
        'name' => $k,
        'description' => @$v['description'],
        'snippet' => getPhpFileContent($sources['source_core'] . '/elements/snippets/' . $v['file'] . '.snippet.php'),
        'static' => BUILD_SNIPPET_STATIC,
        'source' => 1,
        'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/snippets/' . $v['file'] . '.snippet.php',
    ], '', true, true);

    $snippets[] = $snippet;
}

return $snippets;
