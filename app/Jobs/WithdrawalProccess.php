<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\WithdrawHistory;
use App\Http\Services\WalletService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class WithdrawalProccess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $withdrawal;
    protected $customer;
    /**
     * Create a new job instance.
     */
    public function __construct($withdrawal, $customer)
    {
        $this->withdrawal = $withdrawal;
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if(!$withdrawalHistory = WithdrawHistory::find($this->withdrawal)){
            storeException("Withdrawal-proccess job", "Withdrawal id : $this->withdrawal \nWithdrawal history not found");
            return;
        }
        $withdrawalHistory->customer_id = $this->customer;
        $walletService = new WalletService();
        $walletService->walletWithdrawalProccess($withdrawalHistory);

    }
}
