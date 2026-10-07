<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Models\GalleryItem;
use App\Services\ImageProcessor;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GalleryController extends ResourceController
{
    protected function config(): array
    {
        return [
            'model' => GalleryItem::class,
            'title' => 'Gallery',
            'singular' => 'photo',
            'subtitle' => 'Photos of your projects, shown on the Gallery page.',
            'icon' => 'image',
            'route' => 'admin.gallery',
            'search' => ['title', 'caption', 'category'],
            'order' => ['sort_order', 'asc'],
            'per_page' => 24,
            'before' => 'admin.gallery.bulk',
            'filters' => [],
            'view' => fn () => route('gallery'),
            'columns' => [
                ['label' => 'Photo', 'type' => 'image', 'value' => fn (GalleryItem $g) => $g->imageUrl()],
                ['label' => 'Title', 'key' => 'title', 'main' => true, 'sub' => fn (GalleryItem $g) => $g->category],
                ['label' => 'Category', 'key' => 'category'],
                ['label' => 'Featured', 'key' => 'is_featured', 'type' => 'bool'],
                ['label' => 'Visible', 'key' => 'is_active', 'type' => 'bool'],
            ],
            'fields' => [
                ['name' => 'image', 'label' => 'Photo', 'type' => 'image', 'folder' => 'gallery', 'required' => true],
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120']],
                ['name' => 'category', 'label' => 'Category', 'type' => 'text', 'rules' => ['required', 'string', 'max:60'], 'list' => ['Real Estate', 'Construction', 'Agriculture', 'Events'], 'default' => 'Real Estate'],
                ['name' => 'caption', 'label' => 'Caption', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:200'], 'full' => true],
                ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:9999']],
                ['name' => 'is_featured', 'label' => 'Show on the home page', 'type' => 'toggle'],
                ['name' => 'is_active', 'label' => 'Visible on the website', 'type' => 'toggle', 'default' => true],
            ],
        ];
    }

    /** Bulk upload: several photos at once share the category chosen on the form. */
    public function bulk(Request $request, ImageProcessor $images): RedirectResponse
    {
        $data = $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:30'],
            'photos.*' => ['file', 'max:12288'],
            'category' => ['required', 'string', 'max:60'],
        ], ['photos.required' => 'Choose at least one photo.']);

        $count = 0;
        try {
            foreach ($request->file('photos') as $file) {
                GalleryItem::create([
                    'image' => $images->store($file, 'gallery', 1800),
                    'category' => $data['category'],
                    'is_active' => true,
                    'sort_order' => 0,
                ]);
                $count++;
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['photos' => $e->getMessage()]);
        }

        Audit::log('admin.gallery.bulk_uploaded', null, "Uploaded {$count} gallery photo(s) to {$data['category']}");

        return redirect()->route('admin.gallery.index')->with('success', "{$count} photo(s) added to the gallery.");
    }
}
