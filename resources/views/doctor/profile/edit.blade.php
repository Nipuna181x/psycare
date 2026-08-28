@extends('layouts.doctor')

@php
    $title = 'Profile & Settings';
    $subtitle = 'Manage your professional and account information';
    $inputClasses = 'mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100';
@endphp

@section('content')
    @if (session('status'))
        <div class="mb-5 rounded-2xl bg-emerald-50 px-4 py-3 text-[13px] text-emerald-700">{{ session('status') }}</div>
    @endif

    <div class="grid items-start gap-5 xl:grid-cols-2">
        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <div class="flex items-center gap-4 border-b border-border pb-5">
                @if ($doctor->avatarUrl())
                    <img src="{{ $doctor->avatarUrl() }}" alt="{{ $doctor->name }}" class="h-14 w-14 rounded-2xl object-cover">
                @else
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-sky-100 text-[15px] font-semibold text-sky-700">{{ $doctor->initials() }}</span>
                @endif
                <div><h2 class="font-display text-[16px] font-medium text-ink">Professional profile</h2><p class="mt-1 text-[11px] text-ink-soft">Information shown throughout the doctor portal.</p></div>
            </div>

            <form method="POST" action="{{ route('doctor.profile.information.update') }}" enctype="multipart/form-data" class="mt-5 grid gap-4">
                @csrf @method('PATCH')
                <label class="text-[11px] font-medium text-ink">Name<input name="name" value="{{ old('name', $doctor->name) }}" required class="{{ $inputClasses }}"></label>
                <label class="text-[11px] font-medium text-ink">Specialization<input name="specialization" value="{{ old('specialization', $doctor->specialization) }}" class="{{ $inputClasses }}"></label>
                <label class="text-[11px] font-medium text-ink">Active clinics<input value="{{ $doctor->activeAffiliations->pluck('clinic.name')->implode(', ') ?: 'No active clinic affiliations' }}" disabled class="{{ $inputClasses }} bg-secondary text-ink-soft"></label>
                <label class="text-[11px] font-medium text-ink">Bio / about<textarea name="bio" rows="4" class="{{ $inputClasses }} resize-y">{{ old('bio', $doctor->bio) }}</textarea></label>
                <label class="text-[11px] font-medium text-ink">Profile photo<input type="file" name="profile_photo" accept="image/png,image/jpeg,image/webp" class="{{ $inputClasses }} file:mr-3 file:rounded-lg file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-[10px] file:font-semibold file:text-ink"></label>
                @error('profile_photo')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                <button class="justify-self-start rounded-xl bg-sky-700 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-sky-800">Save profile</button>
            </form>
        </section>

        <div class="grid gap-5">
            <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
                <div class="flex items-center justify-between border-b border-border pb-4"><div><h2 class="font-display text-[16px] font-medium text-ink">Contact information</h2><p class="mt-1 text-[11px] text-ink-soft">Used for account and clinic communication.</p></div><x-dashboard.badge :status="$doctor->status" /></div>
                <form method="POST" action="{{ route('doctor.profile.contact.update') }}" class="mt-5 grid gap-4">
                    @csrf @method('PATCH')
                    <label class="text-[11px] font-medium text-ink">Email<input type="email" name="email" value="{{ old('email', $doctor->email) }}" required class="{{ $inputClasses }}"></label>
                    <label class="text-[11px] font-medium text-ink">Phone<input name="phone" value="{{ old('phone', $doctor->phone) }}" class="{{ $inputClasses }}"></label>
                    @error('email')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                    <button class="justify-self-start rounded-xl bg-sky-700 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-sky-800">Save contact</button>
                </form>
            </section>

            <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
                <h2 class="font-display text-[16px] font-medium text-ink">Change password</h2>
                <p class="mt-1 text-[11px] text-ink-soft">Use a unique password you do not use elsewhere.</p>
                <form method="POST" action="{{ route('doctor.profile.password.update') }}" class="mt-5 grid gap-4">
                    @csrf @method('PATCH')
                    <label class="text-[11px] font-medium text-ink">Current password<input type="password" name="current_password" required autocomplete="current-password" class="{{ $inputClasses }}"></label>
                    <label class="text-[11px] font-medium text-ink">New password<input type="password" name="password" required autocomplete="new-password" class="{{ $inputClasses }}"></label>
                    <label class="text-[11px] font-medium text-ink">Confirm new password<input type="password" name="password_confirmation" required autocomplete="new-password" class="{{ $inputClasses }}"></label>
                    @if ($errors->any())<p class="text-[11px] text-red-700">{{ $errors->first() }}</p>@endif
                    <button class="justify-self-start rounded-xl bg-sky-700 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-sky-800">Update password</button>
                </form>
            </section>
        </div>
    </div>
@endsection
