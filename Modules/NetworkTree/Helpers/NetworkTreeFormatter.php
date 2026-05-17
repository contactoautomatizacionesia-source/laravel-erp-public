<?php

namespace Modules\NetworkTree\Helpers;

class NetworkTreeFormatter
{
    public static function resolveTranslatable(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return $value;
        }
        $locale = app()->getLocale();
        return $decoded[$locale] ?? $decoded['es'] ?? $decoded['en'] ?? reset($decoded);
    }

    public static function resolvePlanColor(?string $colorJson): ?string
    {
        if (! $colorJson) {
            return null;
        }
        $decoded = json_decode($colorJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return $colorJson;
        }
        return $decoded['primaryColor'] ?? $decoded['primary'] ?? $decoded['badge'] ?? reset($decoded);
    }

    public static function resolvePlanIcon(?string $stylesJson): ?string
    {
        if (! $stylesJson) {
            return null;
        }
        $decoded = json_decode($stylesJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return null;
        }
        $icon = $decoded['icon'] ?? null;
        return is_string($icon) && $icon !== '' ? $icon : null;
    }
}
