<div class="card p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-content-secondary">Akses tim ke sistem kasir</p>
            <h3 class="text-xl font-semibold">Pengguna</h3>
        </div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="openCreateDialog">Tambah kasir</button>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->isOwner() ? 'Owner' : 'Kasir' }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            @if($user->isCashier())
                                <div class="flex justify-end gap-2">
                                    <button type="button" class="btn btn-secondary btn-sm" wire:click="openPasswordDialog({{ $user->id }})">Reset password</button>
                                    <button
                                        type="button"
                                        class="{{ $user->is_active ? 'btn btn-danger btn-sm' : 'btn btn-primary btn-sm' }}"
                                        wire:click="toggleCashier({{ $user->id }})"
                                        wire:confirm="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $user->name }}?">
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </div>
                            @else
                                <span class="block text-right text-xs text-content-secondary">Dikelola dari konfigurasi owner</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-8 text-content-secondary">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showCreateDialog || $showPasswordDialog)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" role="dialog" aria-modal="true" aria-labelledby="user-dialog-title">
            <form
                wire:submit="{{ $showCreateDialog ? 'createCashier' : 'resetCashierPassword' }}"
                class="w-full max-w-md space-y-4 rounded-2xl border border-border bg-surface p-5 shadow-dialog dark:border-border-dark dark:bg-surface-dark">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-content-secondary">Pemilik mengelola akses staf</p>
                        <h3 id="user-dialog-title" class="text-xl font-semibold">{{ $showCreateDialog ? 'Tambah kasir' : 'Reset password kasir' }}</h3>
                    </div>
                    <button
                        type="button"
                        class="btn btn-ghost btn-sm"
                        wire:click="$set('{{ $showCreateDialog ? 'showCreateDialog' : 'showPasswordDialog' }}', false)">
                        Tutup
                    </button>
                </div>

                @if($showCreateDialog)
                    <div>
                        <label for="new-user-name" class="mb-1 block text-sm font-medium">Nama</label>
                        <input id="new-user-name" type="text" maxlength="255" wire:model="newName" class="input" autocomplete="name">
                        @error('newName') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="new-user-email" class="mb-1 block text-sm font-medium">Email masuk</label>
                        <input id="new-user-email" type="email" maxlength="255" wire:model="newEmail" class="input" autocomplete="email">
                        @error('newEmail') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label for="new-user-password" class="mb-1 block text-sm font-medium">Password sementara</label>
                    <input id="new-user-password" type="password" wire:model="newPassword" class="input" autocomplete="new-password">
                    <p class="mt-1 text-xs text-content-secondary">Minimal 12 karakter. Berikan password awal kepada kasir melalui jalur aman.</p>
                    @error('newPassword') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new-user-password-confirmation" class="mb-1 block text-sm font-medium">Ulangi password</label>
                    <input id="new-user-password-confirmation" type="password" wire:model="newPassword_confirmation" class="input" autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $showCreateDialog ? 'Buat akun kasir' : 'Simpan password baru' }}</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </form>
        </div>
    @endif
</div>
