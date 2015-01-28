<?php

namespace fkooman\Cert;

use fkooman\Rest\Service;
use fkooman\Http\Response;
use fkooman\Http\Request;
use Twig_Loader_Filesystem;
use Twig_Environment;

class KeygenService extends Service
{
    /** @var string */
    private $caCrt;

    /** @var string */
    private $caKey;

    public function __construct($caCrt, $caKey)
    {
        parent::__construct();

        $this->caCrt = $caCrt;
        $this->caKey = $caKey;

        $this->setDefaultRoute('/keygen');

        // PHP 5.3 compatibility
        $compatThis = &$this;

        $this->get(
            '/keygen',
            function (Request $request) use ($compatThis) {
                return $compatThis->getKeygen($request);
            }
        );

        $this->post(
            '/keygen',
            function (Request $request) use ($compatThis) {
                return $compatThis->postKeygen($request);
            }
        );
    }

    public function getKeygen(Request $request)
    {
        $certChallenge = bin2hex(
            openssl_random_pseudo_bytes(8)
        );

        $configTemplateDir = dirname(dirname(dirname(__DIR__))).'/config/views';
        $defaultTemplateDir = dirname(dirname(dirname(__DIR__))).'/views';

        $templateDirs = array();

        // the template directory actually needs to exist, otherwise the
        // Twig_Loader_Filesystem class will throw an exception when loading
        // templates, the actual template does not need to exist though...
        if (false !== is_dir($configTemplateDir)) {
            $templateDirs[] = $configTemplateDir;
        }
        $templateDirs[] = $defaultTemplateDir;

        $loader = new Twig_Loader_Filesystem($templateDirs);
        $twig = new Twig_Environment($loader);
        return $twig->render(
            'keygenPage.twig',
            array(
                'certChallenge' => $certChallenge
            )
        );
    }

    public function postKeygen(Request $request)
    {
        $spkac = $request->getPostParameter('spkac');
        $userAgent = $request->getHeader('USER_AGENT');
        if (false !== strpos($userAgent, 'Chrome')) {
            // Chrom(e)(ium) needs the certificate format to be DER
            $format = CertManager::FORMAT_DER;
        } else {
            $format = CertManager::FORMAT_PEM;
        }

        // determine serialNumber
        $serialNumber = 2342345;
        $commonName = bin2hex(
            openssl_random_pseudo_bytes(16)
        );
        // we want to keep a list of CN/serial for book keeping and revocation

        $certManager = new CertManager($this->caCrt, $this->caKey);
        $clientCert = $certManager->generateClientCertificate($spkac, $commonName, $serialNumber, $format);

        $response = new Response(200, 'application/x-x509-user-cert');
        $response->setContent($clientCert);
        return $response;
    }
}
