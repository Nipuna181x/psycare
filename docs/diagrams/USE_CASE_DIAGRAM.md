# PsyCare — Use Case Diagram

Simplified to the core use cases per actor, derived from [`routes/web.php`](../../routes/web.php)
and the guard-scoped controllers in [`app/Http/Controllers`](../../app/Http/Controllers). Same
PsyCare green theme as the architecture diagram (`#0F7A4C` borders / `#E7F5EC` fills).

---

## 1. Mermaid

Mermaid has no native UML use-case shape, so it's modelled as a `flowchart`: one system-boundary
box holding every use case as an oval, with stick-figure actors outside it connecting straight to
their ovals — no per-actor boxes.

```mermaid
flowchart LR
    classDef actor fill:#E7F5EC,stroke:#0F7A4C,stroke-width:1.5px,color:#0B4A2F,font-weight:bold
    classDef usecase fill:#2E9E5B,stroke:#0F7A4C,stroke-width:1.5px,color:#FFFFFF
    classDef system fill:#FFFFFF,stroke:#0F7A4C,stroke-width:2px,color:#0B4A2F

    Patient(["🧍 Patient"]):::actor
    Doctor(["🧍 Doctor"]):::actor
    MedicalCenter(["🧍 Medical Center"]):::actor
    Admin(["🧍 System Admin"]):::actor
    AI(["🧍 AI Services"]):::actor

    subgraph System["PsyCare System"]
        direction TB
        UC1(("Register / Login"))
        UC2(("Book Appointment & Pay"))
        UC3(("Use AI Companion"))
        UC4(("Join Therapy Session"))
        UC5(("Track Mood & Health Records"))
        UC6(("Manage Appointments"))
        UC7(("Issue Prescription"))
        UC8(("Manage Patients & Reports"))
        UC9(("Manage Clinic & Doctors"))
        UC10(("Approve Doctors & Centers"))
        UC11(("View System Reports"))
    end

    Patient --- UC1
    Patient --- UC2
    Patient --- UC3
    Patient --- UC4
    Patient --- UC5

    Doctor --- UC1
    Doctor --- UC4
    Doctor --- UC6
    Doctor --- UC7
    Doctor --- UC8

    MedicalCenter --- UC1
    MedicalCenter --- UC9

    Admin --- UC1
    Admin --- UC10
    Admin --- UC11

    UC3 --- AI
    UC8 --- AI

    class System system
```

---

## 2. PlantUML

Native UML use-case notation, styled with the same PsyCare green theme.

```plantuml
@startuml PsyCare_UseCase
skinparam backgroundColor #FFFFFF
skinparam shadowing false
skinparam defaultFontName Helvetica
skinparam defaultFontSize 13

skinparam usecase {
  BorderColor #0F7A4C
  BackgroundColor #E7F5EC
  FontColor #0B4A2F
}
skinparam actor {
  BorderColor #0F7A4C
  BackgroundColor #E7F5EC
  FontColor #0B4A2F
}
skinparam rectangle {
  BorderColor #0F7A4C
  FontColor #0B4A2F
}
skinparam ArrowColor #0F7A4C

left to right direction

actor Patient
actor Doctor
actor "Medical Center" as MedicalCenter
actor "System Admin" as Admin
actor "AI Services" as AI

rectangle "PsyCare System" {
  usecase "Register / Login" as UC1
  usecase "Book Appointment & Pay" as UC2
  usecase "Use AI Companion" as UC3
  usecase "Join Therapy Session" as UC4
  usecase "Track Mood & Health Records" as UC5
  usecase "Manage Appointments" as UC6
  usecase "Issue Prescription" as UC7
  usecase "Manage Patients & Reports" as UC8
  usecase "Manage Clinic & Doctors" as UC9
  usecase "Approve Doctors & Centers" as UC10
  usecase "View System Reports" as UC11
}

Patient --> UC1
Patient --> UC2
Patient --> UC3
Patient --> UC4
Patient --> UC5

Doctor --> UC1
Doctor --> UC4
Doctor --> UC6
Doctor --> UC7
Doctor --> UC8

MedicalCenter --> UC1
MedicalCenter --> UC9

Admin --> UC1
Admin --> UC10
Admin --> UC11

UC3 --> AI
UC8 --> AI

@enduml
```

---

## Notes for the report

- **Patient**, **Doctor**, **Medical Center**, and **System Admin** map to the four auth guards in
  [`config/auth.php`](../../config/auth.php) (`web`, `doctor`, `medical_center`, `admin`).
- **AI Services** is one combined supporting actor standing in for both the Python NLP
  microservice and Gemini/Google TTS (see the [architecture diagram](ARCHITECTURE_DIAGRAM.md) for
  the detailed split) — used by "Use AI Companion" (patient chat) and "Manage Patients & Reports"
  (doctor NLP report generation).
- Use cases are deliberately broad groupings (e.g. "Manage Appointments" covers view/accept/
  reschedule) to keep the diagram readable. Let me know if you'd like any group split out further.
