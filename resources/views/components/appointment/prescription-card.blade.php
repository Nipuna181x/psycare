@props(['appointment', 'editable' => false, 'storeRoute' => null, 'downloadRoute' => null])

<section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="prescription-heading">
    <div class="flex items-center justify-between gap-3">
        <h2 id="prescription-heading" class="font-display text-[15px] font-medium text-ink">Prescription</h2>
        @if ($downloadRoute && $appointment->prescription)
            <a href="{{ $downloadRoute }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-[10px] font-semibold tracking-[0.06em] text-sky-700 uppercase transition-colors hover:border-sky-300 hover:bg-sky-100">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15V3"/><path d="m7 10 5 5 5-5"/><path d="M20 21H4"/></svg>
                Print prescription
            </a>
        @endif
    </div>

    @if ($editable)
        <p class="mt-1 text-[11px] leading-relaxed text-ink-soft">Record every medicine prescribed during this appointment.</p>

        @if ($errors->any())
            <div class="mt-4 rounded-xl bg-red-50 px-3 py-2 text-[11px] text-red-700">{{ $errors->first() }}</div>
        @endif

        @php
            $existingItems = $appointment->prescription?->items ?? collect();
        @endphp

        <form method="POST" action="{{ $storeRoute }}" class="mt-4 grid gap-4">
            @csrf

            <div id="prescription-items" class="grid gap-3">
                @forelse ($existingItems as $index => $item)
                    <fieldset data-item-row class="grid gap-2.5 rounded-2xl border border-border bg-white p-3.5">
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Medicine name
                                <input name="items[{{ $index }}][medicine_name]" value="{{ $item->medicine_name }}" required class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                            </label>
                            <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Dosage
                                <input name="items[{{ $index }}][dosage]" value="{{ $item->dosage }}" required class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                            </label>
                        </div>
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Frequency
                                <input name="items[{{ $index }}][frequency]" value="{{ $item->frequency }}" required class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                            </label>
                            <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Duration
                                <input name="items[{{ $index }}][duration]" value="{{ $item->duration }}" class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                            </label>
                        </div>
                        <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Instructions
                            <input name="items[{{ $index }}][special_instructions]" value="{{ $item->special_instructions }}" class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                        </label>
                        <button type="button" data-remove-item-row class="justify-self-start text-[11px] font-semibold text-red-700 hover:text-red-800">Remove medicine</button>
                    </fieldset>
                @empty
                    <fieldset data-item-row class="grid gap-2.5 rounded-2xl border border-border bg-white p-3.5">
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Medicine name
                                <input name="items[0][medicine_name]" required class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                            </label>
                            <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Dosage
                                <input name="items[0][dosage]" required class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                            </label>
                        </div>
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Frequency
                                <input name="items[0][frequency]" required class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                            </label>
                            <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Duration
                                <input name="items[0][duration]" class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                            </label>
                        </div>
                        <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Instructions
                            <input name="items[0][special_instructions]" class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                        </label>
                        <button type="button" data-remove-item-row class="justify-self-start text-[11px] font-semibold text-red-700 hover:text-red-800">Remove medicine</button>
                    </fieldset>
                @endforelse
            </div>

            <template id="prescription-item-template">
                <fieldset data-item-row class="grid gap-2.5 rounded-2xl border border-border bg-white p-3.5">
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Medicine name
                            <input name="items[__INDEX__][medicine_name]" required class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                        </label>
                        <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Dosage
                            <input name="items[__INDEX__][dosage]" required class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                        </label>
                    </div>
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Frequency
                            <input name="items[__INDEX__][frequency]" required class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                        </label>
                        <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Duration
                            <input name="items[__INDEX__][duration]" class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                        </label>
                    </div>
                    <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Instructions
                        <input name="items[__INDEX__][special_instructions]" class="rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
                    </label>
                    <button type="button" data-remove-item-row class="justify-self-start text-[11px] font-semibold text-red-700 hover:text-red-800">Remove medicine</button>
                </fieldset>
            </template>

            <button type="button" id="add-prescription-item" class="justify-self-start rounded-xl border border-sky-200 bg-sky-50 px-4 py-2.5 text-[11px] font-semibold tracking-[0.08em] text-sky-700 uppercase transition-colors hover:border-sky-300 hover:bg-sky-100">Add medicine</button>

            <label class="grid gap-1.5 text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">General notes
                <textarea name="notes" rows="3" class="resize-y rounded-xl border border-border bg-white px-3 py-2.5 text-[12px] font-normal leading-relaxed tracking-normal text-ink normal-case outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">{{ old('notes', $appointment->prescription?->notes) }}</textarea>
            </label>

            <button type="submit" class="rounded-xl bg-sky-700 px-4 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase transition-colors hover:bg-sky-800">Save prescription</button>
        </form>

        <script>
            (() => {
                const container = document.getElementById('prescription-items');
                const template = document.getElementById('prescription-item-template');
                const addButton = document.getElementById('add-prescription-item');
                let nextIndex = container.children.length;

                const wireRemoveButton = (row) => {
                    row.querySelector('[data-remove-item-row]').addEventListener('click', () => {
                        if (container.querySelectorAll('[data-item-row]').length > 1) {
                            row.remove();
                        }
                    });
                };

                container.querySelectorAll('[data-item-row]').forEach(wireRemoveButton);

                addButton.addEventListener('click', () => {
                    const fragment = template.content.cloneNode(true);
                    const row = fragment.querySelector('[data-item-row]');
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace('__INDEX__', nextIndex);
                    });
                    nextIndex += 1;
                    container.appendChild(fragment);
                    wireRemoveButton(container.lastElementChild);
                });
            })();
        </script>
    @else
        <p class="mt-1 text-[11px] leading-relaxed text-ink-soft">Medicines prescribed during this appointment.</p>

        @if ($appointment->prescription)
            <div class="mt-4 grid gap-3">
                @foreach ($appointment->prescription->items as $item)
                    <div class="grid gap-2.5 rounded-2xl border border-border bg-white p-3.5">
                        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                            <p class="text-[13px] font-semibold text-ink">{{ $item->medicine_name }}</p>
                            <p class="text-[12px] text-ink-soft">{{ $item->dosage }}</p>
                        </div>
                        <p class="text-[12px] text-ink-soft">{{ $item->frequency }}@if ($item->duration) · {{ $item->duration }} @endif</p>
                        @if ($item->special_instructions)
                            <p class="rounded-xl bg-secondary px-3 py-2 text-[11px] leading-relaxed text-ink-soft">{{ $item->special_instructions }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($appointment->prescription->notes)
                <div class="mt-4 rounded-2xl bg-secondary p-4">
                    <p class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">General notes</p>
                    <p class="mt-2 text-[12px] leading-relaxed text-ink">{{ $appointment->prescription->notes }}</p>
                </div>
            @endif
        @else
            <div class="mt-4 rounded-2xl border border-dashed border-border bg-secondary/50 p-4">
                <p class="text-[12px] leading-relaxed text-ink-soft">No prescription recorded for this visit.</p>
            </div>
        @endif
    @endif
</section>
