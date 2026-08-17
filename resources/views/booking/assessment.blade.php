<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pre-assessment — Book {{ $doctor->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink">
        <x-booking-header :doctor="$doctor" :step="3" />

        <main class="mx-auto max-w-[840px] px-5 pb-24 md:px-9">
            <div class="mt-8 rounded-3xl bg-card p-6 md:p-8">
                <p class="eyebrow">Step 3 of 4</p>
                <h1 class="display-head mt-2 text-[clamp(1.5rem,3vw,2rem)] text-ink">A quick pre-assessment</h1>
                <p class="mt-2 max-w-[60ch] text-[13px] leading-relaxed text-ink-soft">Our AI voice assistant will ask you a few short questions so {{ $doctor->name }} can prepare before your visit. You can speak your answers or type them — this is an automated check-in, not a diagnosis. If you're in crisis, please call 1926 (free, 24 hours) or go to your nearest emergency department.</p>

                <div class="mt-8 rounded-2xl bg-secondary p-5">
                    <div class="flex items-center justify-between">
                        <label for="mood_rating" class="text-[13px] font-medium text-ink">How would you rate your mood over the past week?</label>
                        <span id="mood-value" class="font-display text-[16px] font-medium text-ink">{{ old('mood_rating', $saved['mood_rating'] ?? 5) }}/10</span>
                    </div>
                    <input type="range" min="1" max="10" id="mood_rating" name="mood_rating" form="assessment-form" value="{{ old('mood_rating', $saved['mood_rating'] ?? 5) }}" class="mt-3 w-full accent-teal-deep">
                    <div class="mt-1 flex justify-between text-[10px] text-ink-soft"><span>Very low</span><span>Great</span></div>
                </div>

                <div class="mt-6 rounded-2xl border border-border p-5 md:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span id="assistant-indicator" class="grid h-9 w-9 place-items-center rounded-full bg-teal/15 text-teal-deep">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v3"/></svg>
                            </span>
                            <div>
                                <p class="text-[12px] font-medium text-ink">PsyCare voice assistant</p>
                                <p id="assistant-status" class="text-[11px] text-ink-soft">Question <span id="question-number">1</span> of {{ count($questions) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            @foreach ($questions as $index => $question)
                                <span data-progress-dot class="h-1.5 w-5 rounded-full {{ $index === 0 ? 'bg-teal-deep' : 'bg-border' }}"></span>
                            @endforeach
                        </div>
                    </div>

                    <p id="question-text" class="mt-5 font-display text-[18px] font-medium text-ink">{{ $questions[0]['question'] }}</p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button type="button" id="speak-button" class="inline-flex items-center gap-1.5 rounded-full bg-secondary px-4 py-2.5 text-[12px] font-medium text-ink transition-colors hover:bg-border">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m11 5-6 4H2v6h3l6 4Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/></svg>
                            Read question aloud
                        </button>
                        <button type="button" id="record-button" class="inline-flex items-center gap-1.5 rounded-full bg-ink px-4 py-2.5 text-[12px] font-medium text-primary-foreground transition-colors hover:bg-ink/90">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v3"/></svg>
                            <span id="record-label">Record my answer</span>
                        </button>
                        <p id="voice-unsupported" hidden class="text-[11px] text-ink-soft">Voice isn't supported in this browser — just type your answer below.</p>
                    </div>

                    <textarea id="answer-textarea" rows="3" placeholder="Your answer will appear here — or type it directly." class="mt-4 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[14px] text-ink outline-none"></textarea>
                    <p id="answer-error" hidden class="mt-1.5 text-[12px] text-red-600">Please add an answer before continuing (or use skip).</p>

                    <div class="mt-5 flex items-center gap-3">
                        <button type="button" id="prev-question" class="rounded-2xl bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5 disabled:pointer-events-none disabled:opacity-0">Previous</button>
                        <button type="button" id="skip-question" class="rounded-2xl px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink-soft uppercase transition-colors hover:text-ink">Skip</button>
                        <button type="button" id="next-question" class="ml-auto flex-1 rounded-2xl bg-ink px-6 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5 sm:flex-none">Next question</button>
                    </div>
                </div>

                <form method="POST" action="{{ route('booking.assessment', $doctor) }}" id="assessment-form" class="mt-6">
                    @csrf
                    <div id="answer-fields"></div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('booking.details', $doctor) }}" class="rounded-2xl bg-secondary px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Back</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        (() => {
            const questions = @json($questions);
            const savedAnswers = @json(collect($saved['answers'] ?? [])->keyBy('key'));
            const optionalKeys = ['notes'];

            let index = 0;
            const answers = questions.map((question) => ({
                key: question.key,
                question: question.question,
                answer: savedAnswers[question.key]?.answer ?? '',
            }));

            const questionText = document.getElementById('question-text');
            const questionNumber = document.getElementById('question-number');
            const textarea = document.getElementById('answer-textarea');
            const answerError = document.getElementById('answer-error');
            const prevButton = document.getElementById('prev-question');
            const nextButton = document.getElementById('next-question');
            const skipButton = document.getElementById('skip-question');
            const speakButton = document.getElementById('speak-button');
            const recordButton = document.getElementById('record-button');
            const recordLabel = document.getElementById('record-label');
            const voiceUnsupported = document.getElementById('voice-unsupported');
            const assistantStatus = document.getElementById('assistant-status');
            const dots = [...document.querySelectorAll('[data-progress-dot]')];
            const moodInput = document.getElementById('mood_rating');
            const moodValue = document.getElementById('mood-value');

            moodInput.addEventListener('input', () => { moodValue.textContent = `${moodInput.value}/10`; });

            const SpeechRecognitionImpl = window.SpeechRecognition || window.webkitSpeechRecognition;
            let recognition = null;
            let recording = false;

            if (SpeechRecognitionImpl) {
                recognition = new SpeechRecognitionImpl();
                recognition.lang = 'en-US';
                recognition.interimResults = false;
                recognition.maxAlternatives = 1;
                recognition.onresult = (event) => {
                    const transcript = [...event.results].map((result) => result[0].transcript).join(' ');
                    textarea.value = (textarea.value ? textarea.value + ' ' : '') + transcript.trim();
                };
                recognition.onend = () => {
                    recording = false;
                    recordLabel.textContent = 'Record my answer';
                    recordButton.classList.remove('bg-red-600');
                    assistantStatus.textContent = `Question ${index + 1} of ${questions.length}`;
                };
                recognition.onerror = () => {
                    recording = false;
                    recordLabel.textContent = 'Record my answer';
                    recordButton.classList.remove('bg-red-600');
                    assistantStatus.textContent = 'Could not hear you — please try again or type your answer.';
                };
            } else {
                recordButton.hidden = true;
                voiceUnsupported.hidden = false;
            }

            if (!('speechSynthesis' in window)) {
                speakButton.hidden = true;
            }

            speakButton.addEventListener('click', () => {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(questions[index].question);
                assistantStatus.textContent = 'Speaking…';
                utterance.onend = () => { assistantStatus.textContent = `Question ${index + 1} of ${questions.length}`; };
                window.speechSynthesis.speak(utterance);
            });

            recordButton.addEventListener('click', () => {
                if (!recognition) return;
                if (recording) {
                    recognition.stop();
                    return;
                }
                recording = true;
                recordLabel.textContent = 'Stop recording';
                recordButton.classList.add('bg-red-600');
                assistantStatus.textContent = 'Listening…';
                recognition.start();
            });

            const render = () => {
                const question = questions[index];
                questionText.textContent = question.question;
                questionNumber.textContent = index + 1;
                textarea.value = answers[index].answer;
                answerError.hidden = true;
                prevButton.disabled = index === 0;
                skipButton.hidden = !optionalKeys.includes(question.key);
                nextButton.textContent = index === questions.length - 1 ? 'Finish & review booking' : 'Next question';
                assistantStatus.textContent = `Question ${index + 1} of ${questions.length}`;
                dots.forEach((dot, dotIndex) => {
                    dot.className = dotIndex <= index ? 'h-1.5 w-5 rounded-full bg-teal-deep' : 'h-1.5 w-5 rounded-full bg-border';
                });
            };

            textarea.addEventListener('input', () => { answers[index].answer = textarea.value; });

            const buildHiddenFields = () => {
                const container = document.getElementById('answer-fields');
                container.innerHTML = '';
                answers.forEach((entry, i) => {
                    ['key', 'question', 'answer'].forEach((field) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `answers[${i}][${field}]`;
                        input.value = entry[field];
                        container.append(input);
                    });
                });
            };

            const goNext = (skip = false) => {
                answers[index].answer = textarea.value.trim();
                const question = questions[index];
                const isOptional = optionalKeys.includes(question.key);

                if (!skip && !isOptional && answers[index].answer === '') {
                    answerError.hidden = false;
                    return;
                }

                window.speechSynthesis?.cancel();

                if (index === questions.length - 1) {
                    buildHiddenFields();
                    document.getElementById('assessment-form').submit();
                    return;
                }

                index += 1;
                render();
            };

            nextButton.addEventListener('click', () => goNext(false));
            skipButton.addEventListener('click', () => goNext(true));
            prevButton.addEventListener('click', () => {
                if (index === 0) return;
                answers[index].answer = textarea.value.trim();
                window.speechSynthesis?.cancel();
                index -= 1;
                render();
            });

            render();
        })();
    </script>
</body>
</html>
