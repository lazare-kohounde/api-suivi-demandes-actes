<?php

namespace App\Http\Requests;

use App\Enums\StatutDemande;
use Illuminate\Contracts\Validation\ValidationRule;
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
     * Règles de validation pour la consultation des demandes avec pagination.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'npi' => ['required', 'string', 'regex:/^\d{10}$/'],
            'statut' => ['nullable', Rule::enum(StatutDemande::class)],
            'page' => ['nullable', 'integer', 'min:1'],
            'taille' => ['nullable', 'integer', 'min:1', 'max:20'],
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
            'page.integer' => 'Le numéro de page doit être un entier.',
            'page.min' => 'Le numéro de page doit être supérieur ou égal à 1.',
            'taille.integer' => 'La taille de page doit être un entier.',
            'taille.min' => 'La taille de page doit être au minimum de 1.',
            'taille.max' => 'La taille de page ne peut pas dépasser 20.',
        ];
    }
}
