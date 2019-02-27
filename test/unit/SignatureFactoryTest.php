<?php

namespace Moneymour;

use PHPUnit\Framework\TestCase;

class SignatureFactoryTest extends TestCase {
    public function testBuildAndVerify() {
        $key = openssl_pkey_new([
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($key, $privateKey);
        $publicKey = openssl_pkey_get_details($key);
        $publicKey = $publicKey["key"];

        $body = [
            'merchantId' => '287cefd4-d0e5-45d7-a853-35b9426996ca',
            'orderId' => '12345678',
            'loanStatus' => 'active'
        ];

        $expiresAt = time() . '';

        $factory = new SignatureFactory($privateKey, $publicKey);
        $signature = $factory->build($expiresAt, $body);

        $isValid = false;

        try {
            $isValid = $factory->verify($signature, $expiresAt, $body);
        } catch (\Exception $e) {
            static::fail($e->getMessage());
        }

        static::assertTrue($isValid);
    }
}
