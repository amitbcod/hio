<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="viewport" content="target-densitydpi=device-dpi, initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="Cache-control" content="public">
    <meta name="robots" content="index,follow">
    <?php
    $FinalPageTitle = (isset($PageTitle) && $PageTitle != '') ?  $PageTitle : SITE_TITLE;
    $PageMetaTitle = (isset($PageMetaTitle) && $PageMetaTitle != '') ? $PageMetaTitle : '';
    $PageMetaDesc = (isset($PageMetaDesc) && $PageMetaDesc != '') ? strip_tags($PageMetaDesc) : '';
    $ProductData = (isset($ProductData) && $ProductData != '') ? $ProductData : '';
    $variableCommonHeader = array('FinalPageTitle' => $FinalPageTitle, 'PageMetaTitle' => $PageMetaTitle, 'PageMetaDesc' => $PageMetaDesc, 'ProductData' => $ProductData);

    $this->template->load('common_for_all/header_meta_details', $variableCommonHeader);

    // $this->template->load('common_for_all/header_script');
    ?>

    <?php $this->template->load('common_for_all/favicon'); ?>
    <?php $this->template->load('common_for_all/header_link_details'); ?>

    <style>
        .wrap-modal-slider {
            padding: 0 30px;
            opacity: 0;
            transition: all 0.3s;
        }

        .wrap-modal-slider.open {
            opacity: 1;
        }

        .your-class {
            width: 100%;
            margin: 0 auto;
            position: relative;
        }

        .your-class .product-item {
            border: 1px solid #f9f9f9;
            margin: 5px;
        }

        /*#e5e5e5*/
        .your-class .pi-img-wrapper {
            position: relative;
            max-width: 210px;
            margin: 0 auto;
            text-align: center;
            box-shadow: 0px 8px 6px -6px rgba(0, 0, 0, 0.2)
        }

        .your-class .pi-img-wrapper img {
            max-width: 100%;
        }

        /*---------- arrows ---------*/
        .slick-arrow {
            z-index: 2 !important;
            width: 25px !important;
            height: 25px !important;
        }

        .slick-arrow:before {
            content: "" !important;
            width: 100% !important;
            height: 100% !important;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 1 !important;
        }

        /*
        .slick-next:before{
        background: url('http://i.imgur.com/TZDsPC0.png')!important;
        background-size: contain!important;
        }
        .slick-prev:before{
            background: url('http://i.imgur.com/AKjTWvT.png')!important;
            background-size: contain!important;
        }
        .slick-next{ right:-25px!important;}
        .slick-prev{ left:-25px!important;}
        */
        /* Dots */
        .slick-dotted.slick-slider {
            margin-bottom: 30px;
        }

        .slick-dots {
            position: absolute;
            bottom: -25px;
            display: block;
            width: 100%;
            padding: 0;
            margin: 0;
            list-style: none;
            text-align: center;
        }

        .slick-dots li {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
            margin: 0 5px;
            padding: 0;
            cursor: pointer;
        }

        .slick-dots li button {
            font-size: 0;
            line-height: 0;
            display: block;
            width: 20px;
            height: 20px;
            padding: 5px;
            cursor: pointer;
            color: transparent;
            border: 0;
            outline: none;
            background: transparent;
        }

        .slick-dots li button:hover,
        .slick-dots li button:focus {
            outline: none;
        }

        .slick-dots li button:hover:before,
        .slick-dots li button:focus:before {
            opacity: 1;
        }

        .slick-dots li button:before {
            font-family: 'slick';
            font-size: 6px;
            line-height: 20px;
            position: absolute;
            top: 0;
            left: 0;
            width: 20px;
            height: 20px;
            content: '•';
            text-align: center;
            opacity: .25;
            color: black;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .slick-dots li.slick-active button:before {
            opacity: .75;
            color: black;
        }
    </style>
   <script type='text/javascript'>
  window.smartlook||(function(d) {
    var o=smartlook=function(){ o.api.push(arguments)},h=d.getElementsByTagName('head')[0];
    var c=d.createElement('script');o.api=new Array();c.async=true;c.type='text/javascript';
    c.charset='utf-8';c.src='https://web-sdk.smartlook.com/recorder.js';h.appendChild(c);
    })(document);
    smartlook('init', 'ad14cb23dc183738197c5fe19645546beb106e3d', { region: 'eu' });
</script>
    <!-- Global site tag (gtag.js) - Google Ads: 860292844 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-11457009026"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        //gtag('config', 'AW-828646846');
        gtag('config', 'AW-11457009026');
        
    </script>



    <!-- Event snippet for Magazine Purchase conversion page (Thank you page) --> 
    <?php 

        if ($this->router->fetch_class()=='CheckoutController' && $this->router->fetch_method()=='success' ) {
            $transactionNewId = '';
            if (isset($_GET['sessionId'])) {
              $transactionNewId=base64_decode($_GET['keys']);
            } elseif ($_GET['key']) {
              $transactionNewId=base64_decode($_GET['key']);
            }
    ?>

            <script> gtag('event', 'conversion', { 'send_to': 'AW-11457009026/G20PCIC5iYMZEIKrkNcq', 'transaction_id': '<?=$transactionNewId?>' }); </script>
    <?php }  ?>



    <!-- Event snippet for Indiamags Purchase conversion page -->

    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-36794290-1']);
        _gaq.push(['_trackPageview']);
        (function() {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();
    </script>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-NN96PH7');
    </script>
    <!-- End Google Tag Manager -->

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-RE88QNP87J"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-RE88QNP87J');
    </script>

    <script>
        (function(h, o, t, j, a, r) {
            h.hj = h.hj || function() {
                (h.hj.q = h.hj.q || []).push(arguments)
            };
            h._hjSettings = {
                hjid: 1684296,
                hjsv: 6
            };
            a = o.getElementsByTagName('head')[0];
            r = o.createElement('script');
            r.async = 1;
            r.src = t + h._hjSettings.hjid + j + h._hjSettings.hjsv;
            a.appendChild(r);
        })(window, document, 'https://static.hotjar.com/c/hotjar-', '.js?sv=');
    </script>

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {},
            Tawk_LoadStart = new Date();
        (function() {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/65421c57a84dd54dc48766f6/1he53tecq';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
    <!--End of Tawk.to Script-->
</head>

<body class="ecommerce">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NN96PH7" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <?php $this->load->view('common/navbar'); ?>