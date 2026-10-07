<x-layouts.admin title="Site settings">
    <x-app.page-header title="Website settings" kicker="Website" subtitle="Only the things that rarely change: logo, photos, contact details and the figures on the counters. For all the wording on the site, use Page content." />

    @error('upload')<div class="flash flash-error"><x-icon name="alert" class="mt-px h-5 w-5 shrink-0" /><span>{{ $message }}</span></div>@enderror

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" data-multi>
        @csrf @method('PUT')

        <div data-tabs>
            <div class="tabs mb-5">
                @foreach (['general' => 'General', 'home' => 'Home & hero', 'about' => 'About', 'ceo' => 'CEO & team', 'banners' => 'Page banners', 'contact' => 'Contact & social', 'seo' => 'SEO & indexing'] as $id => $label)
                    <button type="button" data-tab="{{ $id }}" class="tab">{{ $label }}</button>
                @endforeach
            </div>

            {{-- General --}}
            <div data-tab-panel="general" class="space-y-4">
                <section class="card">
                    <h2 class="card-title">Brand</h2>
                    <div class="form-grid mt-5">
                        <div><label for="site_name" class="field-label">Company name <span class="text-flag-500">*</span></label><input id="site_name" name="site_name" required value="{{ old('site_name', $s->site_name) }}" class="field">@error('site_name')<p class="err">{{ $message }}</p>@enderror</div>
                        <x-admin.image name="logo" label="Logo (for light backgrounds)" :current="$s->logo_path" :wide="true" />
                        <x-admin.image name="logo_dark" label="Logo (for dark backgrounds)" :current="$s->logo_dark_path" :wide="true" help="Use a white/light version." />
                        <x-admin.image name="favicon" label="Favicon (browser tab icon)" :current="$s->favicon_path" help="Square, at least 128px." />
                        <div>
                            <label for="default_theme" class="field-label">Default theme</label>
                            <select id="default_theme" name="default_theme" class="field"><option value="light" @selected(old('default_theme', $s->default_theme) === 'light')>Light (recommended)</option><option value="dark" @selected(old('default_theme', $s->default_theme) === 'dark')>Dark</option></select>
                            <p class="hint">What first-time visitors see. They can always switch.</p>
                        </div>
                    </div>
                </section>
                <section class="card">
                    <h2 class="card-title">Realtor programme</h2>
                    <label class="mt-5 flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-ink/10 bg-page px-4 py-3.5">
                        <span><span class="block text-sm font-bold text-ink">Allow new realtor sign-ups</span><span class="mt-0.5 block text-xs text-mute">Turn off to close registration instantly. Existing realtors are not affected.</span></span>
                        <input type="hidden" name="realtor_registration_enabled" value="0">
                        <input type="checkbox" name="realtor_registration_enabled" value="1" class="sr-only" @checked(old('realtor_registration_enabled', $s->realtor_registration_enabled ?? true))>
                        <span class="switch" aria-hidden="true"></span>
                    </label>
                </section>
            </div>

            {{-- Home & hero --}}
            <div data-tab-panel="home" class="space-y-4" hidden>
                <section class="card">
                    <h2 class="card-title">Hero</h2>
                    <div class="form-grid mt-5">
                        <div class="form-full">
                            <span class="field-label">Hero photos (they rotate)</span>
                            @php $hero = (array) ($s->hero_images ?? []); @endphp
                            @if ($hero)
                                <div class="mb-3 grid grid-cols-3 gap-3 sm:grid-cols-4">
                                    @foreach ($hero as $path)
                                        <div class="space-y-1.5" data-photo>
                                            <span class="thumb block">
                                                <img src="{{ \App\Models\SiteSetting::media($path) }}" alt="" class="h-full w-full object-cover">
                                            </span>
                                            <input type="checkbox" id="remove_hero_{{ $loop->index }}" name="remove_hero_images[]" value="{{ $path }}" class="sr-only" tabindex="-1">
                                            <button type="button" class="btn btn-ghost btn-sm !px-3 !py-1.5 text-xs text-flag-500" data-remove-photo data-remove-target="remove_hero_{{ $loop->index }}" data-remove-label="Home hero photo">
                                                <x-icon name="trash" class="h-3.5 w-3.5" /> <span data-remove-text>Remove photo</span>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="hint mb-3">Each photo can be removed on its own. The change is saved when you press Save.</p>
                            @else
                                <p class="hint mb-3">No photos uploaded yet: the website shows the built-in Nigeria photos.</p>
                            @endif
                            <label class="dropzone cursor-pointer"><x-icon name="upload" class="h-6 w-6" /><span class="font-semibold">Add hero photos (wide, landscape works best)</span><input type="file" name="hero_images[]" accept="image/*" multiple class="sr-only" data-preview="#pv-hero"></label>
                            <div id="pv-hero" class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-4"></div>
                            @error('hero_images.*')<p class="err">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
                <section class="card">
                    <h2 class="card-title">Numbers</h2>
                    <p class="mb-4 mt-1 text-xs text-mute">The counters on the home and About pages. Type in your own figures here, such as happy clients and properties sold. Active realtors is counted for you (see below).</p>
                    <x-admin.repeater name="stats" :rows="$s->manualStats()" add="Add a number" :fields="[['key' => 'value', 'label' => 'Number', 'type' => 'number'], ['key' => 'suffix', 'label' => 'Suffix (e.g. +)'], ['key' => 'label', 'label' => 'Label', 'span' => 'sm:col-span-2']]" />
                </section>
                @php $rs = $s->realtorStat(); $realtorCount = $s::verifiedRealtorCount(); @endphp
                <section class="card">
                    <h2 class="card-title">Active realtors (counted automatically)</h2>
                    <p class="mb-4 mt-1 text-xs text-mute">The system counts every realtor who has confirmed their email and is active, and adds one to the counter each time another joins. You never type this number.</p>
                    <div class="mb-5 flex items-center gap-4 rounded-2xl border border-brand/25 bg-brand/10 px-5 py-4">
                        <span class="display text-5xl text-brand">{{ number_format($realtorCount) }}</span>
                        <span class="text-sm text-ink">verified realtors right now. The website shows <strong>{{ number_format($realtorCount + (int) $rs['extra']) }}{{ $rs['suffix'] }}</strong>.</span>
                    </div>
                    <div class="form-grid">
                        <div><label for="realtor_stat_label" class="field-label">Label</label><input id="realtor_stat_label" name="realtor_stat[label]" value="{{ old('realtor_stat.label', $rs['label']) }}" maxlength="60" class="field"></div>
                        <div><label for="realtor_stat_suffix" class="field-label">Suffix (e.g. +)</label><input id="realtor_stat_suffix" name="realtor_stat[suffix]" value="{{ old('realtor_stat.suffix', $rs['suffix']) }}" maxlength="4" class="field"></div>
                        <div class="form-full"><label for="realtor_stat_extra" class="field-label">Existing realtors to add <span class="normal-case tracking-normal opacity-60">(optional)</span></label><input id="realtor_stat_extra" name="realtor_stat[extra]" type="number" min="0" inputmode="numeric" value="{{ old('realtor_stat.extra', $rs['extra']) }}" class="field"><p class="hint">Realtors who work with you but never registered on the site. This number is added to the automatic count. Leave 0 to show only real sign-ups.</p></div>
                    </div>
                    <label class="mt-5 flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-ink/10 bg-page px-4 py-3.5">
                        <span><span class="block text-sm font-bold text-ink">Show this counter on the website</span></span>
                        <input type="hidden" name="realtor_stat_show" value="0">
                        <input type="checkbox" name="realtor_stat_show" value="1" class="sr-only" @checked(old('realtor_stat_show', $rs['show']))>
                        <span class="switch" aria-hidden="true"></span>
                    </label>
                </section>
            </div>

            {{-- About --}}
            <div data-tab-panel="about" class="space-y-4" hidden>
                <section class="card">
                    <h2 class="card-title">Our story</h2>
                    <div class="form-grid mt-5">
                        <x-admin.image name="about_image" label="About photo 1" :current="$s->about_image" :wide="true" help="Shown on the About page and home." />
                        <x-admin.image name="about_image_2" label="About photo 2" :current="$s->about_image_2" :wide="true" help="Crossfades with photo 1 on the home page." />
                        <div><label for="rc_number" class="field-label">Registration number</label><input id="rc_number" name="rc_number" value="{{ old('rc_number', $s->rc_number) }}" placeholder="RC 1234567" class="field"><p class="hint">Shown in the footer and on the About photo. Builds trust.</p></div>
                    </div>
                </section>
            </div>

            {{-- CEO & team --}}
            <div data-tab-panel="ceo" class="space-y-4" hidden>
                <section class="card">
                    <h2 class="card-title">CEO photo</h2>
                    <p class="mt-1 text-xs text-mute">The portrait shown beside the CEO's message on the home and About pages.</p>
                    <div class="form-grid mt-5">
                        <x-admin.image name="ceo_photo" label="Portrait" :current="$s->ceo_photo" help="A portrait (taller than wide) looks best." />
                    </div>
                </section>
                <section class="card">
                    <h2 class="card-title">Leadership team</h2>
                    <p class="mb-4 mt-1 text-xs text-mute">Shown on the About page. Hidden while empty.</p>
                    <div data-repeater class="space-y-3">
                        <div data-repeater-list class="space-y-3">
                            @foreach ($s->teamItems() as $i => $m)@include('admin.partials.team-row', ['index' => $i, 'row' => $m])@endforeach
                        </div>
                        <template>@include('admin.partials.team-row', ['index' => '__INDEX__', 'row' => []])</template>
                        <button type="button" data-repeater-add class="btn btn-outline btn-sm"><x-icon name="plus" class="h-4 w-4" /> Add a team member</button>
                    </div>
                </section>
            </div>

            {{-- Banners --}}
            <div data-tab-panel="banners" hidden>
                <section class="card">
                    <h2 class="card-title">Page banner photos</h2>
                    <p class="mb-5 mt-1 text-xs text-mute">The wide photo at the top of each page. Landscape, at least 1600px wide. Empty = built-in Nigeria photo.</p>
                    <div class="form-grid">
                        @foreach ($bannerPages as $key => $label)
                            <x-admin.image :name="'banner_'.$key" :label="$label" :current="($s->banners ?? [])[$key] ?? null" :wide="true" />
                        @endforeach
                    </div>
                </section>
            </div>

            {{-- Contact --}}
            <div data-tab-panel="contact" class="space-y-4" hidden>
                <section class="card">
                    <h2 class="card-title">Contact details</h2>
                    <div class="form-grid mt-5">
                        <div><label for="phone" class="field-label">Phone</label><input id="phone" name="phone" value="{{ old('phone', $s->phone) }}" class="field"></div>
                        <div><label for="whatsapp" class="field-label">WhatsApp number</label><input id="whatsapp" name="whatsapp" inputmode="numeric" value="{{ old('whatsapp', $s->whatsapp) }}" placeholder="2348012345678" class="field"><p class="hint">Digits only, with country code, no + or spaces.</p>@error('whatsapp')<p class="err">{{ $message }}</p>@enderror</div>
                        <div><label for="email" class="field-label">Email</label><input id="email" name="email" type="email" value="{{ old('email', $s->email) }}" class="field">@error('email')<p class="err">{{ $message }}</p>@enderror</div>
                        <div><label for="office_hours" class="field-label">Office hours</label><input id="office_hours" name="office_hours" value="{{ old('office_hours', $s->office_hours) }}" placeholder="Mon–Sat, 8am–5pm" class="field"></div>
                        <div class="form-full"><label for="address" class="field-label">Office address</label><textarea id="address" name="address" rows="2" class="field">{{ old('address', $s->address) }}</textarea></div>
                        <div class="form-full"><label for="map_embed_url" class="field-label">Google Maps embed link</label><input id="map_embed_url" name="map_embed_url" value="{{ old('map_embed_url', $s->map_embed_url) }}" placeholder="https://www.google.com/maps/embed?pb=…" class="field"><p class="hint">In Google Maps: Share → Embed a map → copy the src="…" address only.</p>@error('map_embed_url')<p class="err">{{ $message }}</p>@enderror</div>
                    </div>
                </section>
                <section class="card">
                    <h2 class="card-title">Social media</h2>
                    <div class="form-grid mt-5">
                        <div><label for="facebook_url" class="field-label">Facebook</label><input id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $s->facebook_url) }}" placeholder="https://facebook.com/…" class="field">@error('facebook_url')<p class="err">{{ $message }}</p>@enderror</div>
                        <div><label for="instagram_url" class="field-label">Instagram</label><input id="instagram_url" name="instagram_url" value="{{ old('instagram_url', $s->instagram_url) }}" placeholder="https://instagram.com/…" class="field">@error('instagram_url')<p class="err">{{ $message }}</p>@enderror</div>
                        <div><label for="tiktok_url" class="field-label">TikTok</label><input id="tiktok_url" name="tiktok_url" value="{{ old('tiktok_url', $s->tiktok_url) }}" placeholder="https://tiktok.com/@…" class="field">@error('tiktok_url')<p class="err">{{ $message }}</p>@enderror</div>
                    </div>
                </section>
            </div>

            {{-- SEO & indexing --}}
            <div data-tab-panel="seo" class="space-y-4" hidden>
                <section class="card">
                    <div class="flex items-center gap-3">
                        <span class="stat-icon"><x-icon name="search" class="h-5 w-5" /></span>
                        <div>
                            <h2 class="card-title">Search engine indexing</h2>
                            <p class="text-xs text-mute">The master switch for Google and every other crawler.</p>
                        </div>
                    </div>

                    @php $indexingOn = old('seo_indexing_enabled', $s->seo_indexing_enabled ?? true); @endphp
                    <label class="mt-5 flex cursor-pointer items-center justify-between gap-4 rounded-2xl border px-4 py-3.5 {{ $indexingOn ? 'border-ink/10 bg-page' : 'border-flag-500/40 bg-flag-500/10' }}">
                        <span>
                            <span class="block text-sm font-bold text-ink">Allow search engines to crawl & index this site</span>
                            <span class="mt-0.5 block text-xs text-mute">
                                On: Google can find and list the site normally (recommended once you're live).<br>
                                Off: every page is marked <code class="font-mono">noindex</code> and <code class="font-mono">/robots.txt</code> blocks all crawlers — the site effectively disappears from search, instantly, site-wide. Use this while building, testing, or if you ever need to pull the site out of Google quickly.
                            </span>
                        </span>
                        <input type="hidden" name="seo_indexing_enabled" value="0">
                        <input type="checkbox" name="seo_indexing_enabled" value="1" class="sr-only" @checked($indexingOn)>
                        <span class="switch" aria-hidden="true"></span>
                    </label>

                    @unless ($indexingOn)
                        <div class="mt-3 flex items-start gap-3 rounded-2xl border border-flag-500/30 bg-flag-500/10 px-4 py-3 text-xs text-ink">
                            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-flag-500" />
                            <span>Indexing is currently <strong>OFF</strong>. The site is hidden from Google and other search engines. Already-indexed pages may take a few days to drop out of search results after you turn this off.</span>
                        </div>
                    @endunless

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('robots') }}" target="_blank" class="btn btn-outline btn-sm">View /robots.txt</a>
                        <a href="{{ route('sitemap') }}" target="_blank" class="btn btn-outline btn-sm">View /sitemap.xml</a>
                    </div>
                </section>

                <section class="card">
                    <h2 class="card-title">Default search listing</h2>
                    <p class="mt-1 text-xs text-mute">Used on any page that doesn't set its own title/description (most pages already have one). Google often rewrites these anyway, but a good default helps.</p>
                    <div class="form-grid mt-5">
                        <div class="form-full">
                            <label for="seo_meta_title" class="field-label">Default page title</label>
                            <input id="seo_meta_title" name="seo_meta_title" maxlength="70" value="{{ old('seo_meta_title', $s->seo_meta_title) }}" placeholder="{{ $s->site_name }} | Homes Built on Trust" class="field">
                            <p class="hint">Keep it under ~60 characters so Google doesn't cut it off.</p>
                            @error('seo_meta_title')<p class="err">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-full">
                            <label for="seo_meta_description" class="field-label">Default meta description</label>
                            <textarea id="seo_meta_description" name="seo_meta_description" rows="2" maxlength="320" class="field">{{ old('seo_meta_description', $s->seo_meta_description) }}</textarea>
                            <p class="hint">Aim for 140–160 characters. This is the snippet shown under the title in search results.</p>
                            @error('seo_meta_description')<p class="err">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-full">
                            <label for="seo_meta_keywords" class="field-label">Keywords <span class="normal-case tracking-normal opacity-60">(optional, minor effect today)</span></label>
                            <input id="seo_meta_keywords" name="seo_meta_keywords" value="{{ old('seo_meta_keywords', $s->seo_meta_keywords) }}" placeholder="land in Awka, real estate Enugu, buy land Nigeria" class="field">
                            @error('seo_meta_keywords')<p class="err">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="card">
                    <h2 class="card-title">Google tools</h2>
                    <div class="form-grid mt-5">
                        <div>
                            <label for="google_site_verification" class="field-label">Google Search Console verification code</label>
                            <input id="google_site_verification" name="google_site_verification" value="{{ old('google_site_verification', $s->google_site_verification) }}" placeholder="the content= value only" class="field">
                            <p class="hint">In Search Console: Add property → HTML tag → paste only the code inside content="…".</p>
                            @error('google_site_verification')<p class="err">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="google_analytics_id" class="field-label">Google Analytics (GA4) Measurement ID</label>
                            <input id="google_analytics_id" name="google_analytics_id" value="{{ old('google_analytics_id', $s->google_analytics_id) }}" placeholder="G-XXXXXXXXXX" class="field">
                            <p class="hint">Only loads while indexing is ON.</p>
                            @error('google_analytics_id')<p class="err">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="sticky bottom-3 z-30 mt-5 flex items-center justify-between gap-2 rounded-3xl border border-ink/10 bg-card/90 p-3 shadow-soft backdrop-blur-xl">
            <p class="hidden pl-3 text-xs text-mute sm:block">Changes apply to every tab. Save when you&rsquo;re done.</p>
            <button type="submit" class="btn btn-primary btn-sm ml-auto">Save settings <x-icon name="check" class="h-4 w-4" /></button>
        </div>
    </form>
</x-layouts.admin>
