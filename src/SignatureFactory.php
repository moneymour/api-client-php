<?php

namespace Moneymour;

class SignatureFactory {

    /**
     * @var string The RSA private key
     */
    private $privateKey;

    /**
     * @var string The RSA public key
     */
    private $publicKey;

    /**
     * SignatureFactory constructor.
     *
     * @param string $privateKey
     * @param string $publicKey
     */
    public function __construct($privateKey, $publicKey = null) {
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
    }

    /**
     * Build a Moneymour APIs signature based on the given $expiresAt and $body.
     *
     * @param string $expiresAt EPOCH timestamp
     * @param array $body The body to be sent in the POST request
     * @return string The base64 encoded signature string
     */
    public function build($expiresAt, $body) {
        // Build the json payload
        $jsonData = json_encode($body);

        // Build the payload for the signature
        $payload = '';
        $payload .= $body['secret'] . '|';
        $payload .= $expiresAt . '|';
        $payload .= $jsonData;

        // Build the signature
        openssl_sign($payload, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    /**
     * Verify the given $signature based on the given $expiresAt and $body
     *
     * @param string $signature The base64 encoded signature string
     * @param string $expiresAt EPOCH timestamp
     * @param array $body The body to be sent in the POST request
     * @return boolean True if the signature is verified
     * @throws \Exception
     */
    public function verify($signature, $expiresAt, $body) {
        // Build the json payload
        $jsonData = json_encode($body);

        // Build the payload for the signature
        $payload = '';
        $payload .= $body['secret'] . '|';
        $payload .= $expiresAt . '|';
        $payload .= $jsonData;

        $verification = openssl_verify($payload, base64_decode($signature), $this->publicKey, OPENSSL_ALGO_SHA256);

        if ($verification === -1) {
            throw new \Exception("Error during signature verification");
        }

        return $verification === 1;
    }

    /**
     * Generate a 60-second valid expires-at header value.
     *
     * @return string EPOCH timestamp. Now UTC + 60 seconds
     * @throws \Exception
     */
    public function generateExpiresAtHeaderValue() {
        $nowUTC = new \DateTime("now", new \DateTimeZone("UTC"));

        // Add 60 seconds to $nowUTC
        $nowUTC->add(new \DateInterval('PT60S'));

        return $nowUTC->format('U');
    }
}
