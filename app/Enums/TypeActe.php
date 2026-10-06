<?php

namespace App\Enums;

enum TypeActe: string
{
    case ACTE_NAISSANCE = 'acte_naissance';
    case CASIER_JUDICIAIRE = 'casier_judiciaire';
    case CERTIFICAT_RESIDENCE = 'certificat_residence';

    /**
     * Renvoie le libellé en français du type d'acte.
     */
    public function libelle(): string
    {
        return match ($this) {
            self::ACTE_NAISSANCE => 'Acte de naissance',
            self::CASIER_JUDICIAIRE => 'Casier judiciaire',
            self::CERTIFICAT_RESIDENCE => 'Certificat de résidence',
        };
    }
}
