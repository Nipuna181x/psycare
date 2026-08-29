@extends('layouts.doctor')

@php
    $title = 'Appointment #'.str_pad($appointment->id, 6, '0', STR_PAD_LEFT);
    $subtitle = $appointment->patient_name;
    $riskLevel = $appointment->pre_assessment_risk_level;
    $requiresCrisisEscalation = $appointment->requiresCrisisEscalation();
@endphp

@section('content')
    <div class="grid gap-5">
        <nav aria-label="Breadcrumb">
            <a href="{{ route('doctor.appointments.index') }}" class="inline-flex items-center gap-2 text-[12px] font-medium text-sky-700 transition-colors hover:text-sky-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-sky-500">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Back to appointments
            </a>
        </nav>

        @if ($requiresCrisisEscalation)
            <section role="alert" class="rounded-3xl border border-red-200 bg-red-50 p-5 shadow-[0_14px_35px_-28px_rgba(185,28,28,0.5)] md:p-6">
                <div class="flex items-start gap-4">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-red-100 text-red-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 2.86 1.82 17a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 2.86a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-display text-[16px] font-semibold text-red-900">Immediate clinical review required</h2>
                            <span class="rounded-full bg-red-200 px-2.5 py-1 text-[9px] font-bold tracking-[0.08em] text-red-800 uppercase">Elevated risk</span>
                        </div>
                        <p class="mt-2 max-w-[90ch] text-[13px] leading-relaxed text-red-800">The pre-assessment indicates elevated risk, including a positive response relating to death or self-harm. Review the full responses and follow the crisis escalation workflow before continuing with routine visit preparation.</p>
                    </div>
                </div>
            </section>
        @endif

        <div class="grid items-start gap-5 lg:grid-cols-[minmax(280px,0.75fr)_minmax(0,1.4fr)]">
            <aside class="grid gap-5">
                <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="patient-identity-heading">
                    <div class="flex items-center gap-3.5 border-b border-border pb-5">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-sky-100 text-[15px] font-semibold text-sky-700">{{ mb_strtoupper(mb_substr($appointment->patient_name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold tracking-[0.12em] text-ink-soft uppercase">Patient</p>
                            <h2 id="patient-identity-heading" class="mt-0.5 truncate font-display text-[17px] font-medium text-ink">{{ $appointment->patient_name }}</h2>
                            <p class="mt-0.5 text-[11px] text-ink-soft">
                                {{ $appointment->patient_age ? $appointment->patient_age.' years' : 'Age not provided' }}
                                @if ($appointment->patient_gender) · {{ ucfirst($appointment->patient_gender) }} @endif
                            </p>
                        </div>
                    </div>

                    <dl class="mt-5 grid gap-4">
                        <div>
                            <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Phone</dt>
                            <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->patient_phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Email</dt>
                            <dd class="mt-1 break-all text-[12px] text-ink">{{ $appointment->patient_email ?? 'No email provided' }}</dd>
                        </div>
                    </dl>

                    <a href="{{ route('doctor.patients.show', $appointment->user) }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-[11px] font-semibold tracking-[0.08em] text-sky-700 uppercase transition-colors hover:border-sky-300 hover:bg-sky-100 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-sky-500">
                        Patient profile
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                    </a>
                </section>

                <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="visit-details-heading">
                    <h2 id="visit-details-heading" class="font-display text-[15px] font-medium text-ink">Visit details</h2>
                    <dl class="mt-5 divide-y divide-border">
                        <div class="flex items-center justify-between gap-4 py-3 first:pt-0">
                            <dt class="text-[11px] text-ink-soft">Date</dt>
                            <dd class="text-right text-[12px] font-medium text-ink">{{ $appointment->appointment_date->format('D, j M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-[11px] text-ink-soft">Time</dt>
                            <dd class="text-right text-[12px] font-medium text-ink">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-[11px] text-ink-soft">Mode</dt>
                            <dd class="inline-flex items-center gap-1.5 text-right text-[12px] font-medium text-ink">
                                @if ($appointment->mode === 'online')
                                    <svg class="h-3.5 w-3.5 text-sky-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 13 5 3V8l-5 3"/><rect width="13" height="12" x="3" y="6" rx="2"/></svg>
                                    Video consultation
                                @else
                                    <svg class="h-3.5 w-3.5 text-sky-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s6-4.35 6-11a6 6 0 1 0-12 0c0 6.65 6 11 6 11Z"/><circle cx="12" cy="10" r="2"/></svg>
                                    In person
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3 last:pb-0">
                            <dt class="text-[11px] text-ink-soft">Status</dt>
                            <dd><x-dashboard.badge :status="$appointment->status" /></dd>
                        </div>
                    </dl>

                    @if ($appointment->reason)
                        <div class="mt-5 border-t border-border pt-5">
                            <p class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Reason for visit</p>
                            <p class="mt-2 text-[12px] leading-relaxed text-ink">{{ $appointment->reason }}</p>
                        </div>
                    @endif
                </section>

                @if ($appointment->status === 'confirmed')
                    <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="appointment-actions-heading">
                        <h2 id="appointment-actions-heading" class="font-display text-[15px] font-medium text-ink">Appointment actions</h2>
                        <div class="mt-4 grid gap-2.5">
                            <form method="POST" action="{{ route('doctor.appointments.status', $appointment) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="w-full rounded-xl bg-sky-700 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase transition-colors hover:bg-sky-800 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-sky-500">Mark completed</button>
                            </form>
                            <form method="POST" action="{{ route('doctor.appointments.status', $appointment) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="w-full rounded-xl border border-red-200 bg-white px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-red-700 uppercase transition-colors hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-red-500">Cancel appointment</button>
                            </form>
                        </div>
                    </section>
                @endif

                <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="doctor-notes-heading">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="doctor-notes-heading" class="font-display text-[15px] font-medium text-ink">Doctor notes</h2>
                        <span class="rounded-full bg-secondary px-2.5 py-1 text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Unavailable</span>
                    </div>
                    {{-- TODO: Add a persisted doctor notes field before enabling note entry and save controls. --}}
                    <div class="mt-4 rounded-2xl border border-dashed border-border bg-secondary/50 p-4">
                        <p class="text-[12px] leading-relaxed text-ink-soft">Clinical notes are not yet available for this appointment. A notes field must be added to the appointment data model before this section can accept or save content.</p>
                    </div>
                </section>

                <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="prescription-heading">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="prescription-heading" class="font-display text-[15px] font-medium text-ink">Prescription</h2>
                        @if ($appointment->prescription)
                            <a href="{{ route('doctor.appointments.prescription.download', $appointment) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-[10px] font-semibold tracking-[0.06em] text-sky-700 uppercase transition-colors hover:border-sky-300 hover:bg-sky-100">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15V3"/><path d="m7 10 5 5 5-5"/><path d="M20 21H4"/></svg>
                                Print prescription
                            </a>
                        @endif
                    </div>
                    <p class="mt-1 text-[11px] leading-relaxed text-ink-soft">Record every medicine prescribed during this appointment.</p>

                    @if ($errors->any())
                        <div class="mt-4 rounded-xl bg-red-50 px-3 py-2 text-[11px] text-red-700">{{ $errors->first() }}</div>
                    @endif

                    @php
                        $existingItems = $appointment->prescription?->items ?? collect();
                    @endphp

                    <form method="POST" action="{{ route('doctor.appointments.prescription.store', $appointment) }}" class="mt-4 grid gap-4">
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
                </section>
            </aside>

            <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="screener-heading">
                <div class="flex flex-col gap-3 border-b border-border pb-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-[10px] font-semibold tracking-[0.12em] text-sky-700 uppercase">Pre-assessment</p>
                        <h2 id="screener-heading" class="mt-1 font-display text-[17px] font-medium text-ink">PHQ-9 / GAD-7 screener</h2>
                        <p class="mt-1 text-[12px] text-ink-soft">Review severity, risk signals, and the patient's own words before the visit.</p>
                    </div>
                    @if ($riskLevel)
                        <x-dashboard.badge :status="$riskLevel" />
                    @else
                        <span class="inline-flex rounded-full bg-secondary px-2.5 py-1 text-[10px] font-semibold tracking-[0.06em] text-ink-soft uppercase">Not assessed</span>
                    @endif
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <article class="rounded-2xl border border-border bg-white p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-semibold tracking-[0.1em] text-ink-soft uppercase">PHQ-9 · Depression</p>
                                <p class="mt-2 font-display text-[28px] font-medium leading-none text-ink">{{ $appointment->phq9_total ?? '—' }}<span class="text-[13px] text-ink-soft">/27</span></p>
                            </div>
                            <span class="rounded-full bg-secondary px-2.5 py-1 text-[10px] font-medium text-ink">{{ str($appointment->phq9_severity ?? 'Not scored')->replace('_', ' ')->title() }}</span>
                        </div>
                        <progress aria-label="PHQ-9 severity score" value="{{ $appointment->phq9_total ?? 0 }}" max="27" class="mt-5 h-1.5 w-full overflow-hidden rounded-full accent-sky-700"></progress>
                        <div class="mt-1.5 flex justify-between text-[9px] text-ink-soft"><span>Minimal</span><span>Severe</span></div>
                    </article>

                    <article class="rounded-2xl border border-border bg-white p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-semibold tracking-[0.1em] text-ink-soft uppercase">GAD-7 · Anxiety</p>
                                <p class="mt-2 font-display text-[28px] font-medium leading-none text-ink">{{ $appointment->gad7_total ?? '—' }}<span class="text-[13px] text-ink-soft">/21</span></p>
                            </div>
                            <span class="rounded-full bg-secondary px-2.5 py-1 text-[10px] font-medium text-ink">{{ str($appointment->gad7_severity ?? 'Not scored')->replace('_', ' ')->title() }}</span>
                        </div>
                        <progress aria-label="GAD-7 severity score" value="{{ $appointment->gad7_total ?? 0 }}" max="21" class="mt-5 h-1.5 w-full overflow-hidden rounded-full accent-sky-700"></progress>
                        <div class="mt-1.5 flex justify-between text-[9px] text-ink-soft"><span>Minimal</span><span>Severe</span></div>
                    </article>
                </div>

                @if ($appointment->pre_assessment_summary)
                    <div class="mt-5 rounded-2xl border border-sky-100 bg-sky-50 p-4">
                        <div class="flex items-start gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-white text-sky-700 shadow-[0_1px_0_0_rgba(14,116,144,0.14)]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.5 4a2.5 2.5 0 0 1 5 0v.5a2.5 2.5 0 0 1 0 5h-5a2.5 2.5 0 0 1 0-5Z"/><path d="M6 9.5a2.5 2.5 0 0 0 0 5h.5a2.5 2.5 0 0 0 5 0v-5"/><path d="M18 9.5a2.5 2.5 0 0 1 0 5h-.5a2.5 2.5 0 0 1-5 0v-5"/><path d="M9.5 14.5v.5a2.5 2.5 0 0 0 5 0v-.5"/><path d="M12 2v20"/></svg>
                            </span>
                            <div>
                                <p class="text-[10px] font-semibold tracking-[0.1em] text-sky-700 uppercase">AI-generated clinical summary</p>
                                <p class="mt-1.5 text-[13px] leading-relaxed text-slate-700">{{ $appointment->pre_assessment_summary }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-6 border-t border-border pt-5">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <h3 class="font-display text-[15px] font-medium text-ink">Question-by-question review</h3>
                            <p class="mt-1 text-[11px] text-ink-soft">Expand each response for the patient's quote and extracted context.</p>
                        </div>
                        @if ($appointment->pre_assessment)
                            <span class="shrink-0 text-[10px] font-medium text-ink-soft">{{ count($appointment->pre_assessment) }} responses</span>
                        @endif
                    </div>

                    @if ($appointment->pre_assessment)
                        <div class="mt-4 grid gap-3">
                            @foreach ($appointment->pre_assessment as $answer)
                                <details class="group rounded-2xl border border-border bg-white open:border-sky-200 open:shadow-[0_10px_28px_-24px_rgba(14,116,144,0.45)]">
                                    <summary class="flex cursor-pointer list-none items-start justify-between gap-4 p-4 marker:content-none">
                                        <div class="flex min-w-0 items-start gap-3">
                                            <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-secondary text-[10px] font-semibold text-ink-soft">{{ $loop->iteration }}</span>
                                            <p class="text-[12px] font-semibold leading-relaxed text-ink">{{ $answer['question'] }}</p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-2">
                                            @if (array_key_exists('score', $answer))
                                                <span class="rounded-full bg-secondary px-2.5 py-1 text-[10px] font-semibold text-ink">Score {{ $answer['score'] }}</span>
                                            @endif
                                            <svg class="h-4 w-4 text-ink-soft transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                                        </div>
                                    </summary>
                                    <div class="border-t border-border px-4 pt-4 pb-5 sm:pl-13">
                                        @if (array_key_exists('score', $answer))
                                            <p class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">{{ \App\Services\ScreenerAnalyzer::SCALE[$answer['score']] }}</p>
                                            @if (! empty($answer['answer']))
                                                <blockquote class="mt-3 border-l-2 border-sky-300 pl-3 text-[13px] leading-relaxed text-slate-700 italic">“{{ $answer['answer'] }}”</blockquote>
                                            @else
                                                <p class="mt-3 text-[12px] text-ink-soft">No spoken response was recorded.</p>
                                            @endif
                                            @if (! empty($answer['extracted_context']))
                                                <p class="mt-3 rounded-xl bg-secondary px-3 py-2.5 text-[11px] leading-relaxed text-ink-soft"><span class="font-semibold text-ink">Context / translation:</span> {{ $answer['extracted_context'] }}</p>
                                            @endif
                                        @else
                                            <p class="text-[12px] leading-relaxed text-ink-soft">{{ $answer['answer'] !== '' ? $answer['answer'] : 'Skipped' }}</p>
                                        @endif
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-4 grid min-h-36 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 px-6 py-8 text-center">
                            <div>
                                <svg class="mx-auto h-5 w-5 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                <p class="mt-2 text-[12px] font-medium text-ink">Pre-assessment pending</p>
                                <p class="mt-1 text-[11px] text-ink-soft">Responses will appear here when the patient completes the screener.</p>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($appointment->screener_open_notes)
                    <div class="mt-5 border-t border-border pt-5">
                        <div class="rounded-2xl bg-secondary p-4">
                            <p class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Patient's additional note</p>
                            <p class="mt-2 text-[12px] leading-relaxed text-ink">{{ $appointment->screener_open_notes }}</p>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
