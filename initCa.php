<?php

require_once 'vendor/autoload.php';

use fkooman\Keygen\CertManager;

try {
    $ca = CertManager::generateCertificateAuthority(2048, 'My CA');

    if (false === @file_put_contents(sprintf('%s/data/ca.crt', __DIR__), $ca['crt'])) {
        throw new Exception('unable to write file');
    }

    if (false === @file_put_contents(sprintf('%s/data/ca.key', __DIR__), $ca['key'])) {
        throw new Exception('unable to write file');
    }
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
    exit(1);
}
