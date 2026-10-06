<?php

namespace Tests\Feature;

use App\Enums\StatutDemande;
use App\Models\Demande;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatistiquesTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_quatre_statuts_sont_toujours_presents_avec_les_bons_comptes(): void
    {
        // État initial vide : les 4 statuts doivent être présents avec 0
        $responseVide = $this->getJson('/api/demandes/stats');
        $responseVide->assertStatus(200)
            ->assertExactJson([
                StatutDemande::DEPOSEE->value => 0,
                StatutDemande::EN_COURS->value => 0,
                StatutDemande::VALIDEE->value => 0,
                StatutDemande::REJETEE->value => 0,
            ]);

        // Création de demandes réparties sur différents statuts
        Demande::factory()->count(3)->create(['statut' => StatutDemande::DEPOSEE]);
        Demande::factory()->count(2)->enCours()->create();
        Demande::factory()->count(1)->validee()->create();
        // Aucun rejet pour vérifier la présence du 0

        $response = $this->getJson('/api/demandes/stats');
        $response->assertStatus(200)
            ->assertExactJson([
                StatutDemande::DEPOSEE->value => 3,
                StatutDemande::EN_COURS->value => 2,
                StatutDemande::VALIDEE->value => 1,
                StatutDemande::REJETEE->value => 0,
            ]);
    }
}
