<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Website\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_blog_post_page_renders_with_an_author_from_the_same_database(): void
    {
        $author = User::factory()->create([
            'name' => 'Jan Preda',
            'email' => 'jan.preda@preda.info',
            'is_lawyer' => true,
            'website_title' => 'Adwokat',
            'website_is_published' => true,
        ]);

        Post::create([
            'title' => 'Wpis blogowy',
            'excerpt' => 'Krótki opis wpisu.',
            'content' => '<p>Treść wpisu.</p>',
            'date' => now()->toDateString(),
            'slug' => 'wpis-blogowy',
            'metatitle' => 'Wpis blogowy',
            'metadescription' => 'Opis wpisu blogowego',
            'is_published' => true,
            'author_id' => $author->id,
            'category' => 'blog',
        ]);

        Post::create([
            'title' => 'Orzecznictwo startowe',
            'excerpt' => 'Krótki opis orzeczenia.',
            'content' => '<p>Treść orzeczenia.</p>',
            'date' => now()->toDateString(),
            'slug' => 'orzecznictwo-startowe',
            'metatitle' => 'Orzecznictwo startowe',
            'metadescription' => 'Opis orzecznictwa',
            'is_published' => true,
            'category' => 'orzecznictwo',
        ]);

        $response = $this->get('/blog/wpis-blogowy');

        $response->assertOk();
        $response->assertSee('Wpis blogowy');
        $response->assertSee('Jan Preda');
        $response->assertSee('Adwokat');
    }
}
