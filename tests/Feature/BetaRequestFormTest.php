<?php

use App\Livewire\BetaRequestForm;
use Livewire\Livewire;

it('renders explicit autofill hints for the beta request form', function () {
    Livewire::test(BetaRequestForm::class)
        ->call('openModal')
        ->assertSeeHtml('id="beta-full-name"')
        ->assertSeeHtml('name="full_name"')
        ->assertSeeHtml('autocomplete="name"')
        ->assertSeeHtml('id="beta-work-email"')
        ->assertSeeHtml('name="email"')
        ->assertSeeHtml('autocomplete="email"');
});
