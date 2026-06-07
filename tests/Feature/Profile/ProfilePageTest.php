<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Enums\DocumentStatus;
use App\Livewire\Profile\Show;
use App\Models\Document;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\FiliereSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FiliereSeeder::class);
    }

    public function test_guest_is_redirected_from_profile(): void
    {
        $this->get(route('profile.show'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create([
            'email' => 'profil@hestim.ma',
            'name' => 'Cédric Mbarki',
        ]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Cédric Mbarki', false)
            ->assertSeeLivewire('profile.show');
    }

    public function test_profile_lists_user_documents(): void
    {
        $user = User::factory()->create(['email' => 'docs@hestim.ma']);
        $module = Module::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'title' => 'Mon cours de réseaux',
            'status' => DocumentStatus::Approved,
        ]);

        Livewire::actingAs($user)
            ->test(Show::class)
            ->assertSee('Mon cours de réseaux')
            ->assertSee('Documents déposés');
    }

    public function test_user_can_update_profile_from_settings_tab(): void
    {
        $filiere = Filiere::query()->where('code', 'GI')->firstOrFail();
        $user = User::factory()->create([
            'email' => 'update@hestim.ma',
            'name' => 'Ancien Nom',
            'filiere_id' => null,
            'year_level' => null,
        ]);

        Livewire::actingAs($user)
            ->test(Show::class)
            ->set('tab', 'params')
            ->set('name', 'Nouveau Nom')
            ->set('filiereId', $filiere->id)
            ->set('yearLevel', 3)
            ->call('saveProfile')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('Nouveau Nom', $user->name);
        $this->assertSame($filiere->id, $user->filiere_id);
        $this->assertSame(3, $user->year_level);
    }

    public function test_profile_json_api_still_works(): void
    {
        $user = User::factory()->create(['email' => 'api@hestim.ma']);

        $this->actingAs($user)
            ->getJson(route('profile.show'))
            ->assertOk()
            ->assertJsonPath('email', 'api@hestim.ma');
    }

    public function test_profile_shows_download_stats(): void
    {
        $user = User::factory()->create(['email' => 'stats@hestim.ma']);
        $module = Module::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'status' => DocumentStatus::Approved,
            'downloads_count' => 42,
        ]);

        Livewire::actingAs($user)
            ->test(Show::class)
            ->assertSee('42');
    }
}
