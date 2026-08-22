const root = document.getElementById('voice-companion');

if (root) {
    const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const microphoneButton = document.getElementById('microphone-button');
    const endCallButton = document.getElementById('end-call-button');
    const status = document.getElementById('companion-status');
    const orb = document.getElementById('voice-orb');
    const halo = document.getElementById('orb-halo');
    const audio = document.getElementById('companion-audio');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let language = 'en';
    let sessionId = null;
    let recognition = null;
    let sessionEnded = false;
    let sessionStarted = false;
    let busy = false;

    const text = {
        en: { ready: 'Tap the microphone to meet Asha', starting: 'Asha is joining…', listening: 'Asha is listening…', thinking: 'Asha is thinking…', speaking: 'Asha is speaking…', missed: 'I didn’t catch that. Tap the microphone and try again.', unavailable: 'Voice recognition is unavailable in this browser.', error: 'Asha is having trouble connecting. Please try again.' },
        si: { ready: 'ආශා සමඟ කතා කිරීමට මයික්‍රෆෝනය ඔබන්න', starting: 'ආශා සම්බන්ධ වෙමින්…', listening: 'ආශා සවන් දෙමින්…', thinking: 'ආශා සිතමින්…', speaking: 'ආශා කතා කරමින්…', missed: 'එය පැහැදිලිව ඇසුණේ නැහැ. නැවත උත්සාහ කරන්න.', unavailable: 'මෙම බ්‍රවුසරයේ හඬ හඳුනාගැනීම ලබා ගත නොහැක.', error: 'ආශාට සම්බන්ධ වීමේ ගැටලුවක් තිබේ. නැවත උත්සාහ කරන්න.' },
    };

    const setState = (state, message) => {
        root.dataset.state = state;
        status.textContent = message;
        orb.classList.toggle('is-listening', state === 'listening');
        orb.classList.toggle('is-thinking', state === 'thinking');
        orb.classList.toggle('is-speaking', state === 'speaking');
        halo.classList.toggle('scale-125', state === 'speaking' || state === 'listening');
        microphoneButton.setAttribute('aria-label', state === 'listening' ? 'Stop listening' : 'Start listening');
    };

    const stopRecognition = () => {
        if (!recognition) return;
        recognition.onend = null;
        recognition.abort();
        recognition = null;
    };

    const listen = () => {
        if (!Recognition || busy || sessionEnded) return;
        stopRecognition();
        audio.pause();
        let transcript = '';
        let failed = false;
        recognition = new Recognition();
        recognition.lang = language === 'si' ? 'si-LK' : 'en-US';
        recognition.interimResults = true;
        recognition.continuous = false;
        recognition.onresult = (event) => {
            transcript = Array.from(event.results).map((result) => result[0].transcript).join(' ').trim();
        };
        recognition.onerror = () => {
            failed = true;
            setState('ready', text[language].missed);
        };
        recognition.onend = () => {
            recognition = null;
            if (!failed && transcript) sendMessage(transcript);
            if (!failed && !transcript) setState('ready', text[language].missed);
        };
        setState('listening', text[language].listening);
        recognition.start();
    };

    const sendMessage = async (message) => {
        busy = true;
        microphoneButton.disabled = true;
        setState('thinking', text[language].thinking);
        try {
            const response = await fetch(root.dataset.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ message, language, session_id: sessionId }),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message);
            audio.src = `data:${result.audio_type};base64,${result.audio}`;
            setState('speaking', text[language].speaking);
            await audio.play();
        } catch {
            busy = false;
            microphoneButton.disabled = false;
            setState('ready', text[language].error);
        }
    };

    const startSession = async () => {
        busy = true;
        microphoneButton.disabled = true;
        setState('thinking', text[language].starting);
        try {
            const response = await fetch(root.dataset.startEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ language, consent: true }),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message);
            sessionId = result.session_id;
            sessionStarted = true;
            audio.src = `data:${result.audio_type};base64,${result.audio}`;
            setState('speaking', text[language].speaking);
            await audio.play();
        } catch {
            busy = false;
            microphoneButton.disabled = false;
            setState('ready', text[language].error);
        }
    };

    audio.addEventListener('ended', () => {
        busy = false;
        microphoneButton.disabled = false;
        if (!sessionEnded) listen();
    });
    microphoneButton.addEventListener('click', () => {
        if (recognition) {
            recognition.stop();
            return;
        }
        if (!sessionStarted) {
            startSession();
            return;
        }
        listen();
    });
    endCallButton.addEventListener('click', async () => {
        sessionEnded = true;
        stopRecognition();
        audio.pause();
        if (sessionId) {
            endCallButton.disabled = true;
            setState('thinking', language === 'si' ? 'ඔබේ වාර්තාව සුරකිමින්…' : 'Saving your clinical summary…');
            try {
                await fetch(root.dataset.finishEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ session_id: sessionId }),
                });
            } catch {
                // The conversation turns are already securely saved after each exchange.
            }
        }
        window.location.assign(root.dataset.home);
    });
    document.querySelectorAll('.companion-language').forEach((button) => {
        button.addEventListener('click', () => {
            if (busy || recognition || sessionStarted) return;
            language = button.dataset.language;
            document.documentElement.lang = language;
            document.querySelectorAll('.companion-language').forEach((candidate) => {
                const selected = candidate === button;
                candidate.setAttribute('aria-checked', String(selected));
                candidate.classList.toggle('bg-white', selected);
                candidate.classList.toggle('text-black', selected);
                candidate.classList.toggle('text-white/55', !selected);
            });
            setState('ready', text[language].ready);
        });
    });

    if (!Recognition) {
        microphoneButton.disabled = true;
        setState('ready', text[language].unavailable);
    }
}
