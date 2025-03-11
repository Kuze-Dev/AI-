<?php

declare(strict_types=1);

namespace App\Providers\Mixin;

use Closure;
use Filament\Forms;

/**
 * @mixin \Filament\Forms\Components\TextInput
 */
class FilamentTextInputMixin
{
    public function allowedOnlyWholeNumber(): Closure
    {
        return function (): Forms\Components\TextInput {

            /**
             * Add Extra Alphine Attribute for Allowing Whole Numbers
             **/
            $this->extraAlpineAttributes([
                'x-on:input' => "
                let newValue = \$el.value.replace(/[^0-9]/g, ''); 
                if (\$el.value !== newValue) {
                    alert('Only positive numbers are allowed.');
                    \$el.value = newValue;
                }
            ",
                'x-on:keydown' => "
                if (['e', 'E', '-', '+'].includes(event.key)) {
                    event.preventDefault();
                }
            ",
            ]);

            // $this->numeric();
            $this->integer();

            /** @phpstan-ignore return.type */
            return $this;
        };
    }
}
