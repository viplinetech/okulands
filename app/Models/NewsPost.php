<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

use App\Support\SiteDefaults;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NewsPost extends Model
{
    protected $fillable = ['title', 'slug', 'cover_image', 'excerpt', 'category', 'body', 'author_id', 'published_at'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /** Only posts whose publish date has arrived, newest first. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now())->orderByDesc('published_at');
    }

    public function coverUrl(): string
    {
        return SiteSetting::media($this->cover_image, \App\Support\Placeholder::url('Blog · Article cover', (string) ($this->slug ?? $this->id)));
    }

    /** Admin-written excerpt, or the start of the body. */
    public function summary(int $limit = 150): string
    {
        return $this->excerpt ?: \App\Support\RichText::lead($this->body, $limit);
    }

    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(\App\Support\RichText::text($this->body)) / 200));
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
