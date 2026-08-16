import { ArrowUpRight } from "lucide-react";

import doc1 from "@/assets/doc-1.jpg";
import doc2 from "@/assets/doc-2.jpg";
import doc3 from "@/assets/doc-3.jpg";

const doctors = [
  {
    name: "Dr. Anusha Perera",
    role: "Consultant Psychiatrist",
    img: doc1,
    rating: "4.9",
    reviews: "980+ reviews",
    next: "Today, 4:00 PM",
  },
  {
    name: "Dr. S. Rajaratnam",
    role: "Clinical Psychologist · Trauma",
    img: doc2,
    rating: "5.0",
    reviews: "640+ reviews",
    next: "Tuesday, 9:30 AM",
  },
  {
    name: "Ms. Dilani Fernando",
    role: "Counselling Psychologist · Teens",
    img: doc3,
    rating: "4.8",
    reviews: "1,120+ reviews",
    next: "Today, 6:15 PM",
  },
];

export function Doctors() {
  return (
    <section id="doctors" className="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
      <div className="flex flex-wrap items-end justify-between gap-5">
        <h2 className="display-head max-w-[22ch] text-[clamp(1.9rem,3.6vw,3rem)] text-ink">
          Meet the professionals behind your recovery
        </h2>
        <a
          href="#contact"
          className="inline-flex items-center gap-1.5 text-[13px] text-ink-soft transition-colors hover:text-teal-deep"
        >
          Browse all 1,480 clinicians
          <ArrowUpRight className="h-3.5 w-3.5" />
        </a>
      </div>

      <div className="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        {doctors.map((d) => (
          <article key={d.name} className="group">
            <div className="relative overflow-hidden rounded-3xl bg-secondary">
              <img
                src={d.img}
                alt={`${d.name}, ${d.role}`}
                width={800}
                height={1000}
                loading="lazy"
                className="h-[400px] w-full object-cover transition-transform duration-700 group-hover:scale-[1.04]"
              />
              <span className="absolute top-4 right-4 grid h-9 w-9 place-items-center rounded-full bg-card text-ink">
                <ArrowUpRight className="h-4 w-4" />
              </span>
              <span className="absolute bottom-4 left-4 rounded-full bg-card/95 px-3.5 py-1.5 text-[12px] font-medium text-ink backdrop-blur-sm">
                Next: {d.next}
              </span>
            </div>
            <div className="mt-4 flex items-start justify-between gap-4">
              <div className="min-w-0">
                <h3 className="font-display text-[15px] font-medium text-ink">{d.name}</h3>
                <p className="mt-0.5 text-[12px] text-muted-foreground">{d.role}</p>
              </div>
              <div className="shrink-0 text-right">
                <p className="font-display text-[15px] font-medium text-ink">{d.rating}</p>
                <p className="text-[11px] text-muted-foreground">{d.reviews}</p>
              </div>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}
