<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Models\NewsPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends ResourceController
{
    protected function config(): array
    {
        return [
            'model' => NewsPost::class,
            'title' => 'Blog posts',
            'singular' => 'post',
            'subtitle' => 'Articles and company news. A post goes live at its publish date; leave it empty to keep a draft.',
            'icon' => 'newspaper',
            'route' => 'admin.posts',
            'search' => ['title', 'category', 'excerpt'],
            'order' => ['published_at', 'desc'],
            'view' => fn (NewsPost $p) => $p->published_at && $p->published_at->isPast() ? route('blog.show', $p->slug) : null,
            'columns' => [
                ['label' => 'Cover', 'type' => 'image', 'value' => fn (NewsPost $p) => $p->coverUrl()],
                ['label' => 'Title', 'key' => 'title', 'main' => true, 'sub' => fn (NewsPost $p) => $p->category],
                ['label' => 'Status', 'value' => fn (NewsPost $p) => ! $p->published_at ? 'draft' : ($p->published_at->isFuture() ? 'scheduled' : 'published'), 'type' => 'badge'],
                ['label' => 'Publish date', 'key' => 'published_at', 'type' => 'date'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:200'], 'full' => true],
                ['name' => 'category', 'label' => 'Category', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60'], 'list' => ['Buying guide', 'Agriculture', 'Construction', 'Company news', 'Market insight']],
                ['name' => 'published_at', 'label' => 'Publish date & time', 'type' => 'datetime', 'rules' => ['nullable', 'date'], 'help' => 'Empty = draft (not shown on the website).'],
                ['name' => 'excerpt', 'label' => 'Short summary', 'type' => 'textarea', 'rows' => 3, 'rules' => ['nullable', 'string', 'max:300'], 'help' => 'Shown on the blog list and in Google results.'],
                ['name' => 'body', 'label' => 'Article', 'type' => 'richtext', 'ai' => 'blog', 'rules' => ['required', 'string', 'max:100000'], 'help' => 'Use the toolbar for headings, bold, lists and links. Press Enter for a new paragraph. Type a title above, then use the AI button to draft the article; edit it before publishing.'],
                ['name' => 'cover_image', 'label' => 'Cover photo', 'type' => 'image', 'folder' => 'blog'],
            ],
        ];
    }

    protected function prepare(array $data, ?Model $model, Request $request): array
    {
        if (! $model) {
            $base = Str::slug($data['title']) ?: 'post';
            $slug = $base;
            for ($i = 2; NewsPost::where('slug', $slug)->exists(); $i++) {
                $slug = $base.'-'.$i;
            }
            $data['slug'] = $slug;
            $data['author_id'] = $request->user()->id;
        }

        return $data;
    }
}
