<?php

namespace App\Console\Commands;
use App\Models\Product;
use App\Events\AuctionEnded;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckAuctionEndTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:check-end-times';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
   public function handle()
    {
        $endedProducts = Product::where('auction_end_time', '<=', Carbon::now())
            ->whereNull('winner_id')
            ->where('is_active', true)
            ->get();
        foreach ($endedProducts as $product) {
            $highestBid = $product->bids()->orderBy('amount', 'desc')->first();
            
            if ($highestBid) {
                $product->update([
                    'winner_id' => $highestBid->user_id,
                    'is_active' => false
                ]);
            } else {
                $product->update(['is_active' => false]);
            }

            event(new AuctionEnded($product));
        }

        $this->info('Processed ' . $endedProducts->count() . ' ended auctions');
    }
}
