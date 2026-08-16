import { createFileRoute } from "@tanstack/react-router";

import { About } from "@/components/psycare/About";
import { Doctors } from "@/components/psycare/Doctors";
import { Hero } from "@/components/psycare/Hero";
import { Services } from "@/components/psycare/Services";
import { SiteFooter } from "@/components/psycare/SiteFooter";
import { Stats } from "@/components/psycare/Stats";
import { Testimonial } from "@/components/psycare/Testimonial";

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      { title: "PsyCare — Book Any Mental Health Doctor in Sri Lanka" },
      {
        name: "description",
        content:
          "PsyCare is Sri Lanka's single booking platform for registered psychiatrists, psychologists and counsellors, with AI voice intake, pre-booking assessments and group therapy.",
      },
      { property: "og:title", content: "PsyCare — Book Any Mental Health Doctor in Sri Lanka" },
      {
        property: "og:description",
        content:
          "Every registered clinician in one place: AI voice intake, pre-booking assessments and moderated group therapy sessions.",
      },
    ],
  }),
  component: Index,
});

function Index() {
  return (
    <div className="min-h-screen bg-background text-ink selection:bg-teal/20">
      <Hero />
      <main>
        <Stats />
        <Services />
        <About />
        <Doctors />
        <Testimonial />
      </main>
      <SiteFooter />
    </div>
  );
}
