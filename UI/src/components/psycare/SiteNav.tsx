import { Link } from "@tanstack/react-router";
import { ArrowUpRight, Menu } from "lucide-react";

const links = [
  { label: "Home", href: "#top" },
  { label: "Services", href: "#services" },
  { label: "About us", href: "#about" },
  { label: "Doctors", to: "/doctors" as const },
  { label: "Reviews", href: "#reviews" },
  { label: "Contact", href: "#contact" },
];

export function SiteNav() {
  return (
    <nav className="absolute inset-x-0 top-0 z-20 flex items-center justify-between gap-4 px-5 py-5 md:px-9 md:py-7">
      <a href="#top" className="flex items-center gap-2.5 text-primary-foreground">
        <span className="grid h-8 w-8 place-items-center rounded-full bg-primary-foreground/20 backdrop-blur-sm">
          <span className="h-2.5 w-2.5 rounded-full bg-primary-foreground" />
        </span>
        <span className="font-display text-lg font-medium tracking-tight">PsyCare</span>
      </a>

      <div className="hidden items-center gap-1 rounded-full bg-primary-foreground/12 px-2 py-1.5 backdrop-blur-md lg:flex">
        {links.map((l) =>
          l.to ? (
            <Link
              key={l.label}
              to={l.to}
              className="rounded-full px-4 py-2 text-[13px] text-primary-foreground/80 transition-colors hover:bg-primary-foreground/20 hover:text-primary-foreground"
            >
              {l.label}
            </Link>
          ) : (
            <a
              key={l.label}
              href={l.href}
              className="rounded-full px-4 py-2 text-[13px] text-primary-foreground/80 transition-colors hover:bg-primary-foreground/20 hover:text-primary-foreground"
            >
              {l.label}
            </a>
          ),
        )}
      </div>

      <div className="flex items-center gap-2">
        <Link
          to="/doctors"
          className="rounded-full bg-card px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5"
        >
          Book a doctor
        </Link>
        <Link
          to="/login"
          aria-label="Log in"
          title="Log in"
          className="grid h-11 w-11 place-items-center rounded-full bg-card text-ink transition-transform hover:-translate-y-0.5"
        >
          <ArrowUpRight className="h-4 w-4" />
        </Link>
        <button
          type="button"
          aria-label="Open menu"
          className="grid h-11 w-11 place-items-center rounded-full bg-primary-foreground/15 text-primary-foreground backdrop-blur-md lg:hidden"
        >
          <Menu className="h-4 w-4" />
        </button>
      </div>
    </nav>
  );
}
