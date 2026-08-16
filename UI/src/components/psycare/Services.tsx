import { ArrowRight } from "lucide-react";

import serviceVoice from "@/assets/service-voice.jpg";
import serviceAssessment from "@/assets/service-assessment.jpg";
import serviceGroup from "@/assets/service-group.jpg";
import doc1 from "@/assets/doc-1.jpg";
import doc2 from "@/assets/doc-2.jpg";
import doc3 from "@/assets/doc-3.jpg";

const cards = [
  {
    title: "AI Voice Intake",
    img: serviceVoice,
    body: "Speak naturally; the assistant asks what a clinician would ask first.",
  },
  {
    title: "Pre-Booking Assessment",
    img: serviceAssessment,
    body: "A structured clinical note reaches your doctor before you arrive.",
  },
  {
    title: "Group Therapy",
    img: serviceGroup,
    body: "Small moderated circles of ten, island-wide and online.",
  },
];

export function Services() {
  return (
    <section id="services" className="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
      <div className="grid gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-start">
        <div>
          <div className="flex items-baseline gap-3">
            <span className="eyebrow shrink-0">Our services /</span>
            <h2 className="display-head text-[clamp(1.9rem,3.6vw,3rem)] text-ink">
              Discover our signature mental health services
            </h2>
          </div>
          <div className="mt-10 flex items-center gap-4">
            <div className="flex -space-x-3">
              {[doc1, doc2, doc3].map((src, i) => (
                <img
                  key={i}
                  src={src}
                  alt=""
                  width={800}
                  height={1000}
                  loading="lazy"
                  className="h-10 w-10 rounded-full border-2 border-background object-cover"
                />
              ))}
            </div>
            <div>
              <p className="font-display text-xl font-medium text-ink">7,500+</p>
              <p className="text-[12px] text-muted-foreground">Verified patient reviews</p>
            </div>
          </div>
        </div>

        <div className="flex flex-col gap-8">
          <p className="max-w-[52ch] text-[15px] leading-relaxed text-ink-soft">
            Care delivered with privacy, precision and patience. From the first quiet conversation
            to the follow-up review, PsyCare keeps one continuous record so you never have to
            explain yourself twice.
          </p>

          <div className="grid gap-4 sm:grid-cols-3">
            {cards.map((c) => (
              <article
                key={c.title}
                className="group relative overflow-hidden rounded-2xl bg-ink"
              >
                <img
                  src={c.img}
                  alt={c.title}
                  width={900}
                  height={1100}
                  loading="lazy"
                  className="h-80 w-full object-cover transition-transform duration-700 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-ink/95 via-ink/35 to-transparent" />
                <div className="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 p-4">
                  <div className="min-w-0">
                    <h3 className="font-display text-[15px] font-medium text-primary-foreground uppercase">
                      {c.title}
                    </h3>
                    <p className="mt-1.5 text-[12px] leading-snug text-primary-foreground/70">
                      {c.body}
                    </p>
                  </div>
                  <span className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-card text-ink">
                    <ArrowRight className="h-3.5 w-3.5" />
                  </span>
                </div>
              </article>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
