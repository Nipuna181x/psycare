import { Link } from "@tanstack/react-router";
import { ArrowUpRight } from "lucide-react";

const links = [
  { label: "Home", href: "/" },
  { label: "Book a Doctor", href: "/doctors" },
  { label: "AI Companion", href: "/ai-companion" },
  { label: "My Health Records", href: "/health-records" },
  { label: "My Appointments", href: "/appointments" },
  { label: "Group Therapy", href: "/group-therapy" },
  { label: "Mood Tracker", href: "/mood-tracker" },
];

export function SubNav() {
  return (
    <nav className="mx-auto flex max-w-[1320px] items-center justify-between gap-4 px-5 py-6 md:px-9 md:py-7">
      <Link to="/" className="flex items-center gap-2.5 text-ink">
        <span className="grid h-8 w-8 place-items-center rounded-full bg-ink">
          <span className="h-2.5 w-2.5 rounded-full bg-card" />
        </span>
        <span className="font-display text-lg font-medium tracking-tight">PsyCare</span>
      </Link>

      <div className="hidden items-center gap-1 rounded-full bg-card px-2 py-1.5 shadow-[0_1px_0_0_var(--border)] lg:flex">
        {links.map((link) => (
          <a
            key={link.href}
            href={link.href}
            className="whitespace-nowrap rounded-full px-3 py-2 text-[12px] text-ink-soft transition-colors hover:bg-secondary hover:text-ink"
          >
            {link.label}
          </a>
        ))}
      </div>

      <Link
        to="/login"
        className="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5"
      >
        Log in
        <ArrowUpRight className="h-3.5 w-3.5" />
      </Link>
    </nav>
  );
}
