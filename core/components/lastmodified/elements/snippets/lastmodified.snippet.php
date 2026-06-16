<?php
/**
 * lastModified snippet
 *
 * Appends a filemtime() timestamp as query string to a file path for cache-busting.
 *
 * Original author: demon.devin <demon.devin@gmail.com> (2016)
 * Dual-compat build: João Nogueira
 *
 * @var modX $modx
 * @var array $scriptProperties
 *
 * PROPERTIES
 * &path  |  string  |  required  ->  path to the file with trailing slash
 * &file  |  string  |  required  ->  filename of the document you wish to use
 *
 * EXAMPLE:
 * <link rel="stylesheet" href="[[!lastModified? &path=`css/` &file=`style.css`]]" />
 *
 * OUTPUT:
 * css/style.css?1477050530
 */

$path = $modx->getOption('path', $scriptProperties, '');
$file = $modx->getOption('file', $scriptProperties, '');

if (empty($path) || empty($file)) {
    $modx->log(xPDO::LOG_LEVEL_WARN, 'lastModified snippet: &path and &file are required.');
    return '';
}

// Security: resolve real path and ensure it stays within MODX base path
$basePath = $modx->getOption('base_path', null, MODX_BASE_PATH);
$fullPath = $basePath . ltrim($path, '/') . $file;
$realPath = realpath($fullPath);

if ($realPath === false || strpos($realPath, realpath($basePath)) !== 0) {
    $modx->log(xPDO::LOG_LEVEL_WARN, 'lastModified snippet: file not found or path traversal blocked — ' . $path . $file);
    return $path . $file;
}

$lastModified = filemtime($realPath);
$timeStamp = $path . $file . '?' . $lastModified;

return $timeStamp;
