const stats = [
  { value: "1,480", label: "Registered clinicians" },
  { value: "62K", label: "Appointments booked" },
  { value: "4.9", label: "Patient rating" },
  { value: "9 min", label: "Average intake time" },
];

export function Stats() {
  return (
    <section className="mx-auto max-w-[1320px] px-5 py-14 md:px-9 md:py-20">
      <div className="grid gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map((s) => (
          <div key={s.label} className="border-l border-border pl-6 first:border-l-0 first:pl-0">
            <p className="font-display text-[clamp(2rem,3.4vw,2.8rem)] font-medium text-ink">
              {s.value}
            </p>
            <p className="mt-1 text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
              {s.label}
            </p>
          </div>
        ))}
      </div>
    </section>
  );
}
