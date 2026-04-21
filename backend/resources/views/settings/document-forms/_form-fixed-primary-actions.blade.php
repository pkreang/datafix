@php
    $documentForm = $documentForm ?? null;
    $isEdit = $documentForm !== null;
@endphp
{{-- Fixed under app header; spacer height tracks real bar height (ResizeObserver) so fields never sit under the bar. --}}
<div id="doc-form-primary-bar"
     class="right-0 z-[110] bg-white dark:bg-slate-800"
     style="position:fixed;top:4rem;left:0;box-shadow:0 1px 3px 0 rgb(0 0 0/.08),0 4px 12px -2px rgb(0 0 0/.06)">
    {{-- full-height row — everything starts at top-0, buttons use border-t-[3px] to "connect" to the accent --}}
    <div class="flex items-stretch px-4 sm:px-6 lg:px-10" style="min-height:3.25rem">
        <div class="flex-1"></div>
        {{-- buttons: self-stretch so they fill bar height from top-0; border-t-[3px] aligns with accent --}}
        <div class="flex items-stretch">
            @include('settings.document-forms._form-action-buttons')
        </div>
    </div>
</div>
<div id="doc-form-primary-bar-spacer" class="shrink-0" style="min-height: 4rem" aria-hidden="true"></div>
<script>
    (function () {
        function layoutDocFormPrimaryBar() {
            var bar = document.getElementById('doc-form-primary-bar');
            var spacer = document.getElementById('doc-form-primary-bar-spacer');
            if (!bar) return;

            var sidebarSpacer = document.querySelector('[data-sidebar-spacer]');
            var mq = window.matchMedia('(min-width: 1024px)');
            // Use getBoundingClientRect for reliable width — works even when Alpine.js :style hasn't rendered inline style yet
            bar.style.left = (mq.matches && sidebarSpacer) ? (sidebarSpacer.getBoundingClientRect().width + 'px') : '0px';

            if (spacer) {
                spacer.style.minHeight = '';
                // Formula needs to be scroll-independent. Convert spacer's viewport
                // top to a document-coord top (add scrollY) so the calculation is
                // the same whether the user is at the top of the page or scrolled
                // partway down. The previous viewport-only formula produced a
                // wildly oversized spacer mid-scroll because spacer.top was
                // negative once the user had scrolled past it.
                var barBottom = bar.getBoundingClientRect().bottom;
                var spacerDocTop = spacer.getBoundingClientRect().top + window.scrollY;
                var needed = Math.ceil(barBottom - spacerDocTop - 12);
                spacer.style.height = Math.max(0, needed) + 'px';
            }
        }

        function boot() {
            layoutDocFormPrimaryBar();
            requestAnimationFrame(function () {
                requestAnimationFrame(layoutDocFormPrimaryBar);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }

        window.addEventListener('resize', layoutDocFormPrimaryBar);

        var bar = document.getElementById('doc-form-primary-bar');
        if (bar && typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(layoutDocFormPrimaryBar).observe(bar);
        }

        var sp = document.querySelector('[data-sidebar-spacer]');
        if (sp && typeof ResizeObserver !== 'undefined') {
            // ResizeObserver catches width changes from both Alpine.js :style and CSS transitions
            new ResizeObserver(layoutDocFormPrimaryBar).observe(sp);
        }

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function () {
                layoutDocFormPrimaryBar();
            });
        }
    })();
</script>
