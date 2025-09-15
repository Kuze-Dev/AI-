<div class="max-w-lg mx-auto p-6 bg-white rounded-2xl shadow-lg text-center space-y-6">
    <h2 class="text-xl font-bold">🚀 AI Widget</h2>
    <p class="text-gray-600">Upload your file (drag & drop or click)</p>

    <!-- Drag & Drop Upload Zone -->
    <div
        x-data="{ isDropping: false }"
        x-on:drop.prevent="isDropping = false"
        x-on:dragover.prevent="isDropping = true"
        x-on:dragleave.prevent="isDropping = false"
        class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-xl cursor-pointer transition"
        :class="isDropping ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-gray-50'"
    >
        <input type="file" wire:model="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />

        <div class="text-gray-500">
            📂 <span class="font-medium">Drop file here</span> or click to upload
        </div>
    </div>

    <!-- Progress -->
    <div wire:loading wire:target="file" class="mt-2 text-sm text-blue-600">
        Uploading...
    </div>

    <!-- Preview -->
    @if ($file)
        <div class="mt-4 p-4 border rounded-xl bg-gray-50 flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-800">{{ $file->getClientOriginalName() }}</p>
                <p class="text-xs text-gray-500">{{ number_format($file->getSize() / 1024, 2) }} KB</p>
            </div>
            <button wire:click="removeFile" class="text-red-500 hover:text-red-700 text-sm">✖ Remove</button>
        </div>
    @endif

    <!-- Saved File -->
    @if ($uploadedFilePath)
        <div class="mt-4 p-4 border border-green-300 rounded-xl bg-green-50">
            ✅ File saved:
            <a href="{{ $uploadedFilePath }}" target="_blank" class="text-green-700 underline">View</a>
        </div>
    @endif

    <!-- Save Button -->
    <button wire:click="save"
        class="w-full py-3 bg-green-600 hover:bg-green-700 transition text-white rounded-xl font-semibold"
        @disabled(!$file)>
        ✅ Save File
    </button>

    <!-- Flash Message -->
    @if(session()->has('message'))
        <div class="mt-4 p-3 bg-emerald-100 rounded-lg text-emerald-700 text-sm">
            {{ session('message') }}
        </div>
    @endif
</div>
