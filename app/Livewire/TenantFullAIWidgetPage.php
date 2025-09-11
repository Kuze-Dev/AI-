<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class TenantFullAIWidgetPage extends Component
{
    use WithFileUploads;

    public $file;
    public $googleDocsUrl;

    public function save()
    {
        $this->validate([
            'file' => 'nullable|file|max:10240', // max 10MB
            'googleDocsUrl' => 'nullable|url',
        ]);

        // Example: store file if uploaded
        if ($this->file) {
            $path = $this->file->store('uploads', 'public');
            session()->flash('message', "File uploaded to: $path");
        }

        // Example: just flash Google Docs URL
        if ($this->googleDocsUrl) {
            session()->flash('message', "Google Docs URL: {$this->googleDocsUrl}");
        }
    }

    public function render()
    {
        return view('livewire.tenant-full-a-i-widget-page');
    }
}
