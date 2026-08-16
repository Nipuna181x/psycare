import { ArrowUpRight } from "lucide-react";

const columns = [
  {
    title: "Care",
    links: ["Individual therapy", "Psychiatry & medication", "Group circles", "Child & teen care"],
  },
  {
    title: "Company",
    links: ["About PsyCare", "For clinicians", "Careers", "Press"],
  },
  {
    title: "Trust",
    links: ["Privacy & data", "Confidentiality policy", "care@psycare.lk", "+94 11 244 0000"],
  },
];

export function SiteFooter() {
  return (
    <footer id="contact" className="px-3 pb-3 md:px-5 md:pb-5">
      <div className="rounded-3xl bg-ink px-6 py-14 text-primary-foreground md:px-12 md:py-20">
        <div className="grid gap-12 lg:grid-cols-[1.2fr_1.8fr]">
          <div>
            <p className="font-display text-2xl font-medium tracking-tight">PsyCare</p>
            <p className="mt-4 max-w-[34ch] text-[15px] leading-relaxed text-primary-foreground/65">
              One place to find and book every registered mental health professional in Sri Lanka.
            </p>
            <a
              href="#doctors"
              className="mt-8 inline-flex items-center gap-2 rounded-full bg-card px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5"
            >
              Book a doctor
              <ArrowUpRight className="h-3.5 w-3.5" />
            </a>
          </div>

          <div className="grid gap-10 sm:grid-cols-3">
            {columns.map((c) => (
              <div key={c.title}>
                <p className="text-[11px] tracking-[0.16em] text-primary-foreground/45 uppercase">
                  {c.title}
                </p>
                <ul className="mt-5 space-y-2.5 text-[14px] text-primary-foreground/75">
                  {c.links.map((l) => (
                    <li key={l} className="transition-colors hover:text-primary-foreground">
                      {l}
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>
        </div>

        <div className="mt-16 flex flex-wrap items-center justify-between gap-4 border-t border-primary-foreground/15 pt-6 text-[11px] tracking-[0.14em] text-primary-foreground/45 uppercase">
          <span>© 2026 PsyCare Lanka (Pvt) Ltd</span>
          <span>In crisis? Call 1926 — free, 24 hours</span>
        </div>
      </div>
    </footer>
  );
}
