<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ $subject ?? 'JunkHop Notification' }}</title>
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }
            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }

        body {
            background-color: #f8f9fa;
            color: #2d3748;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            width: 100%;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper {
            margin: 0;
            padding: 20px;
            width: 100%;
        }

        /* Full-width colored header */
        .header {
            padding: 30px 0;
            text-align: center;
            background-color: #0F766E;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .logo-container {
            display: inline-block;
            margin-bottom: 8px;
        }

        .brand-name {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin: 10px 0 0;
        }

        /* Clean body design */
        .body {
            background-color: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04);
            margin: 0 auto;
            padding: 0;
            width: 100%;
            max-width: 600px;
            overflow: hidden; /* This ensures the rounded corners work properly */
        }

        .inner-body {
            background-color: #FFFFFF;
            margin: 0 auto;
            padding: 40px;
            width: 100%;
        }

        /* Streamlined footer */
        .footer {
            margin: 0 auto;
            padding: 30px 0;
            text-align: center;
            width: 100%;
            max-width: 600px;
            color: #718096;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Typography */
        h1, h2, h3 {
            color: #0F766E;
            font-weight: 700;
            margin-top: 0;
            letter-spacing: -0.5px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 24px;
        }

        p {
            font-size: 16px;
            line-height: 1.625;
            margin-top: 0;
            margin-bottom: 24px;
        }

        a {
            color: #0F766E;
            text-decoration: none;
        }

        /* Modern button */
        .button {
            background-color: #0F766E;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            color: #ffffff;
            display: inline-block;
            font-size: 16px;
            font-weight: 600;
            padding: 14px 24px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.15s ease;
        }

        .button:hover {
            background-color: #115E59;
        }

        /* Content components */
        .content-box {
            background-color: #f7faf9;
            border-left: 4px solid #115E59;
            margin-bottom: 24px;
            padding: 20px;
        }

        .divider {
            border: none;
            border-top: 1px solid #e9ecef;
            margin: 32px 0;
            height: 1px;
        }

        ul {
            padding-left: 24px;
            margin-bottom: 24px;
        }

        li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        /* Footer links */
        .footer-links {
            margin: 16px 0;
        }

        .footer-links a {
            color: #718096;
            margin: 0 8px;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Full-width colored header -->
                    <tr>
                        <td class="header">
                            <div class="logo-container">
                                <img src="{{ config('app.frontend_url', 'http://localhost:3000') }}/Logo.svg" alt="JunkHop Logo" width="60">
                            </div>
                            <p class="brand-name">JunkHop</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Simplified footer -->
                    <tr>
                        <td>
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center">
                                        <div class="footer-links">
                                            <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/privacy-policy">Privacy Policy</a>
                                            <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/terms-of-service">Terms</a>
                                            <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/contact-us">Contact</a>
                                        </div>
                                        <p>If you have questions, email us at <a href="mailto:support@junkhop.com">support@junkhop.com</a></p>
                                        <p>© {{ date('Y') }} JunkHop. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
