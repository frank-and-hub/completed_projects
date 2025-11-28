<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body style="font-family: 'Poppins', sans-serif;">
    <style>
        .heading-div::after {
            content: "";
            clear: both;
            display: table;
            text-align: center;
        }

        .logo-img {
            float: left;
        }

        .background-img {
            background-image: url("{{ asset('assets/img/email/background_img.png') }}");
            background-repeat: no-repeat;
            height: 300px;
            text-align: center;
            color: #fff;
            padding-top: 50px;
            padding-left: 25px;
            border-top-left-radius: 30px;
            border-bottom-left-radius: 30px;
            border-top-right-radius: 30px;
            border-bottom-right-radius: 30px;
            background-size: cover;
            filter: brightness(0.4);


            /* background-color: #000000; */
        }
    </style>

    <table style="width:1200px; margin: 0 auto;background: #f7f7f7;padding: 70px 50px;height: 150px;">

        <tr>
            <td style="text-align: center">
                <div class="heading-div">
                    <img class="logo-img" src="{{ asset('assets/img/email/logo_img.png') }}">
                    <h1>Please find the code below to <br>verify your email ID</h1>
                </div>
            </td>
        </tr>

        <tr>
            <td style="position: relative">
                {{-- <div class="background-img"
                style='  background-image: url("{{ asset('assets/img/email/background_img.png') }}");
                background-repeat: no-repeat;
                height: 300px;
                text-align: center;
                color: #fff;
                padding-top: 50px;
                padding-left: 25px;
                border-top-left-radius: 30px;
                border-bottom-left-radius: 30px;
                border-top-right-radius: 30px;
                border-bottom-right-radius: 30px;
                background-size: cover;
                filter: brightness(0.4);'
                >
                </div> --}}
                <div
                    style='position: absolute;
                /* top: 0%;
                left: 20%; */
                text-align: center;
                color: #fff;
                 background-image: url("{{ asset('assets/img/email/background_img1.png') }}");
                 background-repeat: no-repeat;
                 height: 300px;
                 text-align: center;
                 color: #fff;
                 /* padding-top: 50px; */
                 padding-left: 25px;
                 border-top-left-radius: 30px;
                 border-bottom-left-radius: 30px;
                 border-top-right-radius: 30px;
                 border-bottom-right-radius: 30px;
                 background-size: cover;
                 /*filter: brightness(0.4);*/'
                >
                    <h1 style="font-size:4rem;padding-top:20px;">One Time Password</h1>
                    <h1 style="font-size:5rem; margin-top:-10px">{{$otp}}</h1>

                </div>
            </td>

        </tr>
        <tr>
            <td>
                <div style="display: inline-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" style="margin-top:18px;" viewBox="0 0 30 30" width="20px"
                        height="20px">
                        <path
                            d="M15,3C8.373,3,3,8.373,3,15c0,6.627,5.373,12,12,12s12-5.373,12-12C27,8.373,21.627,3,15,3z M16,21h-2v-7h2V21z M15,11.5 c-0.828,0-1.5-0.672-1.5-1.5s0.672-1.5,1.5-1.5s1.5,0.672,1.5,1.5S15.828,11.5,15,11.5z" />
                    </svg>
                    <p style="margin-left: 15px;">If you did create an account, no further action is requested</p>
                </div>
            </td>
        </tr>

        <tr>
            <td>

                <div>
                    <span>Thanks for using </span><strong style="color:#48D33A;"> Parkscape</strong>

                    <div
                        style="height:2px; width:78%; background-color:#48D33A; border-color:#48D33A; display:inline-flex;">
                    </div>
                </div>
            </td>

        </tr>



        </tr>

    </table>
</body>

</html>
