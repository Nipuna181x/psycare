<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your details — Book {{ $doctor->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink">
        <x-booking-header :doctor="$doctor" :step="2" />

        <main class="mx-auto max-w-[840px] px-5 pb-24 md:px-9">
            <div class="mt-8 rounded-3xl bg-card p-6 md:p-8">
                <p class="eyebrow">Step 2 of 4</p>
                <h1 class="display-head mt-2 text-[clamp(1.5rem,3vw,2rem)] text-ink">A few details for your visit</h1>
                <p class="mt-2 text-[13px] text-ink-soft">This information is shared only with {{ $doctor->name }} and {{ $doctor->medicalCenter->name }}.</p>

                <form method="POST" action="{{ route('booking.details', $doctor) }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="patient_name" class="block text-[12px] font-medium text-ink-soft">Full name</label>
                        <input type="text" id="patient_name" name="patient_name" required value="{{ old('patient_name', $saved['patient_name'] ?? '') }}" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[14px] text-ink outline-none">
                        @error('patient_name') <p class="mt-1.5 text-[12px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="patient_age" class="block text-[12px] font-medium text-ink-soft">Age</label>
                            <input type="number" id="patient_age" name="patient_age" min="1" max="120" value="{{ old('patient_age', $saved['patient_age'] ?? '') }}" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[14px] text-ink outline-none">
                            @error('patient_age') <p class="mt-1.5 text-[12px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="patient_gender" class="block text-[12px] font-medium text-ink-soft">Gender</label>
                            <select id="patient_gender" name="patient_gender" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[14px] text-ink outline-none">
                                <option value="">Prefer not to say</option>
                                @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('patient_gender', $saved['patient_gender'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="patient_phone" class="block text-[12px] font-medium text-ink-soft">Phone number</label>
                            <input type="text" id="patient_phone" name="patient_phone" required value="{{ old('patient_phone', $saved['patient_phone'] ?? '') }}" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[14px] text-ink outline-none">
                            @error('patient_phone') <p class="mt-1.5 text-[12px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="patient_email" class="block text-[12px] font-medium text-ink-soft">Email</label>
                            <input type="email" id="patient_email" name="patient_email" value="{{ old('patient_email', $saved['patient_email'] ?? '') }}" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[14px] text-ink outline-none">
                            @error('patient_email') <p class="mt-1.5 text-[12px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="reason" class="block text-[12px] font-medium text-ink-soft">What would you like to talk about? (optional)</label>
                        <textarea id="reason" name="reason" rows="3" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[14px] text-ink outline-none">{{ old('reason', $saved['reason'] ?? '') }}</textarea>
                        @error('reason') <p class="mt-1.5 text-[12px] text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <a href="{{ route('booking.schedule', $doctor) }}" class="rounded-2xl bg-secondary px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Back</a>
                        <button type="submit" class="flex-1 rounded-2xl bg-ink px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Continue</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
