<?php
/**
 * LastModified transport package build script.
 *
 * Builds a dual-compat (MODX 2.x + 3.x) transport package.
 *
 * @package lastmodified
 */
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$tstart = microtime(true);

/* ── Paths ─────────────────────────────────────────────────────────────── */
define('PKG_NAME', 'lastmodified');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));
define('PKG_VERSION', '1.2.0');
define('PKG_RELEASE', 'pl');

$root = dirname(__DIR__, 2) . '/';
$buildDir = __DIR__ . '/';
$outputDir = $root . '_packages/lastmodified/';
if (!is_dir($outputDir)) @mkdir($outputDir, 0755, true);

/* ── Bootstrap MODX ────────────────────────────────────────────────────── */
require_once $root . 'config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
echo '<pre>';

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/');

/* ── Package attributes (changelog, readme, license, setup-options) ──── */
$builder->setPackageAttributes(array(
    'changelog' => file_get_contents($buildDir . 'CHANGELOG.txt'),
    'license'   => file_get_contents($buildDir . 'LICENSE.txt'),
    'readme'    => file_get_contents($buildDir . 'README.txt'),
    'setup-options' => array(
        'source' => $buildDir . 'setup-options.php',
    ),
    'requires'  => array(
        'php' => '>=7.4.0',
    ),
));

/* ── Category vehicle ─────────────────────────────────────────────────── */
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', 'LastModified');

/* ── Plugin ────────────────────────────────────────────────────────────── */
$plugin = $modx->newObject('modPlugin');
$plugin->set('name', 'LastModified');
$plugin->set('description', 'MODX Revolution plugin which handles the If-Modified-Since request header.');
$plugin->set('plugincode', file_get_contents($buildDir . 'elements/plugins/plugin.lastmodified.php'));
$plugin->set('static', 0);

$events = array();
$eventNames = array('OnWebPagePrerender', 'OnDocFormSave');
foreach ($eventNames as $eventName) {
    $evt = $modx->newObject('modPluginEvent');
    $evt->fromArray(array(
        'event' => $eventName,
        'priority' => 0,
        'propertyset' => 0,
    ), '', true, true);
    $events[] = $evt;
}
$plugin->addMany($events, 'PluginEvents');

/* ── Snippet ───────────────────────────────────────────────────────────── */
$snippet = $modx->newObject('modSnippet');
$snippet->set('name', 'lastModified');
$snippet->set('description', 'Appends filemtime timestamp to file path for cache-busting.');
$snippet->set('snippet', file_get_contents($buildDir . 'elements/snippets/lastmodified.snippet.php'));
$snippet->set('static', 0);

/* ── Add elements to category ─────────────────────────────────────────── */
$plugins = array($plugin);
$category->addMany($plugins, 'Plugins');

$snippets = array($snippet);
$category->addMany($snippets, 'Snippets');

/* ── Category vehicle attributes ──────────────────────────────────────── */
$attr = array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Plugins' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
                'PluginEvents' => array(
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => array('pluginid', 'event'),
                ),
            ),
        ),
        'Snippets' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
    ),
);

/* ── File resolver for core/components ─────────────────────────────────── */
$vehicle = $builder->createVehicle($category, $attr);

// Prepare core/components source directory
$coreSource = $buildDir . '_dist/core/components/' . PKG_NAME_LOWER . '/';
@mkdir($coreSource . 'docs', 0755, true);
@mkdir($coreSource . 'elements/plugins', 0755, true);
@mkdir($coreSource . 'elements/snippets', 0755, true);
@mkdir($coreSource . 'lexicon/en', 0755, true);
@mkdir($coreSource . 'lexicon/ru', 0755, true);
@mkdir($coreSource . 'lexicon/pt', 0755, true);

copy($buildDir . 'CHANGELOG.txt', $coreSource . 'docs/changelog.txt');
copy($buildDir . 'README.txt', $coreSource . 'docs/readme.txt');
copy($buildDir . 'LICENSE.txt', $coreSource . 'docs/license.txt');
copy($buildDir . 'elements/plugins/plugin.lastmodified.php', $coreSource . 'elements/plugins/plugin.lastmodified.php');
copy($buildDir . 'elements/snippets/lastmodified.snippet.php', $coreSource . 'elements/snippets/lastmodified.snippet.php');
copy($buildDir . 'lexicon/en/setting.inc.php', $coreSource . 'lexicon/en/setting.inc.php');
copy($buildDir . 'lexicon/ru/setting.inc.php', $coreSource . 'lexicon/ru/setting.inc.php');
copy($buildDir . 'lexicon/pt/setting.inc.php', $coreSource . 'lexicon/pt/setting.inc.php');

$vehicle->resolve('file', array(
    'source' => $coreSource,
    'target' => "return MODX_CORE_PATH . 'components/';",
));

// Package rename resolver
$vehicle->resolve('php', array(
    'source' => $buildDir . 'resolvers/package.resolver.php',
));

$builder->putVehicle($vehicle);

/* ── System Settings ───────────────────────────────────────────────────── */
$settings = array(
    'lastmodified.response' => array('value' => 'private', 'xtype' => 'textfield', 'area' => 'lastmodified.main'),
    'lastmodified.maxage' => array('value' => '3600', 'xtype' => 'numberfield', 'area' => 'lastmodified.main'),
    'lastmodified.expires' => array('value' => '3600', 'xtype' => 'numberfield', 'area' => 'lastmodified.main'),
    'lastmodified.update_parent' => array('value' => '', 'xtype' => 'combo-boolean', 'area' => 'lastmodified.main'),
    'lastmodified.update_level' => array('value' => '1', 'xtype' => 'numberfield', 'area' => 'lastmodified.main'),
    'lastmodified.update_start' => array('value' => '', 'xtype' => 'combo-boolean', 'area' => 'lastmodified.main'),
    'lastmodified.prevent_authorized' => array('value' => '1', 'xtype' => 'combo-boolean', 'area' => 'lastmodified.main'),
    'lastmodified.prevent_session' => array('value' => 'minishop2', 'xtype' => 'textfield', 'area' => 'lastmodified.main'),
    'lastmodified.exclude' => array('value' => '', 'xtype' => 'textfield', 'area' => 'lastmodified.main'),
);

foreach ($settings as $key => $data) {
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array(
        'key' => $key,
        'value' => $data['value'],
        'xtype' => $data['xtype'],
        'namespace' => PKG_NAME_LOWER,
        'area' => $data['area'],
    ), '', true, true);

    $vehicle = $builder->createVehicle($setting, array(
        xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    ));
    $builder->putVehicle($vehicle);
}

/* ── Pack & zip ────────────────────────────────────────────────────────── */
$builder->pack();

// Move zip to output dir
$zipName = PKG_NAME_LOWER . '-' . PKG_VERSION . '-' . PKG_RELEASE . '.transport.zip';
$builtZip = MODX_CORE_PATH . 'packages/' . $zipName;
$destZip = $outputDir . $zipName;

if (file_exists($builtZip)) {
    // Move old package to old_packages if it exists
    if (file_exists($destZip)) {
        $oldDir = $outputDir . 'old_packages/';
        if (!is_dir($oldDir)) @mkdir($oldDir, 0755, true);
        $ts = date('Ymd-Hi');
        rename($destZip, $oldDir . PKG_NAME_LOWER . '-' . PKG_VERSION . '-' . PKG_RELEASE . '_' . $ts . '.transport.zip');
    }
    copy($builtZip, $destZip);
    $modx->log(modX::LOG_LEVEL_INFO, 'Package copied to: ' . $destZip);
}

// Clean up _dist
$cleanDist = function($dir) use (&$cleanDist) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        is_dir($path) ? $cleanDist($path) : unlink($path);
    }
    rmdir($dir);
};
$cleanDist($buildDir . '_dist');

$tend = microtime(true);
$totalTime = sprintf("%2.4f s", ($tend - $tstart));
$modx->log(modX::LOG_LEVEL_INFO, "Package built in {$totalTime}");
echo '</pre>';
