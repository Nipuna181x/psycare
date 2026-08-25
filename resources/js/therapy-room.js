import './bootstrap';

const root = document.getElementById('therapy-room');

if (root) {
    const echo = window.Echo;

    const roomId = root.dataset.roomId;
    const role = root.dataset.role;
    const myId = root.dataset.myId;
    const myLabel = root.dataset.myLabel;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const videoGrid = document.getElementById('video-grid');
    const tileTemplate = document.getElementById('tile-template');
    const callStatus = document.getElementById('call-status');
    const endRoomButton = document.getElementById('end-room-button');
    const toggleMicButton = document.getElementById('toggle-mic-button');
    const toggleCameraButton = document.getElementById('toggle-camera-button');
    const leaveCallButton = document.getElementById('leave-call-button');

    /**
     * Full-mesh WebRTC: every participant opens a direct RTCPeerConnection to every other
     * participant. This is fine for small groups (the room is capped at 8), but bandwidth
     * and CPU per client scale O(n) with the room size — it degrades badly past ~6-8
     * participants. Moving to larger group sizes would require an SFU (e.g. mediasoup,
     * LiveKit, Janus) instead, where each client uploads once and the server fans out.
     */
    const iceServers = root.dataset.stunServers
        .split(',')
        .filter(Boolean)
        .map((urls) => ({ urls }));

    if (root.dataset.turnUrl) {
        iceServers.push({
            urls: root.dataset.turnUrl,
            username: root.dataset.turnUsername || undefined,
            credential: root.dataset.turnCredential || undefined,
        });
    }

    const peers = new Map();
    let localStream = null;
    let micEnabled = true;
    let cameraEnabled = true;

    const setStatus = (message) => {
        callStatus.textContent = message;
    };

    const createTile = (id, label, isSelf) => {
        const node = tileTemplate.content.firstElementChild.cloneNode(true);
        node.dataset.tileId = id;
        node.querySelector('[data-tile-label]').textContent = isSelf ? `${label} (You)` : label;

        const controls = node.querySelector('[data-tile-doctor-controls]');
        if (role === 'doctor' && !isSelf) {
            controls.classList.remove('hidden');
            controls.classList.add('flex');
            node.querySelector('[data-tile-mute]').addEventListener('click', () => sendSignal(id, 'mute-request', {}));
            node.querySelector('[data-tile-remove]').addEventListener('click', () => kickParticipant(id));
        } else {
            controls.remove();
        }

        videoGrid.appendChild(node);
        return node;
    };

    const removeTile = (id) => {
        videoGrid.querySelector(`[data-tile-id="${CSS.escape(id)}"]`)?.remove();
    };

    const attachStreamToTile = (id, stream) => {
        const video = videoGrid.querySelector(`[data-tile-id="${CSS.escape(id)}"] video`);
        if (video) {
            video.srcObject = stream;
        }
    };

    const sendSignal = async (to, type, payload) => {
        await fetch(root.dataset.signalEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                Accept: 'application/json',
            },
            body: JSON.stringify({ to, type, payload }),
        });
    };

    const kickParticipant = async (targetId) => {
        if (!root.dataset.kickEndpointTemplate) return;
        const patientId = targetId.replace('patient-', '');
        const endpoint = root.dataset.kickEndpointTemplate.replace('PARTICIPANT_ID', patientId);
        await fetch(endpoint, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        });
    };

    const createPeerConnection = (peerId, label) => {
        const pc = new RTCPeerConnection({ iceServers });

        localStream?.getTracks().forEach((track) => pc.addTrack(track, localStream));

        pc.onicecandidate = (event) => {
            if (event.candidate) {
                sendSignal(peerId, 'ice-candidate', event.candidate.toJSON());
            }
        };

        pc.ontrack = (event) => {
            attachStreamToTile(peerId, event.streams[0]);
        };

        pc.onconnectionstatechange = () => {
            if (['failed', 'closed', 'disconnected'].includes(pc.connectionState)) {
                teardownPeer(peerId);
            }
        };

        createTile(peerId, label, false);
        peers.set(peerId, pc);

        return pc;
    };

    const teardownPeer = (peerId) => {
        peers.get(peerId)?.close();
        peers.delete(peerId);
        removeTile(peerId);
    };

    const teardownAllPeers = () => {
        peers.forEach((pc) => pc.close());
        peers.clear();
        videoGrid.querySelectorAll('[data-tile]').forEach((el) => {
            if (el.dataset.tileId !== myId) el.remove();
        });
    };

    const makeOffer = async (peerId, label) => {
        const pc = createPeerConnection(peerId, label);
        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        sendSignal(peerId, 'offer', { sdp: offer.sdp, type: offer.type });
    };

    const handleOffer = async (peerId, label, payload) => {
        const pc = createPeerConnection(peerId, label);
        await pc.setRemoteDescription(new RTCSessionDescription(payload));
        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);
        sendSignal(peerId, 'answer', { sdp: answer.sdp, type: answer.type });
    };

    const handleAnswer = async (peerId, payload) => {
        await peers.get(peerId)?.setRemoteDescription(new RTCSessionDescription(payload));
    };

    const handleIceCandidate = async (peerId, payload) => {
        await peers.get(peerId)?.addIceCandidate(new RTCIceCandidate(payload));
    };

    const endCall = (redirect) => {
        localStream?.getTracks().forEach((track) => track.stop());
        teardownAllPeers();
        echo.leave(`therapy-room.${roomId}`);
        window.location.href = redirect || root.dataset.redirect;
    };

    (async () => {
        try {
            localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            createTile(myId, myLabel, true);
            attachStreamToTile(myId, localStream);
        } catch {
            setStatus('Camera/microphone access is required to join this session.');
            return;
        }

        setStatus('Connecting…');

        const channel = echo.join(`therapy-room.${roomId}`)
            .here((members) => {
                setStatus('Connected');
                // Deterministic tie-break: the lexicographically earlier id sends the offer, avoiding glare.
                members.forEach((member) => {
                    if (member.id === myId) return;
                    if (myId < member.id) {
                        makeOffer(member.id, member.label);
                    } else {
                        createTile(member.id, member.label, false);
                    }
                });
            })
            .joining((member) => {
                if (member.id === myId) return;
                if (myId < member.id) {
                    makeOffer(member.id, member.label);
                } else {
                    createTile(member.id, member.label, false);
                }
            })
            .leaving((member) => {
                teardownPeer(member.id);
            })
            .listen('.signal', (event) => {
                if (event.to !== myId) return;

                if (event.type === 'offer') {
                    handleOffer(event.from, event.from, event.payload);
                } else if (event.type === 'answer') {
                    handleAnswer(event.from, event.payload);
                } else if (event.type === 'ice-candidate') {
                    handleIceCandidate(event.from, event.payload);
                } else if (event.type === 'mute-request' && role !== 'doctor') {
                    micEnabled = false;
                    localStream?.getAudioTracks().forEach((track) => (track.enabled = false));
                }
            })
            .listen('.room.ended', () => {
                endCall(root.dataset.redirect);
            })
            .listen('.participant.kicked', (event) => {
                if (event.targetId === myId) {
                    endCall(root.dataset.redirect);
                } else {
                    teardownPeer(event.targetId);
                }
            });

        void channel;
    })();

    toggleMicButton?.addEventListener('click', () => {
        micEnabled = !micEnabled;
        localStream?.getAudioTracks().forEach((track) => (track.enabled = micEnabled));
        toggleMicButton.classList.toggle('bg-white/15', !micEnabled);
    });

    toggleCameraButton?.addEventListener('click', () => {
        cameraEnabled = !cameraEnabled;
        localStream?.getVideoTracks().forEach((track) => (track.enabled = cameraEnabled));
        toggleCameraButton.classList.toggle('bg-white/15', !cameraEnabled);
    });

    leaveCallButton?.addEventListener('click', () => endCall());

    endRoomButton?.addEventListener('click', async () => {
        if (!confirm('End this session for everyone?')) return;
        await fetch(root.dataset.endEndpoint, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        });
    });
}
