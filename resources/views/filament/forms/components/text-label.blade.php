@php
    $alignClass = match ($getAlignment()) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
    
    $state = $getState();
@endphp

<div
    {{ $attributes->merge($getExtraAttributes())->class([
        'filament-tables-text-column',
        'px-4 py-3' => !$isInline(),
        match ($getColor()) {
            'danger' => 'text-danger-600',
            'primary' => 'text-primary-600',
            'secondary' => 'text-gray-500',
            'success' => 'text-success-600',
            'warning' => 'text-warning-600',
            default => null,
        },
        match ($getSize()) {
            'xs' => 'text-xs',
            'sm' => 'text-sm',
            'md' => 'text-md',
            'lg' => 'text-lg',
            'xl' => 'text-xl',
            default => null,
        },
        match ($getWeight()) {
            'thin' => 'font-thin',
            'extralight' => 'font-extralight',
            'light' => 'font-light',
            'medium' => 'font-medium',
            'semibold' => 'font-semibold',
            'bold' => 'font-bold',
            'extrabold' => 'font-extrabold',
            'black' => 'font-black',
            default => null,
        },
        match ($getFontFamily()) {
            'sans' => 'font-sans',
            'serif' => 'font-serif',
            'mono' => 'font-mono',
            default => null,
        },
        $alignClass,
    ]) }}>

    <span>
        {{ $hasState() ? $state : $getLabel() }}
    </span>
</div>
