<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Models\Property;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PropertyController extends ResourceController
{
    protected function config(): array
    {
        return [
            'model' => Property::class,
            'title' => 'Properties',
            'singular' => 'property',
            'subtitle' => 'Listings shown on the website and shared by your realtors.',
            'icon' => 'building',
            'route' => 'admin.properties',
            'search' => ['title', 'location', 'type'],
            'filters' => [
                ['name' => 'status', 'label' => 'Any status', 'options' => ['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold']],
                ['name' => 'sector', 'label' => 'Any service', 'options' => Property::SECTORS],
            ],
            'view' => fn (Property $p) => route('properties.show', $p->slug),
            'columns' => [
                ['label' => 'Photo', 'type' => 'image', 'value' => fn (Property $p) => $p->coverUrl()],
                ['label' => 'Property', 'key' => 'title', 'main' => true, 'sub' => fn (Property $p) => $p->location],
                ['label' => 'Service', 'value' => fn (Property $p) => $p->sectorLabel()],
                ['label' => 'Price', 'key' => 'price', 'type' => 'money'],
                ['label' => 'Units left', 'value' => fn (Property $p) => $p->isMultiUnit() ? $p->unitsRemaining().' / '.$p->units_total : '—'],
                ['label' => 'Status', 'key' => 'status', 'type' => 'badge'],
                ['label' => 'Featured', 'key' => 'featured', 'type' => 'bool'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:200'], 'full' => true, 'placeholder' => 'e.g. Prime Residential Plot, Amansea'],
                ['name' => 'sector', 'label' => 'Service', 'type' => 'select', 'options' => Property::SECTORS, 'rules' => ['required', 'in:'.implode(',', array_keys(Property::SECTORS))]],
                ['name' => 'type', 'label' => 'Property type', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:80'], 'placeholder' => 'Land, House, Farmland…', 'list' => ['Land', 'House', 'Apartment', 'Farmland', 'Commercial']],
                ['name' => 'price', 'label' => 'Price (₦)', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0', 'max:999999999999'], 'section' => 'Price & status', 'help' => 'Numbers only, e.g. 8500000. For bulk land, this is the price per unit/plot.'],
                ['name' => 'units_total', 'label' => 'Total units / plots', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1', 'max:999999'], 'section' => 'Price & status', 'help' => 'Leave blank for a single listing sold whole (a house, one plot). Set this for bulk land divided into multiple plots — the system then tracks how many are left automatically.'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold'], 'rules' => ['required', 'in:available,reserved,sold'], 'section' => 'Price & status'],
                ['name' => 'featured', 'label' => 'Featured on home page', 'type' => 'toggle', 'help' => 'Featured listings appear first.', 'section' => 'Price & status'],
                ['name' => 'location', 'label' => 'Location', 'type' => 'text', 'rules' => ['required', 'string', 'max:200'], 'section' => 'Details', 'placeholder' => 'Area, town, state'],
                ['name' => 'size', 'label' => 'Size (sqm)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:40'], 'section' => 'Details', 'placeholder' => '600'],
                ['name' => 'bedrooms', 'label' => 'Bedrooms', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:10'], 'section' => 'Details'],
                ['name' => 'bathrooms', 'label' => 'Bathrooms', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:10'], 'section' => 'Details'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'richtext', 'rules' => ['nullable', 'string', 'max:30000'], 'section' => 'Details', 'help' => 'Use the toolbar for headings, bold, lists and links. Press Enter for a new paragraph.'],
                ['name' => 'images', 'label' => 'Photos', 'type' => 'images', 'folder' => 'properties', 'max' => 15, 'section' => 'Photos', 'help' => 'The first photo is the cover.'],
            ],
        ];
    }

    protected function prepare(array $data, ?Model $model, Request $request): array
    {
        if (! $model) {
            $base = Str::slug($data['title']) ?: 'property';
            $slug = $base;
            for ($i = 2; Property::where('slug', $slug)->exists(); $i++) {
                $slug = $base.'-'.$i;
            }
            $data['slug'] = $slug;
            $data['created_by'] = $request->user()->id;
        }

        return $data;
    }
}
