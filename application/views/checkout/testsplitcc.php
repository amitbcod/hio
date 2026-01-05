<?php $this->load->view('common/header');

?>
<!-- <form method="post" name="redirect" action="https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction"> -->
<!-- <form method="post" name="redirect" action="https://apitest.ccavenue.com/apis/servlet/DoWebTrans"> -->
<form method="post" name="redirect" action="https://180.179.175.17/apis/servlet/DoWebTrans">
    <?php
    echo "<input type=hidden name=encRequest value=$encrypted_data>";
    echo "<input type=hidden name=access_code value=$access_code>";
    echo "<input type=hidden name=Access_code value=$access_code>";
    echo "<input type=hidden name=Command value=createSplitPayout>";
    echo "<input type=hidden name=command value=createSplitPayout>";
    echo "<input type=hidden name=request_type value=JSON>";
    echo "<input type=hidden name=split_tdr_charge_type value=M>";
    echo "<input type=hidden name=reference_no value=$bank_ref_no>";
    echo "<input type=hidden name=split_data_list value=$split_data_list>";
    echo "<input type=hidden name=merComm value=$merComm>";
    echo "<input type=hidden name=split_data value=Yes>";
    echo "<input type=hidden name=version value=1.2>";
    ?>
</form>
<?php

// URL:-https://180.179.175.17/apis/servlet/DoWebTrans
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