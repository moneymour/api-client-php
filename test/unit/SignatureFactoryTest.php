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
            'phoneNumber' => '+39' . rand(1000000000, 9999999999),
            'orderId' => '12345678',
            'amount' => 500,
            'products' => [
                [
                    'name' => 'GoPro Hero7 É',
                    'amount' => 500
                ]
            ]
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
