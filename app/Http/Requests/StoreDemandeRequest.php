<?php

namespace App\Http\Requests;

use App\Enums\TypeActe;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemandeRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour le dépôt d'une demande.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'npi' => ['required', 'string', 'regex:/^\d{10}$/'],
            'type_acte' => ['required', Rule::enum(TypeActe::class)],
            'nombre_copies' => ['required', 'integer', 'between:1,5'],
        ];
    }

    /**
     * Messages de validation personnalisés en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'npi.required' => 'Le NPI est obligatoire.',
            'npi.string' => 'Le NPI doit être une chaîne de caractères.',
            'npi.regex' => 'Le NPI doit comporter exactement 10 chiffres.',
            'type_acte.required' => 'Le type d\'acte est obligatoire.',
            'type_acte.Illuminate\Validation\Rules\Enum' => 'Le type d\'acte doit être l\'un des suivants : acte_naissance, casier_judiciaire, certificat_residence.',
            'type_acte.enum' => 'Le type d\'acte doit être l\'un des suivants : acte_naissance, casier_judiciaire, certificat_residence.',
            'nombre_copies.required' => 'Le nombre de copies est obligatoire.',
            'nombre_copies.integer' => 'Le nombre de copies doit être un entier.',
            'nombre_copies.between' => 'Le nombre de copies doit être compris entre 1 et 5.',
        ];
    }
}
