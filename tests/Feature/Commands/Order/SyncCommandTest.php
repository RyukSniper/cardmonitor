<?php

namespace Tests\Feature\Commands\Order;

use App\Models\Apis\Api;
use App\Models\Articles\Article;
use App\Models\Cards\Card;
use App\Models\Expansions\Expansion;
use App\Models\Items\Item;
use App\Models\Orders\Order;
use App\Models\Users\CardmarketUser;
use Carbon\Carbon;
use Cardmonitor\Cardmarket\Api as CardmarketApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class SyncCommandTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->api = factory(Api::class)->create([
            'user_id' => $this->user->id,
            'accessdata' => [
                'app_token' => '8Ts9QDnOCD7gukTV',
                'app_secret' => 'Zy7x2e1gkVcCQat50qd8XtsyMA9qatRN',
                'access_token' => 'LMDxSPkFfCBIYTULl3yHdswrwbYCZEzf',
                'access_token_secret' => 'PgHYR3j8o0Itktu47AbkRRE1foccd91r',
                'url' => CardmarketApi::URL_SANDBOX,
            ],
        ]);

        $this->expansion = factory(Expansion::class)->create([
            'name' => 'Born of the Gods',
        ]);

        $this->expansion->cards()->create(factory(Card::class)->create([
            'cardmarket_product_id' => 265535,
        ])->toArray());

        $this->expansion->cards()->create(factory(Card::class)->create([
            'cardmarket_product_id' => 360083,
        ])->toArray());

        $this->item = factory(Item::class)->create([
            'user_id' => $this->api->user_id,
            'unit_cost' => 1,
        ]);
        $this->item->quantities()->create([
            'effective_from' => '1970-00-00 02:00:00',
            'end' => 9999,
            'quantity' => 1,
            'start' => 1,
            'user_id' => $this->api->user_id,
        ]);
        $this->item = factory(Item::class)->create([
            'user_id' => $this->api->user_id,
            'unit_cost' => 0.5,
        ]);
        $this->item->quantities()->create([
            'effective_from' => '1970-00-00 02:00:00',
            'end' => 9999,
            'quantity' => 2,
            'start' => 1,
            'user_id' => $this->api->user_id,
        ]);
    }

    /**
     * @test
     */
    public function it_syncs_orders_seller_received()
    {
        $this->artisan('order:sync');

        $order = Order::with([
            'articles',
            'sales',
        ])->orderBy('id', 'DESC')
        ->first();

        $this->assertCount(3, $order->articles);
        $this->assertEquals(0.3, $order->articles_cost);

        $this->assertCount(5, $order->sales);
        $this->assertEquals(2.0579, $order->items_cost);

        $this->assertCount(2, CardmarketUser::all());
    }

    /**
     * @test
     */
    public function it_syncs_orders_seller_paid()
    {
        $this->artisan('order:sync --state=paid');

        $order = Order::with([
            'articles',
            'sales',
        ])->orderBy('id', 'DESC')
        ->first();

        $this->assertCount(3, $order->articles);
        $this->assertEquals(0.3, $order->articles_cost);

        $this->assertCount(5, $order->sales);
        $this->assertEquals(2.0579, $order->items_cost);

        $this->assertCount(2, CardmarketUser::all());
    }
}
