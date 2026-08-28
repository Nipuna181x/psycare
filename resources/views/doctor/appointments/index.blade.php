@extends('layouts.doctor')

@php
    $title = 'Appointments';
    $subtitle = 'Prioritise risk, prepare for visits, and review your clinical schedule';
@endphp

@section('content')
    <div class="grid gap-5" data-appointment-list>
        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="appointment-filters-heading">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-[10px] font-semibold tracking-[0.14em] text-sky-700 uppercase">Clinical schedule</p>
                    <h2 id="appointment-filters-heading" class="mt-1 font-display text-[17px] font-medium text-ink">Filter appointments</h2>
                    <p class="mt-1 text-[12px] text-ink-soft">Narrow the schedule without leaving this page.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[150px_150px_150px_150px_auto]">
                    <label class="grid gap-1.5">
                        <span class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Status</span>
                        <select data-filter-status class="h-10 rounded-xl border border-border bg-white px-3 text-[12px] text-ink outline-none transition-shadow focus:ring-2 focus:ring-sky-500/30">
                            <option value="">All statuses</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">From date</span>
                        <input data-filter-date-from type="date" class="h-10 rounded-xl border border-border bg-white px-3 text-[12px] text-ink outline-none transition-shadow focus:ring-2 focus:ring-sky-500/30">
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">To date</span>
                        <input data-filter-date-to type="date" class="h-10 rounded-xl border border-border bg-white px-3 text-[12px] text-ink outline-none transition-shadow focus:ring-2 focus:ring-sky-500/30">
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Risk level</span>
                        <select data-filter-risk class="h-10 rounded-xl border border-border bg-white px-3 text-[12px] text-ink outline-none transition-shadow focus:ring-2 focus:ring-sky-500/30">
                            <option value="">All risk levels</option>
                            <option value="elevated">Elevated</option>
                            <option value="moderate">Moderate</option>
                            <option value="low">Low</option>
                            <option value="unrated">Not assessed</option>
                        </select>
                    </label>
                    <button data-filter-reset type="button" class="h-10 self-end rounded-xl border border-border px-4 text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase transition-colors hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700">Clear</button>
                </div>
            </div>
        </section>

        <section data-filter-results class="hidden overflow-hidden rounded-3xl bg-white shadow-[0_14px_40px_-28px_rgba(15,23,42,0.32)]" aria-labelledby="filtered-results-heading" aria-live="polite">
            <div class="flex flex-col gap-3 border-b border-border px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="grid h-8 w-8 place-items-center rounded-xl bg-sky-50 text-sky-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M7 12h10"/><path d="M10 18h4"/></svg>
                        </span>
                        <div>
                            <h2 id="filtered-results-heading" class="font-display text-[16px] font-medium text-ink">Filtered results</h2>
                            <p class="mt-0.5 text-[11px] text-ink-soft">Appointments matching your selected criteria</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span data-filter-summary class="text-[11px] text-ink-soft"></span>
                    <span data-filter-result-count class="rounded-full bg-sky-50 px-3 py-1 text-[10px] font-semibold text-sky-700">0 results</span>
                </div>
            </div>

            <div class="min-h-48 p-5 md:p-6">
                <ul data-filter-results-list class="grid gap-3"></ul>
                <div data-filter-results-empty class="hidden min-h-40 place-items-center rounded-2xl border border-dashed border-border bg-slate-50 px-6 py-8 text-center">
                    <div>
                        <span class="mx-auto grid h-10 w-10 place-items-center rounded-full bg-white text-ink-soft shadow-[0_1px_0_0_var(--border)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M8 11h6"/></svg>
                        </span>
                        <p class="mt-3 text-[12px] font-medium text-ink">No appointments match these filters.</p>
                        <p class="mt-1 text-[11px] text-ink-soft">Try widening the date range or selecting another risk level.</p>
                    </div>
                </div>
            </div>
        </section>

        @foreach ([
            ['key' => 'today', 'title' => 'Today', 'subtitle' => $today->count().' appointment'.($today->count() === 1 ? '' : 's'), 'appointments' => $today, 'empty' => 'Nothing on the calendar for today.'],
            ['key' => 'upcoming', 'title' => 'Upcoming', 'subtitle' => 'Confirmed appointments ahead', 'appointments' => $upcoming, 'empty' => 'No upcoming appointments.'],
            ['key' => 'history', 'title' => 'History', 'subtitle' => 'Completed & cancelled', 'appointments' => $history, 'empty' => 'No past appointments yet.'],
        ] as $group)
            <section data-appointment-group="{{ $group['key'] }}" class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="{{ $group['key'] }}-appointments-heading">
                <div class="flex items-start justify-between gap-4 border-b border-border pb-4">
                    <div>
                        <h2 id="{{ $group['key'] }}-appointments-heading" class="font-display text-[16px] font-medium text-ink">{{ $group['title'] }}</h2>
                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $group['subtitle'] }}</p>
                    </div>
                    <span class="rounded-full bg-secondary px-3 py-1 text-[10px] font-semibold text-ink-soft" data-visible-count>{{ $group['appointments']->count() }}</span>
                </div>

                <ul class="mt-4 grid gap-2.5">
                    @forelse ($group['appointments'] as $appointment)
                        @include('doctor.appointments._row', ['appointment' => $appointment])
                    @empty
                        <li class="grid min-h-36 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 px-6 py-8 text-center">
                            <div>
                                <span class="mx-auto grid h-10 w-10 place-items-center rounded-full bg-white text-ink-soft shadow-[0_1px_0_0_var(--border)]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="m9 16 2 2 4-4"/></svg>
                                </span>
                                <p class="mt-3 text-[12px] font-medium text-ink">{{ $group['empty'] }}</p>
                                <p class="mt-1 text-[11px] text-ink-soft">This section will update when appointments are scheduled.</p>
                            </div>
                        </li>
                    @endforelse

                    @if ($group['appointments']->isNotEmpty())
                        <li data-filter-empty class="hidden min-h-32 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 px-6 py-8 text-center">
                            <div>
                                <svg class="mx-auto h-5 w-5 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                <p class="mt-2 text-[12px] font-medium text-ink">No appointments match these filters.</p>
                                <p class="mt-1 text-[11px] text-ink-soft">Adjust or clear the filters to see more results.</p>
                            </div>
                        </li>
                    @endif
                </ul>
            </section>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const list = document.querySelector('[data-appointment-list]');

            if (!list) {
                return;
            }

            const statusFilter = list.querySelector('[data-filter-status]');
            const dateFromFilter = list.querySelector('[data-filter-date-from]');
            const dateToFilter = list.querySelector('[data-filter-date-to]');
            const riskFilter = list.querySelector('[data-filter-risk]');
            const resetButton = list.querySelector('[data-filter-reset]');
            const results = list.querySelector('[data-filter-results]');
            const resultsList = list.querySelector('[data-filter-results-list]');
            const resultsEmpty = list.querySelector('[data-filter-results-empty]');
            const resultCount = list.querySelector('[data-filter-result-count]');
            const filterSummary = list.querySelector('[data-filter-summary]');
            const groups = [...list.querySelectorAll('[data-appointment-group]')];
            const appointmentRows = groups.flatMap((group) => [...group.querySelectorAll('[data-appointment-row]')]);

            const applyFilters = () => {
                const isFiltering = [statusFilter, dateFromFilter, dateToFilter, riskFilter].some((filter) => filter.value !== '');

                groups.forEach((group) => group.classList.toggle('hidden', isFiltering));
                results.classList.toggle('hidden', !isFiltering);

                if (!isFiltering) {
                    resultsList.replaceChildren();
                    return;
                }

                const matchingRows = appointmentRows.filter((row) => {
                    const matchesStatus = !statusFilter.value || row.dataset.status === statusFilter.value;
                    const matchesRisk = !riskFilter.value || row.dataset.risk === riskFilter.value;
                    const matchesStartDate = !dateFromFilter.value || row.dataset.date >= dateFromFilter.value;
                    const matchesEndDate = !dateToFilter.value || row.dataset.date <= dateToFilter.value;

                    return matchesStatus && matchesRisk && matchesStartDate && matchesEndDate;
                });

                const activeLabels = [
                    statusFilter.value ? statusFilter.options[statusFilter.selectedIndex].text : null,
                    riskFilter.value ? `${riskFilter.options[riskFilter.selectedIndex].text} risk` : null,
                    dateFromFilter.value || dateToFilter.value ? 'Custom dates' : null,
                ].filter(Boolean);

                resultsList.replaceChildren(...matchingRows.map((row) => row.cloneNode(true)));
                resultCount.textContent = `${matchingRows.length} ${matchingRows.length === 1 ? 'result' : 'results'}`;
                filterSummary.textContent = activeLabels.join(' · ');
                resultsEmpty.classList.toggle('hidden', matchingRows.length !== 0);
                resultsEmpty.classList.toggle('grid', matchingRows.length === 0);
            };

            [statusFilter, dateFromFilter, dateToFilter, riskFilter].forEach((filter) => {
                filter.addEventListener('change', applyFilters);
            });

            resetButton.addEventListener('click', () => {
                [statusFilter, dateFromFilter, dateToFilter, riskFilter].forEach((filter) => {
                    filter.value = '';
                });
                applyFilters();
            });
        })();
    </script>
@endpush
