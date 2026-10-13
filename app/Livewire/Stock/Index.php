<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public bool $showStockDialog = false;
    public string $movementType = 'in';
    public string $productId = '';
    public string $quantity = '';
    public string $note = '';

    public function openStockDialog(string $type): void
    {
        $this->authorize('create', StockMovement::class);

        if (! in_array($type, ['in', 'out', 'adjust'], true)) {
            abort(404);
        }

        $this->resetValidation();
        $this->movementType = $type;
        $this->productId = '';
        $this->quantity = '';
        $this->note = '';
        $this->showStockDialog = true;
    }

    public function recordStockMovement(StockService $stockService): void
    {
        $this->authorize('create', StockMovement::class);

        $data = $this->validate([
            'movementType' => ['required', 'in:in,out,adjust'],
            'productId' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        if ($data['movementType'] !== 'adjust' && (int) $data['quantity'] < 1) {
            $this->addError('quantity', 'Jumlah harus lebih dari nol.');
            return;
        }

        $product = Product::query()->findOrFail((int) $data['productId']);

        switch ($data['movementType']) {
            case 'in':
                $stockService->addStock($product, (int) $data['quantity'], auth()->id(), $data['note']);
                break;
            case 'out':
                $stockService->removeStock($product, (int) $data['quantity'], auth()->id(), $data['note']);
                break;
            case 'adjust':
                $stockService->adjustStock($product, (int) $data['quantity'], auth()->id(), $data['note']);
                break;
        }

        $this->showStockDialog = false;
        session()->flash('success', 'Mutasi stok berhasil dicatat.');
    }

    public function render()
    {
        $products = Product::with('category')
            ->orderBy('stock')
            ->paginate(12);
        $allProducts = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'stock']);

        $movements = StockMovement::with(['product', 'user'])
            ->latest('created_at')
            ->limit(12)
            ->get();

        return view('livewire.stock.index', compact('products', 'allProducts', 'movements'))
            ->layout('layouts.app', ['title' => 'Stok']);
    }
}
