<?php

//Initialise variable 
$errorCode = "";
$tradeNo = "";
$merTradeNo = "";
$msg = "";
$tradeStatus = "";
$timestamp = "";
$sign = "";
$mf1 = "";
$mf2 = "";
$mf3 = "";
$mf4 = "";
$mf5 = "";
$apiKey = "";
$sitePath = "";
$generatedSignature = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    echo '<pre>';
    print_r($_POST);
    echo '</pre>';

    // Retrieve form data
    $errorCode = $_POST["errorCode"];
    $tradeNo = $_POST["tradeNo"];
    $merTradeNo = $_POST["merTradeNo"];
    $msg = $_POST["msg"];
    $tradeStatus = $_POST["tradeStatus"];
    $timestamp = $_POST["timestamp"];
    $sign = $_POST["sign"];
    $mf1 = $_POST["mf1"];
    $mf2 = $_POST["mf2"];
    $mf3 = $_POST["mf3"];
    $mf4 = $_POST["mf4"];
    $mf5 = $_POST["mf5"];
    $apiKey= $_POST["apiKey"];
    $sitePath= $_POST["sitePath"];

    $signGeneration= $sitePath."merTradeNo=".$merTradeNo."&msg=".$msg.
    "&errorCode=".$errorCode."&tradeNo=".$tradeNo."&tradeStatus=".$tradeStatus.
    "&timestamp=".$timestamp."&mf1=".$mf1."&mf2=".$mf2.
    "&mf3=".$mf3."&mf4=".$mf4."&mf5=".$mf5; 

    $encrypytedSignGeneration= hash_hmac('sha256',$signGeneration,$apiKey,true);

    $generatedSignature= base64_encode($encrypytedSignGeneration);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify response signature</title>
    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f7f7f7;
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: center;
            margin-top: 50px;
            margin-bottom: 30px;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group label {
            font-weight: bold;
            color: #555;
        }

        .form-control {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px 12px;
        }

        .btn-primary {
            background-color: #336699;
            border-color: #336699;
            padding: 8px 20px;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #2b5b94;
            border-color: #2b5b94;
        }
    </style>
</head>
<body>

<h1>Verify response signature</h1>
<div class="container mt-5">
    <form method="POST" id="id" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <div class="form-group">
            <label for="errorCode">errorCode:</label>
            <input type="text" name="errorCode" id="errorCode" required class="form-control" value="<?php echo $errorCode; ?>">
        </div>

        <div class="form-group">
            <label for="tradeNo">tradeNo:</label>
            <input type="text" name="tradeNo" id="tradeNo" required class="form-control" value="<?php echo $tradeNo; ?>">
        </div>

        <div class="form-group">
            <label for="merTradeNo">merTradeNo:</label>
            <input type="text" name="merTradeNo" id="merTradeNo" required class="form-control" value="<?php echo $merTradeNo; ?>">
        </div>

        <div class="form-group">
            <label for="msg">msg:</label>
            <input type="text" name="msg" id="msg" required class="form-control" value="<?php echo $msg; ?>">
        </div>

        <div class="form-group">
            <label for="tradeStatus">tradeStatus:</label>
            <input type="text" name="tradeStatus" id="tradeStatus" required class="form-control" value="<?php echo $tradeStatus; ?>">
        </div>

        <div class="form-group">
            <label for="timestamp">timestamp:</label>
            <input type="text" name="timestamp" id="timestamp" required class="form-control" value="<?php echo $timestamp; ?>">
        </div>

        <div class="form-group">
            <label for="sign">sign (from request):</label>
            <input type="text" name="sign" id="sign" required class="form-control" value="<?php echo $sign; ?>">
        </div>

        <div class="form-group">
            <label for="mf1">mf1:</label>
            <input type="text" name="mf1" id="mf1" class="form-control" value="<?php echo $mf1; ?>">
        </div>

        <div class="form-group">
            <label for="mf2">mf2:</label>
            <input type="text" name="mf2" id="mf2" class="form-control" value="<?php echo $mf2; ?>">
        </div>

        <div class="form-group">
            <label for="mf3">mf3:</label>
            <input type="text" name="mf3" id="mf3" class="form-control" value="<?php echo $mf3; ?>">
        </div>

        <div class="form-group">
            <label for="mf4">mf4:</label>
            <input type="text" name="mf4" id="mf4" class="form-control" value="<?php echo $mf4; ?>">
        </div>

        <div class="form-group">
            <label for="mf5">mf5:</label>
            <input type="text" name="mf5" id="mf5" class="form-control" value="<?php echo $mf5; ?>">
        </div>

        <div class="form-group">
            <label for="apiKey">apiKey:</label>
            <input type="text" name="apiKey" id="apiKey" required class="form-control" value="<?php echo $apiKey; ?>">
        </div>

        <div class="form-group">
            <label for="sitePath">Notify URL:</label>
            <input type="text" name="sitePath" id="sitePath" required class="form-control" value="<?php echo $sitePath; ?>">
        </div>

        <input type="submit" value="Submit" class="btn btn-primary"> 
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST") { ?>
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                Signature Verification Result
            </div>
            <div class="card-body">
                <p><strong>Query String:</strong><br><?php echo $signGeneration; ?></p>
                <p><strong>Generated Signature:</strong> <span style="color:blue;"><?php echo $generatedSignature; ?></span></p>
                <p><strong>Provided Signature:</strong> <span style="color:darkred;"><?php echo $sign; ?></span></p>
                
                <?php if($generatedSignature==$sign) { ?>
                    <p class="mt-3 text-success font-weight-bold" style="font-size:20px;">✅ Signature matched!</p>
                <?php } else { ?>
                    <p class="mt-3 text-danger font-weight-bold" style="font-size:20px;">❌ Verification failed! Signature is different!</p>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>

<!-- Add Bootstrap JavaScript -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
