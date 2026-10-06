<?php

namespace App\Http\Controllers;

use App\Enums\StatutDemande;
use App\Http\Requests\ListeDemandesRequest;
use App\Http\Requests\StoreDemandeRequest;
use App\Http\Requests\UpdateStatutRequest;
use App\Http\Resources\DemandeResource;
use App\Models\Demande;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DemandeController extends Controller
{
    /**
     * Enregistre une nouvelle demande d'acte avec le statut initial "deposee".
     */
    public function store(StoreDemandeRequest $request): JsonResponse
    {
        $demande = Demande::create([
            'npi' => $request->validated('npi'),
            'type_acte' => $request->validated('type_acte'),
            'nombre_copies' => $request->validated('nombre_copies'),
            'statut' => StatutDemande::DEPOSEE,
        ]);

        return (new DemandeResource($demande))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Récupère la liste des demandes d'un usager, triées de la plus récente à la plus ancienne.
     */
    public function indexForUsager(ListeDemandesRequest $request, string $npi): AnonymousResourceCollection
    {
        $demandes = Demande::pourUsager($request->validated('npi'))
            ->ayantStatut($request->validated('statut'))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return DemandeResource::collection($demandes);
    }

    /**
     * Fait avancer le traitement d'une demande selon son cycle de vie.
     */
    public function updateStatut(UpdateStatutRequest $request, Demande $demande): DemandeResource
    {
        $cible = StatutDemande::from($request->validated('statut'));
        $demande->changerStatut($cible, $request->validated('motif'));

        return new DemandeResource($demande);
    }
}
