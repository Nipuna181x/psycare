# PsyCare — System Architecture Diagram

Three-tier architecture (Presentation / Business Logic / Data), following the same layout style
as the reference diagram, adapted to PsyCare's actual stack:

- **Presentation Layer** — Blade templates (Laravel's default templating) + Bootstrap 5
- **Business Logic Layer** — Laravel (PHP) application server, plus Laravel Reverb for real-time
  WebSocket signaling (therapy room video/chat)
- **Data Layer** — MySQL database
- **AI Component (NLP Microservice)** — a separate in-house Python NLP microservice
  (`PSYCARE_NLP_API_URL`), called over HTTP for patient conversation/report risk classification
- **AI Component (Gemini / Google Cloud)** — external third-party AI services: Gemini (text
  generation) and Google Cloud Text-to-Speech, called directly from the Business Logic Layer for
  the AI Companion's conversational + voice features

Colour theme: PsyCare green (`#0F7A4C` borders / `#E7F5EC` fills), matching the brand palette used
across the app UI.

---

## 1. PlantUML

Render with the PlantUML extension/CLI, or paste into https://www.plantuml.com/plantuml (only if
you're comfortable sharing this diagram externally — otherwise render locally).

```plantuml
@startuml PsyCare_Architecture
skinparam backgroundColor #FFFFFF
skinparam shadowing false
skinparam roundcorner 8
skinparam defaultFontName Helvetica
skinparam defaultFontSize 13

skinparam rectangle {
  BorderColor #0F7A4C
  FontColor #0B4A2F
  BackgroundColor #E7F5EC
  BorderThickness 1.5
}
skinparam database {
  BorderColor #0F7A4C
  FontColor #0B4A2F
  BackgroundColor #E7F5EC
}
skinparam ArrowColor #0F7A4C
skinparam ArrowFontColor #0B4A2F
skinparam titleFontColor #0B4A2F

title PsyCare — Architectural Diagram

rectangle "Client Side\n\nWeb Browser" as Client #E7F5EC

rectangle "Server" as Server {
  rectangle "Presentation Layer\n\n(Blade Templates, Bootstrap 5)" as Presentation
  rectangle "Business Logic Layer\n\n(Laravel PHP, Reverb WebSockets)" as Logic
  rectangle "Data Layer\n\n(MySQL Database - Eloquent ORM)" as Data
}

rectangle "AI Component\n\n(Python NLP Microservice)" as AI #E7F5EC
rectangle "AI Component\n\n(Gemini / Google Cloud TTS)" as GenAI #E7F5EC
database "Database\n\n(MySQL)" as DB #E7F5EC

Client -down-> Presentation : HTTP Requests
Presentation -up-> Client : Response

Presentation -down-> Logic
Logic -up-> Presentation

Logic -down-> Data
Data -up-> Logic

Logic -right-> AI : Query (HTTP/JSON)
AI -left-> Logic : Response (risk / classification)

Logic -right-> GenAI : Prompt / Text (HTTPS API)
GenAI -left-> Logic : Response (text / speech audio)

Data -right-> DB : Query
DB -left-> Data : Response

@enduml
```

---

## 2. Mermaid

Renders natively on GitHub and in most Markdown viewers.

```mermaid
flowchart LR
    classDef server fill:#E7F5EC,stroke:#0F7A4C,stroke-width:1.5px,color:#0B4A2F
    classDef layer fill:#F4FBF7,stroke:#0F7A4C,stroke-width:1.2px,color:#0B4A2F
    classDef external fill:#E7F5EC,stroke:#0F7A4C,stroke-width:1.5px,color:#0B4A2F

    Client["Client Side<br/><br/>Web Browser"]:::server

    subgraph Server["Server"]
        direction TB
        Presentation["Presentation Layer<br/><br/>Blade Templates, Bootstrap 5"]:::layer
        Logic["Business Logic Layer<br/><br/>Laravel PHP, Reverb WebSockets"]:::layer
        Data["Data Layer<br/><br/>MySQL Database - Eloquent ORM"]:::layer

        Presentation <--> Logic
        Logic <--> Data
    end

    AI["AI Component<br/><br/>Python NLP Microservice"]:::external
    GenAI["AI Component<br/><br/>Gemini / Google Cloud TTS"]:::external
    DB[("Database<br/>(MySQL)")]:::external

    Client -- "HTTP requests / Response" --> Presentation
    Logic -- "Query / Response" --> AI
    Logic -- "Prompt-Text / Response" --> GenAI
    Data -- "Query / Response" --> DB

    class Server server
```

---

## Notes for the report

- The **AI Component (Python NLP Microservice)** box represents the standalone, in-house Python
  NLP service (`PSYCARE_NLP_API_URL`, called from
  [`app/Services/PatientNlpClassifier.php`](../../app/Services/PatientNlpClassifier.php)) used for
  patient conversation risk classification and NLP report generation.
- The **AI Component (Gemini / Google Cloud TTS)** box represents the external third-party AI
  providers used by the AI Companion for conversational text generation and speech synthesis (see
  [`app/Services/AiCompanion.php`](../../app/Services/AiCompanion.php),
  [`GeminiTextToSpeech.php`](../../app/Services/GeminiTextToSpeech.php),
  [`GoogleTextToSpeech.php`](../../app/Services/GoogleTextToSpeech.php)). These are shown as a
  separate box from the NLP microservice because they're a different system boundary — external
  vendor APIs rather than an in-house service.
- Reverb is included in the Business Logic Layer since it's the real-time signaling channel for
  therapy room video/chat (see [`routes/channels.php`](../../routes/channels.php)); call it out as
  its own box if your report needs that level of detail.
