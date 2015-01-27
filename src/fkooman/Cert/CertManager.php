<?php

namespace fkooman\Cert;

use Crypt_RSA;
use File_X509;

class CertManager
{

    const FORMAT_PEM = 0;
    const FORMAT_DER = 1;

    public function __construct()
    {
    }

    public function generateCertificateAuthority($keySize = 2048, $commonName = 'Demo CA')
    {
        $r = new Crypt_RSA();
        $keyData = $r->createKey($keySize);

        $privateKey = new Crypt_RSA();
        $privateKey->loadKey($keyData['privatekey']);

        $publicKey = new Crypt_RSA();
        $publicKey->loadKey($keyData['publickey']);
        $publicKey->setPublicKey();

        $subject = new File_X509();
        $subject->setDNProp('CN', $commonName);
        $subject->setPublicKey($publicKey);

        $issuer = new File_X509();
        $issuer->setPrivateKey($privateKey);
        $issuer->setDN($subject->getDN());

        $x509 = new File_X509();
        $x509->makeCA();

        $result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');

        return array(
            'crt' => $x509->saveX509($result),
            'key' => $keyData['privatekey']
        );
    }

    public function generateClientCertificate($spkac, $format = CertManager::FORMAT_PEM)
    {
        // verify key size >= 2048

        
        return 'pemblup/derblup';
    }
}
