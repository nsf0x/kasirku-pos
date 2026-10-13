<div class="card p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-content-secondary">Pengelompokan produk</p>
            <h3 class="text-xl font-semibold">Kategori</h3>
        </div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="openCreateDialog">Tambah kategori</button>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($categories as $category)
            <div class="rounded-xl border border-border p-4 dark:border-border-dark">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold">{{ $category->name }}</h4>
                    <span class="badge badge-gray">{{ $category->products_count }} produk</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="edit({{ $category->id }})">Ubah nama</button>
                    <button type="button" class="btn btn-danger btn-sm" wire:click="delete({{ $category->id }})" wire:confirm="Hapus kategori {{ $category->name }}? Kategori yang masih memiliki produk tidak dapat dihapus.">Hapus</button>
                </div>
            </div>
        @empty
            <p class="text-content-secondary">Belum ada kategori.</p>
        @endforelse
    </div>

    @error('categoryDelete')
        <p class="mt-4 rounded-xl bg-danger-50 px-4 py-3 text-sm text-danger-500">{{ $message }}</p>
    @enderror

    @if($showCategoryDialog)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" role="dialog" aria-modal="true" aria-labelledby="category-dialog-title">
            <form wire:submit="save" class="w-full max-w-md space-y-4 rounded-2xl border border-border bg-surface p-5 shadow-dialog dark:border-border-dark dark:bg-surface-dark">
                <div class="flex items-start justify-between gap-4">
                    <h3 id="category-dialog-title" class="text-xl font-semibold">{{ $categoryId ? 'Ubah kategori' : 'Tambah kategori' }}</h3>
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="$set('showCategoryDialog', false)">Tutup</button>
                </div>
                <div>
                    <label for="category-name" class="mb-1 block text-sm font-medium">Nama kategori</label>
                    <input id="category-name" type="text" maxlength="100" wire:model="name" class="input" autofocus>
                    @error('name') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Simpan kategori</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </form>
        </div>
    @endif
</div>
