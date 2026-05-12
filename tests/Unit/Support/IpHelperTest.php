<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Tests\Unit\Support;

use ProgrammerNomad\SessionPilot\Support\IpHelper;
use ProgrammerNomad\SessionPilot\Tests\TestCase;
use Brain\Monkey\Functions;

class IpHelperTest extends TestCase
{
    // -----------------------------------------------------------------------
    // anonymize()
    // -----------------------------------------------------------------------

    public function test_anonymize_ipv4_zeroes_last_octet(): void
    {
        $this->assertSame('192.168.1.0', IpHelper::anonymize('192.168.1.42'));
    }

    public function test_anonymize_ipv4_preserves_first_three_octets(): void
    {
        $this->assertSame('10.0.0.0', IpHelper::anonymize('10.0.0.255'));
    }

    public function test_anonymize_ipv6_zeroes_last_80_bits(): void
    {
        $ip     = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
        $result = IpHelper::anonymize($ip);

        // Result must be valid IPv6
        $this->assertTrue((bool) filter_var($result, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6), "Expected valid IPv6, got: $result");

        // First 48 bits (6 bytes) must be preserved; last 80 bits (10 bytes) zeroed
        $packed_in     = inet_pton($ip);
        $packed_out    = inet_pton($result);

        $this->assertSame(substr($packed_in, 0, 6), substr($packed_out, 0, 6), 'First 6 bytes must be unchanged');
        $this->assertSame(str_repeat("\x00", 10), substr($packed_out, 6), 'Last 10 bytes must be zeroed');
    }

    // -----------------------------------------------------------------------
    // getClientIp() - tests via $_SERVER superglobal manipulation
    // -----------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        // Stub WP functions used inside getClientIp()
        Functions\when('sanitize_text_field')->returnArg();
        Functions\when('wp_unslash')->returnArg();
    }

    public function test_get_client_ip_falls_back_to_remote_addr(): void
    {
        unset($_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['HTTP_X_REAL_IP']);
        $_SERVER['REMOTE_ADDR'] = '203.0.113.1';

        $this->assertSame('203.0.113.1', IpHelper::getClientIp());
    }

    public function test_get_client_ip_prefers_cf_connecting_ip(): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '198.51.100.10';
        $_SERVER['REMOTE_ADDR']           = '10.0.0.1';

        $this->assertSame('198.51.100.10', IpHelper::getClientIp());

        unset($_SERVER['HTTP_CF_CONNECTING_IP']);
    }

    public function test_get_client_ip_takes_first_from_x_forwarded_for(): void
    {
        unset($_SERVER['HTTP_CF_CONNECTING_IP']);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.20, 10.0.0.1, 172.16.0.5';
        $_SERVER['REMOTE_ADDR']          = '10.0.0.1';

        $this->assertSame('198.51.100.20', IpHelper::getClientIp());

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    public function test_get_client_ip_rejects_invalid_ip(): void
    {
        unset($_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['HTTP_X_REAL_IP']);
        $_SERVER['REMOTE_ADDR'] = 'not-an-ip';

        $this->assertSame('', IpHelper::getClientIp());
    }

    public function test_get_client_ip_returns_empty_string_when_no_headers(): void
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $h) {
            unset($_SERVER[$h]);
        }

        $this->assertSame('', IpHelper::getClientIp());
    }
}
