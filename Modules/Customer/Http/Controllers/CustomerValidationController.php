<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Importante para rendimiento

class CustomerValidationController extends Controller
{
    public function checkAvailability(Request $request)
    {
        // 1. Validación ligera de entrada
        $request->validate([
            'field' => 'required|string',
            'value' => 'required|string',
            'customer_id' => 'nullable|integer'
        ]);

        $field = $request->field;
        $value = $request->value;
        $userId = $request->customer_id;

        $exists = false;
        $message = '';

        if (in_array($field, ['email', 'secondary_email'])) {            
            $existsInUsers = DB::table('users')
                ->where('email', $value)
                ->when($userId, fn($q) => $q->where('id', '!=', $userId))
                ->exists();

            if ($existsInUsers) {
                return $this->sendErrorResponse(__('El correo ya está registrado como cuenta principal.'));
            }

            $existsInProfiles = DB::table('customer_profiles')
                ->where('secondary_email', $value)
                ->when($userId, fn($q) => $q->where('user_id', '!=', $userId))
                ->exists();

            if ($existsInProfiles) {
                return $this->sendErrorResponse(__('El correo ya está registrado como alternativo de otro cliente.'));
            }
        }
        
        elseif (in_array($field, ['whatsapp', 'phone', 'phone_calls', 'phone_office'])) {
            $existsInUsers = DB::table('users')
                ->where('phone', $value)
                ->when($userId, fn($q) => $q->where('id', '!=', $userId))
                ->exists();

            if ($existsInUsers) {
                return $this->sendErrorResponse(__('Este número ya está registrado como principal.'));
            }
            
            $existsInProfiles = DB::table('customer_profiles')
                ->where(function($query) use ($value) {
                    $query->where('whatsapp', $value)
                          ->orWhere('phone_calls', $value)
                          ->orWhere('phone_office', $value);
                })
                ->when($userId, fn($q) => $q->where('user_id', '!=', $userId))
                ->exists();

            if ($existsInProfiles) {
                return $this->sendErrorResponse(__('Este número ya está asociado a otro cliente.'));
            }
        }

        elseif ($field === 'document_number') {
            $exists = DB::table('customer_profiles')
                ->where('document_number', $value)
                ->when($userId, fn($q) => $q->where('user_id', '!=', $userId))
                ->exists();

            if ($exists) {
                return $this->sendErrorResponse(__('El número de documento ya está registrado.'));
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => __('Disponible'),
            'is_available' => true
        ], 200);
    }

    /**
     * Helper para retornar error rápido y estandarizado
     */
    private function sendErrorResponse($msg)
    {
        return response()->json([
            'status' => 'error',
            'message' => $msg,
            'is_available' => false
        ], 422);
    }
}