<!DOCTYPE html>
<html>
<body style="margin: 0; padding: 20px; background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <!-- Wrapper Table para sa Centering (Email standard) -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">
                
                <!-- Main Container -->
                <div style="max-width: 500px; background: #ffffff; border: 1px solid #dee2e6; text-align: left;">
                    d
                    <!-- Header -->
                    <div style="padding: 20px 25px 0 25px;">
                        <h2 style="color: #0d6efd; margin: 0; font-size: 20px; font-weight: bold;">
                            Concepcion Integrated School
                        </h2>
                        <p style="font-size: 11px; color: #6c757d; margin: 4px 0 0 0; text-transform: uppercase; letter-spacing: 1px;">
                            Automated Gate Monitoring System
                        </p>
                    </div>

                    <div style="padding: 0 25px;">
                        <hr style="border: 0; border-top: 1px solid #dee2e6; margin: 15px 0;">
                    </div>

                    <!-- Body Content -->
                    <div style="padding: 0 25px 20px 25px;">
                        <p style="font-size: 14px; color: #333; line-height: 1.5; margin-bottom: 20px;">
                            <strong>Magandang araw, Parent/Guardian!</strong><br>
                            Nais naming ipabatid na na-scan na ang ID ng inyong anak sa loob ng paaralan.
                        </p>

                        <!-- Data Table -->
                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size: 14px; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 10px 0; border-bottom: 1px solid #f1f1f1; color: #6c757d; width: 35%;">Estudyante:</td>
                                <td style="padding: 10px 0; border-bottom: 1px solid #f1f1f1; font-weight: 600; color: #212529;">
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0; border-bottom: 1px solid #f1f1f1; color: #6c757d;">Aksyon (Status):</td>
                                <td style="padding: 10px 0; border-bottom: 1px solid #f1f1f1; font-weight: 700; color: #0d6efd;">
                                   TIME  {{ $scanType }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0; border-bottom: 1px solid #f1f1f1; color: #6c757d;">Oras at Petsa:</td>
                                <td style="padding: 10px 0; border-bottom: 1px solid #f1f1f1; color: #212529;">
                                    {{ $time }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Footer -->
                    <div style="padding: 0 25px 20px 25px;">
                        <hr style="border: 0; border-top: 1px solid #dee2e6; margin: 10px 0;">
                        <p style="font-size: 11px; color: #adb5bd; line-height: 1.4; margin: 0;">
                            * Ang abisong ito ay automatic na ipinadala para sa seguridad ng mag-aaral. Hindi na kailangang mag-reply sa email na ito.
                        </p>
                    </div>

                </div> <!-- End Main Container -->

            </td>
        </tr>
    </table>

</body>
</html>