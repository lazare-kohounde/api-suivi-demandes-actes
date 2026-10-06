<?php

namespace App\Models;

use App\Enums\StatutDemande;
use App\Enums\TypeActe;
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
}
