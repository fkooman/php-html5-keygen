<?php

namespace fkooman\Keygen;

use phpseclib\Crypt\RSA;
use phpseclib\File\X509;

class CertManager
{
    const FORMAT_PEM = 0;
    const FORMAT_DER = 1;

    /** @var string */
    private $caCrt;

    /** @var string */
    private $caKey;

    /**
     * Initialize the CertManager.
     *
     * @param string $caCrt the CA certificate as string
     * @param string $caKey the CA key as string
     */
    public function __construct($caCrt, $caKey)
    {
        $this->caCrt = $caCrt;
        $this->caKey = $caKey;
    }

    /**
     * Generate a CA.
     *
     * @param int    $keySize    the keysize of the CA, please use >= 2048
     * @param string $commonName the CN of the CA
     */
    public static function generateCertificateAuthority($keySize = 2048, $commonName = 'Demo CA')
    {
        $keySize = intval($keySize);
        $r = new RSA();
        $keyData = $r->createKey($keySize);

        $privateKey = new RSA();
        $privateKey->loadKey($keyData['privatekey']);

        $publicKey = new RSA();
        $publicKey->loadKey($keyData['publickey']);
        $publicKey->setPublicKey();

        $subject = new X509();
        $subject->setDNProp('CN', $commonName);
        $subject->setPublicKey($publicKey);

        $issuer = new X509();
        $issuer->setPrivateKey($privateKey);
        $issuer->setDN($subject->getDN());

        $x509 = new X509();
        $x509->makeCA();

        $result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');

        return array(
            'crt' => $x509->saveX509($result),
            'key' => $keyData['privatekey'],
        );
    }

    /**
     * Generate a client certificate.
     *
     * @param string $spkac      the SPKAC from the browser
     * @param string $commonName the CN to be used in the client certificate
     * @param string $userAgent  the browser's User-Agent header
     */
    public function enroll($spkac, $commonName, $userAgent = 'Firefox')
    {
        if (false !== strpos($userAgent, 'Chrome')) {
            // Chrom(e)(ium) needs the certificate format to be DER
            $saveFormat = self::FORMAT_DER;
        } else {
            // We assume the rest of the browsers like PEM format
            $saveFormat = self::FORMAT_PEM;
        }

        return $this->generateClientCertificate($spkac, $commonName, $saveFormat);
    }

    private function generateClientCertificate($spkac, $commonName, $saveFormat = self::FORMAT_PEM)
    {
        $caPrivateKey = new RSA();
        $caPrivateKey->loadKey($this->caKey);

        $issuer = new X509();
        $issuer->loadX509($this->caCrt);
        $issuer->setPrivateKey($caPrivateKey);

        $subject = new X509();
        $subject->loadCA($this->caCrt);
        $subject->loadSPKAC($spkac);
        $subject->setDNProp('CN', $commonName);

        $x509 = new X509();
        $x509->setSerialNumber($serialNumber = bin2hex(openssl_random_pseudo_bytes(8)), 16);
        $result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');
        $format = $saveFormat === self::FORMAT_PEM ? X509::FORMAT_PEM : X509::FORMAT_DER;

        return $x509->saveX509($result, $format);
    }
}
