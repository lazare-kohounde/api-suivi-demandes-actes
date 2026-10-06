<?php

namespace Database\Factories;

use App\Enums\StatutDemande;
use App\Enums\TypeActe;
use App\Models\Demande;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Demande>
 */
class DemandeFactory extends Factory
{
    protected $model = Demande::class;

    /**
     * Définition des valeurs par défaut valides pour une demande.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'npi' => fake()->numerify('##########'),
            'type_acte' => fake()->randomElement(TypeActe::cases()),
            'nombre_copies' => fake()->numberBetween(1, 5),
            'statut' => StatutDemande::DEPOSEE,
            'motif_rejet' => null,
        ];
    }

    /**
     * État d'une demande passée au statut "en_cours".
     */
    public function enCours(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => StatutDemande::EN_COURS,
        ]);
    }

    /**
     * État d'une demande passée au statut terminal "validee".
     */
    public function validee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => StatutDemande::VALIDEE,
        ]);
    }

    /**
     * État d'une demande passée au statut terminal "rejetee".
     */
    public function rejetee(?string $motif = null): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => StatutDemande::REJETEE,
            'motif_rejet' => $motif ?? 'Dossier incomplet ou non conforme.',
        ]);
    }
}
