@extends('layouts.medical-center')

@php
    $title = 'Appointment #'.str_pad($appointment->id, 6, '0', STR_PAD_LEFT);
    $subtitle = $appointment->patient_name.' with '.$appointment->doctor->name;
@endphp

@section('content')
    <div class="grid gap-5">
        <nav aria-label="Breadcrumb">
            <a href="{{ route('medical-center.appoinment-managment.index') }}" class="inline-flex items-center gap-2 text-[12px] font-medium text-blue-800 transition-colors hover:text-blue-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-500">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Back to appointments
            </a>
        </nav>

        <x-appointment.crisis-banner :appointment="$appointment" />

        <div class="grid items-start gap-5 lg:grid-cols-[minmax(280px,0.75fr)_minmax(0,1.4fr)]">
            <aside class="grid gap-5">
                <x-appointment.patient-identity-card :appointment="$appointment" :profile-route="null" />

                <x-appointment.visit-details-card :appointment="$appointment" />

                <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="visit-doctor-heading">
                    <h2 id="visit-doctor-heading" class="font-display text-[15px] font-medium text-ink">Treating doctor</h2>
                    <p class="mt-2 text-[13px] font-medium text-ink">{{ $appointment->doctor->name }}</p>
                    @if ($appointment->doctor->specialization)
                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->doctor->specialization }}</p>
                    @endif
                </section>

                @if ($appointment->status === 'confirmed')
                    <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="appointment-actions-heading">
                        <h2 id="appointment-actions-heading" class="font-display text-[15px] font-medium text-ink">Appointment actions</h2>
                        <div class="mt-4">
                            <form method="POST" action="{{ route('medical-center.appoinment-managment.status', $appointment) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="w-full rounded-xl border border-red-200 bg-white px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-red-700 uppercase transition-colors hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-red-500">Cancel appointment</button>
                            </form>
                        </div>
                    </section>
                @endif

                <x-appointment.prescription-card
                    :appointment="$appointment"
                    :editable="false"
                    :download-route="route('medical-center.appoinment-managment.prescription.download', $appointment)"
                />
            </aside>

            <x-appointment.screener-panel :appointment="$appointment" />
        </div>
    </div>
@endsection
