<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Filiere;
use App\Models\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Synchronise les filières et les modules depuis HESTIM Planner, propriétaire de la maquette.
 *
 * Lecture seule côté Planner (GET /api/integration/referentiel, jeton de service dédié).
 * Idempotente : mise à jour par code, rien n'est supprimé (des documents peuvent déjà être
 * rattachés à un module retiré de la maquette ; il est seulement signalé).
 */
class SyncPlannerReferential extends Command
{
    protected $signature = 'planner:sync';

    protected $description = 'Synchronise les filières et modules depuis HESTIM Planner';

    public function handle(): int
    {
        $token = (string) config('planner.integration_token');
        if (strlen($token) < 32) {
            $this->error('PLANNER_INTEGRATION_TOKEN absent ou trop court (32 caractères au moins).');

            return self::FAILURE;
        }

        $response = Http::timeout(15)->acceptJson()
            ->withHeaders(['X-Integration-Token' => $token])
            ->get(config('planner.api_url').'/integration/referentiel');
        if (! $response->successful()) {
            $this->error("Planner a répondu {$response->status()}.");

            return self::FAILURE;
        }

        $filieres = collect($response->json('filieres', []))->filter(fn ($f) => $this->validFiliere($f));
        $modules = collect($response->json('modules', []))->filter(fn ($m) => $this->validModule($m));

        [$filieresCount, $modulesCount, $retires] = DB::transaction(function () use ($filieres, $modules): array {
            foreach ($filieres as $f) {
                Filiere::query()->updateOrCreate(['code' => $f['code']], ['name' => mb_substr($f['nom'], 0, 120)]);
            }
            $ids = Filiere::query()->pluck('id', 'code');
            $vus = [];
            foreach ($modules as $m) {
                $filiereId = $ids[$m['filiere']] ?? null;
                if ($filiereId === null) {
                    continue;
                }
                Module::query()->updateOrCreate(
                    ['filiere_id' => $filiereId, 'code' => $m['code']],
                    ['name' => mb_substr($m['nom'], 0, 150), 'semester' => $m['semestre']],
                );
                $vus[] = $filiereId.'|'.$m['code'];
            }
            $retires = Module::query()->get(['filiere_id', 'code'])
                ->reject(fn (Module $module) => in_array($module->filiere_id.'|'.$module->code, $vus, true))
                ->count();

            return [$filieres->count(), count($vus), $retires];
        });

        $this->info("{$filieresCount} filière(s) et {$modulesCount} module(s) synchronisés.");
        if ($retires > 0) {
            $this->warn("{$retires} module(s) absent(s) de la maquette Planner, conservé(s) pour leurs documents.");
        }

        return self::SUCCESS;
    }

    private function validFiliere(mixed $f): bool
    {
        return is_array($f) && is_string($f['code'] ?? null) && $f['code'] !== '' && strlen($f['code']) <= 20
            && is_string($f['nom'] ?? null) && $f['nom'] !== '';
    }

    private function validModule(mixed $m): bool
    {
        return is_array($m) && is_string($m['code'] ?? null) && $m['code'] !== '' && strlen($m['code']) <= 30
            && is_string($m['nom'] ?? null) && $m['nom'] !== '' && is_string($m['filiere'] ?? null)
            && is_int($m['semestre'] ?? null) && $m['semestre'] >= 1 && $m['semestre'] <= 10;
    }
}
