<?php

namespace Modules\Plans\Services;

use Modules\Plans\Entities\Rule;

class RuleEvaluatorService
{
    /**
     * Evalúa si un usuario cumple con una regla matemática específica.
     *
     * @param Rule $rule La regla a evaluar (desde la base de datos).
     * @param array $userData Arreglo con la data actual del usuario. 
     * Ej: ['puntos_acumulados' => 85.5, 'compras_mes' => 3]
     * @return bool
     */
    public function evaluate(Rule $rule, array $userData): bool
    {
        // 1. Extraemos el valor real del usuario basado en la variable de control que dicta la regla.
        // Si el usuario no tiene esa variable registrada en el arreglo, asumimos 0.
        $userValue = $userData[$rule->control_variable] ?? 0;

        // 2. Ejecutamos la comparación matemática según el operador de la regla
        switch ($rule->operator) {
            case '>':
                return $userValue > $rule->value_a;
            case '<':
                return $userValue < $rule->value_a;
            case '=':
                return $userValue == $rule->value_a;
            case '>=':
                return $userValue >= $rule->value_a;
            case '<=':
                return $userValue <= $rule->value_a;
            case 'BETWEEN':
                // Para el operador BETWEEN, evaluamos que esté en el rango de A y B
                return $userValue >= $rule->value_a && $userValue <= $rule->value_b;
            default:
                return false;
        }
    }

    /**
     * Evalúa un conjunto de reglas para determinar si un usuario es apto para un Plan completo.
     * (Asume que TODAS las reglas deben cumplirse con un operador AND lógico).
     *
     * @param \Illuminate\Support\Collection $rules Colección de reglas atadas a un plan.
     * @param array $userData Datos del usuario.
     * @return bool
     */
    public function evaluatePlanRules($rules, array $userData): bool
    {
        // Si el plan no tiene reglas, asumimos que no hay restricciones (retorna true)
        if ($rules->isEmpty()) {
            return true;
        }

        // Iteramos todas las reglas. Si falla una sola, el usuario no aplica para el plan.
        foreach ($rules as $rule) {
            if (!$this->evaluate($rule, $userData)) {
                return false;
            }
        }

        // Si pasó por todo el ciclo sin retornar false, significa que cumplió todas las reglas.
        return true;
    }
}
