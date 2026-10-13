<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isOwner()
            ? redirect()->route('dashboard')
            : redirect()->route('cashier.index');
    }

    return view('welcome');
});

Route::get('/demo-login/{role}', fn () => redirect()
    ->route('login')
    ->with('status', 'Silakan masuk menggunakan akun usaha Anda.'))
    ->whereIn('role', ['owner', 'cashier']);

Route::middleware(['auth', 'active'])->group(function () {

    Route::middleware('can:viewAny,App\Models\User')->group(function () {
        Route::get('/dashboard', App\Livewire\Dashboard\Index::class)->name('dashboard');
        Route::get('/produk', App\Livewire\Products\Index::class)->name('products.index');
        Route::get('/produk/tambah', App\Livewire\Products\Form::class)->name('products.create');
        Route::get('/produk/{product}/edit', App\Livewire\Products\Form::class)->name('products.edit');
        Route::get('/kategori', App\Livewire\Categories\Index::class)->name('categories.index');
        Route::get('/laporan', App\Livewire\Reports\Index::class)->name('reports.index');
        Route::get('/pengguna', App\Livewire\Users\Index::class)->name('users.index');
    });

    Route::get('/kasir', App\Livewire\Cashier\Index::class)->name('cashier.index');
    Route::get('/riwayat', App\Livewire\Sales\Index::class)->name('sales.index');
    Route::get('/riwayat/{sale}', App\Livewire\Sales\Show::class)->name('sales.show');
    Route::get('/stok', App\Livewire\Stock\Index::class)->name('stock.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
