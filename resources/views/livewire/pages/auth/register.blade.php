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
    public bool $bootstrapRegistrationAvailable = false;
    public string $bootstrapStatusMessage = '';

    public function mount(): void
    {
        $this->invite = (string) request()->query('invite', '');
        $this->syncInviteState();
    }

    public function updatedInvite(): void
    {
        $this->syncInviteState();
    }

    public function updatedEmail(): void
    {
        $this->syncInviteState();
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $betaRequest = $this->resolveInviteRequest();
        $bootstrapRegistrationAvailable = $this->bootstrapRegistrationIsAvailable();

        if ((!$betaRequest || !$this->inviteAccessGranted) && !$bootstrapRegistrationAvailable) {
            throw ValidationException::withMessages([
                'invite' => $this->inviteStatusMessage ?: 'Registration is invite-only during beta.',
            ]);
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['email'] = strtolower($validated['email']);

        if ($betaRequest && $this->inviteAccessGranted) {
            $validated['email'] = strtolower($betaRequest->email);
        } elseif ($bootstrapRegistrationAvailable) {
            if (!$this->emailCanBootstrap($validated['email'])) {
                throw ValidationException::withMessages([
                    'email' => 'This email address is not authorized to bootstrap the platform.',
                ]);
            }
        }

        if (User::where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An account already exists for this email. Try signing in instead.',
            ]);
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_admin'] = false;
        $validated['is_super_admin'] = false;
        $validated['access_level'] = 'all';

        if ($bootstrapRegistrationAvailable) {
            $validated['is_admin'] = true;
            $validated['is_super_admin'] = true;
            $validated['access_level'] = 'admin';
        }

        event(new Registered($user = User::create($validated)));

        if ($betaRequest && $this->inviteAccessGranted) {
            $betaRequest->update([
                'status' => 'onboarded',
                'onboarded_at' => now(),
                'onboarded_user_id' => $user->id,
            ]);
        }

        Auth::login($user);

        $this->redirect(
            route($bootstrapRegistrationAvailable ? 'platform.dashboard' : 'dashboard', absolute: false),
            navigate: true
        );
    }

    protected function syncInviteState(): void
    {
        $this->inviteAccessGranted = false;
        $this->bootstrapRegistrationAvailable = false;
        $this->bootstrapStatusMessage = '';

        $request = $this->resolveInviteRequest();

        $this->inviteStatusMessage = 'Registration is invite-only during beta. Please use the invite link you received after approval.';

        if ($request) {
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
            return;
        }

        if (!$this->bootstrapRegistrationIsAvailable()) {
            return;
        }

        $this->bootstrapRegistrationAvailable = true;
        $this->bootstrapStatusMessage = 'Platform bootstrap mode is active. Only approved owner emails can create the first admin account.';
    }

    protected function resolveInviteRequest(): ?BetaRequest
    {
        $token = trim($this->invite);

        if ($token === '') {
            return null;
        }

        return BetaRequest::where('invite_token', $token)->first();
    }

    protected function bootstrapRegistrationIsAvailable(): bool
    {
        return !User::query()->exists() && count($this->bootstrapEmails()) > 0;
    }

    protected function bootstrapEmails(): array
    {
        return config('auth.bootstrap_super_admin_emails', []);
    }

    protected function emailCanBootstrap(string $email): bool
    {
        return in_array(strtolower(trim($email)), $this->bootstrapEmails(), true);
    }
}; ?>

<div>
    @if(!$inviteAccessGranted && !$bootstrapRegistrationAvailable)
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
            @if($bootstrapRegistrationAvailable)
                <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-100">
                    <h2 class="text-lg font-semibold">{{ __('Create Your First Platform Admin Account') }}</h2>
                    <p class="mt-2">{{ $bootstrapStatusMessage }}</p>
                </div>
            @endif

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="$inviteAccessGranted ? __('Approved Email') : __('Email')" />
                @if($inviteAccessGranted)
                    <x-text-input
                        wire:model="email"
                        id="email"
                        class="block mt-1 w-full bg-gray-100 dark:bg-gray-800"
                        type="email"
                        name="email"
                        required
                        readonly
                        autocomplete="username"
                    />
                @else
                    <x-text-input
                        wire:model="email"
                        id="email"
                        class="block mt-1 w-full"
                        type="email"
                        name="email"
                        required
                        autocomplete="username"
                    />
                @endif
                @if($inviteAccessGranted)
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('This invite is tied to a specific email address.') }}
                    </p>
                @elseif($bootstrapRegistrationAvailable)
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Use an approved owner email to create the first platform admin account.') }}
                    </p>
                @endif
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
