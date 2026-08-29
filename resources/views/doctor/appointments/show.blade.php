@extends('layouts.doctor')

@php
    $title = 'Appointment #'.str_pad($appointment->id, 6, '0', STR_PAD_LEFT);
    $subtitle = $appointment->patient_name;
@endphp

@section('content')
    <div class="grid gap-5">
        <nav aria-label="Breadcrumb">
            <a href="{{ route('doctor.appointments.index') }}" class="inline-flex items-center gap-2 text-[12px] font-medium text-sky-700 transition-colors hover:text-sky-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-sky-500">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Back to appointments
            </a>
        </nav>

        <x-appointment.crisis-banner :appointment="$appointment" />

        <div class="grid items-start gap-5 lg:grid-cols-[minmax(280px,0.75fr)_minmax(0,1.4fr)]">
            <aside class="grid gap-5">
                <x-appointment.patient-identity-card :appointment="$appointment" :profile-route="route('doctor.patients.show', $appointment->user)" />

                <x-appointment.visit-details-card :appointment="$appointment" />

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

                <x-appointment.prescription-card
                    :appointment="$appointment"
                    :editable="true"
                    :store-route="route('doctor.appointments.prescription.store', $appointment)"
                    :download-route="route('doctor.appointments.prescription.download', $appointment)"
                />
            </aside>

            <x-appointment.screener-panel :appointment="$appointment" />
        </div>
    </div>
@endsection
