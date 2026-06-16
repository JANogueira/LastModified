<?php
/**
 * LastModified plugin — handles If-Modified-Since request header and
 * updates parent/start editedon on document save.
 *
 * Dual-compatible: MODX 2.x and MODX 3.x
 * PHP 8.x compatible
 *
 * Original author: Kudashev Sergey <kudashevs@gmail.com>
 * Dual-compat build: João Nogueira
 *
 * @var modX $modx MODX instance
 */

if ($modx->event->name == 'OnWebPagePrerender') {
    if ($modx->getOption('lastmodified.prevent_authorized') && ($modx->user->get('username') !== $modx->getOption('default_username'))) {
        return '';
    }

    $excludeOption = $modx->getOption('lastmodified.exclude', null, '');
    if (!empty($excludeOption)) {
        $excludeOptionValues = explode(',', $excludeOption);

        $excludeIds = array_map(function ($value) {
            return (int)$value;
        }, $excludeOptionValues);

        if (in_array($modx->resource->id, $excludeIds, false)) {
            return '';
        }
    }

    $preventSessionOption = $modx->getOption('lastmodified.prevent_session', null, '');
    if (!empty($preventSessionOption)) {
        $preventOptionValues = explode(',', $preventSessionOption);

        $preventValues = array_map(function ($value) {
            return strtolower(trim($value));
        }, $preventOptionValues);

        if (isset($_SESSION) && is_array($_SESSION)) {
            $sessionKeys = array_map(function ($value) {
                return strtolower(trim($value));
            }, array_keys($_SESSION));

            if (count(array_intersect($preventValues, $sessionKeys)) > 0) {
                return '';
            }
        }
    }

    $editedon = $modx->resource->get('editedon');
    $createdon = $modx->resource->get('createdon');
    $lastUpdateTime = !empty($editedon)
        ? strtotime($editedon)
        : (!empty($createdon) ? strtotime($createdon) : 0);

    if (empty($lastUpdateTime)) {
        return '';
    }

    $cacheControl = trim($modx->getOption('lastmodified.response', null, 'private'));

    if (!in_array($cacheControl, ['private', 'public'])) {
        $modx->log(xPDO::LOG_LEVEL_ERROR, 'LastModified: wrong "' . $cacheControl . '" response value. Check configuration.');
        return '';
    }

    $cacheMaxAge = ((int)$modx->getOption('lastmodified.maxage', null, 3600) > 0) ? (int)$modx->getOption('lastmodified.maxage', null, 3600) : 3600;
    $cacheExpires = ((int)$modx->getOption('lastmodified.expires', null, 3600) > 0) ? (int)$modx->getOption('lastmodified.expires', null, 3600) : 3600;

    if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $lastDownloadTime = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        if ($lastDownloadTime !== false && $lastUpdateTime <= $lastDownloadTime) {
            $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
            header($protocol . ' 304 Not Modified');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastUpdateTime) . ' GMT');
            header('Cache-control: ' . $cacheControl . ', max-age=' . $cacheMaxAge);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheExpires));
            exit();
        }
    }
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastUpdateTime) . ' GMT');
    header('Cache-control: ' . $cacheControl . ', max-age=' . $cacheMaxAge);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheExpires));

    return '';
}

/**
 * OnDocFormSave — updates editedon field of parent/start resources.
 *
 * @var modX $modx MODX instance
 * @var int $id id of saved document (available on OnDocFormSave)
 */
if ($modx->event->name == 'OnDocFormSave') {

    // Get resource from event params (dual-compat: works on MODX 2.x and 3.x)
    $resource = isset($modx->event->params['resource']) ? $modx->event->params['resource'] : null;
    if (!$resource || !is_object($resource)) {
        return '';
    }

    $contextKey = $resource->get('context_key');

    if ($modx->getOption('lastmodified.update_start')) {

        $startId = (int)$modx->getOption('site_start');

        if ($startId > 0 && $startId !== $id) {

            $start = $modx->getObject('modResource', $startId);

            if (!is_object($start)) {
                $modx->log(xPDO::LOG_LEVEL_ERROR, 'LastModified: get wrong modResource instance for site start with id ' . $startId . ' for document ' . $id . '.');
                return '';
            }

            $start->set('editedon', time());
            $start->save();

            unset($start);
        }

        unset($startId);
    }

    if ($modx->getOption('lastmodified.update_parent')) {
        $nesting = (($level = (int)$modx->getOption('lastmodified.update_level', null, 1)) > 0) ? $level : 1;

        $parentIds = $modx->getParentIds($id, $nesting, ['context' => $contextKey]);

        if (empty($parentIds)) {
            $modx->log(xPDO::LOG_LEVEL_ERROR, 'LastModified: get empty ParentIds array. Possible context violation.');
            return '';
        }

        foreach ($parentIds as $parentId) {
            if ($parentId === 0) {
                break;
            }

            $parent = $modx->getObject('modResource', $parentId);

            if (!is_object($parent)) {
                $modx->log(xPDO::LOG_LEVEL_ERROR, 'LastModified: get wrong modResource instance for parent with id ' . $parentId . ' for document ' . $id . '.');
                return '';
            }

            $parent->set('editedon', time());
            $parent->save();

            unset($parent);
        }

        unset($parentIds);

        return '';
    }
}
