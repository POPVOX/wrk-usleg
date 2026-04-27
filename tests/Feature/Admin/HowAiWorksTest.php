<?php

use App\Livewire\Admin\HowAiWorks;
use Livewire\Livewire;

it('shows the configured ai models on the transparency page', function () {
    config()->set('services.anthropic.model', 'claude-sonnet-4-5-20250929');
    config()->set('services.openai.chat_model', 'gpt-5.4-mini-2026-03-17');
    config()->set('services.openai.embedding_model', 'text-embedding-3-small');

    Livewire::test(HowAiWorks::class)
        ->assertSee('Current AI Stack')
        ->assertSee('claude-sonnet-4-5-20250929')
        ->assertSee('gpt-5.4-mini-2026-03-17')
        ->assertSee('text-embedding-3-small');
});
