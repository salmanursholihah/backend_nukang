<?php

namespace App\Jobs;

use App\Models\VirtualAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBcaCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private array $payload)
    {
        //
    }

    public function handle(): void
    {
        $va = VirtualAccount::where('va_number', $this->payload['VirtualAccountNo'])->first();

        if ($va && !$va->isPaid()) {
            $va->update([
                'status'           => 'PAID',
                'paid_at'          => now(),
                'callback_payload' => $this->payload,
            ]);

            // Trigger event lain di sini, misal: kirim email invoice
        }
    }
}
