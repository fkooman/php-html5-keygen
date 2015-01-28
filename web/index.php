<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Cert\KeygenService;
use fkooman\Cert\PdoStorage;
use fkooman\Ini\IniReader;
use fkooman\Http\Exception\InternalServerErrorException;
use fkooman\Http\Exception\HttpException;

try {
    $iniReader = IniReader::fromFile(
        dirname(__DIR__).'/config/config.ini'
    );

    $pdo = new PDO(
        $iniReader->v('PdoStorage', 'dsn'),
        $iniReader->v('PdoStorage', 'username', false),
        $iniReader->v('PdoStorage', 'password', false)
    );
    $pdoStorage = new PdoStorage($pdo);

    $storageDir = $iniReader->v('Common', 'storageDir');
    $caDir = sprintf('%s/ca', $storageDir);
    $caCrt = file_get_contents(sprintf('%s/ca.crt', $caDir));
    $caKey = file_get_contents(sprintf('%s/ca.key', $caDir));

    $service = new KeygenService($pdoStorage, $caCrt, $caKey);
    $service->run()->sendResponse();
} catch (Exception $e) {
    if ($e instanceof HttpException) {
        $response = $e->getHtmlResponse();
    } else {
        // we catch all other (unexpected) exceptions and return a 500
        $e = new InternalServerErrorException($e->getMessage());
        $response = $e->getHtmlResponse();
    }
    $response->sendResponse();
}
