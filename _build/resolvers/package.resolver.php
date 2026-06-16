<?php
/**
 * LastModified — package resolver.
 * Renames the package display name to "LastModified" in the Extras grid.
 *
 * @var modX $modx
 * @var array $options
 */
if (!function_exists('lastmodified_package_resolver')) {
    function lastmodified_package_resolver($modx, $options) {
        if (!isset($options[xPDOTransport::PACKAGE_ACTION])) return true;
        $action = $options[xPDOTransport::PACKAGE_ACTION];
        if ($action !== xPDOTransport::ACTION_INSTALL && $action !== xPDOTransport::ACTION_UPGRADE) return true;

        // Rename package in extras grid
        $pkg = $modx->getObject('transport.modTransportPackage', array('package_name' => 'lastmodified'));
        if ($pkg) {
            $sig = $pkg->get('signature');
            if (stripos($sig, 'lastmodified') !== false) {
                $pkg->set('package_name', 'LastModified');
                $pkg->save();
                $modx->log(xPDO::LOG_LEVEL_INFO, 'LastModified: package display name updated.');
            }
        }
        return true;
    }
}

$object = isset($object) ? $object : null;
$modx = ($object && isset($object->xpdo)) ? $object->xpdo : (isset($modx) ? $modx : null);
if ($modx) {
    lastmodified_package_resolver($modx, $options);
}
return true;
