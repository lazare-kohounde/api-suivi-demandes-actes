<?php

namespace App\Models;

use App\Enums\StatutDemande;
use App\Enums\TypeActe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'npi',
        'type_acte',
        'nombre_copies',
        'statut',
        'motif_rejet',
    ];

    /**
     * Casts des attributs Eloquent.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'type_acte' => TypeActe::class,
            'statut' => StatutDemande::class,
            'nombre_copies' => 'integer',
        ];
    }

    /**
     * Scope pour filtrer les demandes d'un usager par son NPI.
     *
     * @param Builder<self> $query
     * @return Builder<self>
     */
    public function scopePourUsager(Builder $query, string $npi): Builder
    {
        return $query->where('npi', $npi);
    }

    /**
     * Scope pour filtrer optionnellement par statut.
     *
     * @param Builder<self> $query
     * @param null|string|StatutDemande $statut
     * @return Builder<self>
     */
    public function scopeAyantStatut(Builder $query, null|string|StatutDemande $statut): Builder
    {
        if (blank($statut)) {
            return $query;
        }

        $valeur = $statut instanceof StatutDemande ? $statut->value : $statut;

        return $query->where('statut', $valeur);
    }
}
