<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Voice screening — Book {{ $doctor->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink">
        <x-booking-header :doctor="$doctor" :step="3" />

        <main class="mx-auto max-w-[760px] px-5 pb-20 md:px-9">
            <section class="mt-8 overflow-hidden rounded-3xl bg-card shadow-[0_24px_80px_-45px_rgba(18,65,67,0.45)]">
                <header class="border-b border-border px-6 py-5 md:px-8">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="eyebrow">Step 3 of 4 · Voice screening</p>
                            <h1 class="display-head mt-1 text-[clamp(1.35rem,3vw,1.8rem)]">A private check-in</h1>
                        </div>
                        <span id="progress-label" class="rounded-full bg-secondary px-3 py-1.5 text-[11px] font-medium text-ink-soft">Ready</span>
                    </div>
                    <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-secondary">
                        <div id="progress-bar" class="h-full w-0 rounded-full bg-teal-deep transition-[width] duration-500 motion-reduce:transition-none"></div>
                    </div>
                </header>

                <div id="welcome-screen" class="grid min-h-[540px] place-items-center px-6 py-12 text-center md:px-12">
                    <div class="max-w-[52ch]">
                        <span class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-teal/15 text-teal-deep ring-8 ring-teal/5">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2M12 19v3M8 22h8"/></svg>
                        </span>
                        <h2 class="display-head mt-7 text-[clamp(1.7rem,4vw,2.5rem)]">Let’s take this one question at a time</h2>
                        <p class="mt-4 text-[13px] leading-relaxed text-ink-soft">You’ll hear 16 short questions about the last two weeks. Choose your language, then answer naturally by voice.</p>
                        <div class="mt-6 grid grid-cols-2 gap-3" role="radiogroup" aria-label="Screening language">
                            <button type="button" data-language="en" class="language-button rounded-2xl border border-border bg-card p-4 text-left transition hover:border-teal-deep" role="radio" aria-checked="false">
                                <span class="block text-sm font-semibold">English</span><span class="mt-1 block text-[11px] text-ink-soft">Continue in English</span>
                            </button>
                            <button type="button" data-language="si" class="language-button rounded-2xl border border-border bg-card p-4 text-left transition hover:border-teal-deep" role="radio" aria-checked="false">
                                <span class="block text-sm font-semibold" lang="si">සිංහල</span><span class="mt-1 block text-[11px] text-ink-soft" lang="si">සිංහලෙන් ඉදිරියට යන්න</span>
                            </button>
                        </div>
                        <div class="mt-6 rounded-2xl bg-red-50 p-4 text-left text-[12px] leading-relaxed text-red-800">If you may hurt yourself or are in immediate danger, call <strong>1926</strong>, contact emergency services, or go to the nearest emergency department now.</div>
                        <button id="begin-button" type="button" disabled class="mt-7 inline-flex items-center gap-2 rounded-full bg-ink px-7 py-4 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-deep disabled:cursor-not-allowed disabled:opacity-40">
                            Begin voice check-in
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        <button id="skip-screening-button" type="button" class="mt-4 block w-full text-center text-[11px] font-medium text-ink-soft underline-offset-4 hover:underline">Skip this step for now</button>
                    </div>
                </div>

                <div id="agent-screen" hidden class="min-h-[540px] px-6 py-8 md:px-10 md:py-10">
                    <div class="flex items-center gap-3">
                        <div id="agent-orb" class="relative grid h-14 w-14 shrink-0 place-items-center rounded-full bg-ink text-primary-foreground shadow-lg">
                            <span class="absolute inset-0 rounded-full ring-4 ring-teal/20"></span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2M12 19v3"/></svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-medium">PsyCare voice assistant</p>
                            <p id="agent-status" class="text-[11px] text-ink-soft" aria-live="polite">Playing question…</p>
                        </div>
                        <button id="replay-button" type="button" class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-secondary px-3 py-2 text-[11px] font-medium text-ink">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                            Replay
                        </button>
                    </div>

                    <div class="mt-8 rounded-3xl bg-secondary p-6 md:p-8">
                        <p id="instrument-label" class="text-[10px] font-semibold tracking-[0.14em] text-teal-deep uppercase"></p>
                        <p id="question-text" class="mt-3 font-display text-[clamp(1.25rem,3.4vw,1.75rem)] font-medium leading-snug"></p>
                    </div>

                    <div class="mt-6">
                        <button id="record-button" type="button" class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-teal-deep text-white shadow-[0_12px_35px_-12px_rgba(35,120,125,0.8)] transition-transform hover:scale-105 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-deep">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2M12 19v3M8 22h8"/></svg>
                            <span class="sr-only">Record answer</span>
                        </button>
                        <p id="record-hint" class="mt-3 text-center text-[11px] text-ink-soft">Tap the microphone and answer naturally</p>
                        <textarea id="answer-text" rows="2" class="mt-4 w-full rounded-2xl border border-border bg-card px-4 py-3 text-[13px] outline-none focus:border-teal-deep" placeholder="Your voice transcript appears here. You can also type."></textarea>
                        <button id="interpret-button" type="button" class="mt-2 w-full rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-primary-foreground uppercase">Interpret my answer</button>
                        <p id="interpretation-result" class="mt-2 min-h-4 text-center text-[11px] text-ink-soft" aria-live="polite"></p>
                    </div>

                    <div id="safety-message" hidden class="mt-5 rounded-2xl bg-red-50 p-4 text-[12px] leading-relaxed text-red-800">Thank you for telling us. Your response will be urgently highlighted to your doctor. If you might act on these thoughts now, call <strong>1926</strong> or emergency services immediately.</div>

                    <div class="mt-7 flex items-center gap-3">
                        <button id="previous-button" type="button" class="rounded-2xl bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-ink uppercase disabled:opacity-40">Previous</button>
                        <button id="next-button" type="button" disabled class="ml-auto rounded-2xl bg-ink px-6 py-3 text-[11px] font-semibold tracking-[0.1em] text-primary-foreground uppercase disabled:cursor-not-allowed disabled:opacity-35">Next question</button>
                    </div>
                    <button id="skip-screening-button-agent" type="button" class="mt-4 block w-full text-center text-[11px] font-medium text-ink-soft underline-offset-4 hover:underline">Skip screening for now</button>
                </div>

                <form method="POST" action="{{ route('booking.assessment', $doctor) }}" id="assessment-form" class="hidden">
                    @csrf
                    <input type="hidden" name="skipped" id="skipped-field" value="0">
                    <div id="answer-fields"></div>
                    <textarea name="open_notes" id="open-notes-field"></textarea>
                </form>
            </section>

            <div id="notes-section" hidden class="mt-5 rounded-3xl bg-card p-6 md:p-8">
                <label for="open-notes" class="font-display text-[16px] font-medium">Anything else you want your doctor to know?</label>
                <p class="mt-1 text-[11px] text-ink-soft">Optional. This note is shown directly to your doctor.</p>
                <textarea id="open-notes" rows="4" maxlength="4000" class="mt-4 w-full rounded-2xl bg-secondary px-4 py-3 text-[13px] outline-none" placeholder="Add any context we didn’t cover"></textarea>
                <button id="submit-button" type="button" class="mt-4 w-full rounded-2xl bg-ink px-6 py-4 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase">Save screening & review booking</button>
            </div>
        </main>
    </div>

    <audio id="question-audio" preload="auto"></audio>
    <script>
        (() => {
            const questions = @json($questions);
            const scale = @json($scale);
            const saved = @json(collect(old('answers', $saved['answers'] ?? []))->keyBy('key'));
            const endpoint = @json(route('booking.assessment.interpret', $doctor));
            const clarificationAudio = @json($clarificationAudio);
            let selectedLanguage = @json($language);
            const csrf = @json(csrf_token());
            const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const answers = questions.map((question) => ({
                ...question,
                score: saved[question.key]?.score ?? null,
                answer: saved[question.key]?.answer ?? '',
                confidence: saved[question.key]?.confidence ?? 'manual',
                extracted_context: saved[question.key]?.extracted_context ?? '',
            }));
            let index = Math.min(@json($currentQuestion), questions.length - 1);
            let recognition = null;
            let recognitionQuestionIndex = null;
            let recognitionHadResult = false;
            let recognitionFailed = false;
            const copy = {
                en: { playing: 'Playing question…', replay: 'Tap replay to hear the question', listening: 'I’m listening', captured: 'Answer captured', understanding: 'Understanding your answer…', unclear: 'I need a little clarification', retry: 'Tap the microphone and answer again with how often it happened.', understood: (label) => `Understood as “${label}”. Moving to the next question…` },
                si: { playing: 'ප්‍රශ්නය වාදනය වෙමින්…', replay: 'ප්‍රශ්නය ඇසීමට නැවත වාදනය ඔබන්න', listening: 'මම සවන් දෙමි', captured: 'පිළිතුර ලබා ගත්තා', understanding: 'ඔබේ පිළිතුර තේරුම් ගනිමින්…', unclear: 'තව ටිකක් පැහැදිලි කිරීමක් අවශ්‍යයි', retry: 'මයික්‍රෆෝනය ඔබා මෙය කොපමණ වාරයක් සිදු වූවාදැයි නැවත කියන්න.', understood: () => 'පිළිතුර තේරුම් ගත්තා. ඊළඟ ප්‍රශ්නයට යමින්…' },
            };

            const elements = {
                welcome: document.getElementById('welcome-screen'), agent: document.getElementById('agent-screen'),
                progress: document.getElementById('progress-bar'), progressLabel: document.getElementById('progress-label'),
                instrument: document.getElementById('instrument-label'), question: document.getElementById('question-text'),
                status: document.getElementById('agent-status'), audio: document.getElementById('question-audio'),
                answer: document.getElementById('answer-text'), result: document.getElementById('interpretation-result'),
                previous: document.getElementById('previous-button'),
                next: document.getElementById('next-button'), record: document.getElementById('record-button'),
                hint: document.getElementById('record-hint'), safety: document.getElementById('safety-message'),
                notes: document.getElementById('notes-section'), openNotes: document.getElementById('open-notes'),
            };
            elements.openNotes.value = @json(old('open_notes', $saved['open_notes'] ?? ''));

            const playQuestion = () => {
                elements.audio.src = selectedLanguage === 'si' ? answers[index].audio_url_si : answers[index].audio_url;
                elements.status.textContent = copy[selectedLanguage].playing;
                elements.audio.play().catch(() => { elements.status.textContent = copy[selectedLanguage].replay; });
            };

            const stopRecognition = () => {
                if (!recognition) return;
                recognition.onend = null;
                recognition.abort();
                recognition = null;
                recognitionQuestionIndex = null;
                elements.record.classList.remove('animate-pulse', 'bg-red-600');
            };

            const selectScore = (score, confidence = 'manual') => {
                answers[index].score = Number(score);
                answers[index].confidence = confidence;
                elements.next.disabled = false;
                elements.next.removeAttribute('disabled');
                elements.safety.hidden = !(answers[index].key === 'phq_9' && Number(score) > 0);
            };

            const render = (speak = true) => {
                const current = answers[index];
                const item = current.instrument === 'phq9' ? index + 1 : index - 8;
                elements.instrument.textContent = `${current.instrument.toUpperCase()} · Question ${item}`;
                stopRecognition();
                elements.question.textContent = selectedLanguage === 'si' ? current.question_si : current.question;
                elements.answer.value = current.answer;
                elements.result.textContent = '';
                elements.previous.disabled = index === 0;
                elements.next.disabled = current.score === null;
                elements.next.textContent = index === answers.length - 1 ? 'Finish' : 'Next question';
                elements.safety.hidden = !(current.key === 'phq_9' && current.score > 0);
                elements.progress.style.width = `${((index + 1) / answers.length) * 100}%`;
                elements.progressLabel.textContent = `${index + 1} of ${answers.length}`;
                if (speak) playQuestion();
            };

            document.querySelectorAll('.language-button').forEach((button) => {
                button.addEventListener('click', () => {
                    selectedLanguage = button.dataset.language;
                    document.documentElement.lang = selectedLanguage;
                    document.querySelectorAll('.language-button').forEach((candidate) => {
                        const active = candidate === button;
                        candidate.setAttribute('aria-checked', String(active));
                        candidate.classList.toggle('border-teal-deep', active);
                        candidate.classList.toggle('bg-teal/10', active);
                    });
                    document.getElementById('begin-button').disabled = false;
                });
            });
            if (selectedLanguage) document.querySelector(`[data-language="${selectedLanguage}"]`)?.click();

            document.getElementById('begin-button').addEventListener('click', () => {
                elements.welcome.hidden = true;
                elements.agent.hidden = false;
                render();
            });
            document.getElementById('replay-button').addEventListener('click', playQuestion);
            elements.audio.addEventListener('ended', () => { elements.status.textContent = copy[selectedLanguage].listening; });
            elements.answer.addEventListener('input', () => { answers[index].answer = elements.answer.value; });

            if (!Recognition) {
                elements.record.hidden = true;
                elements.hint.textContent = 'Voice input is unavailable in this browser. Type your answer below.';
            } else {
                elements.record.addEventListener('click', () => {
                    if (recognition) {
                        recognition.stop();
                        return;
                    }
                    elements.audio.pause();
                    recognition = new Recognition();
                    recognitionQuestionIndex = index;
                    recognitionHadResult = false;
                    recognitionFailed = false;
                    recognition.lang = selectedLanguage === 'si' ? 'si-LK' : 'en-US';
                    recognition.interimResults = false;
                    elements.record.classList.add('animate-pulse', 'bg-red-600');
                    elements.status.textContent = 'Listening…';
                    elements.hint.textContent = 'Speak now. Tap again to stop.';
                    recognition.onresult = (event) => {
                        if (index !== recognitionQuestionIndex) return;
                        recognitionHadResult = true;
                        elements.answer.value = event.results[0][0].transcript;
                        answers[index].answer = elements.answer.value;
                    };
                    recognition.onend = () => {
                        const capturedIndex = recognitionQuestionIndex;
                        recognition = null;
                        recognitionQuestionIndex = null;
                        elements.record.classList.remove('animate-pulse', 'bg-red-600');
                        if (recognitionFailed || !recognitionHadResult || index !== capturedIndex) return;
                        elements.status.textContent = copy[selectedLanguage].captured;
                        elements.hint.textContent = copy[selectedLanguage].understanding;
                        document.getElementById('interpret-button').click();
                    };
                    recognition.onerror = () => {
                        recognitionFailed = true;
                        elements.result.textContent = selectedLanguage === 'si' ? 'ඔබේ හඬ ඇසුණේ නැහැ. නැවත උත්සාහ කරන්න හෝ පිළිතුර ටයිප් කරන්න.' : 'Could not hear you. Try again or type your answer.';
                    };
                    recognition.start();
                });
            }

            document.getElementById('interpret-button').addEventListener('click', async (event) => {
                const interpretButton = event.currentTarget;
                const interpretedIndex = index;
                const answer = elements.answer.value.trim();
                if (!answer) { elements.result.textContent = 'Record or type an answer first.'; return; }
                answers[interpretedIndex].answer = answer;
                interpretButton.disabled = true;
                elements.result.textContent = copy[selectedLanguage].understanding;
                try {
                    const response = await fetch(endpoint, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify({key: answers[interpretedIndex].key, answer, language: selectedLanguage})});
                    const data = await response.json();
                    if (!response.ok || data.needs_clarification || data.score === null) {
                        elements.status.textContent = copy[selectedLanguage].unclear;
                        elements.result.textContent = data.reason || data.message || 'Please answer again and describe how often this happened.';
                        elements.audio.src = clarificationAudio[selectedLanguage];
                        elements.audio.play().catch(() => {});
                        elements.hint.textContent = copy[selectedLanguage].retry;
                        return;
                    }
                    if (index !== interpretedIndex) return;
                    answers[interpretedIndex].extracted_context = data.extracted_context;
                    selectScore(data.score, data.confidence);
                    elements.status.textContent = 'Answer understood';
                    elements.result.textContent = copy[selectedLanguage].understood(scale[data.score]);
                    const confirmationDelay = answers[interpretedIndex].key === 'phq_9' && Number(data.score) > 0 ? 3500 : 1200;
                    window.setTimeout(() => {
                        if (index === interpretedIndex && answers[interpretedIndex].score !== null) elements.next.click();
                    }, confirmationDelay);
                } catch {
                    elements.result.textContent = 'Interpretation is temporarily unavailable. Please try again.';
                } finally { interpretButton.disabled = false; }
            });

            elements.previous.addEventListener('click', () => { if (index > 0) { index--; render(); } });
            elements.next.addEventListener('click', () => {
                if (answers[index].score === null) return;
                if (index < answers.length - 1) { index++; render(); return; }
                elements.audio.pause();
                elements.agent.hidden = true;
                elements.notes.hidden = false;
                elements.progress.style.width = '100%';
                elements.progressLabel.textContent = 'Complete';
                elements.openNotes.focus();
            });

            document.getElementById('submit-button').addEventListener('click', () => {
                const fields = document.getElementById('answer-fields');
                fields.innerHTML = '';
                answers.forEach((answer, answerIndex) => {
                    ['key', 'instrument', 'question', 'score', 'answer', 'confidence', 'extracted_context'].forEach((field) => {
                        const input = document.createElement('input');
                        input.type = 'hidden'; input.name = `answers[${answerIndex}][${field}]`; input.value = answer[field] ?? '';
                        fields.append(input);
                    });
                });
                document.getElementById('open-notes-field').value = elements.openNotes.value;
                document.getElementById('assessment-form').submit();
            });

            const skipCopy = {
                en: 'Are you sure you want to skip screening? This helps your doctor understand you better, but you can still book without it.',
                si: 'ඔබට පරීක්ෂණය මඟහැරීමට අවශ්‍ය බව විශ්වාසද? මෙය ඔබේ වෛද්‍යවරයාට ඔබව වඩා හොඳින් තේරුම් ගැනීමට උපකාරී වේ, නමුත් එය නොමැතිවත් වෙන්කරවා ගත හැක.',
            };
            const skipScreening = () => {
                if (!window.confirm(skipCopy[selectedLanguage] || skipCopy.en)) return;
                stopRecognition();
                elements.audio.pause();
                document.getElementById('skipped-field').value = '1';
                document.getElementById('answer-fields').innerHTML = '';
                document.getElementById('open-notes-field').value = elements.openNotes.value;
                document.getElementById('assessment-form').submit();
            };
            document.getElementById('skip-screening-button').addEventListener('click', skipScreening);
            document.getElementById('skip-screening-button-agent').addEventListener('click', skipScreening);
        })();
    </script>
</body>
</html>
