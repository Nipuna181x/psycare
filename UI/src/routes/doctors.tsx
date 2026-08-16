import { createFileRoute } from "@tanstack/react-router";
import { ArrowUpRight, CalendarDays, MapPin, Search, Star } from "lucide-react";
import { useMemo, useState } from "react";

import { SubNav } from "@/components/psycare/SubNav";
import { SiteFooter } from "@/components/psycare/SiteFooter";
import doc1 from "@/assets/doc-1.jpg";
import doc2 from "@/assets/doc-2.jpg";
import doc3 from "@/assets/doc-3.jpg";
import doc4 from "@/assets/doc-4.jpg";
import doc5 from "@/assets/doc-5.jpg";
import doc6 from "@/assets/doc-6.jpg";

export const Route = createFileRoute("/doctors")({
  head: () => ({
    meta: [
      { title: "Find & Book a Doctor — PsyCare Sri Lanka" },
      {
        name: "description",
        content:
          "Search every registered psychiatrist, psychologist and counsellor in Sri Lanka by location, date and specialty, then book an appointment in minutes.",
      },
      { property: "og:title", content: "Find & Book a Doctor — PsyCare Sri Lanka" },
      {
        property: "og:description",
        content:
          "Filter clinicians by nearest city, available date and specialty, and reserve the next open slot.",
      },
    ],
  }),
  component: DoctorsPage,
});

type Doctor = {
  name: string;
  role: string;
  specialty: string;
  clinic: string;
  city: string;
  img: string;
  rating: string;
  reviews: string;
  fee: string;
  next: string;
  mode: string;
};

const doctors: Doctor[] = [
  {
    name: "Dr. Anusha Perera",
    role: "Consultant Psychiatrist",
    specialty: "Psychiatry",
    clinic: "Serene Mind Clinic",
    city: "Colombo",
    img: doc1,
    rating: "4.9",
    reviews: "980+ reviews",
    fee: "LKR 4,500",
    next: "Today, 4:00 PM",
    mode: "In-person & online",
  },
  {
    name: "Dr. S. Rajaratnam",
    role: "Clinical Psychologist · Trauma",
    specialty: "Trauma",
    clinic: "Northern Wellbeing Centre",
    city: "Jaffna",
    img: doc2,
    rating: "5.0",
    reviews: "640+ reviews",
    fee: "LKR 3,800",
    next: "Tuesday, 9:30 AM",
    mode: "In-person",
  },
  {
    name: "Ms. Dilani Fernando",
    role: "Counselling Psychologist · Teens",
    specialty: "Child & teen",
    clinic: "Lagoon Counselling Rooms",
    city: "Negombo",
    img: doc3,
    rating: "4.8",
    reviews: "1,120+ reviews",
    fee: "LKR 3,200",
    next: "Today, 6:15 PM",
    mode: "Online",
  },
  {
    name: "Dr. Nuwan Bandara",
    role: "Consultant Psychiatrist · Addiction",
    specialty: "Psychiatry",
    clinic: "Hill Country Medical Institute",
    city: "Kandy",
    img: doc4,
    rating: "4.7",
    reviews: "520+ reviews",
    fee: "LKR 4,000",
    next: "Tomorrow, 11:00 AM",
    mode: "In-person & online",
  },
  {
    name: "Ms. Hasini Jayawardena",
    role: "Counselling Psychologist · Couples",
    specialty: "Counselling",
    clinic: "Southern Care Collective",
    city: "Galle",
    img: doc5,
    rating: "4.9",
    reviews: "760+ reviews",
    fee: "LKR 3,500",
    next: "Today, 7:30 PM",
    mode: "Online",
  },
  {
    name: "Dr. Mahesh Kulasooriya",
    role: "Clinical Psychologist · Anxiety",
    specialty: "Counselling",
    clinic: "Eastern Mind Practice",
    city: "Batticaloa",
    img: doc6,
    rating: "4.8",
    reviews: "410+ reviews",
    fee: "LKR 3,600",
    next: "Wednesday, 2:45 PM",
    mode: "In-person",
  },
];

const cities = ["All locations", "Colombo", "Kandy", "Galle", "Jaffna", "Negombo", "Batticaloa"];
const specialties = ["All", "Psychiatry", "Counselling", "Trauma", "Child & teen"];

function DoctorsPage() {
  const [city, setCity] = useState("All locations");
  const [date, setDate] = useState("");
  const [specialty, setSpecialty] = useState("All");
  const [query, setQuery] = useState("");

  const results = useMemo(
    () =>
      doctors.filter((d) => {
        const cityOk = city === "All locations" || d.city === city;
        const specOk = specialty === "All" || d.specialty === specialty;
        const q = query.trim().toLowerCase();
        const queryOk =
          !q ||
          d.name.toLowerCase().includes(q) ||
          d.clinic.toLowerCase().includes(q) ||
          d.role.toLowerCase().includes(q);
        return cityOk && specOk && queryOk;
      }),
    [city, specialty, query],
  );

  return (
    <div className="min-h-screen bg-background text-ink selection:bg-teal/20">
      <SubNav />

      <main className="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
        <header className="max-w-[38ch]">
          <p className="eyebrow">Appointments</p>
          <h1 className="display-head mt-3 text-[clamp(2.1rem,4.6vw,3.6rem)] text-ink">
            Book any doctor, any clinic, one calm search
          </h1>
        </header>

        <form
          onSubmit={(e) => e.preventDefault()}
          className="mt-10 rounded-3xl bg-card p-4 md:p-5"
        >
          <div className="grid gap-3 md:grid-cols-[1.1fr_1fr_1.3fr_auto]">
            <label className="flex items-center gap-3 rounded-2xl bg-secondary px-4 py-3.5">
              <MapPin className="h-4 w-4 shrink-0 text-teal-deep" />
              <span className="sr-only">Nearest location</span>
              <select
                value={city}
                onChange={(e) => setCity(e.target.value)}
                className="w-full bg-transparent text-[13px] text-ink outline-none"
              >
                {cities.map((c) => (
                  <option key={c} value={c}>
                    {c}
                  </option>
                ))}
              </select>
            </label>

            <label className="flex items-center gap-3 rounded-2xl bg-secondary px-4 py-3.5">
              <CalendarDays className="h-4 w-4 shrink-0 text-teal-deep" />
              <span className="sr-only">Preferred date</span>
              <input
                type="date"
                value={date}
                onChange={(e) => setDate(e.target.value)}
                className="w-full bg-transparent text-[13px] text-ink outline-none"
              />
            </label>

            <label className="flex items-center gap-3 rounded-2xl bg-secondary px-4 py-3.5">
              <Search className="h-4 w-4 shrink-0 text-teal-deep" />
              <span className="sr-only">Doctor or clinic</span>
              <input
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="Doctor, clinic or concern"
                className="w-full bg-transparent text-[13px] text-ink placeholder:text-muted-foreground outline-none"
              />
            </label>

            <button
              type="submit"
              className="rounded-2xl bg-ink px-7 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5"
            >
              Search
            </button>
          </div>

          <div className="mt-4 flex flex-wrap items-center gap-2 border-t border-border pt-4">
            {specialties.map((s) => (
              <button
                key={s}
                type="button"
                onClick={() => setSpecialty(s)}
                className={
                  s === specialty
                    ? "rounded-full bg-ink px-4 py-2 text-[12px] font-medium text-primary-foreground"
                    : "rounded-full bg-secondary px-4 py-2 text-[12px] text-ink-soft transition-colors hover:text-ink"
                }
              >
                {s}
              </button>
            ))}
          </div>
        </form>

        <p className="mt-8 text-[13px] text-ink-soft">
          {results.length} clinician{results.length === 1 ? "" : "s"} available
          {city !== "All locations" ? ` in ${city}` : " island-wide"}
          {date ? ` · from ${date}` : ""}
        </p>

        <div className="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {results.map((d) => (
            <article key={d.name} className="group overflow-hidden rounded-3xl bg-card">
              <div className="relative overflow-hidden">
                <img
                  src={d.img}
                  alt={`${d.name}, ${d.role}`}
                  width={800}
                  height={1000}
                  loading="lazy"
                  className="h-[320px] w-full object-cover transition-transform duration-700 group-hover:scale-[1.04]"
                />
                <span className="absolute bottom-4 left-4 rounded-full bg-card/95 px-3.5 py-1.5 text-[12px] font-medium text-ink backdrop-blur-sm">
                  Next: {d.next}
                </span>
              </div>

              <div className="p-5">
                <div className="flex items-start justify-between gap-4">
                  <div className="min-w-0">
                    <h2 className="font-display text-[16px] font-medium text-ink">{d.name}</h2>
                    <p className="mt-0.5 text-[12px] text-muted-foreground">{d.role}</p>
                  </div>
                  <p className="flex shrink-0 items-center gap-1 text-[13px] text-ink">
                    <Star className="h-3.5 w-3.5 fill-teal text-teal" />
                    {d.rating}
                  </p>
                </div>

                <dl className="mt-4 space-y-1.5 text-[12px] text-ink-soft">
                  <div className="flex items-center gap-1.5">
                    <MapPin className="h-3.5 w-3.5 text-teal-deep" />
                    <dd>
                      {d.clinic} · {d.city}
                    </dd>
                  </div>
                  <div className="flex items-center justify-between">
                    <dd>{d.mode}</dd>
                    <dd className="text-ink">{d.fee}</dd>
                  </div>
                </dl>

                <div className="mt-5 flex items-center gap-2">
                  <button
                    type="button"
                    className="flex-1 rounded-full bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5"
                  >
                    Book appointment
                  </button>
                  <span className="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-secondary text-ink">
                    <ArrowUpRight className="h-4 w-4" />
                  </span>
                </div>
              </div>
            </article>
          ))}
        </div>

        {results.length === 0 && (
          <p className="mt-10 rounded-3xl bg-card p-8 text-[14px] text-ink-soft">
            No clinicians match those filters yet. Try a wider location or clear the specialty.
          </p>
        )}
      </main>

      <SiteFooter />
    </div>
  );
}