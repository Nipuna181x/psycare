import { Plus } from "lucide-react";

import aboutCare from "@/assets/about-care.jpg";

const items = [
  "One island-wide directory, real availability",
  "Assessment summary sent ahead of your visit",
  "Confidential records, encrypted and deletable",
  "Follow-up reminders and medication reviews",
];

export function About() {
  return (
    <section id="about" className="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
      <div className="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
        <img
          src={aboutCare}
          alt="A counsellor speaking with a young patient and her mother in a bright room"
          width={1200}
          height={900}
          loading="lazy"
          className="h-[380px] w-full rounded-3xl object-cover md:h-[480px]"
        />

        <div>
          <div className="flex items-baseline gap-3">
            <span className="eyebrow shrink-0">About PsyCare /</span>
            <h2 className="display-head text-[clamp(1.9rem,3.6vw,3rem)] text-ink">
              Excellence in mental health care, with comfort at the centre
            </h2>
          </div>
          <p className="mt-6 max-w-[54ch] text-[15px] leading-relaxed text-ink-soft">
            PsyCare was built with Sri Lankan clinicians, for Sri Lankan patients. Every
            practitioner is verified against their professional register, and every appointment
            begins with a doctor who already understands why you came.
          </p>

          <ul className="mt-9 divide-y divide-border border-y border-border">
            {items.map((i) => (
              <li key={i} className="flex items-center justify-between gap-4 py-4">
                <span className="text-[15px] text-ink">{i}</span>
                <span className="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-secondary text-ink-soft">
                  <Plus className="h-3.5 w-3.5" />
                </span>
              </li>
            ))}
          </ul>

          <a
            href="#doctors"
            className="mt-9 inline-flex rounded-full bg-ink px-7 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5"
          >
            Book an appointment
          </a>
        </div>
      </div>
    </section>
  );
}
