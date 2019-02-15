<?php

namespace Moneymour;

use PHPUnit\Framework\TestCase;

class SignatureFactoryTest extends TestCase {
    private $secret = 'zmEVRw0rZlIRGUIabCBduCcVo2LklqNmGgZYdEqOhOFRxabxYtyYu3VZH7awOqQR';
    private $merchantId = '3497897e-bf6a-44d4-89b8-e6fc06acc46b';

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
            'productName' => 'GoPro Hero7',
            'productDescription' => '',
            'amount' => 500,
            'secret' => $this->secret,
            'merchantId' => $this->merchantId
        ];

        $expiresAt = time() . '';

        $factory = new SignatureFactory($privateKey, $publicKey);
        $signature = $factory->build($expiresAt, $body);

        try {
            static::assertTrue($factory->verify($signature, $expiresAt, $body));
        } catch (\Exception $e) {
            static::fail($e->getMessage());
        }
    }
}
