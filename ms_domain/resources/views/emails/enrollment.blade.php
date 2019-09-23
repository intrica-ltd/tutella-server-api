<!DOCTYPE html>
<html>

<head>
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400" rel="stylesheet">
    <style>
        @media screen {
            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 300;
                src: local('Source Sans Pro Light'), local('SourceSansPro-Light'), url(https://fonts.gstatic.com/s/sourcesanspro/v11/6xKydSBYKcSV-LCoeQqfX1RYOo3ik4zwlxdu.woff2) format('woff2');
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
            }
            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 400;
                src: local('Source Sans Pro Regular'), local('SourceSansPro-Regular'), url(https://fonts.gstatic.com/s/sourcesanspro/v11/6xK3dSBYKcSV-LCoeQqfX1RYOo3qOK7l.woff2) format('woff2');
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
            }
        }
    </style>
    <title>Email confirmation for the School Admin</title>
</head>

<body style="font-family: 'Source Sans Pro', sans-serif;width: 740px;margin: 0 auto;text-align: center;">
    <div class="_img" style="width: 100%;">
        <img src="https://tutella-api-domain.tutella.io/mail-logo.png" alt="" class="mail-logo" style="text-align: center;height: 100px;margin:auto;">
    </div>
    <h1 style="font-weight: normal;">Welcome to {{$school_name}} {{$name}}!</h1>
    <hr class="green_bg" style="margin-left: 60px;margin-right: 60px;border: 0.5px solid rgb(82, 205, 147);">
    <p class="p-text" style="font-size: 18px;margin-top: 40px;font-weight: 300;">You’ve been invited to join {{$school_name}} at Tutella.</p>
    <p class="p-text" style="font-size: 18px;margin-top: 40px;font-weight: 300;">To use your account on Tutella, please download the iOS or Android app from the respective store.</p>
    <p class="p-text" style="font-size: 18px;margin-top: 40px;font-weight: 300;">Here is your Enrolment Code:</p>
    <div class="enrolment-code" style="width: 100%;display: inline-block;margin-top: 20px;">
        <div class="box-code" style="display: inline-block;width: 40px;height: 75px;font-size: 40px;color: rgb(82, 205, 147);background-image: url(img/rectangle.svg);margin-right: 0px;padding-right: 10px;padding-left: 10px;padding-top: 10px;background-size: 72px 85px;"><span style="padding: 15px;">{{$enrollment_code[0]}}</span></div>
        <div class="box-code" style="display: inline-block;width: 40px;height: 75px;font-size: 40px;color: rgb(82, 205, 147);background-image: url(img/rectangle.svg);margin-right: 0px;padding-right: 10px;padding-left: 10px;padding-top: 10px;background-size: 72px 85px;"><span style="padding: 15px;">{{$enrollment_code[1]}}</span></div>
        <div class="box-code" style="display: inline-block;width: 40px;height: 75px;font-size: 40px;color: rgb(82, 205, 147);background-image: url(img/rectangle.svg);margin-right: 0px;padding-right: 10px;padding-left: 10px;padding-top: 10px;background-size: 72px 85px;"><span style="padding: 15px;">{{$enrollment_code[2]}}</span></div>
        <div class="box-line" style="display: inline-block;width: 25px;margin-right: 0;margin-left: 10px;margin-bottom: 12px;height: 1px;background: rgb(210, 210, 210);"></div>
        <div class="box-code" style="display: inline-block;width: 40px;height: 75px;font-size: 40px;color: rgb(82, 205, 147);background-image: url(img/rectangle.svg);margin-right: 0px;padding-right: 10px;padding-left: 10px;padding-top: 10px;background-size: 72px 85px;"><span style="padding: 15px;">{{$enrollment_code[3]}}</span></div>
        <div class="box-code" style="display: inline-block;width: 40px;height: 75px;font-size: 40px;color: rgb(82, 205, 147);background-image: url(img/rectangle.svg);margin-right: 0px;padding-right: 10px;padding-left: 10px;padding-top: 10px;background-size: 72px 85px;"><span style="padding: 15px;">{{$enrollment_code[4]}}</span></div>
        <div class="box-code" style="display: inline-block;width: 40px;height: 75px;font-size: 40px;color: rgb(82, 205, 147);background-image: url(img/rectangle.svg);margin-right: 0px;padding-right: 10px;padding-left: 10px;padding-top: 10px;background-size: 72px 85px;"><span style="padding: 15px;">{{$enrollment_code[5]}}</span></div>
    </div>
    <p class="text-valid" style="font-size: 18px;color: rgb(210, 210, 210);">VALID FOR 24 HOURS</p>
    <p class="p-text" style="font-size: 16px;margin-top: 40px;font-weight: 300;">Please, do not share this personal enrolment code as it is associated to your personal Tutella account.</p>
    <div class="logo-app" style="margin-top: 60px;">
        @if($role == 'student')
        <div class="logo-left" style="float: left;margin-left: 60px;">
            <a href="https://itunes.apple.com/us/app/student-tutella/id1435790887">
                <img src="https://tutella-api-domain.tutella.io/apple.png" alt="" style="width: 170px;">
            </a>
        </div>
        <div class="logo-right" style="float: right;margin-right: 60px;display:block;">
            <a href="https://play.google.com/store/apps/details?id=com.tutella.student">
                <img src="https://tutella-api-domain.tutella.io/google-play.png" alt="" style="width: 170px;display:block;">
            </a>
        </div>
        @else
        <div class="logo-left" style="float: left;margin-left: 60px;">
            <a href="https://itunes.apple.com/us/app/leader-tutella/id1435793392">
                <img src="https://tutella-api-domain.tutella.io/apple.png" alt="" style="width: 170px;">
            </a>
        </div>
        <div class="logo-right" style="float: right;margin-right: 60px;display:block;">
            <a href="https://play.google.com/store/apps/details?id=com.tutella.leader">
                <img src="https://tutella-api-domain.tutella.io/google-play.png" alt="" style="width: 170px;display:block;">
            </a>
        </div>
        @endif
    </div>
    <div class="footer" style="display: inline-block;margin-top: 60px;">
        <p class="footer-logo" style="margin-top: 30px;text-align: center;margin-bottom: 0;">
            <a href="#">
                <img class="logo" src="https://tutella-api-domain.tutella.io/tutella-logo.png" alt="" style="max-width: 120px;width: 100%;">
            </a>
        </p>
        <p class="copyright" style="font-size: 12px;font-weight: 300;color: rgb(134, 134, 134);text-align: center;">© 2018 - Tutella. All rights reserved</p>
    </div>
</body>

</html>