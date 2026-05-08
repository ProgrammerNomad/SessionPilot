<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Services;

use ProgrammerNomad\SessionPilot\Models\Device;
use WhichBrowser\Parser;
use Mobile_Detect;

class DeviceService
{
    /**
     * Parse a user-agent string into browser/OS/device components.
     */
    public function parseUserAgent(string $ua): array
    {
        if ( empty($ua) ) {
            return $this->emptyParsed();
        }

        try {
            $result = new Parser(['User-Agent' => $ua]);

            $deviceType = 'desktop';
            $detect     = new Mobile_Detect(null, $ua);

            if ($detect->isTablet()) {
                $deviceType = 'tablet';
            } elseif ($detect->isMobile()) {
                $deviceType = 'mobile';
            }

            return [
                'browser'         => $result->browser->name ?? 'Unknown',
                'browser_version' => $result->browser->version?->value ?? '',
                'os'              => ($result->os->name ?? 'Unknown') . ($result->os->version?->value ? ' ' . $result->os->version->value : ''),
                'device_type'     => $deviceType,
            ];
        } catch (\Throwable) {
            return $this->emptyParsed();
        }
    }

    /**
     * Find or create a device record for this user + UA + IP combination.
     */
    public function resolveDevice(int $userId, string $ua, string $ip): ?Device
    {
        if ( empty($ua) ) {
            return null;
        }

        $parsed = $this->parseUserAgent($ua);
        $name   = trim(($parsed['browser'] ?? '') . ' on ' . ($parsed['os'] ?? ''));

        // Look for an existing device match by browser + OS for this user
        $device = Device::where('user_id', $userId)
            ->where('browser', $parsed['browser'])
            ->where('os', $parsed['os'])
            ->first();

        if ($device) {
            $device->update(['last_seen' => current_time('mysql'), 'last_ip' => $ip]);
            return $device;
        }

        return Device::create([
            'user_id'         => $userId,
            'device_name'     => $name,
            'browser'         => $parsed['browser'],
            'browser_version' => $parsed['browser_version'],
            'os'              => $parsed['os'],
            'device_type'     => $parsed['device_type'],
            'last_ip'         => $ip,
            'created_at'      => current_time('mysql'),
            'last_seen'       => current_time('mysql'),
        ]);
    }

    private function emptyParsed(): array
    {
        return [
            'browser'         => 'Unknown',
            'browser_version' => '',
            'os'              => 'Unknown',
            'device_type'     => 'desktop',
        ];
    }
}
