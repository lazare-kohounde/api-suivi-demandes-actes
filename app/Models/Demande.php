<?php

namespace App\Models;

use App\Enums\StatutDemande;
use App\Enums\TypeActe;
use App\Exceptions\TransitionInterditeException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

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

    /**
     * Fait évoluer le statut de la demande selon les règles de gestion strictes.
     *
     * @throws TransitionInterditeException
     * @throws ValidationException
     */
    public function changerStatut(StatutDemande $cible, ?string $motif = null): self
    {
        // 1. Contrôle de la transition selon le cycle de vie (409)
        if (! $this->statut->peutPasserA($cible)) {
            throw new TransitionInterditeException(
                $this->statut->messageTransitionInterdite($cible)
            );
        }

        // 2. Contrôle du motif obligatoire en cas de rejet (422)
        $motifNettoye = is_string($motif) ? trim($motif) : null;
        if ($cible === StatutDemande::REJETEE && empty($motifNettoye)) {
            throw ValidationException::withMessages([
                'motif' => 'Un rejet doit être motivé.',
            ]);
        }

        // 3. Application du changement (le motif n'est conservé que pour un rejet)
        $this->statut = $cible;
        $this->motif_rejet = ($cible === StatutDemande::REJETEE) ? $motifNettoye : null;
        $this->save();

        return $this;
    }
}
