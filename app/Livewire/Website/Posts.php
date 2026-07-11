<?php

namespace App\Livewire\Website;

use Livewire\Component;
use App\Models\Website\Post;
use Livewire\WithPagination;

class Posts extends Component
{

    use WithPagination;
    public $search = '';
    public $more = false;
    public string $category;

    public function mount()
    {
        $this->category = request()->segment(1);
    }


    public function updatedSearch()
    {
        return Post::query()
            ->with('author')
            ->where('category', $this->category)
            ->where('is_published', true)
            ->where(function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhereHas('author', function ($authorQuery) {
                        $authorQuery->where('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->orderBy('date', 'desc');
    }

    public function render()
    {

        $query = $this->updatedSearch();

        if($this->more) {

            $posts = $query->limit(4)->get();

            return view('livewire.website.posts', [
                'posts' => $posts,
                'links' => null
            ]);
        } else {

            $posts = $query->paginate(10);

            return view('livewire.website.posts', [
                'posts' => $posts->items(),
                'links' => $posts
            ]);
        }
    }


}
