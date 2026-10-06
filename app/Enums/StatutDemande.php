<?php

namespace App\Enums;

enum StatutDemande: string
{
    case DEPOSEE = 'deposee';
    case EN_COURS = 'en_cours';
    case VALIDEE = 'validee';
    case REJETEE = 'rejetee';

    /**
     * Liste des statuts immédiatement accessibles depuis l'état courant.
     *
     * @return array<self>
     */
    public function transitionsAutorisees(): array
    {
        return match ($this) {
            self::DEPOSEE => [self::EN_COURS],
            self::EN_COURS => [self::VALIDEE, self::REJETEE],
            self::VALIDEE => [],
            self::REJETEE => [],
        };
    }

    /**
     * Vérifie si la transition vers le statut cible est permise.
     */
    public function peutPasserA(self $cible): bool
    {
        return in_array($cible, $this->transitionsAutorisees(), true);
    }

    /**
     * Indique si le statut est un état terminal (irréversible).
     */
    public function estFinal(): bool
    {
        return $this === self::VALIDEE || $this === self::REJETEE;
    }

    /**
     * Renvoie le libellé en français du statut.
     */
    public function libelle(): string
    {
        return match ($this) {
            self::DEPOSEE => 'Déposée',
            self::EN_COURS => 'En cours de traitement',
            self::VALIDEE => 'Validée',
            self::REJETEE => 'Rejetée',
        };
    }
}
