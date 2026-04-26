<?php

use App\Models\BetaRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $invite = '';
    public bool $inviteAccessGranted = false;
    public string $inviteStatusMessage = '';

    public function mount(): void
    {
        $this->invite = (string) request()->query('invite', '');
        $this->syncInviteState();
    }

    public function updatedInvite(): void
    {
        $this->syncInviteState();
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $betaRequest = $this->resolveInviteRequest();

        if (!$betaRequest || !$this->inviteAccessGranted) {
            throw ValidationException::withMessages([
                'invite' => $this->inviteStatusMessage ?: 'Registration is invite-only during beta.',
            ]);
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['email'] = strtolower($betaRequest->email);

        if (User::where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An account already exists for this approved beta invite. Try signing in instead.',
            ]);
        }

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        $betaRequest->update([
            'status' => 'onboarded',
            'onboarded_at' => now(),
            'onboarded_user_id' => $user->id,
        ]);

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    protected function syncInviteState(): void
    {
        $request = $this->resolveInviteRequest();

        $this->inviteAccessGranted = false;
        $this->inviteStatusMessage = 'Registration is invite-only during beta. Please use the invite link you received after approval.';

        if (!$request) {
            return;
        }

        if ($request->status === 'onboarded') {
            $this->inviteStatusMessage = 'This invite link has already been used. Try signing in if you already created your account.';
            return;
        }

        if ($request->status !== 'approved') {
            $this->inviteStatusMessage = 'This invite link is no longer active.';
            return;
        }

        if ($request->invite_expires_at && $request->invite_expires_at->isPast()) {
            $this->inviteStatusMessage = 'This invite link has expired. Please ask us for a fresh invite.';
            return;
        }

        $this->inviteAccessGranted = true;
        $this->inviteStatusMessage = '';
        $this->name = $this->name ?: $request->full_name;
        $this->email = $request->email;
    }

    protected function resolveInviteRequest(): ?BetaRequest
    {
        $token = trim($this->invite);

        if ($token === '') {
            return null;
        }

        return BetaRequest::where('invite_token', $token)->first();
    }
}; ?>

<div>
    @if(!$inviteAccessGranted)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100">
            <h2 class="text-xl font-semibold">{{ __('Registration Is Invite-Only') }}</h2>
            <p class="mt-3">{{ $inviteStatusMessage }}</p>
            @if($errors->has('invite'))
                <p class="mt-3 text-red-600 dark:text-red-400">{{ $errors->first('invite') }}</p>
            @endif
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ url('/') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    {{ __('Return Home') }}
                </a>
                <a class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800" href="{{ route('login') }}" wire:navigate>
                    {{ __('Already registered? Sign in') }}
                </a>
            </div>
        </div>
    @else
        <form wire:submit="register">
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Approved Email')" />
                <x-text-input wire:model="email" id="email" class="block mt-1 w-full bg-gray-100 dark:bg-gray-800" type="email" name="email" required readonly autocomplete="username" />
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('This invite is tied to a specific email address.') }}
                </p>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}" wire:navigate>
                    {{ __('Already registered?') }}
                </a>

                <x-primary-button class="ms-4">
                    {{ __('Register') }}
                </x-primary-button>
            </div>
        </form>
    @endif
</div>
