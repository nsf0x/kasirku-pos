<?php

namespace App\Livewire\Categories;

use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{
    public bool $showCategoryDialog = false;
    public ?int $categoryId = null;
    public string $name = '';

    public function openCreateDialog(): void
    {
        $this->authorize('create', Category::class);
        $this->resetValidation();
        $this->categoryId = null;
        $this->name = '';
        $this->showCategoryDialog = true;
    }

    public function edit(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);
        $this->authorize('update', $category);
        $this->resetValidation();
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->showCategoryDialog = true;
    }

    public function save(): void
    {
        $category = $this->categoryId
            ? Category::query()->findOrFail($this->categoryId)
            : null;

        $this->authorize($category ? 'update' : 'create', $category ?? Category::class);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category?->id)],
        ]);

        if ($category) {
            $oldName = $category->name;
            $category->update($data);
            ActivityLog::record(
                action: 'category.updated',
                subjectType: Category::class,
                subjectId: $category->id,
                meta: ['old_name' => $oldName, 'name' => $category->name]
            );
        } else {
            $category = Category::query()->create($data);
            ActivityLog::record(
                action: 'category.created',
                subjectType: Category::class,
                subjectId: $category->id,
                meta: ['name' => $category->name]
            );
        }

        $this->showCategoryDialog = false;
        session()->flash('success', 'Kategori berhasil disimpan.');
    }

    public function delete(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);
        $this->authorize('delete', $category);

        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'categoryDelete' => 'Kategori masih dipakai produk dan tidak dapat dihapus.',
            ]);
        }

        $name = $category->name;
        $category->delete();
        ActivityLog::record(
            action: 'category.deleted',
            subjectType: Category::class,
            subjectId: $category->id,
            meta: ['name' => $name]
        );

        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function render()
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::withCount('products')->get();

        return view('livewire.categories.index', compact('categories'))->layout('layouts.app', ['title' => 'Kategori']);
    }
}
