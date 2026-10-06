<?php

namespace Tests\Feature;

use App\Enums\StatutDemande;
use App\Enums\TypeActe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepotDemandeTest extends TestCase
{
    use RefreshDatabase;

    public function test_depot_valide_cree_une_demande_avec_statut_deposee_et_npi_en_chaine(): void
    {
        $payload = [
            'npi' => '0123456789',
            'type_acte' => TypeActe::ACTE_NAISSANCE->value,
            'nombre_copies' => 2,
        ];

        $response = $this->postJson('/api/demandes', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'npi' => '0123456789',
                'type_acte' => TypeActe::ACTE_NAISSANCE->value,
                'nombre_copies' => 2,
                'statut' => StatutDemande::DEPOSEE->value,
                'motif_rejet' => null,
            ]);

        $this->assertDatabaseHas('demandes', [
            'npi' => '0123456789',
            'statut' => StatutDemande::DEPOSEE->value,
            'nombre_copies' => 2,
        ]);
    }

    public function test_npi_invalide_est_refuse_avec_message_clair(): void
    {
        // 9 chiffres
        $this->postJson('/api/demandes', [
            'npi' => '123456789',
            'type_acte' => TypeActe::ACTE_NAISSANCE->value,
            'nombre_copies' => 1,
        ])->assertStatus(422)
            ->assertJson([
                'erreur' => 'Le NPI doit comporter exactement 10 chiffres.',
            ]);

        // 11 chiffres
        $this->postJson('/api/demandes', [
            'npi' => '12345678901',
            'type_acte' => TypeActe::ACTE_NAISSANCE->value,
            'nombre_copies' => 1,
        ])->assertStatus(422)
            ->assertJson([
                'erreur' => 'Le NPI doit comporter exactement 10 chiffres.',
            ]);

        // Contenant des lettres
        $this->postJson('/api/demandes', [
            'npi' => '012345678A',
            'type_acte' => TypeActe::ACTE_NAISSANCE->value,
            'nombre_copies' => 1,
        ])->assertStatus(422)
            ->assertJson([
                'erreur' => 'Le NPI doit comporter exactement 10 chiffres.',
            ]);
    }

    public function test_type_acte_inconnu_est_refuse(): void
    {
        $response = $this->postJson('/api/demandes', [
            'npi' => '0123456789',
            'type_acte' => 'passeport_express',
            'nombre_copies' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'erreur' => "Le type d'acte doit être l'un des suivants : acte_naissance, casier_judiciaire, certificat_residence.",
            ]);
    }

    public function test_nombre_copies_limites_et_intervalles(): void
    {
        // copies = 0 => 422
        $this->postJson('/api/demandes', [
            'npi' => '0123456789',
            'type_acte' => TypeActe::CASIER_JUDICIAIRE->value,
            'nombre_copies' => 0,
        ])->assertStatus(422)
            ->assertJson([
                'erreur' => 'Le nombre de copies doit être compris entre 1 et 5.',
            ]);

        // copies = 6 => 422
        $this->postJson('/api/demandes', [
            'npi' => '0123456789',
            'type_acte' => TypeActe::CASIER_JUDICIAIRE->value,
            'nombre_copies' => 6,
        ])->assertStatus(422)
            ->assertJson([
                'erreur' => 'Le nombre de copies doit être compris entre 1 et 5.',
            ]);

        // copies = 1 => 201
        $this->postJson('/api/demandes', [
            'npi' => '0123456789',
            'type_acte' => TypeActe::CERTIFICAT_RESIDENCE->value,
            'nombre_copies' => 1,
        ])->assertStatus(201);

        // copies = 5 => 201
        $this->postJson('/api/demandes', [
            'npi' => '0123456789',
            'type_acte' => TypeActe::CERTIFICAT_RESIDENCE->value,
            'nombre_copies' => 5,
        ])->assertStatus(201);
    }

    public function test_statut_fourni_dans_la_requete_est_ignore_et_force_a_deposee(): void
    {
        $response = $this->postJson('/api/demandes', [
            'npi' => '0123456789',
            'type_acte' => TypeActe::ACTE_NAISSANCE->value,
            'nombre_copies' => 1,
            'statut' => StatutDemande::VALIDEE->value,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'statut' => StatutDemande::DEPOSEE->value,
            ]);

        $this->assertDatabaseHas('demandes', [
            'npi' => '0123456789',
            'statut' => StatutDemande::DEPOSEE->value,
        ]);
    }
}
