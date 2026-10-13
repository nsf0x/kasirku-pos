<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Services\SaleService;
use Livewire\Component;

class Show extends Component
{
    public Sale $sale;
    public bool $showVoidDialog = false;
    public string $voidReason = '';

    public function mount(Sale $sale): void
    {
        $this->authorize('view', $sale);
        $this->sale = $sale->load(['cashier', 'items.product', 'voidedByUser']);
    }

    public function render()
    {
        $this->authorize('view', $this->sale);

        return view('livewire.sales.show', ['sale' => $this->sale])->layout('layouts.app', ['title' => 'Detail transaksi']);
    }

    public function openVoidDialog(): void
    {
        $this->authorize('void', $this->sale);
        $this->resetValidation();
        $this->voidReason = '';
        $this->showVoidDialog = true;
    }

    public function void(SaleService $saleService): void
    {
        $this->authorize('void', $this->sale);
        $data = $this->validate([
            'voidReason' => ['required', 'string', 'max:255'],
        ]);

        $this->sale = $saleService->void($this->sale, auth()->id(), $data['voidReason']);
        $this->showVoidDialog = false;
        session()->flash('success', 'Transaksi dibatalkan dan stok dikembalikan.');
    }
}
