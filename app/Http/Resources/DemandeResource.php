<?php

namespace App\Http\Resources;

use App\Enums\StatutDemande;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandeResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau pour la réponse JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'npi' => $this->npi,
            'type_acte' => $this->type_acte instanceof \BackedEnum ? $this->type_acte->value : $this->type_acte,
            'nombre_copies' => $this->nombre_copies,
            'statut' => $this->statut instanceof \BackedEnum ? $this->statut->value : $this->statut,
            'statut_libelle' => $this->statut instanceof StatutDemande ? $this->statut->libelle() : null,
            'motif_rejet' => $this->motif_rejet,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
