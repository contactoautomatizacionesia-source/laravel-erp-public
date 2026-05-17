<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Modules\GeneralSetting\Entities\ParameterSetting;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureKycIsUpdated
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        // 1. Verificamos si la ruta o el usuario están exentos
        if ($this->shouldSkipValidation($user, $request)) {
            return $next($request);
        }

        // 2. Validamos el KYC
        if (!$this->isKycValid($user)) {
            return redirect()->route('kyc.update.index')
                ->with('status', 'update-required')
                ->with('warning', __('Tus datos han expirado. Por favor actualízalos para continuar.'));
        }

        // 3. Correcto, continúa
        return $next($request);
    }

    private function shouldSkipValidation(?User $user, Request $request): bool
    {
        $shouldSkip = false;

        if ( (!$user || !$user->role || $user->role->type !== 'customer') || ($request->routeIs('kyc.update.*', 'logout')) ) {
            $shouldSkip = true;
        }

        return $shouldSkip;
    }

    private function isKycValid(User $user): bool
    {
        $ttlSetting = ParameterSetting::where('slug', 'entrepreneur-data-ttl')
            ->where('is_active', 1)
            ->first();

        if (!$ttlSetting || $ttlSetting->value_limit == 0) {
            return true;
        }

        $ttlValue = $ttlSetting->value_limit;
        $cacheKey = "user_{$user->id}_kyc_valid_ttl_{$ttlValue}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($user, $ttlValue) {
            $lastUpdate = $user->data_verified_at ?? $user->created_at;
            $expirationDate = Carbon::parse($lastUpdate)->addMonths($ttlValue);

            return now()->lessThanOrEqualTo($expirationDate);
        });
    }
}
