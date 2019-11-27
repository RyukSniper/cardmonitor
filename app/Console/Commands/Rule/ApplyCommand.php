<?php

namespace App\Console\Commands\Rule;

use App\Models\Articles\Article;
use App\Models\Rules\Rule;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class ApplyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rule:apply {user} {--sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Applies all active rules';

    protected $user;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->user = User::findOrFail($this->argument('user'));

        $this->user->update([
            'is_applying_rules' => true,
        ]);

        $rules = Rule::where('user_id', $this->user->id)
            ->where('active', true)
            ->orderBy('order_column', 'ASC')
            ->get();

        Rule::reset($this->user->id);

        foreach ($rules as $rule) {
            $rule->apply($this->option('sync'));
        }

        if ($this->option('sync')) {
            $this->sync();
        }

        $this->user->update([
            'is_applying_rules' => false,
        ]);
    }

    protected function sync()
    {
        $cardmarketApi = $this->user->cardmarketApi;

        $this->user->articles()->whereNotNull('rule_id')
            // ->where('price_rule', '>=', 0.02)
            ->whereNull('order_id')
            ->orderBy('cardmarket_article_id', 'ASC')
            ->chunk(100, function ($articles) use ($cardmarketApi) {
                $articles->sync($cardmarketApi);
                usleep(50);
        });
    }
}
