{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Quick multi-photo upload shown above the gallery list.
--}}
<form method="POST" action="{{ route('admin.gallery.bulk') }}" enctype="multipart/form-data" class="card mb-4">
    @csrf
    <div class="flex items-center gap-3">
        <span class="stat-icon"><x-icon name="upload" class="h-5 w-5" /></span>
        <div>
            <h2 class="card-title">Upload several photos at once</h2>
            <p class="text-xs text-mute">Choose many photos, pick a category, done. Edit titles later if you like.</p>
        </div>
    </div>
    <div class="mt-5 grid gap-4 md:grid-cols-[1fr_16rem_auto] md:items-end">
        <label class="dropzone cursor-pointer">
            <x-icon name="image" class="h-6 w-6" />
            <span class="font-semibold">Tap to choose photos, or drop them here</span>
            <input type="file" name="photos[]" accept="image/*" multiple required class="sr-only" data-preview="#pv-bulk">
        </label>
        <div>
            <label for="bulk-category" class="field-label">Category</label>
            <input id="bulk-category" name="category" list="bulk-cats" value="{{ old('category', 'Real Estate') }}" required class="field">
            <datalist id="bulk-cats"><option value="Real Estate"><option value="Construction"><option value="Agriculture"><option value="Events"></datalist>
        </div>
        <button type="submit" class="btn btn-primary btn-sm !py-3.5">Upload</button>
    </div>
    <div id="pv-bulk" class="mt-4 grid grid-cols-4 gap-2 sm:grid-cols-6 lg:grid-cols-8"></div>
    @error('photos')<p class="err">{{ $message }}</p>@enderror
    @error('photos.*')<p class="err">{{ $message }}</p>@enderror
    @error('category')<p class="err">{{ $message }}</p>@enderror
</form>
