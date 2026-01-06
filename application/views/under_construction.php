<!DOCTYPE html>
<html lang="en">
<head>
    <title>Website Under Construction</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link rel="icon" href="<?=BASE_URL . 'uploads/favicon.ico'?>">
    <style>
        .under-construcation-page {
            background: #506BFA;
            background: linear-gradient(90deg, #506BFA, #7257dc);
            height: 100%;
            color: #fff;
            font-family:'Roboto';
        }


        @media (min-width: 1200px){
            .container {
                max-width: 1140px;
            }}
        @media (min-width: 992px)}
        .container {
            max-width: 960px;
        }}

        @media (min-width: 768px){
            .container {
                max-width: 720px;
            }}
        @media (min-width: 576px){
            .container {
                max-width: 540px;
            }}
        .container {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
        @media (min-width: 768px){
            .col-md-12 {
                -webkit-box-flex: 0;
                -ms-flex: 0 0 100%;
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        .row {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        .under-construcation-page .thankyou-page.under-construcation-inner.text-center {
            padding-top: 10%;
        }

        .thankyou-page {
            width: 100%;
            text-align: center;
            padding: 40px 20px;
            height: auto;
        }
        .text-center {
            text-align: center!important;
        }
        img {
            vertical-align: middle;
            border-style: none;
        }
        .under-construcation-page h1 {
            font-family: "Anton";
            padding-top: 20px;
            margin-top: 0;
        }
        .h6, h6 {
            font-size: 1rem;
            margin-top:10px;
        }
        .h1, h1 {
            font-size: 2.5rem;
        }
        .h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
            margin-bottom: 0.5rem;
            font-family: inherit;
            font-weight: 500;
            line-height: 1.2;
            color: inherit;
            margin-bottom:0;
        }
    </style>

</head>
<body class="under-construcation-page">
<div class="site-wrap ">
    <div class="container">
        <div class="col-md-12">
            <div class="row">

                <div class="thankyou-page under-construcation-inner  text-center">
                    <img src="public/images/under-contrucation.png">
                    <h1><?=lang('website_under_construction')?></h1>
                    <h6><?=lang('please_forgive_the_inconvenience')?></h6>
                </div><!-- under-contruction page -->

            </div>
        </div>
    </div>
</div>

</body>
</html>
