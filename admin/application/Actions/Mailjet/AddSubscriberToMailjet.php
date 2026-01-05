<?php

namespace App\Actions\Mailjet;

use Mailjet\Client as MailjetClient;
use Mailjet\Resources as MailjetResources;

class AddSubscriberToMailjet
{
	public function execute($email, $language = '', $source = ''){
		$mj = new MailjetClient(MAILJET_API_KEY, MAILJET_API_SECRET);

		$body = [
			'Action' => "addnoforce",
			'Email' => $email,
			'Properties' => [
				'source' => $source,
				'language' => $language,
			],
		];

		$response = $mj->post(MailjetResources::$ContactslistManagecontact, ['id' => 10998, 'body' => $body]);

		return $response->success();
	}
}
