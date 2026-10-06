<?php

namespace App\Http\Requests;

use App\Enums\StatutDemande;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListeDemandesRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Injection du paramètre d'URL 'npi' dans les données à valider.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'npi' => $this->route('npi'),
        ]);
    }

    /**
     * Règles de validation pour la consultation des demandes.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'npi' => ['required', 'string', 'regex:/^\d{10}$/'],
            'statut' => ['nullable', Rule::enum(StatutDemande::class)],
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
            'statut.Illuminate\Validation\Rules\Enum' => 'Le statut doit être l\'un des suivants : deposee, en_cours, validee, rejetee.',
            'statut.enum' => 'Le statut doit être l\'un des suivants : deposee, en_cours, validee, rejetee.',
        ];
    }
}
