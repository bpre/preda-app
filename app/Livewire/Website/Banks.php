<?php

namespace App\Livewire\Website;

use Livewire\Component;
use App\Models\Website\Bank;

class Banks extends Component
{
    public $search = '';
    public function render()
    {

        $banks = Bank::where(function ($q) {
            $q->where('label', 'like', '%'.$this->search.'%')
                ->orWhereHas('successor', function($q){
                    $q->where('label', 'like', '%'.$this->search.'%');
                });
            })
            ->where('is_published', true)
            ->orderBy('label')
            ->get();

        return view('livewire.website.banks', [
            'banks' => $banks
        ]);
    }
}
