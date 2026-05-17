<?php

namespace Modules\Incidents\Entities\Traits;

trait HasTranslatableLabel
{
    /**
     * Resuelve un campo traducible almacenado como JSON {"en":"...","es":"..."}
     * o como string plano (compatibilidad con registros legacy).
     *
     * @param  string|array|null  $value   Valor del campo (string plano o array decodificado)
     * @param  string|null        $locale  Locale a resolver; si es null usa app()->getLocale()
     * @return string
     */
    public function translateLabel(string|array|null $value, ?string $locale = null): string
    {
        if ($value === null) {
            return '';
        }

        $array = \is_array($value) ? $value : json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && \is_array($array)) {
            return $this->resolveFromArray($array, $locale ?? app()->getLocale());
        }

        // String plano (registros legacy)
        return $value;
    }

    private function resolveFromArray(array $translations, string $locale): string
    {
        return $translations[$locale] ?? $translations['es'] ?? $translations['en'] ?? reset($translations) ?? '';
    }
}
