<?php

namespace Tests\Feature;

use App\Filament\Pages\UserPreferences;
use App\Models\User;
use App\Support\FilamentContentLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentLayoutPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_width_toggle_updates_user_layout_preferences(): void
    {
        $user = $this->createUser();

        $this
            ->actingAs($user)
            ->postJson(route('filament-layout.preferences.table-width'), [
                'record_list_pages_full_width' => false,
            ])
            ->assertOk()
            ->assertJson([
                'mode' => 'contained',
                'record_list_pages_full_width' => false,
            ]);

        $this->assertFalse($user->fresh()->filament_layout_preferences['record_list_pages_full_width']);
    }

    public function test_layout_helper_reads_user_preferences(): void
    {
        $user = $this->createUser([
            'filament_layout_preferences' => [
                'content_full_width' => true,
                'content_max_width' => '6xl',
                'content_alignment' => 'center',
                'record_list_pages_full_width' => false,
                'record_list_pages_full_width_toggle' => false,
                'record_list_pages_full_width_storage_key' => 'custom-table-width',
            ],
        ]);

        $this->actingAs($user);

        $this->assertTrue(FilamentContentLayout::shouldUseFullWidthForAllContent());
        $this->assertSame('6xl', FilamentContentLayout::contentMaxWidth());
        $this->assertFalse(FilamentContentLayout::shouldAlignWrappedContentToLeft());
        $this->assertFalse(FilamentContentLayout::shouldUseFullWidthForRecordListPagesByDefault());
        $this->assertFalse(FilamentContentLayout::shouldShowRecordListPagesFullWidthToggle());
        $this->assertSame('custom-table-width', FilamentContentLayout::recordListPagesFullWidthStorageKey());
    }

    public function test_content_max_width_options_are_limited_to_practical_wrapper_sizes(): void
    {
        $this->assertSame([
            'lg' => 'lg',
            'xl' => 'xl',
            '2xl' => '2xl',
            '3xl' => '3xl',
            '4xl' => '4xl',
            '5xl' => '5xl',
            '6xl' => '6xl',
            '7xl' => '7xl',
        ], FilamentContentLayout::contentMaxWidthOptions());
    }

    public function test_user_preferences_page_can_be_rendered(): void
    {
        $user = $this->createUser([
            'email' => 'employee@preda.info',
            'is_employee' => true,
            'is_active' => true,
        ]);

        $this
            ->actingAs($user)
            ->get('http://ewidencja.preda-app.test/preferencje-uzytkownika')
            ->assertOk()
            ->assertSee('Preferencje użytkownika')
            ->assertSee('Przywróć ustawienia domyślne')
            ->assertSee('Pełna szerokość całego panelu')
            ->assertDontSee('Klucz zapisu szerokości tabel');
    }

    public function test_user_can_restore_default_layout_preferences(): void
    {
        $user = $this->createUser([
            'filament_layout_preferences' => [
                'content_full_width' => true,
                'content_max_width' => 'lg',
                'content_alignment' => 'center',
                'record_list_pages_full_width' => false,
                'record_list_pages_full_width_toggle' => false,
                'record_list_pages_full_width_storage_key' => 'custom-table-width',
            ],
        ]);

        $this->actingAs($user);

        Livewire::test(UserPreferences::class)
            ->call('restoreDefaults')
            ->assertSet('data.content_full_width', false)
            ->assertSet('data.content_max_width', '7xl')
            ->assertSet('data.content_alignment', 'left')
            ->assertSet('data.record_list_pages_full_width', true)
            ->assertSet('data.record_list_pages_full_width_toggle', true);

        $preferences = $user->fresh()->filament_layout_preferences;

        $this->assertFalse($preferences['content_full_width']);
        $this->assertSame('7xl', $preferences['content_max_width']);
        $this->assertSame('left', $preferences['content_alignment']);
        $this->assertTrue($preferences['record_list_pages_full_width']);
        $this->assertTrue($preferences['record_list_pages_full_width_toggle']);
        $this->assertSame('custom-table-width', $preferences['record_list_pages_full_width_storage_key']);
    }

    protected function createUser(array $attributes = []): User
    {
        return User::query()->create([
            'name' => 'Test User',
            'email' => fake()->unique()->safeEmail(),
            'phone' => '123456789',
            'password' => 'password',
            ...$attributes,
        ]);
    }
}
