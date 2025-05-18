<?php

namespace App\Console\Commands;

use App\Models\Bid;
use App\Models\Merchant;
use App\Notifications\BidWonNotification;
use App\Notifications\BidEndedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProcessEndedBids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-ended-bids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process bids that have ended and notify winners';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process ended bids...');
        
        // Get bids that have ended but haven't been processed yet
        $endedBids = Bid::where('is_bidding_enabled', true)
            ->where('end_date', '<=', now())
            ->where('bidding_processed', false)
            ->whereNotNull('current_bidder_id')
            ->get();
            
        $this->info("Found {$endedBids->count()} ended bids to process.");
        
        foreach ($endedBids as $bid) {
            $this->processEndedBid($bid);
        }
        
        $this->info('Finished processing ended bids.');
        
        return Command::SUCCESS;
    }
    
    /**
     * Process a single ended bid
     */
    private function processEndedBid(Bid $bid)
    {
        try {
            $this->info("Processing bid: {$bid->ulid}");
            
            // Mark the bid as processed
            $bid->bidding_processed = true;
            $bid->save();
            
            // If there's a winner (current bidder), notify them and the junkshop
            if ($bid->current_bidder_id) {
                $winner = Merchant::where('ulid', $bid->current_bidder_id)->first();
                
                if ($winner && $winner->user) {
                    $this->info("Notifying winner: {$winner->user->name}");
                    $winner->user->notify(new BidWonNotification($bid));
                }
                
                // Notify junkshop owner
                if ($bid->junkshop && $bid->junkshop->user) {
                    $this->info("Notifying junkshop owner: {$bid->junkshop->user->name}");
                    $bid->junkshop->user->notify(new BidEndedNotification($bid, $winner));
                }
            } else {
                $this->info("No winner for bid: {$bid->ulid}");
                
                // Notify junkshop owner that no one won
                if ($bid->junkshop && $bid->junkshop->user) {
                    $this->info("Notifying junkshop owner of no winner: {$bid->junkshop->user->name}");
                    $bid->junkshop->user->notify(new BidEndedNotification($bid, null));
                }
            }
            
            $this->info("Successfully processed bid: {$bid->ulid}");
            
        } catch (\Exception $e) {
            $this->error("Error processing bid {$bid->ulid}: {$e->getMessage()}");
            Log::error("Error processing ended bid {$bid->ulid}: {$e->getMessage()}");
        }
    }
}
