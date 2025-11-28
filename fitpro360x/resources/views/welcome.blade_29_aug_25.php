<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FitPro360x – Coming Soon</title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="favicon.ico" />

  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #ec5d1f, #d94d0d, #b63a05);
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
    }
    .container {
      max-width: 700px;
      padding: 20px;
    }
    h1 {
      font-size: 48px;
      margin-bottom: 10px;
    }
    h2 {
      font-size: 24px;
      font-weight: 400;
      margin-bottom: 20px;
      color: #ffe3d1;
    }
    p {
      font-size: 18px;
      line-height: 1.6;
      margin-bottom: 30px;
    }
    .timer {
      font-size: 26px;
      letter-spacing: 1px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Get Ready to Transform Your Fitness Journey</h1>
    <h2>FitPro360x – Track. Train. Transform.</h2>
    <p>
      FitPro360x is your personal fitness partner — bringing workouts, meal planning, progress tracking, and expert guidance into one powerful mobile app.
      <br><br>
      Whether you're a beginner or a pro, we're building the tools to help you reach your full potential.
    </p>
    <div class="timer" id="countdown">Website launching soon...</div>
  </div>

  <script>
    const launchDate = new Date("2025-08-15T00:00:00").getTime();
    const countdown = document.getElementById("countdown");

    setInterval(function() {
      const now = new Date().getTime();
      const distance = launchDate - now;

      if (distance < 0) {
        countdown.innerHTML = "We're Live!";
        return;
      }

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      countdown.innerHTML = `Launching in: ${days}d ${hours}h ${minutes}m ${seconds}s`;
    }, 1000);
  </script>
</body>
</html>