{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Confirmation dialog for destructive actions. Any <form data-confirm="Are you sure?"> opens it.
--}}
<div id="confirm-modal" class="modal-backdrop" hidden role="dialog" aria-modal="true" aria-labelledby="confirm-title">
    <div class="modal">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-flag-500/10 text-flag-500"><x-icon name="alert" class="h-6 w-6" /></span>
        <h2 id="confirm-title" class="mt-4 font-serif text-2xl text-ink">Please confirm</h2>
        <p data-confirm-text class="mt-2 text-sm leading-relaxed text-mute"></p>
        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" data-confirm-no class="btn btn-outline btn-sm">Cancel</button>
            <button type="button" data-confirm-yes class="btn btn-danger btn-sm">Yes, continue</button>
        </div>
    </div>
</div>
