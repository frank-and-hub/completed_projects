<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f30051;
            background-image: url('{{asset("_next/static/media/header_banner.558b8a42.png")}}'); /* Replace with your image URL */
            background-size: cover;
            background-position: center;
            color: #fff;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
	.content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            color:#f30051;
        }
	h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }
	.countdown {
            font-size: 2em;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="content">
        <img src="{{asset('_next/static/media/logo.f88f19f0.svg')}}" width ="80%">
        <h1>Coming Soon!</h1>
        <div class="countdown" id="countdown"></div>
    </div>

    <script type="text/javascript">
	// Set the date we're counting down to
        var countDownDate = new Date('5 Aug ,2024 15:30:00');
        countDownDate.setDate(countDownDate.getDate()); // 15 days from now

        // Update the countdown every 1 second
        var x = setInterval(function() {
            // Get today's date and time
            var now = new Date().getTime();

            // Find the distance between now and the countdown date
            var distance = countDownDate.getTime() - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result in the element with id="countdown"
            document.getElementById("countdown").innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";

            // If the countdown is over, write some text
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("countdown").innerHTML = "EXPIRED";
            }
        }, 1000);
    </script>
</body>
</html>
