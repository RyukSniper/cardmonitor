<?php

namespace App\Jobs\Articles;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAll implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            set_time_limit(0);
            $this->user->cardmarketApi->syncAllSellerOrders();
            $this->user->cardmarketApi->syncAllArticles();
        }
        catch (\Exception $exc) {
            dd($exc);
        }
    }

    public function processing()
    {
        $this->user->api()->update([
            'is_syncing_articles' => true,
        ]);
    }

    public function processed()
    {
        $this->user->api()->update([
            'is_syncing_articles' => false,
        ]);
    }
}
