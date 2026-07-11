<?php

namespace Tests\Feature;

use App\Livewire\TaskComments;
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
}
