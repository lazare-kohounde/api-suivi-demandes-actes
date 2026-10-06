<?php

namespace Tests\Feature;

use App\Enums\StatutDemande;
use App\Models\Demande;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CycleDeVieTest extends TestCase
{
    use RefreshDatabase;

    public function test_transitions_valides_du_cycle_de_vie(): void
    {
        $demande = Demande::factory()->create(['statut' => StatutDemande::DEPOSEE]);

        // 1. deposee -> en_cours
        $responseEnCours = $this->patchJson("/api/demandes/{$demande->id}/statut", [
            'statut' => StatutDemande::EN_COURS->value,
        ]);
        $responseEnCours->assertStatus(200)
            ->assertJsonPath('statut', StatutDemande::EN_COURS->value)
            ->assertJsonPath('statut_libelle', 'En cours de traitement');

        // 2. en_cours -> validee
        $responseValidee = $this->patchJson("/api/demandes/{$demande->id}/statut", [
            'statut' => StatutDemande::VALIDEE->value,
        ]);
        $responseValidee->assertStatus(200)
            ->assertJsonPath('statut', StatutDemande::VALIDEE->value)
            ->assertJsonPath('statut_libelle', 'Validée');

        // 3. Autre demande : en_cours -> rejetee avec motif
        $demandeRejet = Demande::factory()->enCours()->create();
        $responseRejet = $this->patchJson("/api/demandes/{$demandeRejet->id}/statut", [
            'statut' => StatutDemande::REJETEE->value,
            'motif' => 'Document officiel manquant.',
        ]);
        $responseRejet->assertStatus(200)
            ->assertJsonPath('statut', StatutDemande::REJETEE->value)
            ->assertJsonPath('statut_libelle', 'Rejetée')
            ->assertJsonPath('motif_rejet', 'Document officiel manquant.');
    }

    public function test_saut_d_etape_interdit_depuis_deposee_renvoie_409(): void
    {
        $demande = Demande::factory()->create(['statut' => StatutDemande::DEPOSEE]);

        // deposee -> validee (409)
        $this->patchJson("/api/demandes/{$demande->id}/statut", [
            'statut' => StatutDemande::VALIDEE->value,
        ])->assertStatus(409)
          ->assertJson([
              'erreur' => "Impossible de passer de 'deposee' à 'validee' : la demande doit d'abord être en cours de traitement.",
          ]);

        // deposee -> rejetee (409 même avec motif)
        $this->patchJson("/api/demandes/{$demande->id}/statut", [
            'statut' => StatutDemande::REJETEE->value,
            'motif' => 'Tentative de rejet direct.',
        ])->assertStatus(409)
          ->assertJson([
              'erreur' => "Impossible de passer de 'deposee' à 'rejetee' : la demande doit d'abord être en cours de traitement.",
          ]);
    }

    public function test_statuts_finaux_sont_irreversibles_renvoie_409(): void
    {
        $validee = Demande::factory()->validee()->create();
        $rejetee = Demande::factory()->rejetee('Motif initial')->create();

        // validee -> en_cours
        $this->patchJson("/api/demandes/{$validee->id}/statut", [
            'statut' => StatutDemande::EN_COURS->value,
        ])->assertStatus(409)
          ->assertJson([
              'erreur' => "Impossible de passer de 'validee' à 'en_cours' : une demande validée ne peut plus changer.",
          ]);

        // rejetee -> en_cours
        $this->patchJson("/api/demandes/{$rejetee->id}/statut", [
            'statut' => StatutDemande::EN_COURS->value,
        ])->assertStatus(409)
          ->assertJson([
              'erreur' => "Impossible de passer de 'rejetee' à 'en_cours' : une demande rejetée ne peut plus changer.",
          ]);

        // validee -> rejetee
        $this->patchJson("/api/demandes/{$validee->id}/statut", [
            'statut' => StatutDemande::REJETEE->value,
            'motif' => 'Tentative de modification après validation',
        ])->assertStatus(409)
          ->assertJson([
              'erreur' => "Impossible de passer de 'validee' à 'rejetee' : une demande validée ne peut plus changer.",
          ]);
    }

    public function test_rejet_sans_motif_ou_avec_espaces_renvoie_422(): void
    {
        $demande = Demande::factory()->enCours()->create();

        // Motif absent / vide
        $this->patchJson("/api/demandes/{$demande->id}/statut", [
            'statut' => StatutDemande::REJETEE->value,
        ])->assertStatus(422)
          ->assertJson([
              'erreur' => 'Un rejet doit être motivé.',
          ]);

        // Motif composé uniquement d'espaces
        $this->patchJson("/api/demandes/{$demande->id}/statut", [
            'statut' => StatutDemande::REJETEE->value,
            'motif' => '     ',
        ])->assertStatus(422)
          ->assertJson([
              'erreur' => 'Un rejet doit être motivé.',
          ]);
    }

    public function test_le_motif_n_est_pas_enregistre_pour_une_validation(): void
    {
        $demande = Demande::factory()->enCours()->create();

        $response = $this->patchJson("/api/demandes/{$demande->id}/statut", [
            'statut' => StatutDemande::VALIDEE->value,
            'motif' => 'Commentaire informatif',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('motif_rejet', null);

        $this->assertDatabaseHas('demandes', [
            'id' => $demande->id,
            'statut' => StatutDemande::VALIDEE->value,
            'motif_rejet' => null,
        ]);
    }

    public function test_demande_inconnue_renvoie_404(): void
    {
        $response = $this->patchJson('/api/demandes/99999/statut', [
            'statut' => StatutDemande::EN_COURS->value,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'erreur' => 'Demande introuvable.',
            ]);
    }
}
