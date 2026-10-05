<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Filiere;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use UnexpectedValueException;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * Inscrit un nouvel étudiant HESTIM.
     *
     * @param  array{name: string, email: string, password: string, filiere_id?: string|null, year_level?: int|null}  $data
     */
    public function register(array $data): User
    {
        return $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'filiere_id' => $data['filiere_id'] ?? null,
            'year_level' => $data['year_level'] ?? null,
            'role' => UserRole::Student->value,
        ]);
    }

    /**
     * Compte StudyLib d'une personne connectée par HESTIM Planner (connexion unique).
     *
     * Retrouvé par l'identifiant Planner, sinon par l'email (rattachement d'un compte existant),
     * sinon créé. Nom, rôle, filière et année suivent Planner à chaque visite. Aucun mot de passe
     * local n'est utilisable : un aléa haché est enregistré, la connexion passe par Planner.
     *
     * @param  array<string, mixed>  $claims  revendications vérifiées (PlannerTokenVerifier)
     */
    public function provisionFromPlanner(array $claims): User
    {
        $role = UserRole::fromPlanner((string) $claims['role']);
        if ($role === null) {
            throw new UnexpectedValueException('Rôle Planner sans accès à StudyLib');
        }
        $plannerId = (string) $claims['sub'];
        $email = strtolower(trim((string) $claims['email']));
        $name = trim(((string) ($claims['prenom'] ?? '')).' '.((string) ($claims['nom'] ?? ''))) ?: $email;
        $filiereId = is_string($claims['filiere'] ?? null) ? Filiere::query()->where('code', $claims['filiere'])->value('id') : null;
        $year = is_int($claims['annee'] ?? null) && $claims['annee'] >= 1 && $claims['annee'] <= 5 ? $claims['annee'] : null;

        return DB::transaction(function () use ($plannerId, $email, $name, $role, $filiereId, $year): User {
            $user = User::withTrashed()->where('planner_id', $plannerId)->first()
                ?? User::withTrashed()->where('email', $email)->whereNull('planner_id')->first();

            $attributes = [
                'planner_id' => $plannerId,
                'email' => $email,
                'name' => mb_substr($name, 0, 150),
                'role' => $role->value,
                'filiere_id' => $filiereId ?? $user?->filiere_id,
                'year_level' => $year ?? $user?->year_level,
            ];

            if ($user === null) {
                $user = User::create($attributes + ['password' => Hash::make(Str::random(64))]);
            } else {
                $user->fill($attributes);
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->save();
            }
            // L'adresse est vérifiée par l'établissement (compte créé par l'administration)
            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            return $user;
        });
    }

    public function emailBelongsToHestim(string $email): bool
    {
        return str_ends_with(strtolower($email), '@hestim.ma');
    }
}
