<?php

namespace App\Models\Expansions;

use App\Models\Cards\Card;
use App\Traits\HasLocalizations;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expansion extends Model
{
    use HasLocalizations;

    const GAME_ID_MAGIC = 1;
    const GAME_ID_YUGIOH = 3;

    const GAMES = [
        self::GAME_ID_MAGIC => 'Magic the Gathering',
        self::GAME_ID_YUGIOH => 'Yu-Gi-Oh',
    ];

    protected $dates = [
        'released_at',
    ];

    protected $guarded = [];

    public $incrementing = false;

    /**
     * The booting method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function($model)
        {
            $model->id = $model->cardmarket_expansion_id;

            if (! $model->game_id) {
                $model->game_id = 1;
            }

            return true;
        });
    }

    public static function createFromCsv(array $row) : self
    {
        $model = self::create([
            'cardmarket_expansion_id' => $row[0],
            'name' => $row[1],
            'abbreviation' => $row[6],
            'icon' => $row[7],
            'is_released' => $row[9] ?: 0,
            'released_at' => ($row[9] == 1 ? new Carbon($row[8]) : null)
        ]);
        for ($i = 2; $i <= 5; $i++) {
            $model->localizations()->create([
                'language_id' => $i,
                'name' => $row[$i],
            ]);
        }

        return $model;
    }

    public static function createFromCardmarket(array $cardmarketExpansion) : self
    {
        $model = self::create([
            'cardmarket_expansion_id' => $cardmarketExpansion['idExpansion'],
            'name' => $cardmarketExpansion['enName'],
            'abbreviation' => $cardmarketExpansion['abbreviation'],
            'icon' => $cardmarketExpansion['icon'],
            'is_released' => $cardmarketExpansion['isReleased'],
            'released_at' => ($cardmarketExpansion['isReleased'] == 'true' ? new Carbon($cardmarketExpansion['releaseDate']) : null)
        ]);
        foreach ($cardmarketExpansion['localization'] as $key => $localization) {
            if ($localization['idLanguage'] == 1) {
                continue;
            }

            $model->localizations()->create([
                'language_id' => $localization['idLanguage'],
                'name' => $localization['name'],
            ]);
        }

        return $model;
    }

    public static function createOrUpdateFromCardmarket(array $cardmarketExpansion) : self
    {
        $values = [
            'abbreviation' => $cardmarketExpansion['abbreviation'],
            'cardmarket_expansion_id' => $cardmarketExpansion['idExpansion'],
            'game_id' => $cardmarketExpansion['idGame'],
            'icon' => $cardmarketExpansion['icon'],
            'is_released' => $cardmarketExpansion['isReleased'],
            'name' => $cardmarketExpansion['enName'],
            'released_at' => ($cardmarketExpansion['isReleased'] == 'true' ? new Carbon($cardmarketExpansion['releaseDate']) : null)
        ];

        $attributes = [
            'cardmarket_expansion_id' => $cardmarketExpansion['idExpansion'],
        ];

        $model = self::updateOrCreate($attributes, $values);

        if ($model->wasRecentlyCreated) {
            foreach ($cardmarketExpansion['localization'] as $key => $localization) {
                if ($localization['idLanguage'] == 1) {
                    continue;
                }

                $model->localizations()->create([
                    'language_id' => $localization['idLanguage'],
                    'name' => $localization['name'],
                ]);
            }
        }

        return $model;
    }

    public function setAbbreviationFromCardImagePathAttribute($value) : void
    {
        $parts = explode('/', $value);
        $this->attributes['abbreviation'] = strtolower($parts[4]);
    }

    public function getIconPositionAttribute() : array
    {
        return [
            'x' => (($this->icon % 10) * 21 * -1),
            'y' => ((floor($this->icon / 10) * 21) * -1),
        ];
    }

    public function getIconPositionStringAttribute() : string
    {
        return $this->icon_position['x'] . 'px ' . $this->icon_position['y'] . 'px';
    }

    public function cards() : HasMany
    {
        return $this->hasMany(Card::class, 'expansion_id');
    }
}
