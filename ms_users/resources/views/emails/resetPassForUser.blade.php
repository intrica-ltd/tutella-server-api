<!DOCTYPE html>
<html>

<head>
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

        body {
            font-family: 'Source Sans Pro', sans-serif;
            width: 670px;
            margin: 0 auto;
            text-align: center;
        }

        h1 {
            font-weight: normal;
        }

        ._img {
            width: 100%;
            margin-top: 100px;
        }

        .mail-logo {
            text-align: center;
            height: 100px;
        }

        .green_bg {
            margin-left: 30px;
            margin-right: 30px;
            border: 0.5px solid rgb(82, 205, 147);
        }

        .p-text {
            font-size: 24px;
            font-weight: 300;
        }

        .p-text a {
            color: rgb(82, 205, 147);
            text-decoration: none;
            font-weight: 400;
        }

        .btn_green {
            width: 180px;
            background: rgb(82, 205, 147);
            border-radius: 23px;
            font-size: 14px;
            font-weight: 300;
            color: white;
            border-width: 0;
            padding: 13px;
        }

        .btn_green:focus {
            outline: 0;
        }

        .btn_green:hover {
            cursor: pointer;
            background: rgb(71, 177, 127);
        }

        .grey_bg {
            margin: 40px 30px 0 30px;
            border: 0.5px solid rgb(235, 235, 235);
        }

        .footer {
            text-align: left;
        }

        .footer .small_text {
            font-size: 10px;
            font-weight: 300;
            color: rgb(117, 117, 117);
            margin-left: 30px;
            margin-bottom: 0;
            line-height: 0px;
        }

        .footer a {
            font-size: 10px;
            font-weight: 300;
            color: rgb(210, 210, 210);
            margin-left: 30px;
        }

        .footer .footer-logo {
            margin-top: 30px;
            text-align: center;
            margin-bottom: 0;
        }

        .footer-logo .logo {
            max-width: 120px;
            width: 100%;
        }

        .footer .copyright {
            font-size: 12px;
            font-weight: 300;
            color: rgb(134, 134, 134);
            text-align: center;
        }
    </style>
    <title>Email confirmation for the School Admin</title>
</head>

<body style="font-family: 'Source Sans Pro', sans-serif;width: 670px;margin: 0 auto;text-align: center;">
    <div class="_img" style="width: 100%;">
        <img src="https://tutella-api-domain.tutella.io/mail-logo.png" alt="" class="mail-logo" style="text-align: center;height: 100px;margin:auto;">
    </div>
    <h1 style="font-weight: normal;">The School Admin has changed your password!</h1>
    <hr class="green_bg" style="margin-left: 30px;margin-right: 30px;border: 0.5px solid rgb(82, 205, 147);">
    <p class="p-text" style="font-size: 24px;font-weight: 300;">This email confirms that your password has been changed.</p>
    <p class="p-text" style="font-size: 24px;font-weight: 300;">Use the following credentials in order to login<br>on your mobile app:</p>
    <p class="p-text" style="font-size: 20px;font-weight: 600;">Username: {{$email}}</p>
    <p class="p-text" style="font-size: 20px;font-weight: 600;">Password: {{$code}}</p>
    
    <hr class="grey_bg" style="margin: 40px 30px 0 30px;border: 0.5px solid rgb(235, 235, 235);">
    <div class="footer" style="text-align: left;">
        <p class="footer-logo" style="margin-top: 30px;text-align: center;margin-bottom: 0;">
            <a href="#" style="font-size: 10px;font-weight: 300;color: rgb(210, 210, 210);margin-left: 30px;">
                <img class="logo" src="https://tutella-api-domain.tutella.io/tutella-logo.png" alt="" style="max-width: 120px;width: 100%;">
            </a>
        </p>
        <p class="copyright" style="font-size: 12px;font-weight: 300;color: rgb(134, 134, 134);text-align: center;">© 2018 - Tutella. All rights reserved</p>
    </div>
</body>

</html>