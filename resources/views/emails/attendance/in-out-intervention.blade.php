<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $recipientType === 'advisor' ? 'Administrative Memo' : 'Attendance Advisory' }}</title>
</head>
<body style="margin: 0; padding: 20px; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.6;">

    <!-- Wrapper Table for Centering -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <div style="max-width: 600px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; text-align: left; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                    
                    <!-- Header -->
                    <div style="background: {{ $recipientType === 'advisor' ? '#1e3a8a' : '#0284c7' }}; padding: 22px 28px; color: #ffffff;">
                        <h2 style="margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.3px;">
                            Concepcion Integrated School
                        </h2>
                        <p style="font-size: 11px; margin: 4px 0 0 0; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">
                            {{ $recipientType === 'advisor' ? 'Internal School Administration • Attendance Monitoring Memo' : 'Student Attendance & Campus Safety Monitoring' }}
                        </p>
                    </div>

                    <!-- Notice Status Banner -->
                    <div style="background-color: {{ $recipientType === 'advisor' ? '#eff6ff' : '#fef3c7' }}; border-bottom: 1px solid {{ $recipientType === 'advisor' ? '#dbeafe' : '#fde68a' }}; padding: 12px 28px; font-size: 13px; color: {{ $recipientType === 'advisor' ? '#1e40af' : '#92400e' }}; font-weight: 600;">
                        <span>
                            @if ($recipientType === 'advisor')
                                📋 <strong>MEMORANDUM:</strong> {{ $data['concern_title'] ?? 'IN/OUT Attendance Follow-Up' }}
                            @else
                                ⚠️ <strong>{{ ($data['language'] ?? 'en') === 'tl' ? 'PAABISO SA ATTENDANCE:' : 'ATTENDANCE ADVISORY:' }}</strong> {{ $data['concern_title'] ?? 'Attendance Monitoring Notice' }}
                            @endif
                        </span>
                    </div>

                    <!-- Body Content -->
                    <div style="padding: 26px 28px;">
                        
                        <p style="font-size: 14px; margin-top: 0; margin-bottom: 16px;">
                            <strong>{{ $data['salutation'] ?? ($recipientType === 'advisor' ? 'Dear Class Adviser,' : 'Dear Parent / Guardian,') }}</strong>
                        </p>

                        <!-- Student Details Box -->
                        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 22px;">
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size: 13px; line-height: 1.7;">
                                <tr>
                                    <td style="color: #64748b; width: 38%; padding: 2px 0;">
                                        {{ ($data['language'] ?? 'en') === 'tl' && $recipientType === 'parent' ? 'Pangalan ng Estudyante:' : 'Student Name:' }}
                                    </td>
                                    <td style="font-weight: 700; color: #0f172a; padding: 2px 0;">{{ $data['student_name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b; padding: 2px 0;">Student ID / LRN:</td>
                                    <td style="color: #334155; padding: 2px 0;">{{ $data['student_number'] }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b; padding: 2px 0;">
                                        {{ ($data['language'] ?? 'en') === 'tl' && $recipientType === 'parent' ? 'Baitang at Seksiyon:' : 'Grade & Section:' }}
                                    </td>
                                    <td style="color: #334155; padding: 2px 0;">Grade {{ $data['grade_level'] }} — {{ $data['section_name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b; padding: 2px 0;">
                                        {{ ($data['language'] ?? 'en') === 'tl' && $recipientType === 'parent' ? 'Uri ng Concern:' : 'Attendance Concern:' }}
                                    </td>
                                    <td style="font-weight: 600; color: {{ ($data['reason_type'] ?? '') === 'late' ? '#b45309' : '#dc2626' }}; padding: 2px 0;">
                                        {{ $data['concern_title'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b; padding: 2px 0;">
                                        {{ ($data['language'] ?? 'en') === 'tl' && $recipientType === 'parent' ? 'Padrong Naitala:' : 'Pattern Observed:' }}
                                    </td>
                                    <td style="color: #334155; padding: 2px 0;">{{ $data['pattern_desc'] ?? 'Multiple occurrences observed' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Main Narrative Message -->
                        <div style="font-size: 14px; line-height: 1.65; color: #334155; margin-bottom: 20px;">
                            {!! nl2br(e($data['message_body'])) !!}
                        </div>

                        <!-- Evidence / Log Dates -->
                        @if (!empty($data['log_dates']) && count($data['log_dates']) > 0)
                        <div style="background-color: #f1f5f9; border-left: 3px solid #64748b; border-radius: 4px; padding: 12px 16px; margin-bottom: 22px;">
                            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #475569; letter-spacing: 0.5px; margin-bottom: 6px;">
                                {{ ($data['language'] ?? 'en') === 'tl' && $recipientType === 'parent' ? 'Mga Naitalang Petsa at Oras:' : 'Recorded Logs / Dates:' }}
                            </div>
                            <ul style="margin: 0; padding-left: 18px; font-size: 13px; color: #334155;">
                                @foreach (array_slice($data['log_dates'], 0, 8) as $dateStr)
                                    <li style="margin-bottom: 3px;">{{ $dateStr }}</li>
                                @endforeach
                                @if (count($data['log_dates']) > 8)
                                    <li style="color: #64748b; font-style: italic;">...at iba pang naitalang scans</li>
                                @endif
                            </ul>
                        </div>
                        @endif

                        <!-- Admin Note / Remarks -->
                        @if (!empty($adminNotes))
                        <div style="margin-bottom: 22px; padding: 14px 18px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #0284c7; border-radius: 6px;">
                            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #0369a1; letter-spacing: 0.5px; margin-bottom: 4px;">
                                {{ ($data['language'] ?? 'en') === 'tl' && $recipientType === 'parent' ? 'Pahayag mula sa School Administrator:' : 'Note from School Administration:' }}
                            </div>
                            <p style="margin: 0; font-size: 13px; color: #1e293b; line-height: 1.5;">
                                {{ $adminNotes }}
                            </p>
                        </div>
                        @endif

                        <!-- Sign-off -->
                        <div style="font-size: 14px; line-height: 1.5; color: #475569; border-top: 1px solid #f1f5f9; padding-top: 18px;">
                            <p style="margin: 0 0 4px 0;">
                                @if (($data['language'] ?? 'en') === 'tl' && $recipientType === 'parent')
                                    Lubos na gumagalang,
                                @else
                                    Respectfully yours,
                                @endif
                            </p>
                            <strong style="color: #0f172a; font-size: 14px;">Office of the School Administrator</strong><br>
                            <span style="font-size: 12px; color: #64748b;">Concepcion Integrated School</span>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 28px; text-align: center;">
                        <p style="margin: 0; font-size: 11px; color: #94a3b8; line-height: 1.4;">
                            This is an official administrative communication generated by the Concepcion Integrated School Automated Attendance System.<br>
                            For inquiries, please coordinate with the school administration office.
                        </p>
                    </div>

                </div>

            </td>
        </tr>
    </table>

</body>
</html>
