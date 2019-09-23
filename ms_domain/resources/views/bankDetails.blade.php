<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            padding: 30px;
        }

        .billing-details-content .billing-details-input {
            width: 500px;
            margin-top: 20px;
        }

        .billing-details-content .billing-details-input .lighter .personal-info-value {
            font-weight: lighter;
        }

        .billing-details-content .billing-details-input .header .personal-info-value {
            font-size: 20px;
        }

        .billing-details-content .billing-details-input .header .personal-info-div {
            font-size: 14px;
        }

        .billing-bank-account-details .billing-bank-account-details-segment {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            width: 40%;
        }

        /* .billing-bank-account-details .billing-bank-account-details-segment-left-column {
            width: 50%;
        } */

        .billing-bank-account-details .billing-bank-account-details-segment-right-column {
            width: 50%;
        }

        .personalInfo {
            margin-bottom: 10px;
        }

        .personalInfo .personal-info-value {
            font-size: 14px;
            font-weight: normal;
        }

        .personalInfo .personal-info-div {
            font-size: 12px;
            font-weight: lighter;
            color: #9B9B9B;
        }

        .header {
            font-weight: bold;
        }

        h1 {
            margin-bottom: 50px;
        }
        
        h3 {
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <div>
        <h1 style="margin-bottom:0px;">Wire Transfer Details</h1>
        <div class="billing-details-content">
            <div class="billing-company-details">
                <h3>Company Details</h3>
                <div class="billing-details-input">
                    <div class="personalInfo">
                        <div class="personal-info-value header">
                            {{$companyDetails->company_name}}
                        </div>
                        <div class="personal-info-div">
                            Company Name
                        </div>
                    </div>
                </div>
                <div class="billing-details-input">
                    <div class="personalInfo">
                        <div class="personal-info-value">
                            {{$companyDetails->company_address}}
                        </div>
                        <div class="personal-info-div">
                            Company Address
                        </div>
                    </div>
                </div>
                <div class="billing-details-input">
                    <div class="personalInfo">
                        <div class="personal-info-value">
                            {{$companyDetails->company_registration_number}}
                        </div>
                        <div class="personal-info-div">
                            Company Registration Number
                        </div>
                    </div>
                </div>
            </div>
            <div class="billing-bank-account-details">
                <h3 style="margin-top:20px">Bank Account Details</h3>
                <div class="billing-details-input">
                    <div class="personalInfo">
                        <div class="personal-info-value header">
                            {{$bankDetails->bank_name}}
                        </div>
                        <div class="personal-info-div">
                            Bank Name
                        </div>
                    </div>
                </div>
                <div class="billing-details-input">
                    <div class="personalInfo">
                        <div class="personal-info-value">
                        {{$bankDetails->bank_address}}
                        </div>
                        <div class="personal-info-div">
                            Bank Address
                        </div>
                    </div>
                </div>
                <div class="billing-bank-account-details-segment">
                    <div class="billing-bank-account-details-segment-left-column">
                        <div class="billing-details-input">
                            <div class="personalInfo" style="width:45%;display:inline-block">
                                <div class="personal-info-value">
                                {{$bankDetails->account_number}}
                                </div>
                                <div class="personal-info-div">
                                    Account Number
                                </div>
                            </div>
                            <div class="personalInfo" style="display:inline-block">
                                <div class="personal-info-value">
                                {{$bankDetails->account_name}}
                                </div>
                                <div class="personal-info-div">
                                    Account Name
                                </div>
                            </div>
                        </div>
                        <div class="billing-details-input">
                            <div class="personalInfo" style="width:45%;display:inline-block">
                                <div class="personal-info-value">
                                {{$bankDetails->sort_code}}
                                </div>
                                <div class="personal-info-div">
                                    Sort Code
                                </div>
                            </div>
                            <div class="personalInfo" style="display:inline-block">
                                <div class="personal-info-value">
                                {{$bankDetails->swift}}
                                </div>
                                <div class="personal-info-div">
                                    SWIFT-BIC Code
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="billing-details-input">
                    <div class="personalInfo">
                        <div class="personal-info-value">
                        {{$bankDetails->iban}}
                        </div>
                        <div class="personal-info-div">
                            IBAN Number
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="margin: 0 auto;">
        <div style='height: 50px; text-align: center; margin-top: 100px;'>
            <img style="height: auto; width: 50px;" src="https://tutella-api-domain.tutella.io/tutella-logo.png" />
            <div style='font-weight: normal'>© Tutella Language Travel 2018. All rights reserved.</div>
        </div>
    </div>
</body>

</html>