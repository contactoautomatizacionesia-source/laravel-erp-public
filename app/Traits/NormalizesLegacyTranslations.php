<?php

namespace App\Traits;

trait NormalizesLegacyTranslations
{
    /**
     * Normaliza los campos de traducción antiguos que son texto plano.
     * Convierte texto simple en JSON asignándolo a la clave por defecto (ej: 'en').
     *
     * @param array $fields Campos específicos a revisar (opcional).
     * @param string $defaultLocale El idioma al que asignar el texto antiguo (default: 'en').
     * @return void
     */
    public function fixLegacyTranslations(array $fields = [], string $defaultLocale = 'en')
    {
        if (empty($fields) && property_exists($this, 'translatable')) {
            $fields = $this->translatable;
        }

        foreach ($fields as $field) {
            $rawValue = $this->getRawOriginal($field);
            if (!empty($rawValue) && !$this->isJson($rawValue)) {
                $this->setTranslation($field, $defaultLocale, $rawValue);
            }
        }
    }

    /**
     * Helper interno para validar si un string es JSON.
     * @param mixed $string
     * @return bool
     */
    protected function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}