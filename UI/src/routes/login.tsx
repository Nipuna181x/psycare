import { createFileRoute, Link } from "@tanstack/react-router";
import { Building2, User } from "lucide-react";
import { useState } from "react";

import { SubNav } from "@/components/psycare/SubNav";
import aboutCare from "@/assets/about-care.jpg";

export const Route = createFileRoute("/login")({
  head: () => ({
    meta: [
      { title: "Log in or Register — PsyCare Sri Lanka" },
      {
        name: "description",
        content:
          "Sign in to PsyCare as a patient to manage appointments, or as a clinic to publish your clinicians and availability.",
      },
      { property: "og:title", content: "Log in or Register — PsyCare Sri Lanka" },
      {
        property: "og:description",
        content: "Patient and clinic accounts for Sri Lanka's single mental health booking platform.",
      },
    ],
  }),
  component: LoginPage,
});

type Role = "patient" | "clinic";
type Mode = "login" | "register";

function LoginPage() {
  const [role, setRole] = useState<Role>("patient");
  const [mode, setMode] = useState<Mode>("login");

  const isClinic = role === "clinic";

  return (
    <div className="min-h-screen bg-background text-ink selection:bg-teal/20">
      <SubNav />

      <main className="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
        <div className="grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
          <section className="relative hidden overflow-hidden rounded-3xl bg-ink lg:block">
            <img
              src={aboutCare}
              alt="A counsellor speaking with a patient in a calm consulting room"
              width={1200}
              height={1500}
              loading="lazy"
              className="h-full w-full object-cover"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/25 to-ink/40" />
            <div className="absolute inset-x-0 bottom-0 p-8">
              <h2 className="display-head max-w-[18ch] text-[clamp(1.6rem,2.6vw,2.4rem)] text-primary-foreground">
                One account, every clinic on the island
              </h2>
              <p className="mt-4 max-w-[38ch] text-[14px] leading-relaxed text-primary-foreground/75">
                Patients keep their assessments, notes and upcoming sessions in one place. Clinics
                manage clinicians, rooms and live availability.
              </p>
            </div>
          </section>

          <section className="rounded-3xl bg-card p-6 md:p-10">
            <p className="eyebrow">Account access</p>
            <h1 className="display-head mt-3 text-[clamp(1.8rem,3.2vw,2.6rem)] text-ink">
              {mode === "login" ? "Welcome back to PsyCare" : "Create your PsyCare account"}
            </h1>

            <div className="mt-8 grid grid-cols-2 gap-2">
              {(
                [
                  { key: "patient" as Role, label: "Patient", icon: User, note: "Book & track care" },
                  { key: "clinic" as Role, label: "Clinic", icon: Building2, note: "Manage clinicians" },
                ]
              ).map((t) => (
                <button
                  key={t.key}
                  type="button"
                  onClick={() => setRole(t.key)}
                  aria-pressed={role === t.key}
                  className={
                    role === t.key
                      ? "rounded-2xl bg-ink p-4 text-left text-primary-foreground"
                      : "rounded-2xl bg-secondary p-4 text-left text-ink-soft transition-colors hover:text-ink"
                  }
                >
                  <t.icon className="h-4 w-4" />
                  <span className="mt-3 block font-display text-[15px] font-medium">{t.label}</span>
                  <span className="mt-0.5 block text-[12px] opacity-70">{t.note}</span>
                </button>
              ))}
            </div>

            <div className="mt-6 inline-flex rounded-full bg-secondary p-1">
              {(["login", "register"] as Mode[]).map((m) => (
                <button
                  key={m}
                  type="button"
                  onClick={() => setMode(m)}
                  className={
                    mode === m
                      ? "rounded-full bg-card px-5 py-2 text-[12px] font-medium text-ink"
                      : "rounded-full px-5 py-2 text-[12px] text-ink-soft transition-colors hover:text-ink"
                  }
                >
                  {m === "login" ? "Log in" : "Register"}
                </button>
              ))}
            </div>

            <form onSubmit={(e) => e.preventDefault()} className="mt-7 space-y-4">
              {mode === "register" && (
                <Field
                  label={isClinic ? "Clinic name" : "Full name"}
                  placeholder={isClinic ? "Serene Mind Clinic" : "Amaya Silva"}
                />
              )}
              {mode === "register" && isClinic && (
                <Field label="Ministry of Health registration no." placeholder="PHSRC/2019/0421" />
              )}
              <Field
                label={isClinic ? "Clinic email" : "Email"}
                type="email"
                placeholder={isClinic ? "admin@clinic.lk" : "you@email.com"}
              />
              {mode === "register" && !isClinic && (
                <Field label="Mobile number" type="tel" placeholder="+94 77 123 4567" />
              )}
              <Field label="Password" type="password" placeholder="••••••••" />

              {mode === "login" ? (
                <div className="flex items-center justify-between text-[12px] text-ink-soft">
                  <label className="flex items-center gap-2">
                    <input type="checkbox" className="accent-teal-deep" />
                    Keep me signed in
                  </label>
                  <button type="button" className="transition-colors hover:text-teal-deep">
                    Forgot password?
                  </button>
                </div>
              ) : (
                <label className="flex items-start gap-2.5 text-[12px] leading-relaxed text-ink-soft">
                  <input type="checkbox" className="mt-0.5 accent-teal-deep" />
                  I agree to PsyCare's clinical privacy policy and consent to secure storage of my
                  records.
                </label>
              )}

              <button
                type="submit"
                className="w-full rounded-full bg-ink px-6 py-4 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5"
              >
                {mode === "login"
                  ? `Log in as ${isClinic ? "clinic" : "patient"}`
                  : `Register as ${isClinic ? "clinic" : "patient"}`}
              </button>
            </form>

            <p className="mt-6 text-[12px] text-ink-soft">
              Looking for a clinician instead?{" "}
              <Link to="/doctors" className="text-teal-deep underline-offset-4 hover:underline">
                Browse doctors
              </Link>
            </p>
          </section>
        </div>
      </main>
    </div>
  );
}

function Field({
  label,
  type = "text",
  placeholder,
}: {
  label: string;
  type?: string;
  placeholder?: string;
}) {
  return (
    <label className="block">
      <span className="text-[12px] text-ink-soft">{label}</span>
      <input
        type={type}
        placeholder={placeholder}
        className="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none focus:ring-2 focus:ring-ring"
      />
    </label>
  );
}