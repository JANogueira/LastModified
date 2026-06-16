<?php
/**
 * LastModified — install setup-options screen.
 *
 * Renders an HTML page on install/upgrade with environment checks.
 * Purely informational; does NOT block install.
 *
 * @var array $options
 * @var modX  $modx
 */

if (!isset($options[xPDOTransport::PACKAGE_ACTION])) {
    return '';
}
$action = $options[xPDOTransport::PACKAGE_ACTION];
if (!in_array($action, [xPDOTransport::ACTION_INSTALL, xPDOTransport::ACTION_UPGRADE], true)) {
    return '';
}

$rows = [];

/* ── PHP version ────────────────────────────────────────────────────────── */
$phpOk  = version_compare(PHP_VERSION, '7.4', '>=');
$rows[] = [
    'PHP version', PHP_VERSION,
    $phpOk ? 'ok' : 'warn',
    $phpOk
        ? 'Meets the recommended minimum (≥ 7.4). Tested on 8.x.'
        : 'Detected PHP version is older than 7.4. The extra may still run but is unsupported.',
];

/* ── MODX version ───────────────────────────────────────────────────────── */
$modxVer = '?';
if (defined('MODX_VERSION')) {
    $modxVer = MODX_VERSION;
} elseif (isset($modx->version) && is_array($modx->version) && !empty($modx->version['full_version'])) {
    $modxVer = $modx->version['full_version'];
} else {
    try {
        $sv = (string)$modx->getOption('settings_version', null, '');
        if ($sv !== '') {
            $modxVer = $sv;
        }
    } catch (\Throwable $e) { /* ignore */ }
    if ($modxVer === '?') {
        $modxVer = is_object($modx) && strpos(get_class($modx), 'MODX\\Revolution') === 0 ? '3.x' : '?';
    }
}
$modxNumeric = ($modxVer !== '?') ? preg_replace('/[^\d.].*$/', '', (string)$modxVer) : '';
$modxOk = $modxNumeric !== '' ? version_compare($modxNumeric, '2.6', '>=') : true;
$rows[] = [
    'MODX version', $modxVer,
    $modxOk ? 'ok' : 'warn',
    $modxOk
        ? 'Compatible with MODX Revolution ≥ 2.6 and MODX 3.x. This package supports both.'
        : 'Detected MODX version is older than 2.6. Some features may not work.',
];

/* ── Required PHP extensions ───────────────────────────────────────────── */
$extMb = extension_loaded('mbstring');
$rows[] = [
    'PHP extension: mbstring', $extMb ? 'loaded' : 'missing',
    $extMb ? 'ok' : 'warn',
    $extMb
        ? 'Recommended for proper UTF-8 handling of resource dates.'
        : 'Recommended — most MODX installs have this. Not strictly required by LastModified.',
];

/* ── Render ─────────────────────────────────────────────────────────────── */
ob_start();
?>
<style>
.lm-setup{font-family:'Segoe UI',Tahoma,Verdana,Arial,sans-serif;color:#1f2937;padding:10px 6px;}
.lm-setup h2{margin:0 0 6px;color:#0b3a67;font-size:18px;}
.lm-setup p.lead{margin:0 0 12px;color:#475569;font-size:13px;line-height:1.45;}
.lm-setup table{width:100%;border-collapse:collapse;font-size:12.5px;background:#fff;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;table-layout:fixed;}
.lm-setup th,.lm-setup td{padding:7px 10px;border-bottom:1px solid #eef2f7;text-align:left;vertical-align:top;word-wrap:break-word;overflow-wrap:break-word;}
.lm-setup th{background:#f8fafc;color:#0b3a67;font-weight:600;}
.lm-setup th.col-check  {width:30%;}
.lm-setup th.col-value  {width:18%;}
.lm-setup th.col-status {width:9%;text-align:center;}
.lm-setup th.col-notes  {width:43%;}
.lm-setup td.col-status {text-align:center;}
.lm-setup td.col-value code{display:inline-block;max-width:100%;background:#eef2f7;padding:1px 6px;border-radius:3px;font-size:12px;word-break:break-all;}
.lm-setup .lm-pill{display:inline-block;padding:2px 8px;border-radius:11px;font-size:11px;font-weight:600;letter-spacing:.3px;}
.lm-setup .pill-ok  {background:#dcfce7;color:#166534;}
.lm-setup .pill-warn{background:#fef3c7;color:#92400e;}
.lm-setup .pill-err {background:#fee2e2;color:#991b1b;}
.lm-setup .pill-info{background:#e0f2fe;color:#075985;}
.lm-setup .summary{display:flex;gap:8px;margin:8px 0 14px;flex-wrap:wrap;}
.lm-setup .summary .lm-pill{padding:4px 12px;font-size:12px;}
</style>
<div class="lm-setup">
    <h2>LastModified — environment check</h2>
    <p class="lead">
        The package will install regardless of the results below. Items flagged
        <span class="lm-pill pill-warn">WARN</span> indicate a potential compatibility concern.
    </p>
    <?php
    $sum = ['ok' => 0, 'warn' => 0, 'err' => 0, 'info' => 0];
    foreach ($rows as $r) { $sum[$r[2]] = ($sum[$r[2]] ?? 0) + 1; }
    ?>
    <div class="summary">
        <span class="lm-pill pill-ok">OK · <?php echo $sum['ok']; ?></span>
        <span class="lm-pill pill-warn">WARN · <?php echo $sum['warn']; ?></span>
        <?php if ($sum['err'] > 0): ?><span class="lm-pill pill-err">ERR · <?php echo $sum['err']; ?></span><?php endif; ?>
        <?php if ($sum['info'] > 0): ?><span class="lm-pill pill-info">INFO · <?php echo $sum['info']; ?></span><?php endif; ?>
    </div>
    <table>
        <thead><tr>
            <th class="col-check">Check</th>
            <th class="col-value">Detected</th>
            <th class="col-status">Status</th>
            <th class="col-notes">Notes</th>
        </tr></thead>
        <tbody>
<?php foreach ($rows as $r): list($name, $val, $level, $note) = $r; ?>
            <tr>
                <td><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="col-value"><code><?php echo htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); ?></code></td>
                <td class="col-status"><span class="lm-pill pill-<?php echo $level; ?>"><?php echo strtoupper($level); ?></span></td>
                <td><?php echo htmlspecialchars($note, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
return ob_get_clean();
