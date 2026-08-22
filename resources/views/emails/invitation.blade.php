<!DOCTYPE html>
<html>

<body
    style="margin: 0; padding: 20px; background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <!-- Wrapper Table para sa Centering (Email standard) -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <div style="max-width: 500px; background: #ffffff; border: 1px solid #dee2e6; text-align: left;">
                    <!-- Header -->
                    <div style="padding: 20px 25px 0 25px;">
                        <h2 style="color: #0d6efd; margin: 0; font-size: 20px; font-weight: bold;">
                            Concepcion Integrated School
                        </h2>
                        <p
                            style="font-size: 11px; color: #6c757d; margin: 4px 0 0 0; text-transform: uppercase; letter-spacing: 1px;">
                            Automated Gate Monitoring System
                        </p>
                    </div>

                    <div style="padding: 0 25px;">
                        <hr style="border: 0; border-top: 1px solid #dee2e6; margin: 15px 0;">
                    </div>

                    <!-- Body Content -->
                    <div style="padding: 0 25px 20px 25px;">
                        <p style="font-size: 14px; color: #333; line-height: 1.5; margin-bottom: 20px;">
                            <strong>Magandang araw!</strong><br>
                            Nais naming ipabatid na may bagong account setup na kailangan ninyong gawin.
                        </p>

                        <p style="font-size: 14px; color: #333; line-height: 1.5; margin-bottom: 20px;">
                            <strong>{{ $recipientName }},</strong><br>
                            Nawa po nating isama ang inyong account sa Concepcion Integrated School system.
                            Gumawa po ng account setup gamit ang link na ibaba bago ang expiration date.
                        </p>

                        <!-- Setup Link Table -->
                        <table width="100%" cellspacing="0" cellpadding="0" border="0"
                            style="font-size: 14px; border-collapse: collapse; margin: 20px 0;">
                            <tr>
                                <td
                                    style="padding: 10px 0; border-bottom: 1px solid #f1f1f1; color: #6c757d; text-align: center;">
                                    <a href="{{ $setupLink }}"
                                        style="background-color: #0d6efd; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;">
                                        Complete Account Setup
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="font-size: 14px; color: #333; line-height: 1.5; margin-bottom: 20px;">
                            Ang link na ito ay mag-expiri ng 72 oras mula sa oras na ipadala ang email na ito.
                            Pagkatapos mag-expiri, mag-uudyong ng bagong invitation para sa bagong setup.
                        </p>

                        <p style="font-size: 14px; color: #333; line-height: 1.5; margin-bottom: 20px;">
                            Huwag po itaguyod ang anumang password o credential sa pamamagitan ng email.
                            Ang account setup ay dapat gawin gamit ang secure link na ibaba.
                        </p>

                        <hr style="border: 0; border-top: 1px solid #dee2e6; margin: 15px 0;">

                        <!-- Footer -->
                        <div style="padding: 0 25px 20px 25px;">
                            <p style="font-size: 11px; color: #adb5bd; line-height: 1.4; margin: 0;">
                                * Ang abisong ito ay automatic na ipinadala para sa seguridad ng mag-aaral.
                                Hindi na kailangang mag-reply sa email na ito.
                            </p>
                        </div>

                    </div> <!-- End Body Content -->

                </div> <!-- End Main Container -->

            </td>
        </tr>
    </table>

</body>

</html>