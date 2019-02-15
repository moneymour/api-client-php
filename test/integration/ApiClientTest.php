<?php

namespace Moneymour;

use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase {
    private $privateKey = '-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAxdXgok3JMP0WNiDHc+d2UGTW5Rba+lX4QeXNzEVagCgeho8Q
aRGTp4sYMvgWqzGkF9EYU/LNkg9LHa1k7fpGEPF4v72uXWDr+zzQpR1+I1o+GC91
P0RC+TBeKSgpd2VRvAm3s3mDbRR/iAzhxAgwx5JzKCiA1QEYUYeG2Ir8KDy/XCeJ
kQg5MaxAeJvW6+7VypI6Fm6otUq9w4SUKqFrLaoUVWS5SID4nSOtpFNNmPHie25V    
HTf+QQ6AMPhR7gJ0jFuBkURBgPDRldKl/hbttjy1dAZ9h1NpYP28pUfDnXDn+/M6
AzEKkU4qKroIr7fbcRz7DYn/eu3kRQ+SDem+JQIDAQABAoIBAE2nEbEjTyFHdfxx
CVjZNXuA2Ui6m+E+9V8IUKspXciH9tNiPFLBb2+IDUuUTXGM3pfqnt30o/P8xMM1
WDSA1Be6cGbykQpeDMJksJDe7q/5r/3WQvM+W/trnMoS1ZPj1R01w0FTJN8f+J7y
T3ueq2bqeQD+RPNWprD5vVgp1agE3zFBMTebfzHdHhVKGUpjaZPVfqyH3ZTjwlnk
4FLVeyzel7k1SXcx9AgXIryY80QPC//OSYsS8Sk/VAnt0YjjsAZ9Qj/9Iz+wX+mX
nAee0xIlJDYsT1BgH0ul5vbOEonCAQepxN2IGFJFzYYvYCiUWhs1y76sidabDvhO
y3U98tECgYEA7mj0PE0JN0qmAYr4osJdnsnRcH7GwIhDjc2r0bzJQv4++29irdJz
2wVAazRBFVCDPVEelKZtss5QGOydkzyQxeN4D4eiRTGaqtN4r8jdoQOAA+ItEbxC
T3xMvX7LtnvugJ8BVkL4sgnpcT0SDW4tDyioN6+zO6jBYtpURnKjMDsCgYEA1G6P
HMXCq/H34D4qZzC5vSkgdX/doCg85REK7yzcb6fRRsIhtGXlMlox4/AsfnP1T/MT
gpcRqqpFGRztR81MA/wSnA/E53FV6qF5tjSjW+UXT6afmzJfPvTC9qoTqdp87CSr
YshUyh1yBR6R/A14JDixhPG4RHx/9yxBr80FRR8CgYEAmiA/xLwIuTYJG/Y7xvzg
iUbvn0pWMyHkikfsTCs/8Y9sKCBaKwVi4LUEcEnXyW/DaGCI5JCzWmUGYxaUyBrf
fQ8RDvkgbpsi4kzUONAid3VLzTbq7AxI0hoJgnf2OoLkLUKeGqYxIOhHgm14vjX7
61DdbyKnPGpcmwuKcACsfwECgYArnpwSS2VCy0ebqwgn8jBhcB1zIxNN/JUscAhv
viVxY+MsBVbIZRff8/dEl597Q7I+fWonKX/+LEJN+suaL8SJGOx1USOcZoA+0nNc
f4h/qRXVTCB/zKMUGUwjPB8XhH6cOpdajEokylEMTg+p0tY1kdKZusyce3gHN/Yw
9Ac9/wKBgQDEgsPT/figOZIRwng11Ak6CDAReVTPiGl+u8+mCC4rFG/rFUZth92k
WFqL3fsPA5mLjP+VWnBJjDcFZjI4rAIHEMLLnVypBJDeXfXUt+LPqMfQibbGDb68
5ULD1FHR/SZC9xXPnYoJQX9/M8PZovQOOLINKsxGMQd/3wYy9KILkA==
-----END RSA PRIVATE KEY-----';

    private $merchantId = '3497897e-bf6a-44d4-89b8-e6fc06acc46b';
    private $merchantSecret = 'zmEVRw0rZlIRGUIabCBduCcVo2LklqNmGgZYdEqOhOFRxabxYtyYu3VZH7awOqQR';

    public function testRequest() {
        $signatureFactory = new SignatureFactory($this->privateKey);
        $client = new ApiClient($this->merchantId, $this->merchantSecret, $signatureFactory);
        $client->setBaseUrl('http://localhost:3000');

        try {
            $jsonResponse = $client->request([
                'phoneNumber' => '+39' . rand(1000000000, 9999999999),
                'orderId' => '12345678',
                'productName' => 'GoPro Hero7',
                'productDescription' => '',
                'amount' => 500,
            ]);

            static::assertTrue(isset($jsonResponse['status']));
            static::assertEquals($jsonResponse['status'], 'accepted');
        } catch (\Exception $e) {
            static::fail($e->getMessage());
        }
    }
}
