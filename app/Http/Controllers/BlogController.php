<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\NewsPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:60'],
        ]);

        $query = NewsPost::published()
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%")
                ->orWhere('body', 'like', "%{$term}%")))
            ->when($filters['category'] ?? null, fn ($q, $cat) => $q->where('category', $cat));

        $filtering = filled($filters['q'] ?? null) || filled($filters['category'] ?? null);
        $posts = $query->paginate(9)->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'filters' => $filters,
            'filtering' => $filtering,
            'categories' => NewsPost::published()->whereNotNull('category')->pluck('category')->unique()->values(),
            // The lead story is only pulled out on the unfiltered first page.
            'lead' => (! $filtering && $posts->currentPage() === 1) ? $posts->first() : null,
        ]);
    }

    public function show(string $slug)
    {
        $post = NewsPost::published()->where('slug', $slug)->with('author')->firstOrFail();

        $related = NewsPost::published()->where('id', '!=', $post->id)->take(3)->get();

        return view('blog.show', compact('post', 'related'));
    }
}
