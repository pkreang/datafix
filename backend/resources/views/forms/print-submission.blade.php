<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $submission->reference_no ?: ('#' . $submission->id) }} — {{ $submission->form->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Sarabun", "Noto Sans Thai", Roboto, sans-serif; color: #111827; background: #fff; padding: 24px; max-width: 900px; margin: 0 auto; }
        .print-header { border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 20px; }
        .print-header h1 { font-size: 20px; margin: 0 0 4px; }
        .print-header .meta { font-size: 12px; color: #4b5563; }
        .field-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 20px; margin-bottom: 24px; }
        .field-grid .full { grid-column: span 2; }
        .field label { display: block; font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px; }
        .field .val { font-size: 14px; color: #111827; white-space: pre-wrap; word-break: break-word; }
        .approval-trail { border-top: 1px solid #e5e7eb; padding-top: 16px; }
        .approval-trail h2 { font-size: 14px; margin: 0 0 10px; }
        .step { display: flex; justify-content: space-between; font-size: 12px; padding: 6px 0; border-bottom: 1px dashed #e5e7eb; }
        .step:last-child { border-bottom: none; }
        .step .label { font-weight: 600; }
        .step .action-approved { color: #059669; }
        .step .action-rejected { color: #dc2626; }
        .step .action-pending { color: #6b7280; }
        .print-toolbar { position: fixed; top: 12px; right: 12px; display: flex; gap: 8px; }
        .print-btn { background: #2563eb; color: #fff; padding: 6px 14px; border: 0; border-radius: 6px; font-size: 13px; cursor: pointer; }
        .print-btn.secondary { background: #6b7280; }
        @media print {
            .print-toolbar { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button class="print-btn" onclick="window.print()">{{ __('common.action_print') }}</button>
        <button class="print-btn secondary" onclick="window.close()">{{ __('common.cancel') }}</button>
    </div>

    <div class="print-header">
        <h1>{{ $submission->form->name }}</h1>
        <div class="meta">
            <div>{{ __('common.reference_no') }}: <strong>{{ $submission->reference_no ?: ('#' . $submission->id) }}</strong></div>
            <div>{{ __('common.submitted_at') ?? 'ส่งเมื่อ' }}: {{ $submission->created_at->format('d M Y H:i') }}</div>
            @if($submission->user)
                <div>{{ __('common.requester') ?? 'ผู้ขอ' }}: {{ $submission->user->first_name }} {{ $submission->user->last_name }}</div>
            @endif
            @if($submission->department)
                <div>{{ __('common.department') }}: {{ $submission->department->name }}</div>
            @endif
        </div>
    </div>

    <div class="field-grid">
        @foreach($submission->form->fields as $field)
            @php
                $val = $submission->payload[$field->field_key] ?? null;
                $display = is_array($val) ? implode(', ', array_map('strval', $val)) : (string) ($val ?? '');
                $isSection = $field->field_type === 'section';
                $isLong = in_array($field->field_type, ['textarea', 'signature'], true);
            @endphp
            @if($isSection)
                <div class="full" style="margin-top: 12px; padding-bottom: 4px; border-bottom: 1px solid #d1d5db; font-weight:600; font-size:13px; color:#374151;">
                    {{ $field->label }}
                </div>
            @else
                <div class="field {{ $isLong ? 'full' : '' }}">
                    <label>{{ $field->label }}</label>
                    <div class="val">{{ $display !== '' ? $display : '—' }}</div>
                </div>
            @endif
        @endforeach
    </div>

    @if($submission->instance && $submission->instance->steps->isNotEmpty())
        <div class="approval-trail">
            <h2>{{ __('common.approval_history') ?? 'ประวัติการอนุมัติ' }}</h2>
            @foreach($submission->instance->steps as $step)
                @php
                    $actionClass = [
                        'approved' => 'action-approved',
                        'rejected' => 'action-rejected',
                    ][$step->action] ?? 'action-pending';
                @endphp
                <div class="step">
                    <span class="label">{{ $step->step_no }}. {{ $step->stage_name }}</span>
                    <span class="{{ $actionClass }}">
                        {{ __('common.approval_status_' . $step->action) }}
                        @if($step->actioned_at)
                            · {{ \Carbon\Carbon::parse($step->actioned_at)->format('d M Y H:i') }}
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    <script>
        // Auto-open print dialog once. User can cancel and still read the page.
        window.addEventListener('load', () => setTimeout(() => window.print(), 250));
    </script>
</body>
</html>
