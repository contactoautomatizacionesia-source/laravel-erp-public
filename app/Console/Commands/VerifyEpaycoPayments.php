<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\PaymentGateway\Entities\PaymentMethod;
use Modules\UserActivityLog\Traits\LogActivity;

class VerifyEpaycoPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epayco:verify-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify pending Epayco payments via API and update their status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Find Epayco pending payments filtered by payment_method (slug='ePayco')
        $epayco_method = PaymentMethod::where('slug', 'ePayco')->first();

        if (!$epayco_method) {
            $this->error('Epayco payment method not found in database.');
            return 1;
        }

        $found = 0;

        OrderPayment::where('status', 0)
            ->where('payment_method', $epayco_method->id)
            ->where('created_at', '<=', now()->subMinutes(config('services.epayco.cron_min_age_minutes', 5)))
            ->where('created_at', '>=', now()->subDays(config('services.epayco.cron_max_age_days', 7)))
            ->chunkById(100, function ($payments) use (&$found) {
                $found += $payments->count();
                foreach ($payments as $payment) {
                    $this->verifyPayment($payment);
                }
            });

        if ($found === 0) {
            $this->info('No pending Epayco payments found.');
        } else {
            $this->info("Processed {$found} pending Epayco payment(s).");
        }

        return 0;
    }

    /**
     * Verify a single payment against Epayco API
     */
    private function verifyPayment(OrderPayment $payment)
    {
        $txn_id = $payment->txn_id;

        // If txn_id starts with 'epayco_', it means success() never ran
        // and we don't have the real x_ref_payco to query the API
        if (str_starts_with($txn_id, 'epayco_')) {
            // Check if payment is older than 24h with temp txn_id — likely abandoned
            if ($payment->created_at->lt(now()->subHours(24))) {
                $payment->update(['status' => 2]);
                Log::info('Epayco cron: Payment abandonado (temp txn_id)', [
                    'id' => $payment->id,
                    'txn_id' => $txn_id,
                ]);
                $this->warn("Payment #{$payment->id} ({$txn_id}): Marked as abandoned (24h+ with temp txn_id)");
            } else {
                $this->line("Payment #{$payment->id} ({$txn_id}): Skipped (temp txn_id, waiting for success redirect)");
            }
            return;
        }

        // Query Epayco API with the real x_ref_payco
        try {
            $url = config('services.epayco.validation_url') . $txn_id;
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                Log::warning('Epayco cron: API call failed', [
                    'id' => $payment->id,
                    'txn_id' => $txn_id,
                    'status_code' => $response->status(),
                ]);
                $this->error("Payment #{$payment->id} ({$txn_id}): API call failed (HTTP {$response->status()})");
                return;
            }

            $responseData = $response->json();

            if (!isset($responseData['success']) || !$responseData['success']) {
                $this->error("Payment #{$payment->id} ({$txn_id}): API returned error");
                return;
            }

            $data = $responseData['data'];
            $x_cod_response = $data['x_cod_response'];

            // 1=Aceptada, 2=Rechazada, 3=Pendiente, 4=Fallida
            if ($x_cod_response == 1) {
                DB::beginTransaction();
                try {
                    $payment->update(['status' => 1]);

                    $order = $payment->order;
                    if ($order) {
                        $order->update(['is_paid' => 1]);
                        $order->packages()->update(['is_paid' => 1]);
                    }

                    DB::commit();
                    Log::info('Epayco cron: Payment confirmado', [
                        'id' => $payment->id,
                        'txn_id' => $txn_id,
                    ]);
                    LogActivity::successLog('Epayco cron: Payment ' . $txn_id . ' confirmado');
                    $this->info("Payment #{$payment->id} ({$txn_id}): CONFIRMED");
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Epayco cron: Error confirmando payment', [
                        'id' => $payment->id,
                        'txn_id' => $txn_id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("Payment #{$payment->id} ({$txn_id}): DB error - {$e->getMessage()}");
                    return;
                }

            } elseif (in_array($x_cod_response, [2, 4])) {
                $payment->update(['status' => 2]);

                Log::info('Epayco cron: Payment rechazado', [
                    'id' => $payment->id,
                    'txn_id' => $txn_id,
                    'cod_response' => $x_cod_response,
                ]);
                LogActivity::successLog('Epayco cron: Payment ' . $txn_id . ' rechazado');
                $this->warn("Payment #{$payment->id} ({$txn_id}): REJECTED (cod={$x_cod_response})");

            } elseif ($x_cod_response == 3) {
                $this->line("Payment #{$payment->id} ({$txn_id}): Still pending");
            }

        } catch (\Exception $e) {
            Log::error('Epayco cron: Exception verifying payment', [
                'id' => $payment->id,
                'txn_id' => $txn_id,
                'error' => $e->getMessage(),
            ]);
            $this->error("Payment #{$payment->id} ({$txn_id}): Exception - {$e->getMessage()}");
        }
    }
}
