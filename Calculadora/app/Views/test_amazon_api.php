<?php

namespace App\Services;

use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class AmazonProductService
{
    private $accessKey;
    private $secretKey;
    private $associateTag;
    private $region;
    private $client;
    private $endpoint = 'https://webservices.amazon.com/paapi5/getitems';

    public function __construct()
    {
        $this->accessKey = getenv('AWS_ACCESS_KEY_ID');
        $this->secretKey = getenv('AWS_SECRET_ACCESS_KEY');
        $this->associateTag = getenv('AWS_ASSOCIATE_TAG');
        $this->region = getenv('AWS_REGION') ?: 'us-east-1';
        $this->client = new Client();
    }

    public function getProductByASIN($asin)
    {
        $payload = [
            'ItemIds' => [$asin],
            'Resources' => [
                'ItemInfo.Title',
                'Offers.Listings.Price',
                'Images.Primary.Large'
            ],
            'PartnerTag' => $this->associateTag,
            'PartnerType' => 'Associates',
            'Marketplace' => 'www.amazon.com'
        ];

        $jsonPayload = json_encode($payload);
        $headers = $this->getSignedHeaders($jsonPayload);

        $response = $this->client->post($this->endpoint, [
            'headers' => $headers,
            'body' => $jsonPayload
        ]);

        return json_decode($response->getBody(), true);
    }

    private function getSignedHeaders($payload)
    {
        $credentials = new Credentials($this->accessKey, $this->secretKey);
        $signer = new SignatureV4('ProductAdvertisingAPI', $this->region);

        $request = new Request('POST', $this->endpoint, [
            'content-encoding' => 'amz-1.0',
            'content-type' => 'application/json; charset=utf-8',
            'host' => 'webservices.amazon.com',
            'x-amz-target' => 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems',
        ], $payload);

        $signedRequest = $signer->signRequest($request, $credentials);

        return $signedRequest->getHeaders();
    }
}
