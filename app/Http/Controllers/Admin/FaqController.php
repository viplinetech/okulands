<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Models\FaqItem;

class FaqController extends ResourceController
{
    protected function config(): array
    {
        return [
            'model' => FaqItem::class,
            'title' => 'FAQs',
            'singular' => 'question',
            'subtitle' => 'The knowledge base behind the Help Center. Helpful votes show which answers need work.',
            'icon' => 'help',
            'route' => 'admin.faqs',
            'search' => ['question', 'answer', 'category'],
            'order' => ['sort_order', 'asc'],
            'per_page' => 20,
            'view' => fn () => route('faq'),
            'columns' => [
                ['label' => 'Question', 'key' => 'question', 'main' => true, 'sub' => fn (FaqItem $f) => $f->category],
                ['label' => 'Helpful', 'value' => fn (FaqItem $f) => $f->helpful_yes.' 👍  '.$f->helpful_no.' 👎'],
                ['label' => 'Order', 'key' => 'sort_order'],
                ['label' => 'Visible', 'key' => 'is_active', 'type' => 'bool'],
            ],
            'fields' => [
                ['name' => 'category', 'label' => 'Topic', 'type' => 'text', 'rules' => ['required', 'string', 'max:60'], 'list' => FaqItem::query()->distinct()->pluck('category')->all() ?: ['General'], 'default' => 'General', 'help' => 'Questions with the same topic are grouped together.'],
                ['name' => 'sort_order', 'label' => 'Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:9999'], 'help' => 'Lower numbers appear first.'],
                ['name' => 'question', 'label' => 'Question', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'full' => true],
                ['name' => 'answer', 'label' => 'Answer', 'type' => 'richtext', 'compact' => true, 'rules' => ['required', 'string', 'max:20000'], 'help' => 'Use the toolbar for headings, bold, lists and links. Press Enter for a new paragraph.'],
                ['name' => 'is_active', 'label' => 'Visible on the website', 'type' => 'toggle', 'default' => true],
            ],
        ];
    }
}
