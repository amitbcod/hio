<?php $this->load->view('common/header');
// print_r($_POST);
// die();
?>
<form method="post" name="redirect" action="https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction">
    <?php
    echo "<input type=hidden name=encRequest value=$encrypted_data>";
    echo "<input type=hidden name=access_code value=$access_code>";
    echo "<input type=hidden name=Access_code value=$access_code>";
    echo "<input type=hidden name=Command value=initiateTransaction>";
    echo "<input type=hidden name=command value=initiateTransaction>";
    echo "<input type=hidden name=request_type value=JSON>";
    echo "<input type=hidden name=split_tdr_charge_type value=M>";
    echo "<input type=hidden name=reference_no value=$reference_no>";
    echo "<input type=hidden name=split_data_list value=$split_data_list>";
    echo "<input type=hidden name=merComm value=$merComm>";
    echo "<input type=hidden name=split_data value=Yes>";
    echo "<input type=hidden name=version value=1.2>";
    ?>
</form>
<?php
$post_array = array(
    'encRequest' => $encrypted_data,
    'access_code' => $access_code

);
// $curl_handle = curl_init();
// curl_setopt($curl_handle, CURLOPT_URL, 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction');
// curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($curl_handle, CURLOPT_POST, 1);
// curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
// curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
// curl_setopt($curl_handle, CURLOPT_POSTFIELDS, urldecode(http_build_query($post_array)));
// $buffer = curl_exec($curl_handle);

// curl_close($curl_handle);
// print_r($buffer);
// die();
// return json_decode($buffer);

echo "Please wait While we redirect you to payment gateway"; ?>
<script language='javascript'>
    document.redirect.submit();
</script>



<?php $this->load->view('common/footer');
echo "Please wait While we redirect you to payment gateway";
die(); ?>

<script>

</script>

</body>

</html>