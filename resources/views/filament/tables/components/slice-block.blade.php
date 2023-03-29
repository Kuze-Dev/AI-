<div class="w-full">
    @php
        $slice = $getRecord();
        $blueprint = $slice->load('blueprint');
        $image = $slice->getFirstMediaUrl('image') ?: 'https://via.placeholder.com/500x400.png?text=No+Image';
        $updated_at = $slice->updated_at->format('F j, Y g:iA');
    @endphp

    <img src="{{ $image }}" style="width: 100%; height: 400px; object-fit: contain;" />
    <div class="flex flex-col gap-2 mt-4">
        <div class="font-bold text-xl">{{ $slice->name }}</div>
        <div class="flex items-center justify-between">
            <span>Blueprint</span>
            <span>{{ $blueprint->name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span>Updated</span>
            <span>{{ $updated_at }}</span>
        </div>
    </div>
</div>
