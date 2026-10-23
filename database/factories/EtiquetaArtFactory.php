<?php

namespace Database\Factories;

use App\Models\EtiquetaArt;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EtiquetaArt>
 */
class EtiquetaArtFactory extends Factory
{
    protected $model = EtiquetaArt::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->unique()->words(fake()->numberBetween(1, 3), true);

        return [
            'ea_nombre' => ucfirst($nombre),
            'ea_slug' => Str::slug($nombre),
        ];
    }
}
