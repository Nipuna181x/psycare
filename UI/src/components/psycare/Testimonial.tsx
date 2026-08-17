import doc3 from "@/assets/doc-3.jpg";

export function Testimonial() {
  return (
    <section id="reviews" className="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
      <div className="rounded-3xl bg-secondary px-6 py-16 text-center md:px-16 md:py-24">
        <blockquote className="mx-auto max-w-[52ch] font-display text-[clamp(1.15rem,2.1vw,1.6rem)] leading-snug font-medium tracking-tight text-ink uppercase">
          “From the first call, I did not have to repeat my story. The assistant listened, my
          doctor had already read it, and the session felt like continuing a conversation rather
          than starting one.”
        </blockquote>
        <div className="mt-10 flex items-center justify-center gap-3">
          <img
            src={doc3}
            alt="Nimasha Gunawardena"
            width={800}
            height={1000}
            loading="lazy"
            className="h-10 w-10 rounded-full object-cover"
          />
          <div className="text-left">
            <p className="text-[13px] font-medium text-ink">Nimasha Gunawardena</p>
            <p className="text-[12px] text-muted-foreground">Patient · Negombo</p>
          </div>
        </div>
        <div className="mt-9 flex items-center justify-center gap-2">
          {[0, 1, 2].map((i) => (
            <span
              key={i}
              className={i === 0 ? "h-1.5 w-6 rounded-full bg-ink" : "h-1.5 w-1.5 rounded-full bg-ink/25"}
            />
          ))}
        </div>
      </div>
    </section>
  );
}
