<?php

namespace App\Http\Controllers;

use App\Enums\StatutDemande;
use App\Http\Requests\StoreDemandeRequest;
use App\Http\Resources\DemandeResource;
use App\Models\Demande;
use Illuminate\Http\JsonResponse;

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
}
