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
</head>
{{-- <style>
     body {
        font-family: "Outfit", sans-serif;
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
        background-color: white;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    h1, h2 {
        color: #2c3e50;
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
        background-color: #2c3e50;
        color: white;
    }
    .contact-info {
        color: #3498db;
        text-decoration: none;
    }
    .sende_mail{
        cursor: pointer;
        text-decoration: none;
    }
</style> --}}
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
<body>
    
    <div class="container">
      <header class="section">
          <h1>Terms and Conditions</h1>
          <p>Welcome to FundamentalFitness. By using this app, you agree to the following terms.</p>
      </header>
      
        <section class="section">
            <h2>1. Eligibility</h2>
            <p>You must be at least 18 years old to use this App. By using the App, you represent and warrant that you are at least 18 years old and have the legal capacity to enter into these Terms.</p>
        </section>

        <section class="section">
            <h2>2. Medical Disclaimer</h2>
            <p><strong>Not Medical Advice:</strong> The App provides fitness and wellness-related content for informational purposes only. It is not a substitute for professional medical advice, diagnosis, or treatment.</p>
            <p><strong>Consult a Professional:</strong> Always consult your physician or other qualified healthcare provider before beginning any new fitness program.</p>
            <p><strong>User Responsibility:</strong> You assume full responsibility for your health and any injuries or damages resulting from your use of the App.</p>
        </section>

        <section class="section">
            <h2>3. User Accounts</h2>
            <p>You may be required to create an account to access certain features. You are responsible for maintaining the confidentiality of your account information. You agree to provide accurate, current, and complete information and to update it as necessary.</p>
        </section>

        <section class="section">
            <h2>4. Subscriptions and Payments</h2>
            <ul>
                <li>The App may offer free and paid subscription plans.</li>
                <li>All payments are processed through the App Store or Google Play, depending on your device.</li>
                <li><strong>Auto-Renewal:</strong> Subscriptions may renew automatically unless canceled at least 24 hours before the end of the billing period.</li>
                <li><strong>Refunds:</strong> All purchases are final. Refund requests must comply with the policies of the platform through which you made the purchase.</li>
            </ul>
        </section>

        <section class="section">
            <h2>5. Acceptable Use</h2>
            <ul>
                <li>You agree not to use the App for any unlawful purpose.</li>
                <li>You agree not to post or transmit content that is harmful, offensive, or infringes on others' rights.</li>
                <li>You agree not to attempt to reverse-engineer or interfere with the App's functionality or security.</li>
                <li>You agree not to use bots, scrapers, or other automated means to access the App.</li>
            </ul>
        </section>

        <section class="section">
            <h2>6. Intellectual Property</h2>
            <p>All content on the App, including text, graphics, videos, and software, is owned or licensed by us and is protected by copyright and other intellectual property laws. You may not reproduce, modify, or distribute any content without our prior written permission.</p>
        </section>

        <section class="section">
            <h2>7. Termination</h2>
            <p>We reserve the right to suspend or terminate your account at any time for violation of these Terms or for any reason at our sole discretion.</p>
        </section>

        <section class="section">
            <h2>8. Disclaimer of Warranties</h2>
            <p>The App is provided "as is" and "as available" without warranties of any kind. We do not guarantee that the App will be error-free, secure, or uninterrupted.</p>
        </section>

        <section class="section">
            <h2>9. Limitation of Liability</h2>
            <p>To the fullest extent permitted by law, FundamentalFitness and its affiliates shall not be liable for any direct, indirect, incidental, special, or consequential damages arising out of your use of or inability to use the App.</p>
        </section>

        <section class="section">
            <h2>10. Changes to Terms</h2>
            <p>We may update these Terms from time to time. Continued use of the App after changes means you accept the updated Terms. We encourage you to review this page periodically.</p>
        </section>

        <section class="section">
            <h2>11. Privacy Policy</h2>
            <p>Please refer to our <a href="{{ route('privacy-policy') }}" class="contact-info">Privacy Policy</a> for information on how we collect, use, and protect your personal data.</p>
        </section>

        <section class="section">
            <h2>12. Governing Law</h2>
            <p>These Terms shall be governed by and construed in accordance with the laws of [Your Country/State], without regard to its conflict of law provisions.</p>
        </section>

        <section class="section">
            <h2>13. Contact Us</h2>
            <p>If you have any questions or concerns about these Terms, please contact us at: <a href="mailto:support@fundamental-fit.co.uk" class="sende_mail">support@fundamental-fit.co.uk</a></p>
        </section>
    </div>

    <footer>
        <p>© {{date('Y')}} FundamentalFitness. All rights reserved.</p>
    </footer>

</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>