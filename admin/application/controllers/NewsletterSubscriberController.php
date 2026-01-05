<?php

use App\Actions\Mailjet\AddSubscriberToMailjet;

/**
 * @property WebshopModel $WebshopModel
 */
class NewsletterSubscriberController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('WebshopModel');
	}

	public function syncNewsletterSubscribersToMailjet(): void
	{
		require_once __DIR__ . '/../Actions/Mailjet/AddSubscriberToMailjet.php';
		$all_newsletter_subscriber = $this->WebshopModel->get_all_newsletter_subscriber();
		$shop_id		=	$this->session->userdata('ShopID');
		foreach($all_newsletter_subscriber as $newsletter_subscriber){
			if(!filter_var($newsletter_subscriber->email, FILTER_VALIDATE_EMAIL)){
				continue;
			}

			(new AddSubscriberToMailjet())->execute($newsletter_subscriber->email, '', 'newsletter_subscriber_website_' . $shop_id);
		}

		echo "success";
		exit;
	}
}
