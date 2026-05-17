<?php

namespace Modules\CycleClosure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\CycleClosure\Entities\BlockedPeriod;

class BlockedPeriodMiddleware
{
    /**
     * Reject write operations (POST/PUT/PATCH/DELETE) targeting dates
     * within a closed and blocked cycle period.
     *
     * The request may pass a `transaction_date` field; if absent,
     * today's date is used as a fallback.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $writeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

        if (in_array($request->method(), $writeMethods, true)) {
            $date = $request->input('transaction_date', now()->toDateString());

            if (BlockedPeriod::isDateBlocked($date)) {
                return $this->blockedResponse($request);
            }
        }

        return $next($request);
    }

    private function blockedResponse(Request $request): mixed
    {
        $message = __('cycleclosure::messages.period_blocked');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors(['transaction_date' => $message]);
    }
}
