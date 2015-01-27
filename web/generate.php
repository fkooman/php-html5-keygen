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
$subject->loadSPKAC($spkac);
$subject->setDN('CN=WebID Client');

$x509 = new File_X509();
$x509->setExtension('id-ce-keyUsage', array('digitalSignature', 'keyEncipherment', 'dataEncipherment'));

//Not Critical
//TLS Web Client Authentication (1.3.6.1.5.5.7.3.2)
// critial not a CA

// FIXME: keep track of serial and update it every time
$x509->setSerialNumber('14234213', 10);
$result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');

header('Content-Type: application/x-x509-user-cert');
echo $x509->saveX509($result);
