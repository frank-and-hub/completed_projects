<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarsbox </title>
</head>



@if($content=='newsletter')
    <body style="font-family: Arial, sans-serif;">

    <p>Hi there!</p>

    <p>You have successfully subscribed to the ScholarsBox Newsletter. Welcome to the community!</p>

    <p>Catch all the latest updates, exclusively curated content, and a plethora of opportunities—from ScholarsBox, directly to your inbox!</p>

    <p>Thank you for choosing to be a part of the ScholarsBox community. We look forward to sharing our insights with you.</p>

    <p>Stay tuned for our upcoming editions! And feel free to spread the word about the scholarships and opportunities with others in your network!</p>

    <br>

    <p>Kind regards,</p>
    <p>Team ScholarsBox</p>

</body>



@elseif($content=='Position')

<p>Thank you for applying for the job, Our team will reach out to you soon. !</p>

<p>Kind regards,</p>
<p>Team ScholarsBox</p>



@elseif($csasontent['content'] == 'Welcome')
<body style="font-family: Arial, sans-serif;">

    <div style="max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px;">
    
        <h1>Dear ({{$csasontent['name']}}),</h1>
    
        <p>Congratulations and welcome! We are happy to have you at ScholarsBox!</p>
    
       <p> As part of your onboarding, we're thrilled to provide you with the credentials you need to access ScholarsBox. Below are your login details:<p>
    
        <p>Username:{{$csasontent['email']}} </p>
        <p>Password:{{$csasontent['password']}} </p>
        <p>Applicant Id :{{$csasontent['id']}} </p>

        <p>You can start exploring and using ScholarsBox right away by clicking Login ScholarsBox.  We recommend changing your password for added security when you log in for the first time.<p>

            <p>If you have any questions or need assistance, don't hesitate to reach out to our friendly support team at info@scholarsbox.in or  8401019730. We're here to help!</p>


            <p>Looking forward to seeing you thrive with us at ScholarsBox.</p>
    
        <p><strong>Best wishes,</strong><br>Team ScholarsBox</p>
    
    </div>
    
    </body>
    

@else
    <body style="font-family: Arial, sans-serif;">

<div style="max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px;">

    <h1>Hello!</h1>

    <p>Welcome to the ScholarsBox Community!</p>

    <p>We’re excited to have you as a part of our growing ecosystem aimed at nurturing the ambitions of aspirants through scholarships.</p>

    <p>Thank you for reaching out to us with your message. Our team will get in touch with you to explore this conversation further.</p>

    <p>Glad to have you with us!</p>

    <p><strong>Sincerely,</strong><br>Team ScholarsBox</p>

</div>

</body>

@endif

</html>