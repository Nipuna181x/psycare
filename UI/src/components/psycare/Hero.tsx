import { ChevronLeft, ChevronRight, Star } from "lucide-react";

import heroConsult from "@/assets/hero-consult.jpg";
import doc1 from "@/assets/doc-1.jpg";
import { SiteNav } from "./SiteNav";

const chips = [
  "Psychiatry",
  "Counselling",
  "Group therapy",
  "AI voice intake",
  "Child & teen",
  "Online consults",
];

export function Hero() {
  return (
    <section id="top" className="px-3 pt-3 md:px-5 md:pt-5">
      <div className="relative overflow-hidden rounded-3xl bg-ink">
        <img
          src={heroConsult}
          alt="A psychiatrist listening to a patient in a sunlit consulting room in Sri Lanka"
          width={1920}
          height={1200}
          className="h-[720px] w-full object-cover object-center md:h-[780px]"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/30 to-ink/55" />

        <SiteNav />

        <div className="absolute inset-x-0 bottom-0 px-5 pb-6 md:px-9 md:pb-9">
          <div className="grid gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
            <div className="rise-in">
              <h1 className="display-head max-w-[16ch] text-[clamp(2.4rem,5.6vw,4.4rem)] text-primary-foreground">
                Every doctor in Sri Lanka, one calm booking.
              </h1>
              <div className="mt-7 flex flex-wrap gap-2">
                {chips.map((c, i) => (
                  <span
                    key={c}
                    className={
                      i === 0
                        ? "rounded-full bg-card px-4 py-2 text-[12px] font-medium text-ink"
                        : "rounded-full bg-primary-foreground/14 px-4 py-2 text-[12px] text-primary-foreground/85 backdrop-blur-md"
                    }
                  >
                    {c}
                  </span>
                ))}
              </div>
            </div>

            <div className="flex flex-col gap-6">
              <p className="max-w-[44ch] text-[15px] leading-relaxed text-primary-foreground/80">
                PsyCare brings every registered psychiatrist, psychologist and counsellor on the
                island into one place — with an AI voice assistant that prepares your assessment
                before the appointment begins.
              </p>

              <div className="flex items-center gap-4 text-primary-foreground/70">
                <button type="button" aria-label="Previous" className="hover:text-primary-foreground">
                  <ChevronLeft className="h-4 w-4" />
                </button>
                <span className="text-[12px] tracking-widest">01</span>
                <span className="h-px flex-1 bg-primary-foreground/25">
                  <span className="block h-px w-1/5 bg-primary-foreground" />
                </span>
                <span className="text-[12px] tracking-widest">05</span>
                <button type="button" aria-label="Next" className="hover:text-primary-foreground">
                  <ChevronRight className="h-4 w-4" />
                </button>
              </div>

              <article className="flex items-center gap-4 rounded-2xl bg-card p-3.5">
                <img
                  src={doc1}
                  alt="Dr. Anusha Perera, consultant psychiatrist"
                  width={800}
                  height={1000}
                  loading="lazy"
                  className="h-16 w-16 shrink-0 rounded-xl object-cover"
                />
                <div className="min-w-0">
                  <p className="truncate font-display text-[15px] font-medium text-ink">
                    Dr. Anusha Perera
                  </p>
                  <p className="text-[12px] text-muted-foreground">
                    Consultant Psychiatrist · Colombo 07
                  </p>
                  <p className="mt-1.5 flex items-center gap-1.5 text-[12px] text-ink-soft">
                    <Star className="h-3 w-3 fill-teal text-teal" />
                    14 years experience (4.9 rating)
                  </p>
                </div>
              </article>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
