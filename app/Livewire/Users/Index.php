<?php

namespace App\Livewire\Users;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public bool $showCreateDialog = false;
    public bool $showPasswordDialog = false;
    public string $newName = '';
    public string $newEmail = '';
    public string $newPassword = '';
    public string $newPassword_confirmation = '';
    public ?int $passwordUserId = null;

    public function openCreateDialog(): void
    {
        $this->authorize('create', User::class);
        $this->resetValidation();
        $this->reset(['newName', 'newEmail', 'newPassword', 'newPassword_confirmation']);
        $this->showCreateDialog = true;
    }

    public function createCashier(): void
    {
        $this->authorize('create', User::class);
        $this->newEmail = strtolower(trim($this->newEmail));

        $data = $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newEmail' => ['required', 'string', 'email', 'max:255', 'lowercase', Rule::unique('users', 'email')],
            'newPassword' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => trim($data['newName']),
            'email' => strtolower(trim($data['newEmail'])),
            'password' => $data['newPassword'],
            'role' => 'cashier',
            'is_active' => true,
        ]);

        ActivityLog::record(
            action: 'user.created',
            subjectType: User::class,
            subjectId: $user->id,
            meta: ['email' => $user->email, 'role' => $user->role]
        );

        $this->showCreateDialog = false;
        $this->reset(['newName', 'newEmail', 'newPassword', 'newPassword_confirmation']);
        session()->flash('success', 'Akun kasir berhasil dibuat.');
    }

    public function toggleCashier(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $this->authorize('update', $user);

            if (! $user->isCashier()) {
                abort(403);
            }

            $user->is_active = ! $user->is_active;
            $user->save();

            ActivityLog::record(
                action: $user->is_active ? 'user.activated' : 'user.deactivated',
                subjectType: User::class,
                subjectId: $user->id,
                meta: ['email' => $user->email]
            );
        });

        session()->flash('success', 'Status akun kasir diperbarui.');
    }

    public function openPasswordDialog(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        $this->authorize('update', $user);

        if (! $user->isCashier()) {
            abort(403);
        }

        $this->resetValidation();
        $this->reset(['newPassword', 'newPassword_confirmation']);
        $this->passwordUserId = $user->id;
        $this->showPasswordDialog = true;
    }

    public function resetCashierPassword(): void
    {
        $user = User::query()->findOrFail($this->passwordUserId);
        $this->authorize('update', $user);

        if (! $user->isCashier()) {
            abort(403);
        }

        $data = $this->validate([
            'newPassword' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $user->update(['password' => $data['newPassword']]);
        ActivityLog::record(
            action: 'user.password_reset',
            subjectType: User::class,
            subjectId: $user->id,
            meta: ['email' => $user->email]
        );

        $this->showPasswordDialog = false;
        $this->passwordUserId = null;
        $this->reset(['newPassword', 'newPassword_confirmation']);
        session()->flash('success', 'Password kasir berhasil direset.');
    }

    public function render()
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()->orderBy('role')->orderBy('name')->get();

        return view('livewire.users.index', compact('users'))->layout('layouts.app', ['title' => 'Pengguna']);
    }
}
