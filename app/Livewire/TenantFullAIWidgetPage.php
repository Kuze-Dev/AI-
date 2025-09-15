<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TenantFullAIWidgetPage extends Component
{
    use WithFileUploads;

    public $file;
    public $uploadedFilePath;

    // Automatically handle file once it's selected
    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|file|max:10240', // 10 MB
        ]);

        $path = $this->file->store('uploads', 'public');

        $this->uploadedFilePath = Storage::url($path);

        Log::info('File uploaded instantly', ['path' => $this->uploadedFilePath]);

        session()->flash('message', "File uploaded successfully!");
    }

    public function removeFile()
    {
        if ($this->uploadedFilePath) {
            $storagePath = str_replace('/storage/', '', $this->uploadedFilePath);
            Storage::disk('public')->delete($storagePath);
        }

        $this->reset(['file', 'uploadedFilePath']);
        session()->flash('message', "File removed.");
    }

    public function render()
    {
        return view('livewire.tenant-full-a-i-widget-page');
    }
}
