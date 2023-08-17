<tr class="filament-tables-row">
    <td colspan="4" class="px-4 py-3 filament-tables-text-column">
        {{ trans('Total:') }}
    </td>

    <td class="filament-tables-cell">
        <div class="px-4 py-3 filament-tables-text-column">
            {{ $this->ownerRecord->currency_symbol . ' ' . number_format($this->getTableRecords()->sum('sub_total'), 2, '.', '') }}
        </div>
    </td>
</tr>
