<div class="min-h-screen" style="background:#F4F6F5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Flash message --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="mb-5 flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium"
                 style="background:#D1FAE5; color:#065F46; border:1px solid #6EE7B7">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="font-bold" style="font-size:30px; color:#1B2430">Produk</h1>
                <p class="mt-0.5" style="font-size:13px; color:#5B6672">Kelola semua produk yang dijual</p>
            </div>
            <a href="{{ route('products.create') }}" wire:navigate
               class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 font-semibold text-white transition-colors"
               style="font-size:15px; background:#0E7C86"
               onmouseover="this.style.background='#0A646C'" onmouseout="this.style.background='#0E7C86'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah produk
            </a>
        </div>

        {{-- Filters --}}
        <div class="rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-center" style="background:#FFFFFF; border:1px solid #E5E7EB">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color:#5B6672" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
                <input wire:model.live.debounce.400ms="search" type="search" placeholder="Cari nama atau SKU..."
                       class="w-full pl-9 pr-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-2"
                       style="border-color:#D1D5DB; font-size:15px; color:#1B2430; --tw-ring-color:#0E7C86">
            </div>

            {{-- Category filter --}}
            <select wire:model.live="categoryFilter"
                    class="rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                    style="border-color:#D1D5DB; font-size:15px; color:#1B2430; --tw-ring-color:#0E7C86; min-width:160px">
                <option value="">Semua kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            {{-- Status filter --}}
            <select wire:model.live="statusFilter"
                    class="rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                    style="border-color:#D1D5DB; font-size:15px; color:#1B2430; --tw-ring-color:#0E7C86; min-width:140px">
                <option value="">Semua status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>

            {{-- Stock filter --}}
            <select wire:model.live="stockFilter"
                    class="rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                    style="border-color:#D1D5DB; font-size:15px; color:#1B2430; --tw-ring-color:#0E7C86; min-width:140px">
                <option value="">Semua stok</option>
                <option value="low">Stok rendah</option>
                <option value="out">Habis</option>
            </select>
        </div>

        {{-- Table --}}
        <div class="rounded-xl overflow-hidden" style="background:#FFFFFF; border:1px solid #E5E7EB">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#F9FAFB; border-bottom:1px solid #E5E7EB">
                            <th class="px-4 py-3 text-left font-semibold" style="color:#5B6672; font-size:13px">SKU</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color:#5B6672; font-size:13px">Nama produk</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color:#5B6672; font-size:13px">Kategori</th>
                            @if(auth()->user()->isOwner())
                                <th class="px-4 py-3 text-right font-semibold" style="color:#5B6672; font-size:13px">Harga beli</th>
                            @endif
                            <th class="px-4 py-3 text-right font-semibold" style="color:#5B6672; font-size:13px">Harga jual</th>
                            <th class="px-4 py-3 text-center font-semibold" style="color:#5B6672; font-size:13px">Stok</th>
                            <th class="px-4 py-3 text-center font-semibold" style="color:#5B6672; font-size:13px">Status</th>
                            <th class="px-4 py-3 text-center font-semibold" style="color:#5B6672; font-size:13px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:#F3F4F6">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3" style="font-size:13px; color:#5B6672; font-family:monospace">
                                    {{ $product->sku }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium" style="font-size:15px; color:#1B2430">{{ $product->name }}</span>
                                </td>
                                <td class="px-4 py-3" style="font-size:15px; color:#5B6672">
                                    {{ $product->category?->name ?? '-' }}
                                </td>
                                @if(auth()->user()->isOwner())
                                    <td class="px-4 py-3 text-right" style="font-size:15px; color:#5B6672">
                                        {{ rupiah($product->cost_price) }}
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-right font-medium" style="font-size:15px; color:#1B2430">
                                    {{ rupiah($product->price) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($product->stock === 0)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                              style="background:#FEE2E2; color:#D64545">
                                            Habis
                                        </span>
                                    @elseif($product->is_low_stock)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                              style="background:#FEF3C7; color:#D98A1F">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            {{ $product->stock }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                              style="background:#D1FAE5; color:#2E9E5B">
                                            {{ $product->stock }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="toggleActive({{ $product->id }})"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                            style="background: {{ $product->is_active ? '#0E7C86' : '#D1D5DB' }}"
                                            title="{{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform transition duration-200"
                                              style="transform: translateX({{ $product->is_active ? '20px' : '0' }})"></span>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('products.edit', $product) }}" wire:navigate
                                       class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                                       style="color:#0E7C86; background:#F0FDFA; border:1px solid #99F6E4"
                                       onmouseover="this.style.background='#CCFBF1'" onmouseout="this.style.background='#F0FDFA'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isOwner() ? 8 : 7 }}" class="px-4 py-12 text-center" style="color:#5B6672; font-size:15px">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-10 h-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4m8-6h.01"/></svg>
                                        <span>Tidak ada produk ditemukan</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="px-4 py-3" style="border-top:1px solid #E5E7EB">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
