<?php

namespace Moneymour;

class ApiClient {

    const ENVIRONMENT_PRODUCTION = 'production';
    const ENVIRONMENT_SANDBOX = 'sandbox';
    const ENVIRONMENT_DEVELOPMENT = 'development';

    const API_BASE_URL = 'https://api.moneymour.com';
    const API_SANDBOX_BASE_URL = 'https://api.sandbox.moneymour.com';
    const API_DEVELOPMENT_BASE_URL = 'http://localhost:3000';

    const ENDPOINT_MERCHANT_REQUEST = '/merchant-request';

    /**
     * @var string Moneymour APIs' base url
     */
    protected $baseUrl = self::API_SANDBOX_BASE_URL;

    /**
     * @var string The merchant identifier
     */
    protected $merchantId;

    /**
     * @var string The merchant secret
     */
    protected $merchantSecret;

    /**
     * @var SignatureFactory
     */
    protected $signatureFactory;

    /**
     * ApiClient constructor.
     *
     * @param string $merchantId The merchant identifier
     * @param string $merchantSecret The merchant secret
     * @param SignatureFactory $signatureFactory
     * @param string $environment The API environment: production, sandbox or development. Default: sandbox
     * @throws \Exception In case of a wrong environment name
     */
    public function __construct($merchantId, $merchantSecret, $signatureFactory, $environment = self::ENVIRONMENT_SANDBOX)
    {
        $environment = strtolower($environment);

        if (!in_array($environment, [
            self::ENVIRONMENT_PRODUCTION,
            self::ENVIRONMENT_SANDBOX,
            self::ENVIRONMENT_DEVELOPMENT
        ])) {
            throw new \Exception("Invalid environment, please use 'production', 'sandbox' or 'development'");
        }

        if ($environment === self::ENVIRONMENT_PRODUCTION) {
            $this->baseUrl = self::API_BASE_URL;
        } elseif ($environment === self::ENVIRONMENT_DEVELOPMENT) {
            $this->baseUrl = self::API_DEVELOPMENT_BASE_URL;
        }

        $this->merchantId = $merchantId;
        $this->merchantSecret = $merchantSecret;
        $this->signatureFactory = $signatureFactory;
    }

    /**
     * Request a loan.
     *
     * @param array $body The body to be sent in the POST request
     * @return mixed JSON decoded object
     * @throws \Exception
     */
    public function request(array $body) {
        // Add identification fields to the request
        $body['merchantId'] = $this->merchantId;
        $body['secret'] = $this->merchantSecret;

        $expiresAt = $this->signatureFactory->generateExpiresAtHeaderValue();
        $signature = $this->signatureFactory->build($expiresAt, $body);

        // Perform the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->baseUrl . self::ENDPOINT_MERCHANT_REQUEST);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Expires-at: ' . $expiresAt,
            'Signature: ' . $signature
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close ($ch);

        return json_decode($response, true);
    }
}
