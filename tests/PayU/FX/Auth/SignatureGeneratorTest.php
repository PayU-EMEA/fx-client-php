<?php

namespace PayU\FX\Tests\Auth;

use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\Psr7\parse_query;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use PayU\FX\Auth\SignatureGenerator;

class SignatureGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SignatureGenerator
     */
    private $signatureGenerator;

    public function setUp()
    {
        $this->signatureGenerator = new SignatureGenerator();
    }

    public function testSignGetRequest()
    {
        // Given
        $queryParameters = [
            'merchant' => 'TEST',
            'dateTime' => date(DATE_ATOM)
        ];

        $secreyKey = 'SECRETKEY';
        $signature = $this->computeSignature($queryParameters, $secreyKey);

        $request = new Request(
            'GET',
            new Uri('https://myPayuServer.com/api/fx/rates?' . build_query($queryParameters))
        );

        // Then
        $signedRequest = $this->signatureGenerator->signGetRequest($request, $secreyKey);
        $signedRequestParameters = array_merge($queryParameters, ['signature' => $signature]);

        $this->assertEquals($signedRequestParameters, parse_query($signedRequest->getUri()->getQuery()));
    }

    private function computeSignature($parameters, $hashKey)
    {
        ksort($parameters);

        $sourceString = '';

        foreach ($parameters as $parameter) {
            $sourceString .= strlen($parameter) . $parameter;
        }

        return hash_hmac('sha256', $sourceString, $hashKey);
    }
}
