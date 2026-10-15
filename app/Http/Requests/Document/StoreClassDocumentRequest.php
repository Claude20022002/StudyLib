<?php

declare(strict_types=1);

namespace App\Http\Requests\Document;

use App\Enums\DocumentType;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Dépôt d'un document de classe, transmis par le serveur de Planner avec le jeton de l'enseignant.
 * Le module est désigné par les codes de Planner (filière, module), que `planner:sync` recopie.
 * Types acceptés : ceux de la Bibliothèque, plus les tableurs, OpenDocument, texte, archives et
 * images d'un support de classe ; jamais de HTML, de SVG ni d'exécutable. Le contrôle porte sur le
 * contenu réel du fichier (Laravel le devine par finfo), pas sur le nom.
 */
class StoreClassDocumentRequest extends FormRequest
{
    public const MIMES = 'pdf,doc,docx,ppt,pptx,xls,xlsx,odt,odp,ods,txt,csv,zip,png,jpg,jpeg,webp';

    public function authorize(): bool
    {
        return in_array($this->user()?->role, [UserRole::Teacher, UserRole::Admin], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'filiere' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/'],
            'module' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'type' => ['nullable', 'string', Rule::enum(DocumentType::class)],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'max:20480', 'mimes:'.self::MIMES],
        ];
    }
}
