<?php

namespace Tests\Feature;

use App\Enums\StatutDemande;
use App\Models\Demande;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationDemandesTest extends TestCase
{
    use RefreshDatabase;

    public function test_tri_de_la_plus_recente_a_la_plus_ancienne(): void
    {
        $npi = '0123456789';

        $demande1 = Demande::factory()->create([
            'npi' => $npi,
            'created_at' => Carbon::now()->subMinutes(10),
        ]);

        $demande2 = Demande::factory()->create([
            'npi' => $npi,
            'created_at' => Carbon::now()->subMinutes(5),
        ]);

        $demande3 = Demande::factory()->create([
            'npi' => $npi,
            'created_at' => Carbon::now(),
        ]);

        $response = $this->getJson("/api/usagers/{$npi}/demandes");

        $response->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertEquals([$demande3->id, $demande2->id, $demande1->id], $ids);
    }

    public function test_filtre_par_statut_et_rejet_si_statut_inconnu(): void
    {
        $npi = '0123456789';

        Demande::factory()->count(2)->create(['npi' => $npi, 'statut' => StatutDemande::DEPOSEE]);
        Demande::factory()->enCours()->create(['npi' => $npi]);
        Demande::factory()->validee()->create(['npi' => $npi]);

        // Filtrage valide par en_cours
        $response = $this->getJson("/api/usagers/{$npi}/demandes?statut=en_cours");
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.statut', StatutDemande::EN_COURS->value);

        // Statut invalide => 422
        $invalideResponse = $this->getJson("/api/usagers/{$npi}/demandes?statut=statut_inexistant");
        $invalideResponse->assertStatus(422)
            ->assertJson([
                'erreur' => "Le statut doit être l'un des suivants : deposee, en_cours, validee, rejetee.",
            ]);
    }

    public function test_npi_valide_sans_demande_renvoie_200_vide_et_npi_invalide_renvoie_422(): void
    {
        // NPI valide (10 chiffres) sans demande
        $response = $this->getJson('/api/usagers/9999999999/demandes');
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');

        // NPI invalide (longueur incorrecte)
        $erreurResponse = $this->getJson('/api/usagers/12345/demandes');
        $erreurResponse->assertStatus(422)
            ->assertJson([
                'erreur' => 'Le NPI doit comporter exactement 10 chiffres.',
            ]);
    }

    public function test_les_demandes_d_un_autre_usager_n_apparaissent_pas(): void
    {
        $usagerA = '0123456789';
        $usagerB = '9876543210';

        Demande::factory()->count(3)->create(['npi' => $usagerA]);
        Demande::factory()->count(2)->create(['npi' => $usagerB]);

        $response = $this->getJson("/api/usagers/{$usagerA}/demandes");
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        foreach ($response->json('data') as $item) {
            $this->assertEquals($usagerA, $item['npi']);
        }
    }

    public function test_pagination_taille_limites_et_page_suivante(): void
    {
        $npi = '0123456789';
        Demande::factory()->count(25)->create(['npi' => $npi]);

        // Taille par défaut (20) ou spécifiée à 20 => OK
        $responseTaille20 = $this->getJson("/api/usagers/{$npi}/demandes?taille=20");
        $responseTaille20->assertStatus(200)
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 25);

        // Taille supérieure au maximum (21) => 422
        $responseTaille21 = $this->getJson("/api/usagers/{$npi}/demandes?taille=21");
        $responseTaille21->assertStatus(422)
            ->assertJson([
                'erreur' => 'La taille de page ne peut pas dépasser 20.',
            ]);

        // Page 2 avec taille 20 (devrait contenir les 5 demandes restantes)
        $responsePage2 = $this->getJson("/api/usagers/{$npi}/demandes?taille=20&page=2");
        $responsePage2->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }
}
