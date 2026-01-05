<?php

use App\Actions\AddSubscriberToMailjetList;
use Mailjet\Client as MailjetClient;
use Mailjet\Resources as MailjetResources;

class MailjetSignupController extends CI_Controller
{
    public function index(){
        $data = json_decode(file_get_contents('php://input'));
        if(empty($data->email)) {
            exit(json_encode(['message' => 'error: not all fields entered']));
        }
        $result = $this->signup($data->email, $this->session->userdata('lcode') ?? 'en');

        if ($result) {
            exit(json_encode(['message' => 'success']));
        }
        exit(json_encode(['message' => 'error']));
    }

    public function signup($email, $language){
        $mj = new MailjetClient(MAILJET_API_KEY, MAILJET_API_SECRET);

        $body = [
            'Action' => "addnoforce",
            'Email' => $email,
            'Properties' => [
                'source' => 'website-popup-signup',
                'language' => $language,
            ],
        ];

        $response = $mj->post(MailjetResources::$ContactslistManagecontact, ['id' => 10998, 'body' => $body]);

        return $response->success();
    }
}
