<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?string $status = null;
    public ?string $paymentMethod = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function render()
    {
        $sales = Sale::with(['cashier', 'items'])
            ->when(auth()->user()->isCashier(), fn ($query) => $query->where('user_id', auth()->id()))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->paymentMethod, fn ($query) => $query->where('payment_method', $this->paymentMethod))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(12);

        return view('livewire.sales.index', compact('sales'))->layout('layouts.app', ['title' => 'Riwayat']);
    }
}
