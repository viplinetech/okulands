<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends ResourceController
{
    protected function config(): array
    {
        $icons = ['map' => 'Map / land', 'building' => 'Building', 'leaf' => 'Leaf / farming', 'shield' => 'Shield / verified', 'compass' => 'Compass / advice', 'wallet' => 'Wallet / payments', 'key' => 'Key', 'users' => 'People'];

        return [
            'model' => Service::class,
            'title' => 'Services',
            'singular' => 'service',
            'subtitle' => 'What Oku Lands offers, shown on the Services page and the home page.',
            'icon' => 'key',
            'route' => 'admin.services',
            'search' => ['title', 'summary'],
            'order' => ['sort_order', 'asc'],
            'view' => fn () => route('services'),
            'columns' => [
                ['label' => 'Image', 'type' => 'image', 'value' => fn (Service $s) => $s->imageUrl()],
                ['label' => 'Service', 'key' => 'title', 'main' => true, 'sub' => fn (Service $s) => \Illuminate\Support\Str::limit($s->summary, 60)],
                ['label' => 'Order', 'key' => 'sort_order'],
                ['label' => 'Home feature', 'key' => 'is_featured', 'type' => 'bool'],
                ['label' => 'Visible', 'key' => 'is_active', 'type' => 'bool'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:120'], 'full' => true],
                ['name' => 'sector', 'label' => 'Related service', 'type' => 'select', 'options' => ['real_estate' => 'Real Estate', 'construction' => 'Construction', 'agriculture' => 'Agriculture', 'general' => 'General'], 'rules' => ['required', 'in:real_estate,construction,agriculture,general'], 'help' => 'Used to pre-select the enquiry form.'],
                ['name' => 'icon', 'label' => 'Icon', 'type' => 'select', 'options' => $icons, 'rules' => ['nullable', 'in:'.implode(',', array_keys($icons))]],
                ['name' => 'summary', 'label' => 'Short summary', 'type' => 'textarea', 'rows' => 3, 'rules' => ['required', 'string', 'max:300'], 'help' => 'One or two sentences (max 300 characters).'],
                ['name' => 'description', 'label' => 'Full description', 'type' => 'richtext', 'rules' => ['nullable', 'string', 'max:30000'], 'help' => 'Use the toolbar for headings, bold, lists and links. Press Enter for a new paragraph.'],
                ['name' => 'features', 'label' => 'Key points', 'type' => 'lines', 'rules' => ['nullable', 'string', 'max:3000'], 'help' => 'One benefit per line. Shown as a checklist.'],
                ['name' => 'image', 'label' => 'Photo', 'type' => 'image', 'folder' => 'services', 'section' => 'Display'],
                ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:9999'], 'section' => 'Display', 'help' => 'Lower numbers appear first.'],
                ['name' => 'is_featured', 'label' => 'Feature on the home page', 'type' => 'toggle', 'section' => 'Display', 'help' => 'The first three featured services get the large home panels.'],
                ['name' => 'is_active', 'label' => 'Visible on the website', 'type' => 'toggle', 'section' => 'Display', 'default' => true],
            ],
        ];
    }

    protected function prepare(array $data, ?Model $model, Request $request): array
    {
        if (! $model) {
            $base = Str::slug($data['title']) ?: 'service';
            $slug = $base;
            for ($i = 2; Service::where('slug', $slug)->exists(); $i++) {
                $slug = $base.'-'.$i;
            }
            $data['slug'] = $slug;
        }

        return $data;
    }
}
