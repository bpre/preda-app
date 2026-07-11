<?php

namespace App\Livewire;

use Livewire\Component;

class Alpine extends Component
{
    // Pusty komponent dołączany tylko po to, by na wszystkich stronach ładował się alpinejs

    public function render()
    {
        return view('livewire.alpine');
    }
}
