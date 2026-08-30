# Design Patterns Used in PsyCare

This document records the software design patterns applied in the PsyCare Laravel application, with the exact file(s) and line numbers where each pattern is implemented, and where to point a screenshot of the corresponding code when writing up the report. Only patterns that are genuinely present in the codebase are listed — patterns that were considered but rejected (Repository, Observer, Singleton, Resource/Transformer, dedicated Job classes, Listener classes) are noted at the end for completeness.

---

## 1. MVC (Model–View–Controller)

**Files:** `app/Models/Doctor.php` (lines 21–96), `app/Http/Controllers/DoctorController.php` (lines 42–50), `resources/views/doctors/show.blade.php`
**Screenshot:** open `DoctorController.php` lines 42–50 alongside `Doctor.php` line 21 and `doctors/show.blade.php` line 6.

MVC is the backbone of PsyCare's architecture, cleanly separating data, logic, and presentation across roughly fifty controllers. In the doctor-profile example, `DoctorController::show()` uses route-model binding to resolve a `Doctor`, eager-loads its `bookableAffiliations.clinic` relation, calls the model's own `hasBookableAffiliation()` helper, and passes plain view-model data to a dedicated Blade template. Keeping relationship logic on the Eloquent model rather than in the controller keeps each layer focused on a single responsibility, though a few controllers still let query logic creep in, showing the separation is a discipline rather than an enforced boundary.

---

## 2. Middleware Pattern

**Files:** `app/Http/Middleware/EnsureMedicalCenterIsApproved.php` (lines 10–30, `handle()` at line 17), `app/Http/Middleware/EnsureDoctorOnboardingComplete.php` (lines 17–44), registered in `bootstrap/app.php` (lines 22–27) and applied in `routes/web.php` (lines 201, 260)
**Screenshot:** `EnsureMedicalCenterIsApproved.php` full file, plus the `Route::middleware([...])` chain at `routes/web.php:201`.

Middleware forms a pipeline of independent request filters placed in front of route handlers, and PsyCare uses one per guarded role. `EnsureMedicalCenterIsApproved::handle()` checks the authenticated clinic's `status` column and force-logs-out and redirects any account that has lost approval, before the request ever reaches a controller. This keeps authorization state-checks out of every controller action, but because each guard (patient, doctor, clinic, staff) needed its own near-identical middleware class, the pattern here trades a little duplication for very explicit, easy-to-audit access rules.

---

## 3. Service Layer Pattern

**Files:** `app/Services/AppointmentPaymentService.php` (class at line 11, constructor lines 13–16, `start()` at line 19), called from `app/Http/Controllers/BookingController.php` (parameter injection line 297, invocation line 380)
**Screenshot:** `AppointmentPaymentService.php` lines 11–25 next to `BookingController.php` lines 297 and 380.

The Service Layer is the dominant architectural pattern in this codebase, with twenty-four classes in `app/Services/` encapsulating business logic that would otherwise bloat controllers. `AppointmentPaymentService` receives a `StripeCheckoutGateway` and a `PaymentCompletionNotifier` through constructor injection, and its `start()` method wraps checkout-session creation and payment persistence into one atomic operation that `BookingController::confirm()` simply calls. This makes payment logic independently testable and reusable across `PaymentCheckoutController`, though it does mean tracing a full request now requires jumping between controller and service files.

---

## 4. Strategy Pattern

**Files:** `app/Services/StripeCheckoutGateway.php` (interface, lines 7–12), `app/Services/StripeHttpCheckoutGateway.php` (implementation, line 10), bound in `app/Providers/AppServiceProvider.php` (line 20), consumed in `app/Services/AppointmentPaymentService.php` (line 13)
**Screenshot:** the interface file next to the `$this->app->bind(...)` line in `AppServiceProvider.php`.

`StripeCheckoutGateway` is defined as an interface with `createSession()`, `retrieveSession()`, and `expireSession()`, implemented concretely by `StripeHttpCheckoutGateway` and bound in the service container rather than hard-coded. `AppointmentPaymentService` depends only on the interface, so the real Stripe HTTP client could be swapped for a mock or a different gateway without touching payment logic. This is the one place in the app where behaviour is genuinely selected through polymorphism rather than conditionals — by contrast, the text-to-speech services (`GoogleTextToSpeech`, `GeminiTextToSpeech`) are chosen via an if-statement instead of a shared interface, showing where the same pattern could have been applied but wasn't.

---

## 5. Facade Pattern

**Files:** `Auth::` in `app/Http/Controllers/BookingController.php` (line 147) and `app/Http/Middleware/EnsureMedicalCenterIsApproved.php` (line 19); `DB::` in `app/Services/AppointmentPaymentService.php` (line 73); `Vite::` in `resources/views/home.blade.php` (line 26); `Mail::` in `app/Services/PaymentCompletionNotifier.php` (line 33)
**Screenshot:** any two or three of these `Auth::`, `DB::`, or `Mail::` call sites side by side.

Laravel's Facade pattern gives PsyCare's codebase a static-looking, expressive syntax (`Auth::user()`, `DB::transaction()`, `Mail::to(...)->queue(...)`) while the underlying objects are still resolved from the service container and remain fully swappable and testable. `AppointmentPaymentService` wraps its payment-confirmation logic in `DB::transaction()` to guarantee the payment record and appointment status update commit atomically. The convenience is real, but overuse of facades throughout services can quietly hide dependencies that would otherwise be visible in a constructor signature.

---

## 6. Form Request Validation Pattern

**Files:** `app/Http/Requests/Booking/StoreScheduleRequest.php` (class line 9), used by `app/Http/Controllers/BookingController.php` (line 97, `storeSchedule(StoreScheduleRequest $request, ...)`)
**Screenshot:** `StoreScheduleRequest.php` full file next to `BookingController.php` line 97.

With forty `FormRequest` subclasses under `app/Http/Requests/`, PsyCare pushes validation and authorization rules entirely out of controllers and into dedicated, reusable classes. `StoreScheduleRequest` defines its `rules()` for `appointment_date` and `appointment_time`, and Laravel automatically validates the incoming request before `BookingController::storeSchedule()` ever executes, guaranteeing the method body only runs against clean data. This keeps controllers thin and validation rules unit-testable in isolation, at the cost of one extra file per form.

---

## 7. Policy Pattern (Authorization)

**Files:** `app/Policies/TherapyRoomPolicy.php` (class line 9, methods `view()` line 15, `manageParticipants()` line 29, `start()` line 34, `join()` line 47)
**Screenshot:** `TherapyRoomPolicy.php` full file.

`TherapyRoomPolicy` centralises every authorization rule for the live group-therapy feature — a doctor may only manage or start a room they own, and a patient may only join one they are actively assigned to and only while its lifecycle status allows it. Consolidating these checks in one policy class, rather than scattering `abort_unless` calls across `TherapyRoomController`, makes the access rules for this sensitive real-time feature easy to review together. It is currently the only Policy class in the app, so equivalent authorization elsewhere (e.g. appointments) still relies on ad-hoc controller checks instead of this same structure.

---

## 8. Queued Notifications & Mailables (Command-style deferred execution)

**Files:** `app/Notifications/BookingConfirmed.php` (line 12, `implements ShouldQueue`), `app/Mail/PaymentReceipt.php` (line 14, `implements ShouldQueue`), dispatched from `app/Services/PaymentCompletionNotifier.php` (lines 22–40, including `->onQueue('high')->afterCommit()` for `ElevatedRiskFlagged`)
**Screenshot:** `PaymentCompletionNotifier.php` lines 22–40.

Rather than dedicated `app/Jobs` classes, PsyCare defers slow work — emailing receipts, sending booking confirmations — by marking `Notification` and `Mailable` classes with `ShouldQueue`, so `PaymentCompletionNotifier` can fire off several notifications without blocking the HTTP response. The `ElevatedRiskFlagged` notification is explicitly routed to a separate `high` queue with `afterCommit()`, ensuring a clinical risk alert is only queued once the database transaction has safely committed. This is a lighter-weight variant of the classic Command pattern: it defers execution like a queued job, but reuses Laravel's mail/notification objects instead of introducing a parallel `Job` class hierarchy.

---

## 9. Broadcasting (Event-driven real-time updates)

**Files:** `app/Events/TherapyRoomSignal.php` (line 18, `implements ShouldBroadcastNow`), `app/Events/TherapyRoomEnded.php` (line 16), `app/Events/TherapyRoomParticipantKicked.php` (line 17), dispatched from `app/Http/Controllers/Doctor/TherapyRoomController.php` (lines 197, 212, 259)
**Screenshot:** `TherapyRoomController.php` lines 195–215 showing the `broadcast(new ...)` calls.

Three `ShouldBroadcastNow` event classes push real-time state over WebSockets (Laravel Reverb/Echo) to every participant in a live therapy room the instant a doctor ends the session, kicks a participant, or relays a WebRTC signal. This is a one-directional variant of the Event pattern aimed at the browser rather than server-side listeners — there are no corresponding `Listener` classes, because the events exist purely to notify connected clients, not to trigger further server-side workflows. It is an efficient fit for the video-call feature, but it also means this event system cannot currently be reused for internal domain events without adding a proper listener layer.

---

## 10. View Composer Pattern

**Files:** `app/Providers/AppServiceProvider.php` (lines 28–30), `app/View/Composers/DoctorPortalComposer.php`, `MedicalCenterPortalComposer.php`, `AdminPortalComposer.php`
**Screenshot:** `AppServiceProvider.php` lines 28–30.

Each portal layout (`layouts.doctor`, `layouts.medical-center`, `layouts.admin`) is bound to its own composer class via `View::composer()`, so shared sidebar/topbar data — pending counts, the logged-in user's profile — is injected automatically every time that layout renders, without every controller having to remember to pass it manually. This is Laravel's own take on the Observer idea applied to views: the composer "listens" for its layout being rendered. It keeps navigation-chrome data consistent across dozens of pages, though it does mean that data dependency is implicit rather than visible in each controller's return statement.

---

## Patterns considered and confirmed absent

For accuracy, the following commonly-taught patterns were checked for and are **not** implemented in PsyCare, so they should not be claimed in the report:

- **Repository pattern** — no `app/Repositories/` directory; controllers and services query Eloquent models directly.
- **Observer pattern (model events)** — no `app/Observers/` directory and no `booted()`/`static::created()` hooks on any model.
- **Singleton pattern** — no `->singleton()` bindings and no manually implemented singleton class; the one container binding present (`StripeCheckoutGateway`) uses `->bind()`, a fresh instance per resolution.
- **Resource/Transformer pattern** — no `app/Http/Resources/`; JSON responses are built as plain arrays.
- **Classic Listener pattern** — `app/Listeners/` does not exist; the three broadcast events above have no server-side listeners.
- **Generic (GoF) Factory pattern** — `database/factories/` only contains standard Eloquent model factories for testing/seeding, not a broader object-creation abstraction.
</content>
