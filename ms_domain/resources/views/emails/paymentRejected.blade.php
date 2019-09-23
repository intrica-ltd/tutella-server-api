<!DOCTYPE html>
<html>
<head>
</head>
<body>
    <div style="text-align: center; width: 500px; margin: 0 auto; font-family: 'Source Sans Pro',sans-serif">
        <div style='padding-bottom: 20px; margin-bottom: 20px;  border-bottom: 2px solid #eee'>
            <div><img style="height: 30px; width: auto;" src="https://tutella-api-domain.tutella.io/tutella-logo.png"/></div>
            <div style='font-weight: bold;font-size:20px;padding:20px 0px;'>Your payment has been rejected!</div>
            <div style='font-weight: normal; font-size: 14px'>
                The Administrator has rejected your payment. <br> Below is the reason for rejecting:
            </div>
            <div style='font-weight: normal; font-size: 16px; border: 1px solid #efefef; padding: 15px; margin-top: 15px;'>
                {{$reason}}
            </div>
        </div>
        <div>
            <span style='font-weight: bold; font-size: 12px;'>
                Visit Our Page
            </span>
        </div>
    </div>
</body>

</html>