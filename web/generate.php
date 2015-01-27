<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

$spkac = $_POST['spkac'];
$caCrt = dirname(__DIR__).'/data/ca/ca.crt';
$caKey = dirname(__DIR__).'/data/ca/ca.key';

$capem = file_get_contents($caCrt);

$issuer = new File_X509();
$issuer->loadX509($capem);
$cakey = new Crypt_RSA();
$cakey->loadKey(file_get_contents($caKey));
$issuer->setPrivateKey($cakey);

$subject = new File_X509();
$subject->loadCA($capem);

// FIXME: must be >= 2048 bits!
$subject->loadSPKAC($spkac);
$subject->setDN('CN=WebID Client');

$x509 = new File_X509();
// FIXME: keep track of serial and update it every time
$x509->setSerialNumber(5, 10);

// FIXME: add Subject Key Identifier???


// https://stackoverflow.com/questions/17355088/how-do-i-set-extkeyusage-with-phpseclib

#        X509v3 extensions:
#            X509v3 Authority Key Identifier:
#                keyid:63:4D:43:5A:19:48:3F:C4:46:C1:02:BA:BF:EE:0E:E5:82:B7:66:A6
#            X509v3 Subject Key Identifier:
#                50:78:36:97:D7:35:E3:29:43:02:44:C9:D7:37:D7:8A:61:D7:D8:AF
#            X509v3 Key Usage: critical
#                Digital Signature, Key Encipherment
#            X509v3 Basic Constraints: critical
#                CA:FALSE
#            X509v3 Extended Key Usage:
#                TLS Web Client Authentication

$result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');

$x509->loadX509($result);
$x509->setExtension('id-ce-keyUsage', array('digitalSignature', 'keyEncipherment'), true);
$x509->setExtension('id-ce-extKeyUsage', array('id-kp-clientAuth'));
$x509->setExtension('id-ce-basicConstraints', array('cA' => false), true);
$result = $x509->sign($issuer, $x509, 'sha256WithRSAEncryption');

//echo "<pre>";
header('Content-Type: application/x-x509-user-cert');
// Chromium needs FILE_X509_FORMAT_DER and not PEM :(
echo $x509->saveX509($result, FILE_X509_FORMAT_PEM);
//echo "</pre>";
