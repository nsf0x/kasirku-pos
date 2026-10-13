<div class="min-h-screen" style="background:#F4F6F5">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 mb-6" style="font-size:13px; color:#5B6672">
            <a href="{{ route('products.index') }}" wire:navigate
               class="hover:underline" style="color:#0E7C86">Produk</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span style="color:#1B2430; font-weight:500">
                {{ $product ? 'Edit: ' . $product->name : 'Tambah produk' }}
            </span>
        </nav>

        <form wire:submit="save">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Main fields (left / 2-col) --}}
                <div class="lg:col-span-2 space-y-5">
                    <div class="rounded-xl p-6 space-y-5" style="background:#FFFFFF; border:1px solid #E5E7EB">
                        <h2 class="font-semibold" style="font-size:17px; color:#1B2430">Informasi produk</h2>

                        {{-- SKU --}}
                        <div>
                            <label class="block mb-1.5 font-medium" style="font-size:15px; color:#1B2430">SKU</label>
                            <input wire:model="sku" type="text" placeholder="Contoh: PRD-001"
                                   class="w-full rounded-lg border px-3 py-2.5 focus:outline-none focus:ring-2 transition"
                                   style="border-color: {{ $errors->has('sku') ? '#D64545' : '#D1D5DB' }}; font-size:15px; color:#1B2430">
                            @error('sku')
                                <p class="mt-1 text-xs" style="color:#D64545">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nama produk --}}
                        <div>
                            <label class="block mb-1.5 font-medium" style="font-size:15px; color:#1B2430">Nama produk</label>
                            <input wire:model="name" type="text" placeholder="Nama lengkap produk"
                                   class="w-full rounded-lg border px-3 py-2.5 focus:outline-none focus:ring-2 transition"
                                   style="border-color: {{ $errors->has('name') ? '#D64545' : '#D1D5DB' }}; font-size:15px; color:#1B2430">
                            @error('name')
                                <p class="mt-1 text-xs" style="color:#D64545">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label class="block mb-1.5 font-medium" style="font-size:15px; color:#1B2430">Kategori</label>
                            <select wire:model="category_id"
                                    class="w-full rounded-lg border px-3 py-2.5 focus:outline-none focus:ring-2 transition"
                                    style="border-color: {{ $errors->has('category_id') ? '#D64545' : '#D1D5DB' }}; font-size:15px; color:#1B2430">
                                <option value="">-- Pilih kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-xs" style="color:#D64545">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Harga beli (owner only) + Harga jual --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if(auth()->user()->isOwner())
                                <div>
                                    <label class="block mb-1.5 font-medium" style="font-size:15px; color:#1B2430">Harga beli</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 font-medium" style="font-size:15px; color:#5B6672">Rp</span>
                                        <input wire:model.live="cost_price" type="number" min="0" step="50" placeholder="0"
                                               class="w-full rounded-lg border pl-10 pr-3 py-2.5 focus:outline-none focus:ring-2 transition"
                                               style="border-color: {{ $errors->has('cost_price') ? '#D64545' : '#D1D5DB' }}; font-size:15px; color:#1B2430">
                                    </div>
                                    @error('cost_price')
                                        <p class="mt-1 text-xs" style="color:#D64545">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <div>
                                <label class="block mb-1.5 font-medium" style="font-size:15px; color:#1B2430">Harga jual</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 font-medium" style="font-size:15px; color:#5B6672">Rp</span>
                                    <input wire:model.live="price" type="number" min="0" step="50" placeholder="0"
                                           class="w-full rounded-lg border pl-10 pr-3 py-2.5 focus:outline-none focus:ring-2 transition"
                                           style="border-color: {{ $errors->has('price') ? '#D64545' : '#D1D5DB' }}; font-size:15px; color:#1B2430">
                                </div>
                                @error('price')
                                    <p class="mt-1 text-xs" style="color:#D64545">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Margin/profit display (owner only) --}}
                        @if(auth()->user()->isOwner())
                            @php
                                $profitVal = $profit ?? 0;
                                $marginVal = $margin;
                                $isPositive = $profitVal >= 0;
                            @endphp
                            <div class="rounded-lg px-4 py-3 flex items-center gap-2"
                                 style="background: {{ $isPositive ? '#F0FDF4' : '#FFF1F2' }}; border:1px solid {{ $isPositive ? '#BBF7D0' : '#FECDD3' }}">
                                <svg class="w-4 h-4 flex-shrink-0" style="color: {{ $isPositive ? '#2E9E5B' : '#D64545' }}" fill="currentColor" viewBox="0 0 20 20">
                                    @if($isPositive)
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    @else
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    @endif
                                </svg>
                                <span class="text-sm font-medium" style="color: {{ $isPositive ? '#2E9E5B' : '#D64545' }}">
                                    Laba: {{ rupiah($profitVal) }}
                                    @if($marginVal !== null)
                                        ({{ number_format($marginVal, 1) }}%)
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right sidebar: settings --}}
                <div class="space-y-5">

                    {{-- Stok --}}
                    <div class="rounded-xl p-6 space-y-4" style="background:#FFFFFF; border:1px solid #E5E7EB">
                        <h2 class="font-semibold" style="font-size:17px; color:#1B2430">Inventori</h2>

                        <div>
                            <label class="block mb-1.5 font-medium" style="font-size:15px; color:#1B2430">Stok saat ini</label>
                            <input wire:model="stock" type="number" min="0" placeholder="0"
                                   class="w-full rounded-lg border px-3 py-2.5 focus:outline-none focus:ring-2 transition"
                                   style="border-color: {{ $errors->has('stock') ? '#D64545' : '#D1D5DB' }}; font-size:15px; color:#1B2430">
                            @error('stock')
                                <p class="mt-1 text-xs" style="color:#D64545">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-1.5 font-medium" style="font-size:15px; color:#1B2430">Stok minimum</label>
                            <input wire:model="min_stock" type="number" min="0" placeholder="5"
                                   class="w-full rounded-lg border px-3 py-2.5 focus:outline-none focus:ring-2 transition"
                                   style="border-color: {{ $errors->has('min_stock') ? '#D64545' : '#D1D5DB' }}; font-size:15px; color:#1B2430">
                            <p class="mt-1 text-xs" style="color:#5B6672">Notifikasi muncul jika stok ≤ nilai ini</p>
                            @error('min_stock')
                                <p class="mt-1 text-xs" style="color:#D64545">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="rounded-xl p-6" style="background:#FFFFFF; border:1px solid #E5E7EB">
                        <h2 class="font-semibold mb-4" style="font-size:17px; color:#1B2430">Status produk</h2>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium" style="font-size:15px; color:#1B2430">
                                    {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                </p>
                                <p class="mt-0.5" style="font-size:13px; color:#5B6672">
                                    {{ $is_active ? 'Produk dapat dijual di kasir' : 'Produk disembunyikan dari kasir' }}
                                </p>
                            </div>
                            <button type="button" wire:click="$set('is_active', {{ $is_active ? 'false' : 'true' }})"
                                    class="relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                                    style="background: {{ $is_active ? '#0E7C86' : '#D1D5DB' }}">
                                <span class="pointer-events-none inline-block h-6 w-6 rounded-full bg-white shadow transform transition duration-200"
                                      style="transform: translateX({{ $is_active ? '28px' : '0px' }})"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="w-full rounded-lg py-3 font-semibold text-white transition-colors"
                                style="font-size:15px; background:#0E7C86"
                                onmouseover="this.style.background='#0A646C'" onmouseout="this.style.background='#0E7C86'">
                            Simpan produk
                        </button>
                        <a href="{{ route('products.index') }}" wire:navigate
                           class="w-full rounded-lg py-3 font-semibold text-center transition-colors"
                           style="font-size:15px; color:#5B6672; background:#F3F4F6; border:1px solid #E5E7EB"
                           onmouseover="this.style.background='#E5E7EB'" onmouseout="this.style.background='#F3F4F6'">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
