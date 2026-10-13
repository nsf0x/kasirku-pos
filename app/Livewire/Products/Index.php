<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = '';
    public string $stockFilter = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingStockFilter(): void { $this->resetPage(); }

    public function toggleActive(Product $product): void
    {
        $this->authorize('update', $product);
        $product->update(['is_active' => !$product->is_active]);
        session()->flash('success', $product->fresh()->is_active ? 'Produk diaktifkan.' : 'Produk dinonaktifkan.');
    }

    public function render()
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with('category')
            ->when($this->search, fn($q) => $q->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            }))
            ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->statusFilter === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->stockFilter === 'low', fn($q) => $q->lowStock())
            ->when($this->stockFilter === 'out', fn($q) => $q->outOfStock())
            ->orderBy('name')
            ->paginate(20);

        $categories = Category::orderBy('name')->get();

        return view('livewire.products.index', compact('products', 'categories'))
            ->layout('layouts.app', ['title' => 'Produk']);
    }
}
