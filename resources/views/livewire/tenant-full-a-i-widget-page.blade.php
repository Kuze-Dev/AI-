<div style="max-width: 800px; margin: 2rem auto; padding: 2rem; background:#fff; border:1px solid #ddd; border-radius:12px;">
    <h1 style="font-size:20px; font-weight:bold; margin-bottom:1rem;">AI Widget</h1>

    <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:14px; margin-bottom:4px;">Upload File</label>
        <input type="file" wire:model="file" style="display:block; width:100%; font-size:14px; border:1px solid #ccc; border-radius:6px; padding:6px;" />
        @error('file') <span style="color:red; font-size:12px;">{{ $message }}</span> @enderror
    </div>

    <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:14px; margin-bottom:4px;">Google Docs URL</label>
        <input type="url" wire:model="googleDocsUrl" placeholder="https://docs.google.com/..." style="display:block; width:100%; font-size:14px; border:1px solid #ccc; border-radius:6px; padding:6px;" />
        @error('googleDocsUrl') <span style="color:red; font-size:12px;">{{ $message }}</span> @enderror
    </div>

    <div style="margin-top:1rem;">
        <button wire:click="save" style="padding:8px 16px; background:#2563eb; color:#fff; border:none; border-radius:6px; cursor:pointer;">
            Save
        </button>
    </div>

    @if (session()->has('message'))
        <div style="margin-top:1rem; padding:10px; background:#d1fae5; color:#065f46; border-radius:6px;">
            {{ session('message') }}
        </div>
    @endif
</div>
