<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Supports de cours d'une séance HESTIM Planner : pour chaque code de module (unique dans
 * Planner), le nombre de documents publiés et le lien vers la bibliothèque filtrée sur ce module.
 * Appelé par le tableau de bord de Planner (cookie, même origine) et par l'application mobile.
 */
class ModuleSupportsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codes' => ['required', 'array', 'min:1', 'max:50'],
            'codes.*' => ['string', 'regex:/^[A-Za-z0-9_.-]{1,30}$/'],
        ]);

        $modules = Module::query()
            ->whereIn('code', array_unique($validated['codes']))
            ->withCount(['documents as published_count' => fn ($q) => $q->visible()])
            ->get(['id', 'code', 'filiere_id']);

        $supports = [];
        foreach ($modules as $module) {
            // Un même code dans deux filières (données anciennes) : on garde le module le plus fourni
            if (isset($supports[$module->code]) && $supports[$module->code]['documents'] >= $module->published_count) {
                continue;
            }
            $supports[$module->code] = [
                'module_id' => $module->id,
                'documents' => $module->published_count,
                'url' => route('documents.index', ['filiere' => $module->filiere_id, 'module' => $module->id]),
            ];
        }

        return response()->json(['supports' => (object) $supports]);
    }
}
