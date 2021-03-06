<?php

namespace Tests\Unit\Models\Expansions;

use App\Models\Cards\Card;
use App\Models\Expansions\Expansion;
use App\Models\Localizations\Language;
use App\Models\Localizations\Localization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RelationshipAssertions;

class ExpansionTest extends TestCase
{
    use RelationshipAssertions;

    /**
     * @test
     */
    public function it_has_many_localizations()
    {
        $model = factory(Expansion::class)->create();

        $this->assertMorphMany($model, Localization::class, 'localizations', 1);
    }

    /**
     * @test
     */
    public function a_default_localization_is_created_after_model_is_created()
    {
        $model = factory(Expansion::class)->create();

        $this->assertCount(1, $model->localizations);

        $this->assertDatabaseHas('localizations', [
            'localizationable_type' => Expansion::class,
            'localizationable_id' => $model->id,
            'language_id' => Language::DEFAULT_ID,
            'name' => $model->name,
        ]);
    }

    /**
     * @test
     */
    public function it_has_many_cards()
    {
        $model = factory(Expansion::class)->create();
        $related = factory(Card::class)->create([
            'expansion_id' => $model->id,
        ]);

        $this->assertHasMany($model, $related, 'cards');
    }

    /**
     * @test
     */
    public function it_sets_its_abbrecation_from_image_path()
    {
        $model = new Expansion();

        $model->abbreviationFromCardImagePath = './img/items/1/BNG/265535.jpg';

        $this->assertEquals('bng', $model->abbreviation);
    }

    /**
     * @test
     */
    public function it_can_be_created_from_cardmarket()
    {
        $cardmarketExpansion = [
            "idExpansion" => 1408,
            "enName" => "Filler Cards",
            "localization" => [
                0 => [
                    "name" => "Filler Cards",
                    "idLanguage" => "1",
                    "languageName" => "English",
                ],
                1 => [
                    "name" => "Filler Cards",
                    "idLanguage" => "2",
                    "languageName" => "French",
                ],
                2 => [
                    "name" => "Filler Cards",
                    "idLanguage" => "3",
                    "languageName" => "German",
                ],
                3 => [
                    "name" => "Filler Cards",
                    "idLanguage" => "4",
                    "languageName" => "Spanish",
                ],
                4 => [
                    "name" => "Filler Cards",
                    "idLanguage" => "5",
                    "languageName" => "Italian",
                ],
            ],
            "abbreviation" => "MGFC",
            "icon" => 203,
            "releaseDate" => "1900-01-01T00:00:00+0100",
            "isReleased" => true,
            "idGame" => "1",
            "links" => [
                0 => [
                    "rel" => "singles",
                    "href" => "/expansions/1408/singles",
                    "method" => "GET",
                ],
            ],
        ];
        $expansion = Expansion::createFromCardmarket($cardmarketExpansion);
        $this->assertEquals(1408, $expansion->cardmarket_expansion_id);
        $this->assertEquals('Filler Cards', $expansion->name);
        $this->assertEquals('MGFC', $expansion->abbreviation);
        $this->assertTrue($expansion->is_released);
        $this->assertEquals('1900-01-01', $expansion->released_at->format('Y-m-d'));
        $this->assertEquals(1, $expansion->game_id);
        $this->assertCount(5, $expansion->localizations);
    }
}
