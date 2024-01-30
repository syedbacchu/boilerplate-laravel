<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Services\CurrencyService;

class CoinRateUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coin-rate-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        storeException("updateCoinUsdPrice:",'called');
        $currency = new CurrencyService();
        $response = $currency->updateCoinRateCorn();
        if(!$response["success"]) storeException("CoinRateUpdate:",$response["message"]);
        storeException("updateCoinUsdPrice:",$response["message"]);
    }
}
