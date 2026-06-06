<?php

declare(strict_types=1);

namespace Tests\Feature\Foundation;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DesignSystemComponentsTest extends TestCase
{
    public function test_core_ui_components_render_without_error(): void
    {
        $components = [
            '<x-ui.button>Action</x-ui.button>',
            '<x-ui.badge variant="primary">Nouveau</x-ui.badge>',
            '<x-ui.empty-state title="Vide" description="Aucun élément" />',
            '<x-ui.table><x-slot:head><tr><th>Col</th></tr></x-slot:head><tr><td>Val</td></tr></x-ui.table>',
            '<x-ui.uploader />',
            '<x-ui.pagination><button type="button" class="sl-page-btn is-active">1</button></x-ui.pagination>',
            '<x-ui.toast variant="success" title="OK" text="Fait" />',
        ];

        foreach ($components as $markup) {
            $html = Blade::render($markup);
            $this->assertNotSame('', trim($html));
        }
    }
}
