<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Ini\IniReader;
use fkooman\Cert\CertManager;

$config = IniReader::fromFile(dirname(__DIR__).'/config/config.ini');
$storageDir = $config->v('Common', 'storageDir');
$caDir = sprintf('%s/ca', $storageDir);
 
$caCrtFile = sprintf('%s/ca.crt', $caDir);
$caKeyFile = sprintf('%s/ca.key', $caDir);

// Mozilla/5.0 (X11; Linux x86_64; rv:35.0) Gecko/20100101 Firefox/35.0
// Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36

$userAgent = $_SERVER['HTTP_USER_AGENT'];
if (false !== strpos($userAgent, 'Chrome')) {
    $format = CertManager::FORMAT_DER;
} else {
    $format = CertManager::FORMAT_PEM;
}

$spkac = $_POST['spkac'];
$serialNumber = 234234;

$commonName = bin2hex(
    openssl_random_pseudo_bytes(16)
);

$certManager = new CertManager(file_get_contents($caCrtFile), file_get_contents($caKeyFile));
$clientCert = $certManager->generateClientCertificate($spkac, $commonName, $serialNumber, $format);

header('Content-Type: application/x-x509-user-cert');
echo $clientCert;
