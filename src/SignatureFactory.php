<?php

namespace Moneymour;

class SignatureFactory {

    const developmentMoneymourPublicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0xjRECYz5oKWyjmCOQc3
x9D9eC8v79iRsMScCu9fHesM0Znkto73tvfUhGDmTms6NIgVDDWzLwf40rRPFkxK
zuw0ZGRJDSRw7dGNQ/yjM+R3WOE9HAaUjtX6rX6t/urvQW0XN057/clfMeebEQR0
knJhOuukrgaZC54XbMitlGNk4UxXkbaTD+h0UoSAqxVSM1riUTbNef6mWWHOZGB+
Dpi6lNI6Y6WX9w4nTwXiOWkthM+jsGTV1Vz49UB8gDmcZSgBp1dRLVzTm7NH8H3v
rgrjADr43io1gUC1N0zrXxzyX+xNLABkLW+Oi3lbSXSFFxCjdl2vlUs2SSW78EMD
KwIDAQAB
-----END PUBLIC KEY-----';

    const stageMoneymourPublicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAty+wRd3ArC2RfUA1Ypua
KkXp/bEs6KgRX68NrenZ3yk3jx7M72EeQS0tgNvWVfVC3NdhX9rJCM2JgkmlDIOk
keRj+S2BWJ1sIo5a/Haxkgm745Vd1McOz+VciWPY5p9OJB7xQX+sKhrfKzjfWLAs
+e3Kre/l5OzhvzHf7yvzJueRHHvqX9epygVBhaYwiS+VtUhNPmBB0CwTkAUMTIQ1
u2iv0c/beutBHshexO51AzGsH/LHy5LyJcgZYQ3YYRc/KABJb6A02I/V7H1Aa8Uz
qKrx4ZKW1h7t8q3gCBvPRe6CVft/yHISE9UL7sflQnelBVdLO5Miy9MEZDRJUVCY
TwIDAQAB
-----END PUBLIC KEY-----';

    const sandboxMoneymourPublicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0A0WavOzsKJn0SHdQrF1
ThSdWly629clB6y4crZ0D778rQGmBFSkvdceIt3fERGYuCyWHFtiOS6pIlfIcJgR
hDoA0N6UKlT777KH8s/B3+cMnEHhPBiD0Lq8w8yjWdal1BvFkuUOionNm9q9OA2g
uD4BWv9WZBm1/mB7kNczvEGxvN1E601lJztU8WahWH1w0fEmRsW9BpcVrqlqfkuw
hPUnjeVXWDTX05gVyAr/Do6yNcJi6M5/4hU6EcQiQ+d1pHgd/mCLN/hoiPvGG5y9
UrR2av3bgfefF5QU7ZRzjMV3X7bGXPG2pH+L8kbHCPB78j5rzxHViSKIpPKkMg+P
FwIDAQAB
-----END PUBLIC KEY-----';

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
     * If you don't give any public key, the official public key will be used depending on the environment.
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
     * @param string $environment The API environment: production, sandbox, stage or development. Default: sandbox
     * @return boolean True if the signature is verified
     * @throws \Exception
     */
    public function verify($signature, $expiresAt, $body, $environment = ApiClient::ENVIRONMENT_SANDBOX) {
        ApiClient::validateEnvironment($environment);

        if (!$this->publicKey) {
            $this->publicKey = constant('self::' . $environment . 'MoneymourPublicKey');
        }

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
