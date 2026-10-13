<?php

namespace App\Livewire\Products;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Form extends Component
{
    public ?Product $product = null;
    public string $sku = '';
    public string $name = '';
    public int $category_id = 0;
    public string $cost_price = '';
    public string $price = '';
    public int $stock = 0;
    public int $min_stock = 5;
    public bool $is_active = true;

    protected function rules(): array
    {
        $uniqueSkuRule = $this->product
            ? 'required|string|max:50|unique:products,sku,' . $this->product->id
            : 'required|string|max:50|unique:products,sku';

        return [
            'sku'         => $uniqueSkuRule,
            'name'        => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'cost_price'  => 'required|numeric|min:0',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'min_stock'   => 'required|integer|min:0',
        ];
    }

    protected $messages = [
        'sku.required'         => 'SKU wajib diisi.',
        'sku.unique'           => 'SKU sudah dipakai produk lain.',
        'name.required'        => 'Nama produk wajib diisi.',
        'category_id.required' => 'Pilih kategori.',
        'cost_price.required'  => 'Harga beli wajib diisi.',
        'price.required'       => 'Harga jual wajib diisi.',
    ];

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->product    = $product;
            $this->sku        = $product->sku;
            $this->name       = $product->name;
            $this->category_id = $product->category_id;
            $this->cost_price = (string) $product->cost_price;
            $this->price      = (string) $product->price;
            $this->stock      = $product->stock;
            $this->min_stock  = $product->min_stock;
            $this->is_active  = $product->is_active;
        }
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['cost_price'] = (float) $data['cost_price'];
        $data['price']      = (float) $data['price'];
        $targetStock = (int) $data['stock'];
        unset($data['stock']);

        if ($this->product && $this->product->exists) {
            $this->authorize('update', $this->product);
            DB::transaction(function () use ($data, $targetStock) {
                $old = $this->product->only(['price', 'cost_price']);
                $this->product->update($data);

                if ($old['price'] != $data['price'] || $old['cost_price'] != $data['cost_price']) {
                    ActivityLog::record(
                        action: 'product.price_updated',
                        userId: auth()->id(),
                        subjectType: Product::class,
                        subjectId: $this->product->id,
                        meta: ['old' => $old, 'new' => ['price' => $data['price'], 'cost_price' => $data['cost_price']]]
                    );
                }

                $this->product->refresh();
                if ($targetStock !== $this->product->stock) {
                    app(StockService::class)->adjustStock(
                        $this->product,
                        $targetStock,
                        auth()->id(),
                        'Penyesuaian dari formulir produk'
                    );
                }
            });

            session()->flash('success', 'Produk disimpan.');
            $this->redirect(route('products.index'), navigate: true);
        } else {
            $this->authorize('create', Product::class);
            DB::transaction(function () use ($data, $targetStock) {
                $data['stock'] = 0;
                $product = Product::create($data);

                if ($targetStock > 0) {
                    app(StockService::class)->addStock(
                        $product,
                        $targetStock,
                        auth()->id(),
                        'Stok awal saat produk dibuat'
                    );
                }
            });
            session()->flash('success', 'Produk ditambahkan.');
            $this->redirect(route('products.index'), navigate: true);
        }
    }

    public function getMarginAttribute(): ?float
    {
        $cost  = (float) $this->cost_price;
        $price = (float) $this->price;
        if ($price <= 0) return null;
        return (($price - $cost) / $price) * 100;
    }

    public function getProfitAttribute(): float
    {
        return (float) $this->price - (float) $this->cost_price;
    }

    public function render()
    {
        $categories = Category::orderBy('name')->get();
        $title = $this->product ? 'Edit produk' : 'Tambah produk';

        return view('livewire.products.form', [
            'categories' => $categories,
            'margin'     => $this->getMarginAttribute(),
            'profit'     => $this->getProfitAttribute(),
        ])->layout('layouts.app', ['title' => $title]);
    }
}
