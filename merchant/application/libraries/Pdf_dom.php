<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//Github link https://github.com/hanzzame/ci3-pdf-generator-library

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf_dom extends Dompdf {
	private $CI;

	public function __construct() {
        parent::__construct();
		$this->CI =& get_instance();
		$this->CI->load->library('S3_filesystem');
    }

	public function create($html,$filename)
	{
		// Added this for displaying image in https sites
	    $options = new Options();
	    $options->set('isRemoteEnabled', TRUE);
	    $dompdf = new Dompdf($options);
	    $context = stream_context_create([
	    	'ssl' => [
	    		'verify_peer' => FALSE,
	    		'verify_peer_name' => FALSE,
	    		'allow_self_signed'=> TRUE
	    	]
	    ]);
	    $dompdf->setHttpContext($context);

		$filename='invoice-'.$filename.'-'.time().'.pdf'; //invoice_no.pdf
	    $dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');//landscape
	    $dompdf->render();

	    $pdf_gen = $dompdf->output();
		$this->CI->s3_filesystem->put($pdf_gen, 'invoices' . '/' . $filename);
	    return $filename;
  }

  // create by shop working
	public function createbyshop($html,$filename,$shopid)
	{
		// Added this for displaying image in https sites
	    $options = new Options();
	    $options->set('isRemoteEnabled', TRUE);
	    $dompdf = new Dompdf($options);
	    $context = stream_context_create([
	    	'ssl' => [
	    		'verify_peer' => FALSE,
	    		'verify_peer_name' => FALSE,
	    		'allow_self_signed'=> TRUE
	    	]
	    ]);
	    $dompdf->setHttpContext($context);

		$filename='invoice-'.$filename.'-'.time().'.pdf'; //invoice_no.pdf
	    $dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');//landscape
	    $dompdf->render();

	    $pdf_gen = $dompdf->output();
		$this->CI->s3_filesystem->put($pdf_gen, 'invoices' . '/' . $filename);
	    return $filename;
  }

  // create by shop working
	public function createbyshopB2b($html,$filename,$shopid)
	{
		// Added this for displaying image in https sites
	    $options = new Options();
	    $options->set('isRemoteEnabled', TRUE);
	    $dompdf = new Dompdf($options);
	    $context = stream_context_create([
	    	'ssl' => [
	    		'verify_peer' => FALSE,
	    		'verify_peer_name' => FALSE,
	    		'allow_self_signed'=> TRUE
	    	]
	    ]);
	    $dompdf->setHttpContext($context);

		$filename='invoice-'.$filename.'-'.time().'.pdf'; //invoice_no.pdf
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');//landscape
	    $dompdf->render();

	    $pdf_gen = $dompdf->output();

		require_once(APPPATH . '/libraries/S3_filesystem.php');
		$s3_filesystem = new S3_filesystem(['bucket' => get_s3_bucket($shopid)]);
		$s3_filesystem->put($pdf_gen, 'invoices' . '/' . $filename);

	    return $filename;
  }

	// start delivery
  public function createbyPrintingSlip($html,$name)
	{
		// Added this for displaying image in https sites
	    $options = new Options();
	    $options->set('isRemoteEnabled', TRUE);
	    $dompdf = new Dompdf($options);
	    $context = stream_context_create([
	    	'ssl' => [
	    		'verify_peer' => FALSE,
	    		'verify_peer_name' => FALSE,
	    		'allow_self_signed'=> TRUE
	    	]
	    ]);
	    $dompdf->setHttpContext($context);
		$filename=$name.' Delivery Slip.pdf'; //invoice_no.pdf
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A3', 'landscape');//landscape/portrait
	    $dompdf->render();

	    $pdf_gen = $dompdf->output();
	   	$dompdf->stream($filename.'.pdf',array("Attachment"=>1)); // 1 - Downlaod 0 - View
	    return $filename;
  }

  // b2b issue pdf generate
  public function createbyshopIssue($html,$filename,$shopid)
	{
		// Added this for displaying image in https sites
	    $options = new Options();
	    $options->set('isRemoteEnabled', TRUE);
	    $dompdf = new Dompdf($options);
	    $context = stream_context_create([
	    	'ssl' => [
	    		'verify_peer' => FALSE,
	    		'verify_peer_name' => FALSE,
	    		'allow_self_signed'=> TRUE
	    	]
	    ]);
	    $dompdf->setHttpContext($context);

		//$folderpath=SIS_SERVER_PATH.'/shop'.$shopid.INVOICES_PATH;

		$filename=$filename; //invoice_no.pdf
		// $filename='invoice-'.$filename.'-'.time().'.pdf'; //invoice_no.pdf


	    $dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');//landscape
	    $dompdf->render();

	    $pdf_gen = $dompdf->output();

	    /*file_put_contents($folderpath.$filename, $pdf_gen); // save file in invoice folder
		$this->CI->s3_filesystem->put($pdf_gen, 'invoices' . '/' . $filename);*/

		require_once(APPPATH . '/libraries/S3_filesystem.php');
		$s3_filesystem = new S3_filesystem(['bucket' => get_s3_bucket($shopid)]);
		$s3_filesystem->put($pdf_gen, 'invoices' . '/' . $filename);

		$dompdf->stream($filename,array("Attachment"=>0));
	    // return $filename;
  }

  //end b2b order issue
}
