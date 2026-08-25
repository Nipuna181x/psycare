@php
    $findingSections = [
        'presenting_concerns' => 'Presenting Concerns',
        'symptoms' => 'Reported Symptoms',
        'stressors' => 'Stressors',
        'protective_factors' => 'Protective Factors',
        'functional_impact' => 'Functional Impact',
    ];
    $leftKeys = ['presenting_concerns', 'symptoms', 'stressors'];
    $rightKeys = ['protective_factors', 'functional_impact'];
@endphp

@if (($nlpReport['risk']['requires_immediate_review'] ?? false) === true)
    <div class="alert-block">
        <p class="alert-title">Immediate Clinical Review Required</p>
        <p style="font-size: 9.5pt; margin: 0;">{{ $nlpReport['risk']['recommended_action'] ?? '' }}</p>
        @if (! empty($nlpReport['risk']['evidence']))
            <ul>
                @foreach ($nlpReport['risk']['evidence'] as $evidence)
                    <li>{{ $evidence }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@elseif (! empty($nlpReport['risk']['evidence']))
    <div class="section">
        <p class="section-title">Risk Evidence</p>
        <ul class="plain-list">
            @foreach ($nlpReport['risk']['evidence'] as $evidence)
                <li>{{ $evidence }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="section">
    <p class="section-title">Clinical Summary</p>
    <p>{{ $nlpReport['summary'] ?? 'No summary available.' }}</p>
</div>

@if (($nlpReport['screening']['available'] ?? false) === true)
    <div class="section">
        <p class="section-title">PHQ-9 / GAD-7 Screening</p>
        <table class="screening-table">
            <tr>
                <th>Instrument</th>
                <th>Score</th>
                <th>Severity</th>
            </tr>
            <tr>
                <td>PHQ-9 (Depression)</td>
                <td>{{ $nlpReport['screening']['phq9_total'] ?? '—' }} / 27</td>
                <td>{{ str($nlpReport['screening']['phq9_severity'] ?? 'Not scored')->replace('_', ' ')->title() }}</td>
            </tr>
            <tr>
                <td>GAD-7 (Anxiety)</td>
                <td>{{ $nlpReport['screening']['gad7_total'] ?? '—' }} / 21</td>
                <td>{{ str($nlpReport['screening']['gad7_severity'] ?? 'Not scored')->title() }}</td>
            </tr>
            <tr>
                <td>Self-harm screening item</td>
                <td colspan="2">{{ ($nlpReport['screening']['self_harm_flag'] ?? false) ? 'Positive — endorsed' : 'Negative — not endorsed' }}</td>
            </tr>
        </table>
    </div>
@endif

<div class="section">
    <p class="section-title">Clinical Findings</p>
    <table class="findings-grid">
        <tr>
            <td class="left">
                @foreach ($leftKeys as $key)
                    <p style="font-weight: bold; font-size: 9.5pt; color: #14343a; margin: 0 0 4pt;">{{ $findingSections[$key] }}</p>
                    @forelse ($nlpReport[$key] ?? [] as $item)
                        <div class="finding">
                            <p class="finding-label">{{ $item['label'] }}</p>
                            <p class="finding-evidence confidence-{{ $item['confidence'] ?? 'low' }}">{{ $item['evidence'] }} — {{ ucfirst($item['confidence'] ?? 'low') }} confidence</p>
                        </div>
                    @empty
                        <p class="empty-note" style="margin-bottom: 8pt;">Not established in this conversation.</p>
                    @endforelse
                @endforeach
            </td>
            <td class="right">
                @foreach ($rightKeys as $key)
                    <p style="font-weight: bold; font-size: 9.5pt; color: #14343a; margin: 0 0 4pt;">{{ $findingSections[$key] }}</p>
                    @forelse ($nlpReport[$key] ?? [] as $item)
                        <div class="finding">
                            <p class="finding-label">{{ $item['label'] }}</p>
                            <p class="finding-evidence confidence-{{ $item['confidence'] ?? 'low' }}">{{ $item['evidence'] }} — {{ ucfirst($item['confidence'] ?? 'low') }} confidence</p>
                        </div>
                    @empty
                        <p class="empty-note" style="margin-bottom: 8pt;">Not established in this conversation.</p>
                    @endforelse
                @endforeach

                <p style="font-weight: bold; font-size: 9.5pt; color: #14343a; margin: 0 0 4pt;">Clinician Follow-up</p>
                <ul class="plain-list">
                    @forelse ($nlpReport['clinician_follow_up_questions'] ?? [] as $question)
                        <li>{{ $question }}</li>
                    @empty
                        <li class="empty-note">No questions generated.</li>
                    @endforelse
                </ul>
            </td>
        </tr>
    </table>
</div>

@if (! empty($nlpReport['inconsistencies']))
    <div class="section">
        <p class="section-title">Inconsistencies</p>
        <ul class="plain-list">
            @foreach ($nlpReport['inconsistencies'] as $inconsistency)
                <li>{{ $inconsistency }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (! empty($nlpReport['limitations']))
    <div class="section">
        <p class="section-title">Limitations</p>
        <ul class="plain-list">
            @foreach ($nlpReport['limitations'] as $limitation)
                <li>{{ $limitation }}</li>
            @endforeach
        </ul>
    </div>
@endif
