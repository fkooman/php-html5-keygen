<?php

require_once 'vendor/autoload.php';

use fkooman\Keygen\CertManager;

try {
    $ca = new CertManager(
        @file_get_contents(sprintf('%s/data/ca.crt', __DIR__)),
        @file_get_contents(sprintf('%s/data/ca.key', __DIR__))
    );

    // the SPKAC
    $spkac = $_POST['key'];

    // the User Agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    header('Content-Type: application/x-x509-user-cert');
    echo $ca->enroll($spkac, 'My Client Certificate', $userAgent);
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
    exit(1);
}
