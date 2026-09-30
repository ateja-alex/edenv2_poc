<?php
// Custom phpMyAdmin configuration overrides
// These settings increase list and navigation limits as requested

if (!isset($cfg) || !is_array($cfg)) {
    $cfg = [];
}

$cfg['MaxRows'] = 500;
$cfg['MaxTableList'] = 500;
$cfg['MaxNavigationItems'] = 500;
