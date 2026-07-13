<?php

namespace Tests\Feature;

use App\Filament\Resources\MatterResource;
use App\Livewire\TaskComments;
use App\Models\Matter;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_task_comment_without_closing_comment_view(): void
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'task-comment@example.test',
            'phone' => '123456789',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $task = Task::query()->create([
            'label' => 'Zadanie testowe',
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'is_private' => false,
            'priority' => 2,
        ]);

        Livewire::test(TaskComments::class, ['task' => $task])
            ->set('data.comment', [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Nowy ',
                            ],
                            [
                                'type' => 'text',
                                'text' => 'komentarz',
                                'marks' => [
                                    [
                                        'type' => 'bold',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->call('addComment')
            ->assertSet('data.comment.content.0.type', 'paragraph')
            ->assertSee(['Nowy', 'komentarz']);

        $this->assertDatabaseHas('filament_comments', [
            'user_id' => $user->id,
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'comment' => '<p>Nowy <strong>komentarz</strong></p>',
        ]);
    }

    public function test_task_modal_labels_and_links_accepted_matter(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $matter = Matter::create([
            'label' => 'Kowalski Jan / Bank Testowy',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => true,
        ]);
        $task = Task::create([
            'label' => 'Zadanie dla sprawy',
            'matter_id' => $matter->getKey(),
            'created_by' => $user->getKey(),
            'assigned_to' => $user->getKey(),
            'is_private' => false,
            'priority' => 2,
        ]);

        $html = view('filament.task-comments.modal', ['task' => $task])->render();

        $this->assertStringContainsString('Sprawa', $html);
        $this->assertStringContainsString('href="'.MatterResource::getEditUrlForMatter($matter).'"', $html);
        $this->assertStringNotContainsString('Potencjalna sprawa', $html);
    }

    public function test_task_modal_labels_and_links_potential_matter_to_crm(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $matter = Matter::create([
            'label' => 'Nowak Anna / Bank Testowy',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);
        $task = Task::create([
            'label' => 'Zadanie dla potencjalnej sprawy',
            'matter_id' => $matter->getKey(),
            'created_by' => $user->getKey(),
            'assigned_to' => $user->getKey(),
            'is_private' => false,
            'priority' => 3,
        ]);

        $html = view('filament.task-comments.modal', ['task' => $task])->render();
        $url = MatterResource::getEditUrlForMatter($matter);

        $this->assertStringContainsString('Potencjalna sprawa', $html);
        $this->assertStringContainsString('href="'.$url.'"', $html);
        $this->assertStringContainsString('http://crm.preda-app.test', $url);
    }
}
