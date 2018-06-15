<?php

namespace PayU\FX\Tests;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\Psr7\parse_query;
use PayU\FX\Auth\SignatureGenerator;
use PayU\FX\Client as FXClient;
use PayU\FX\Config\MerchantCredentials;
use PayU\FX\Config\Platform;
use PayU\FX\Exceptions\ClientException;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    const MERCHANT = 'PTEST';
    const SECRET_KEY = 'SECRETKEY';

    const ERROR_RESPONSE_400 = '{"meta": {"code": 400, "message": "A 400 error"}}';
    const RESPONSE_INVALID = '{"}}';
    const SUCCESS_RESPONSE = '{"meta":{"code":200,"message":"success"},"baseCurrency":"RON","rates":{"PLN":0.9329,"EUR":0.2161},"expiresAt":"2018-05-24T11:28:13+00:00"}';

    /**
     * @var Client|\PHPUnit_Framework_MockObject_MockObject
     */
    private $guzzleClientMock;

    /**
     * @var SignatureGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $signatureGeneratorMock;

    /**
     * @var FXClient
     */
    private $fxClient;

    public function setUp()
    {
        $this->guzzleClientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->signatureGeneratorMock = $this->getMockBuilder(SignatureGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fxClient = new FXClient(
            new MerchantCredentials(self::MERCHANT, self::SECRET_KEY),
            Platform::RO(),
            $this->guzzleClientMock,
            $this->signatureGeneratorMock
        );
    }

    public function testGetAllFxRatesWhenBaseCurrencyIsNotString()
    {
        // Given
        $baseCurrency = null;

        // Then
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Invalid base currency');

        // When
        $this->fxClient->getAllFxRates($baseCurrency);
    }

    public function testGetAllFxRatesWhenBaseCurrencyIsAStringWithInvalidLength()
    {
        // Given
        $baseCurrency = 'EURR';

        // Then
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Invalid base currency');

        // When
        $this->fxClient->getAllFxRates($baseCurrency);
    }

    public function testGetAllFxRatesWhenServerRespondsWith400AndValidJson()
    {
        // Given
        $baseCurrency = 'EUR';
        $parameters = [
            'merchant' => 'TEST',
            'dateTime' => date(DATE_ATOM),
            'signature' => 'hashed_signature'
        ];

        $signedRequest = new Request(
            'GET',
            new Uri('https://secure.payu.ro/api/fx/rates?' . build_query($parameters))
        );

        $response400 = new Response(400, [], self::ERROR_RESPONSE_400);
        $guzzleClientException = new GuzzleClientException('400 Error encountered', $signedRequest, $response400);

        // When
        $this->signatureGeneratorMock->expects($this->once())
            ->method('signGetRequest')
            ->with(
                $this->callback(function(Request $request){
                    return $this->checkGetRequestBasicInfo($request);
                }),
                self::SECRET_KEY
            )
            ->willReturn($signedRequest);

        $this->guzzleClientMock->expects($this->once())
            ->method('send')
            ->with($signedRequest)
            ->willThrowException($guzzleClientException);

        // Then
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('A 400 error');

        $this->fxClient->getAllFxRates($baseCurrency);
    }

    public function testGetAllFxRatesWhenServerRespondsWith400AndInvalidJsonIsReceived()
    {
        // Given
        $baseCurrency = 'EUR';
        $parameters = [
            'merchant' => 'TEST',
            'dateTime' => date(DATE_ATOM),
            'signature' => 'hashed_signature'
        ];

        $signedRequest = new Request(
            'GET',
            new Uri('https://secure.payu.ro/api/fx/rates?' . build_query($parameters))
        );

        $response400 = new Response(400, [], self::RESPONSE_INVALID);
        $guzzleClientException = new GuzzleClientException('400 Error encountered', $signedRequest, $response400);

        // When
        $this->signatureGeneratorMock->expects($this->once())
            ->method('signGetRequest')
            ->with(
                $this->callback(function(Request $request){
                    return $this->checkGetRequestBasicInfo($request);
                }),
                self::SECRET_KEY
            )
            ->willReturn($signedRequest);

        $this->guzzleClientMock->expects($this->once())
            ->method('send')
            ->with($signedRequest)
            ->willThrowException($guzzleClientException);

        // Then
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Unable to decode the error JSON received from server');

        $this->fxClient->getAllFxRates($baseCurrency);
    }

    public function testGetAllFxRatesWhenServerCannotRespond()
    {
        // Given
        $baseCurrency = 'EUR';
        $parameters = [
            'merchant' => 'TEST',
            'dateTime' => date(DATE_ATOM),
            'signature' => 'hashed_signature'
        ];

        $signedRequest = new Request(
            'GET',
            new Uri('https://secure.payu.ro/api/fx/rates?' . build_query($parameters))
        );

        $guzzleClientException = new ConnectException('Cannot connect to server', $signedRequest);

        // When
        $this->signatureGeneratorMock->expects($this->once())
            ->method('signGetRequest')
            ->with(
                $this->callback(function(Request $request){
                    return $this->checkGetRequestBasicInfo($request);
                }),
                self::SECRET_KEY
            )
            ->willReturn($signedRequest);

        $this->guzzleClientMock->expects($this->once())
            ->method('send')
            ->with($signedRequest)
            ->willThrowException($guzzleClientException);

        // Then
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Unable to connect to server. Check your network or firewall settings');

        $this->fxClient->getAllFxRates($baseCurrency);
    }

    public function testGetAllFxRatesWhenGuzzleThrowsOtherException()
    {
        // Given
        $baseCurrency = 'EUR';
        $parameters = [
            'merchant' => 'TEST',
            'dateTime' => date(DATE_ATOM),
            'signature' => 'hashed_signature'
        ];

        $signedRequest = new Request(
            'GET',
            new Uri('https://secure.payu.ro/api/fx/rates?' . build_query($parameters))
        );

        $guzzleClientException = new TooManyRedirectsException('Too many redirects', $signedRequest);

        // When
        $this->signatureGeneratorMock->expects($this->once())
            ->method('signGetRequest')
            ->with(
                $this->callback(function(Request $request){
                    return $this->checkGetRequestBasicInfo($request);
                }),
                self::SECRET_KEY
            )
            ->willReturn($signedRequest);

        $this->guzzleClientMock->expects($this->once())
            ->method('send')
            ->with($signedRequest)
            ->willThrowException($guzzleClientException);

        // Then
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Guzzle exception encountered');

        $this->fxClient->getAllFxRates($baseCurrency);
    }

    public function testGetAllFxRatesWhenServerRespondsOkWithInvalidJson()
    {
        // Given
        $baseCurrency = 'EUR';
        $parameters = [
            'merchant' => 'TEST',
            'dateTime' => date(DATE_ATOM),
            'signature' => 'hashed_signature'
        ];

        $signedRequest = new Request(
            'GET',
            new Uri('https://secure.payu.ro/api/fx/rates?' . build_query($parameters))
        );

        $invalidResponse = new Response(200, [], self::RESPONSE_INVALID);

        // When
        $this->signatureGeneratorMock->expects($this->once())
            ->method('signGetRequest')
            ->with(
                $this->callback(function(Request $request){
                    return $this->checkGetRequestBasicInfo($request);
                }),
                self::SECRET_KEY
            )
            ->willReturn($signedRequest);

        $this->guzzleClientMock->expects($this->once())
            ->method('send')
            ->with($signedRequest)
            ->willReturn($invalidResponse);

        // Then
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Unable to decode the FX rates JSON');

        $this->fxClient->getAllFxRates($baseCurrency);
    }

    public function testGetAllFxRatesWhenServerRespondsOkWithValidJson()
    {
        // Given
        $baseCurrency = 'EUR';
        $parameters = [
            'merchant' => 'TEST',
            'dateTime' => date(DATE_ATOM),
            'signature' => 'hashed_signature'
        ];

        $signedRequest = new Request(
            'GET',
            new Uri('https://secure.payu.ro/api/fx/rates?' . build_query($parameters))
        );

        $response = new Response(200, [], self::SUCCESS_RESPONSE);

        // When
        $this->signatureGeneratorMock->expects($this->once())
            ->method('signGetRequest')
            ->with(
                $this->callback(function(Request $request){
                    return $this->checkGetRequestBasicInfo($request);
                }),
                self::SECRET_KEY
            )
            ->willReturn($signedRequest);

        $this->guzzleClientMock->expects($this->once())
            ->method('send')
            ->with($signedRequest)
            ->willReturn($response);

        $result = $this->fxClient->getAllFxRates($baseCurrency);

        // Then
        $this->assertEquals('PLN', $result[0]->getRateCurrency());
        $this->assertEquals('0.9329', $result[0]->getRateValue());
        $this->assertEquals('2018-05-24T11:28:13+00:00', $result[0]->getExpirationDate());
        $this->assertEquals('EUR', $result[1]->getRateCurrency());
        $this->assertEquals('0.2161', $result[1]->getRateValue());
        $this->assertEquals('2018-05-24T11:28:13+00:00', $result[1]->getExpirationDate());
    }

    private function checkGetRequestBasicInfo(Request $request, $httpMethod = 'GET', $merchant = 'PTEST')
    {
        $queryParameters = parse_query($request->getUri()->getQuery());
        $date = DateTime::createFromFormat(DATE_ATOM, $queryParameters['dateTime']);

        return
            Platform::RO == $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . '/' &&
            $httpMethod == $request->getMethod() &&
            $queryParameters['merchant'] === $merchant &&
            $date && $date->format(DATE_ATOM) === $queryParameters['dateTime'];
    }
}
