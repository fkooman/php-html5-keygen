<?php
$vendorDir = '/usr/share/php';
$pearDir   = '/usr/share/pear';
$baseDir   = dirname(__DIR__);

require_once $vendorDir.'/Symfony/Component/ClassLoader/UniversalClassLoader.php';
require_once $vendorDir.'/phpseclib/phpseclib/phpseclib/Crypt/Random.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(
    array(
        'fkooman\\Cert'                       => $baseDir.'/src',
        'fkooman\\Rest'                       => $vendorDir,
        'fkooman\\Json'                       => $vendorDir,
        'fkooman\\Ini'                        => $vendorDir,
        'fkooman\\Http'                       => $vendorDir,
        'Symfony\\Component\\EventDispatcher' => $vendorDir,
        'Guzzle'                              => $vendorDir
    )
);

$loader->registerPrefixes(array(
    'Twig_'               => array($pearDir, $vendorDir),
#    'System' => array($vendorDir . '/phpseclib/phpseclib/phpseclib'),
#    'Net' => array($vendorDir . '/phpseclib/phpseclib/phpseclib'),
#    'Math' => array($vendorDir . '/phpseclib/phpseclib/phpseclib'),
#    'File' => array($vendorDir . '/phpseclib/phpseclib/phpseclib'),
#    'Crypt' => array($vendorDir . '/phpseclib/phpseclib/phpseclib'),
));

$loader->register();
