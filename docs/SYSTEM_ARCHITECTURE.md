graph LR
    CLIENT[Client Side - Web Browser]

    subgraph SERVER[PsyCare Server]
        direction TB
        VIEW[Presentation Layer - Blade Templates and Tailwind CSS]
        LOGIC[Business Logic Layer - Laravel Controllers and Services]
        DATA[Data Layer - Eloquent Models and File Storage]

        VIEW --> LOGIC
        LOGIC --> DATA
    end

    AI[AI Component - Gemini, Google TTS and PsyCare NLP]
    DB[(Database - MySQL)]

    CLIENT -->|HTTP Request| SERVER
    SERVER -->|HTML Response| CLIENT
    SERVER -->|API Query| AI
    AI -->|AI Response| SERVER
    SERVER -->|Database Query| DB
    DB -->|Data Response| SERVER

    style CLIENT fill:#dbeafe,stroke:#0369a1,color:#0f172a,stroke-width:2px
    style VIEW fill:#e0f2fe,stroke:#0284c7,color:#0c4a6e,stroke-width:2px
    style LOGIC fill:#e0f2fe,stroke:#0284c7,color:#0c4a6e,stroke-width:2px
    style DATA fill:#e0f2fe,stroke:#0284c7,color:#0c4a6e,stroke-width:2px
    style AI fill:#dbeafe,stroke:#0369a1,color:#0f172a,stroke-width:2px
    style DB fill:#dbeafe,stroke:#0369a1,color:#0f172a,stroke-width:2px
    style SERVER fill:#f0f9ff,stroke:#38bdf8,color:#0f172a,stroke-width:2px
