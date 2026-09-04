<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin: 0; padding: 25px 15px; background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333333;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">
                <!-- Main Card Container -->
                <div style="max-width: 540px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); text-align: left;">
                    
                    <!-- Header -->
                    <div style="padding: 24px 28px 20px 28px; background-color: #ffffff; border-bottom: 1px solid #edf2f7;">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td>
                                    <h2 style="color: #0b3a82; margin: 0; font-size: 18px; font-weight: 700; letter-spacing: -0.2px;">
                                        Concepcion Integrated School
                                    </h2>
                                    <p style="font-size: 11px; color: #64748b; margin: 3px 0 0 0; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                                        Teacher Portal &bull; System Alert
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Body Content -->
                    <div style="padding: 24px 28px;">
                        <!-- Category Badge -->
                        @php
                            $badgeColor = match ($category ?? 'general') {
                                'attendance' => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                                'grading' => ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0'],
                                'at_risk' => ['bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
                                'analytics' => ['bg' => '#f0f9ff', 'text' => '#0369a1', 'border' => '#bae6fd'],
                                'import' => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0'],
                                default => ['bg' => '#f8fafc', 'text' => '#334155', 'border' => '#e2e8f0'],
                            };
                        @endphp
                        
                        <div style="margin-bottom: 14px;">
                            <span style="display: inline-block; background-color: {{ $badgeColor['bg'] }}; color: {{ $badgeColor['text'] }}; border: 1px solid {{ $badgeColor['border'] }}; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                {{ $categoryLabel ?? ucfirst($category ?? 'Notice') }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #1e293b; line-height: 1.4;">
                            {{ $title }}
                        </h3>

                        <!-- Greeting -->
                        <p style="font-size: 14px; color: #475569; margin: 0 0 14px 0; line-height: 1.5;">
                            Hello {{ $recipientName ?? 'Teacher' }},
                        </p>

                        <!-- Notification Message Box -->
                        <div style="background-color: #f8fafc; border-left: 4px solid #0b3a82; padding: 14px 16px; border-radius: 0 6px 6px 0; margin-bottom: 22px;">
                            <p style="font-size: 14px; color: #334155; line-height: 1.6; margin: 0;">
                                {{ $notificationMessage }}
                            </p>
                        </div>

                        <!-- Action Button (if URL available) -->
                        @if (!empty($actionUrl))
                            <div style="text-align: left; margin-bottom: 20px;">
                                <a href="{{ $actionUrl }}" style="background-color: #0b3a82; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; display: inline-block;">
                                    View in Teacher Portal &rarr;
                                </a>
                            </div>
                        @endif

                        <p style="font-size: 12px; color: #64748b; margin: 0; line-height: 1.5;">
                            This notification has also been saved to your in-app notification bell inside the Teacher Portal.
                        </p>
                    </div>

                    <!-- Footer -->
                    <div style="padding: 16px 28px; background-color: #f8fafc; border-top: 1px solid #edf2f7; text-align: center;">
                        <p style="font-size: 11px; color: #94a3b8; margin: 0 0 4px 0;">
                            &copy; {{ date('Y') }} Concepcion Integrated School. All rights reserved.
                        </p>
                        <p style="font-size: 11px; color: #94a3b8; margin: 0;">
                            You can manage your notification category preferences anytime in Portal Settings.
                        </p>
                    </div>

                </div>
            </td>
        </tr>
    </table>

</body>
</html>

