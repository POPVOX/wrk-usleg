<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Staff Management')]
class StaffManagement extends Component
{
    public string $newName = '';
    public string $newEmail = '';
    public string $newTitle = '';
    public string $newRole = 'staff';
    public string $newAccessLevel = 'all';
    public bool $newIsAdmin = false;
    public string $search = '';

    public bool $showAddModal = false;
    public bool $showEditModal = false;
    public ?string $tempPassword = null;

    // Edit fields
    public ?int $editingUserId = null;
    public string $editName = '';
    public string $editEmail = '';
    public string $editTitle = '';
    public string $editRole = 'staff';
    public string $editAccessLevel = 'all';
    public bool $editIsAdmin = false;

    protected array $roleOptions = [
        'chief_of_staff' => 'Chief of Staff',
        'deputy_chief' => 'Deputy Chief of Staff',
        'legislative_director' => 'Legislative Director',
        'communications_director' => 'Communications Director',
        'press_secretary' => 'Press Secretary',
        'scheduler' => 'Scheduler',
        'legislative_assistant' => 'Legislative Assistant',
        'legislative_correspondent' => 'Legislative Correspondent',
        'staff_assistant' => 'Staff Assistant',
        'caseworker' => 'Caseworker',
        'district_director' => 'District Director',
        'field_representative' => 'Field Representative',
        'intern' => 'Intern',
        'staff' => 'Staff',
    ];

    protected array $accessLevelOptions = [
        'admin' => 'Admin (Full Access)',
        'management' => 'Management',
        'all' => 'Standard Staff',
    ];

    protected function rules(): array
    {
        $emailRule = $this->editingUserId
            ? 'required|email|unique:users,email,' . $this->editingUserId
            : 'required|email|unique:users,email';

        return [
            'newName' => 'required|string|max:255',
            'newEmail' => $emailRule,
        ];
    }

    public function getRoleOptionsProperty(): array
    {
        return $this->roleOptions;
    }

    public function getAccessLevelOptionsProperty(): array
    {
        return $this->accessLevelOptions;
    }

    public function openAddModal()
    {
        $this->reset(['newName', 'newEmail', 'newTitle', 'newRole', 'newAccessLevel', 'newIsAdmin', 'tempPassword']);
        $this->newRole = 'staff';
        $this->newAccessLevel = 'all';
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->tempPassword = null;
    }

    public function addStaff()
    {
        $this->validate([
            'newName' => 'required|string|max:255',
            'newEmail' => 'required|email|unique:users,email',
        ]);

        // Generate a temporary password
        $this->tempPassword = Str::random(12);

        User::create([
            'name' => $this->newName,
            'email' => $this->newEmail,
            'password' => Hash::make($this->tempPassword),
            'title' => $this->newTitle,
            'role' => $this->newRole,
            'access_level' => $this->newAccessLevel,
            'is_admin' => $this->newIsAdmin || $this->newAccessLevel === 'admin',
        ]);

        $this->reset(['newName', 'newEmail', 'newTitle', 'newRole', 'newAccessLevel', 'newIsAdmin']);
        $this->dispatch('notify', type: 'success', message: 'Staff member added! Share the temporary password with them.');
    }

    public function openEditModal(int $userId)
    {
        $user = User::find($userId);
        if (!$user)
            return;

        $this->editingUserId = $userId;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editTitle = $user->title ?? '';
        $this->editRole = $user->role ?? 'staff';
        $this->editAccessLevel = $user->access_level ?? 'all';
        $this->editIsAdmin = $user->is_admin;
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingUserId = null;
    }

    public function updateStaff()
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editEmail' => 'required|email|unique:users,email,' . $this->editingUserId,
        ]);

        $user = User::find($this->editingUserId);
        if (!$user)
            return;

        $user->update([
            'name' => $this->editName,
            'email' => $this->editEmail,
            'title' => $this->editTitle,
            'role' => $this->editRole,
            'access_level' => $this->editAccessLevel,
            'is_admin' => $this->editIsAdmin || $this->editAccessLevel === 'admin',
        ]);

        $this->closeEditModal();
        $this->dispatch('notify', type: 'success', message: 'Staff member updated.');
    }

    public function resetPassword(int $userId)
    {
        $user = User::find($userId);
        if (!$user)
            return;

        $tempPassword = Str::random(12);
        $user->update(['password' => Hash::make($tempPassword)]);

        $this->dispatch(
            'notify',
            type: 'info',
            message: "New password for {$user->name}: {$tempPassword}"
        );
    }

    public function toggleAdmin(int $userId)
    {
        $user = User::find($userId);

        // Prevent removing admin from yourself
        if ($user->id === auth()->id() && $user->is_admin) {
            $this->dispatch('notify', type: 'error', message: 'You cannot remove admin status from yourself.');
            return;
        }

        $user->update(['is_admin' => !$user->is_admin]);
        $this->dispatch('notify', type: 'success', message: 'Admin status updated.');
    }

    public function deleteStaff(int $userId)
    {
        $user = User::find($userId);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'You cannot delete your own account.');
            return;
        }

        $user->delete();
        $this->dispatch('notify', type: 'success', message: 'Staff member removed.');
    }

    public function render()
    {
        $staff = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('title', 'like', '%' . $this->search . '%')
                    ->orWhere('role', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->get();

        return view('livewire.admin.staff-management', [
            'staff' => $staff,
            'roleOptions' => $this->roleOptions,
            'accessLevelOptions' => $this->accessLevelOptions,
        ]);
    }
}
