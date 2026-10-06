<?php

namespace App\Http\Requests;

use App\Enums\StatutDemande;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatutRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la mise à jour du statut d'une demande.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'statut' => ['required', Rule::enum(StatutDemande::class)],
            'motif' => ['nullable', 'string', 'max:500'],
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
            'statut.required' => 'Le statut est obligatoire.',
            'statut.Illuminate\Validation\Rules\Enum' => 'Le statut doit être l\'un des suivants : deposee, en_cours, validee, rejetee.',
            'statut.enum' => 'Le statut doit être l\'un des suivants : deposee, en_cours, validee, rejetee.',
            'motif.string' => 'Le motif doit être une chaîne de caractères.',
            'motif.max' => 'Le motif ne doit pas dépasser 500 caractères.',
        ];
    }
}
