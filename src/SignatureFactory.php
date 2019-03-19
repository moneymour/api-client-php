<?php

namespace Moneymour;

class SignatureFactory {

    /**
     * @var string The merchant RSA private key
     */
    private $privateKey;

    /**
     * @var string Moneymour RSA public key
     */
    private $publicKey;

    /**
     * SignatureFactory constructor.
     *
     * @param string $privateKey The merchant RSA private key
     * @param string $publicKey Moneymour RSA public key
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
        openssl_sign(
            $this->buildSignatureString($expiresAt, $body),
            $signature,
            $this->privateKey,
            OPENSSL_ALGO_SHA256
        );

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
        $verification = openssl_verify(
            $this->buildSignatureString($expiresAt, $body),
            base64_decode($signature),
            $this->publicKey,
            OPENSSL_ALGO_SHA256
        );

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

    /**
     * Build the string to be signed
     *
     * @param string $expiresAt EPOCH timestamp
     * @param array $body The body to be sent in the POST request
     * @return string
     */
    private function buildSignatureString($expiresAt, $body) {
        return $expiresAt . '|' . json_encode($body, JSON_UNESCAPED_UNICODE);
    }
}
