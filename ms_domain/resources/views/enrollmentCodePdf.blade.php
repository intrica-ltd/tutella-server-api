<!DOCTYPE html>

<html>
<!-- <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600" rel="stylesheet"> -->
<style type="text/css">
    @page { margin: 0; }
    body {
        font-family: 'Source Sans Pro', sans-serif;
        margin: 0;
    }

    .section-1 .banner {
        width: 100%;
        height: 40px;
        background: #52cd94;
        text-align: center;
    }

    .banner .title {
        color: white;
        font-size: 30px;
        font-weight: 600;
    }

    .title.float-left {
        float: left;
        margin-left: 15px;
    }

    .section-1 img {
        width: 100%
    }

    .enrolment-code {
        position: relative;
    }

    .code {
        position: absolute;
        bottom: 53%;
        left: 20%;
    }

    .code>div {
        display: inline-block;
    }

    .number {
        font-size: 100px;
        padding: 10px;
        font-weight: 700;
    }

    hr {
        height: 5px;
        background: black;
        margin-bottom: 18px;
        width: 15px;
    }
</style>

<body>
    <div class="section-1">
        <div class="banner">
            <span class="title float-left">1.</span>
            <span class="title">DOWNLOAD THE APP</span>
        </div>
        <img src="http://tutella-api-domain.devsy.xyz/TUTELLAPOSTER2-1.jpg">
    </div>
<div class="section-1 enrolment-code">
        <div class="banner">
            <span class="title float-left">2.</span>
            <span class="title">ENROL AT YOUR SCHOOL</span>
        </div>
        <img src="http://tutella-api-domain.devsy.xyz/TUTELLAPOSTER2-3.jpg">
        <div class="code">
            <div class="code-left">
                <span class="number">{{$code[0]}}</span>
                <span class="number">{{$code[1]}}</span>
                <span class="number">{{$code[2]}}</span>
                <span class="number">-</span>
                <span class="number">{{$code[3]}}</span>
                <span class="number">{{$code[4]}}</span>
                <span class="number">{{$code[5]}}</span>
            </div>
        </div>
    </div>

    <div class="section-1">

        <div class="banner">
            <span class="title float-left">3.</span>
            <span class="title">SIGN-UP</span>
        </div>
        <img src="http://tutella-api-domain.devsy.xyz/TUTELLAPOSTER2-2.jpg">
    </div>
</body>

</html>
