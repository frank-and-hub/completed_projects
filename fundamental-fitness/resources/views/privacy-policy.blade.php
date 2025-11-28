<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

    <style>

    /* Import Outfit font from Google Fonts */
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    /* Apply Outfit font globally */
    * {
        font-family: 'Outfit', sans-serif !important;
        box-sizing: border-box;
    }

    body {
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        color: #333;
    }

    header {
        text-align: center;
    }

    .container {
        width: 100%;
        margin: auto;
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1, h2, h3, h4, h5, h6 {
        color: #2c3e50;
        font-weight: 600;
    }

    ul {
        list-style-type: none;
        padding: 0;
    }

    li {
        margin-bottom: 15px;
    }

    .section {
        margin-bottom: 30px;
    }

    .section p {
        line-height: 1.6;
    }

    footer {
        text-align: center;
        padding: 10px;
    }

    a,
    .contact-info,
    .sende_mail {
        color: #3498db;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    a:hover,
    .contact-info:hover,
    .sende_mail:hover {
        color: #1d6fa5;
    }

    .list-item {
        margin-left: 20px;
    }

    .list-item li {
        margin-bottom: 10px;
    }
    </style>
</head>

<body>
    <div class="container">
        <header  class="section">
            <h1>Privacy Policy</h1>
            <p>Thank you for using Fundamental Fitness. We value your privacy and are committed to protecting your personal information.</p>
        </header>
        <section class="section">
            <h2>1. Information We Collect</h2>
            <p>We collect the following types of information:</p>
            <ul>
                <li><strong>a. Personal Information</strong>
                    <ul class="list-item">
                        <li>Full name</li>
                        <li>Email address</li>
                        <li>Date of birth</li>
                        <li>Gender</li>
                        <li>Height and weight</li>
                    </ul>
                </li>
                <li><strong>b. Health & Fitness Data</strong>
                    <ul class="list-item">
                        <li>Workout and activity data</li>
                        <li>Fitness goals and progress</li>
                        <li>Calories burned</li>
                        <li>Heart rate, steps, and other metrics from connected devices or apps (e.g., Apple Health, Google Fit)</li>
                    </ul>
                </li>
                <li><strong>c. Payment Information</strong>
                    <ul class="list-item">
                        <li>Billing name and address</li>
                        <li>Credit/debit card details or other payment method information (handled by third-party payment processors)</li>
                        <li>Transaction history</li>
                    </ul>
                    <p><em>Note: We do not store your full payment card details on our servers. All payments are securely processed through third-party services (e.g., Stripe, Apple Pay, Google Pay), which comply with PCI-DSS standards.</em></p>
                </li>
                <li><strong>d. Device & Technical Information</strong>
                    <ul class="list-item">
                        <li>Device type and model</li>
                        <li>Operating system and version</li>
                        <li>IP address</li>
                        <li>App usage logs and diagnostics</li>
                        <li>Crash reports</li>
                    </ul>
                </li>
                <li><strong>e. Location Data (Optional)</strong>
                    <p>If you enable location access, we may collect GPS or approximate location data for features like route tracking or location-based challenges.</p>
                </li>
            </ul>
        </section>

        <section class="section">
            <h2>2. How We Use Your Information</h2>
            <p>We use your information to:</p>
            <ul>
                <li>Provide and personalize your fitness experience</li>
                <li>Process transactions and manage subscriptions</li>
                <li>Track and display your workouts and health data</li>
                <li>Improve app performance and functionality</li>
                <li>Communicate with you about your account or purchases</li>
                <li>Send relevant notifications, promotions, or fitness tips</li>
                <li>Detect and prevent fraud, abuse, or security issues</li>
            </ul>
        </section>

        <section class="section">
            <h2>3. How We Share Your Information</h2>
            <p>We do not sell your personal information. However, we may share it with:</p>
            <ul>
                <li>Payment processors (e.g., Stripe, Apple, Google) to complete transactions</li>
                <li>Service providers that help us deliver core services (e.g., cloud storage, analytics)</li>
                <li>Third-party integrations (e.g., Apple Health, Google Fit), with your explicit consent</li>
                <li>Authorities or legal entities when required by law or necessary to protect legal rights</li>
                <li>A new owner if we are involved in a merger, acquisition, or asset sale</li>
            </ul>
        </section>

        <section class="section">
            <h2>4. Data Retention</h2>
            <p>We retain your data only as long as necessary for the purposes described in this policy, or as required by applicable law. You may request deletion of your personal data at any time.</p>
        </section>

        <section class="section">
            <h2>5. Security of Your Information</h2>
            <p>We use reasonable administrative, technical, and physical safeguards to protect your information. However, no method of data transmission or storage is 100% secure.</p>
            <p>Payment data is processed by third-party providers who comply with industry-standard security practices (PCI-DSS).</p>
        </section>

        <section class="section">
            <h2>6. Your Privacy Rights</h2>
            <p>Depending on your location, you may have the right to:</p>
            <ul>
                <li>Access or request a copy of the personal data we hold</li>
                <li>Correct or update your data</li>
                <li>Request deletion of your account and data</li>
                <li>Object to or restrict certain data uses</li>
                <li>Withdraw consent where applicable</li>
            </ul>
            <p>To exercise these rights, contact us at <a href="mailto:support@fundamental-fit.co.uk" class="sende_mail">support@fundamental-fit.co.uk</a>.</p>
        </section>

        <section class="section">
            <h2>7. Children's Privacy</h2>
            <p>Our App is not intended for children under 16 (or the age required by your local laws). We do not knowingly collect data from children. If we learn we have, we will delete it promptly.</p>
        </section>

        <section class="section">
            <h2>8. Third-Party Services</h2>
            <p>We may link to or integrate with third-party platforms or services. This Privacy Policy does not govern those third parties. Please review their privacy policies before sharing data with them.</p>
        </section>

        <section class="section">
            <h2>9. Changes to This Privacy Policy</h2>
            <p>We may update this Privacy Policy from time to time. Material changes will be communicated via the App or email. Your continued use of the App after updates means you accept the revised policy.</p>
        </section>

        <section class="section">
            <h2>10. Contact Us</h2>
            <p>If you have questions about this Privacy Policy or our data practices, please contact us at: <a href="mailto:support@fundamental-fit.co.uk" class="sende_mail">support@fundamental-fit.co.uk</a></p>
        </section>
    </div>

    <footer>
        <p>© {{ date('Y') }} FundamentalFitness. All rights reserved.</p>
    </footer>
</body>

</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
