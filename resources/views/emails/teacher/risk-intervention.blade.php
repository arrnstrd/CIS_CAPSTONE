<!DOCTYPE html>
<html>
<body style="margin: 0; padding: 20px; background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #212529;">

    <!-- Wrapper Table for Centering -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <div style="max-width: 580px; background: #ffffff; border: 1px solid #dee2e6; border-radius: 8px; text-align: left; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                    
                    <!-- Header -->
                    <div style="background-color: #0d6efd; padding: 22px 28px; color: #ffffff;">
                        <h2 style="margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">
                            Concepcion Integrated School
                        </h2>
                        <p style="font-size: 11px; margin: 4px 0 0 0; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">
                            Student Academic & Attendance Monitoring
                        </p>
                    </div>

                    <!-- Notice Banner -->
                    <div style="background-color: #fff3cd; border-bottom: 1px solid #ffe69c; padding: 10px 28px; font-size: 13px; color: #664d03; font-weight: 600;">
                        <span>⚠️ {{ $data['status_label'] ?? ('Status: ' . ($data['risk_condition_label'] ?? 'Academic Monitoring')) }}</span>
                    </div>

                    <!-- Body Content -->
                    <div style="padding: 24px 28px;">
                        
                        <p style="font-size: 14px; margin-top: 0; margin-bottom: 18px; line-height: 1.5;">
                            <strong>{{ $data['salutation'] ?? ('Dear ' . ($data['guardian_name'] ?? 'Parent / Guardian')) }},</strong>
                        </p>

                        <!-- Student Details Summary Box -->
                        <div style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 14px 18px; margin-bottom: 20px;">
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size: 13px; line-height: 1.6;">
                                <tr>
                                    <td style="color: #6c757d; width: 38%; padding: 3px 0;">
                                        {{ ($data['language'] ?? 'en') === 'tl' ? 'Pangalan ng Estudyante:' : 'Student Name:' }}
                                    </td>
                                    <td style="font-weight: 600; color: #111827; padding: 3px 0;">{{ $data['student_name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #6c757d; padding: 3px 0;">
                                        {{ ($data['language'] ?? 'en') === 'tl' ? 'Student ID:' : 'Student ID:' }}
                                    </td>
                                    <td style="color: #374151; padding: 3px 0;">{{ $data['student_number'] }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #6c757d; padding: 3px 0;">
                                        {{ ($data['language'] ?? 'en') === 'tl' ? 'Baitang at Seksiyon:' : 'Grade & Section:' }}
                                    </td>
                                    <td style="color: #374151; padding: 3px 0;">Grade {{ $data['grade_level'] }} - {{ $data['section_name'] }}</td>
                                </tr>
                                @if(!empty($data['subject_name']))
                                <tr>
                                    <td style="color: #6c757d; padding: 3px 0;">
                                        {{ ($data['language'] ?? 'en') === 'tl' ? 'Asignatura:' : 'Subject:' }}
                                    </td>
                                    <td style="color: #374151; padding: 3px 0;">{{ $data['subject_name'] }}</td>
                                </tr>
                                @endif
                                @if($data['current_grade'] !== null)
                                <tr>
                                    <td style="color: #6c757d; padding: 3px 0;">
                                        {{ ($data['language'] ?? 'en') === 'tl' ? 'Kasalukuyang Grade Average:' : 'Current Average Grade:' }}
                                    </td>
                                    <td style="font-weight: 600; color: {{ $data['current_grade'] < 75 ? '#dc3545' : '#198754' }}; padding: 3px 0;">
                                        {{ $data['current_grade'] }}%
                                    </td>
                                </tr>
                                @endif
                                @if($data['attendance_rate'] !== null)
                                <tr>
                                    <td style="color: #6c757d; padding: 3px 0;">
                                        {{ ($data['language'] ?? 'en') === 'tl' ? 'Attendance Rate:' : 'Attendance Rate:' }}
                                    </td>
                                    <td style="font-weight: 600; color: {{ $data['attendance_rate'] < 85 ? '#dc3545' : '#198754' }}; padding: 3px 0;">
                                        {{ $data['attendance_rate'] }}%
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        <!-- Main Message Paragraphs -->
                        <div style="font-size: 14px; line-height: 1.6; color: #374151;">
                            {!! nl2br(e($data['message_body'])) !!}
                        </div>

                        <!-- Optional Teacher Note -->
                        @if(!empty($teacherNote))
                        <div style="margin-top: 20px; padding: 14px 18px; background-color: #eef2ff; border-left: 4px solid #4f46e5; border-radius: 4px;">
                            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #4338ca; letter-spacing: 0.5px; margin-bottom: 4px;">
                                {{ $data['teacher_note_label'] ?? ('Note from Teacher (' . $data['teacher_name'] . '):') }}
                            </div>
                            <p style="margin: 0; font-size: 13px; color: #1e1b4b; line-height: 1.5;">
                                {{ $teacherNote }}
                            </p>
                        </div>
                        @endif

                        <!-- Closing -->
                        <div style="margin-top: 28px; font-size: 14px; line-height: 1.5; color: #374151;">
                            {!! nl2br(e($data['closing'] ?? ("Warm regards,\n" . $data['teacher_name'] . "\nSubject Teacher / Faculty\nConcepcion Integrated School"))) !!}
                        </div>

                    </div> <!-- End Body Content -->

                    <!-- Footer -->
                    <div style="padding: 16px 28px; background-color: #f9fafb; border-top: 1px solid #e5e7eb;">
                        <p style="font-size: 11px; color: #9ca3af; line-height: 1.4; margin: 0;">
                            @if(($data['language'] ?? 'en') === 'tl')
                                * Ito ay opisyal na abiso sa pagsubaybay sa mag-aaral mula sa Concepcion Integrated School Academic Portal. Maaari pong makipag-ugnayan sa guro o pamunuan ng paaralan para sa karagdagang impormasyon o tulong.
                            @else
                                * This is an official student monitoring notification sent via the Concepcion Integrated School Academic Portal. Please contact the teacher or school administration if you need further clarification or assistance.
                            @endif
                        </p>
                    </div>

                </div> <!-- End Main Container -->

            </td>
        </tr>
    </table>

</body>
</html>
