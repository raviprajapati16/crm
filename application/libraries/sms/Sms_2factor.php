<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sms_2factor extends App_sms
{
    private $api_key;

    private $otp_template_name;

    private $requestURL = 'https://2factor.in/API/V1/';

    public function __construct()
    {
        parent::__construct();

        $this->api_key   = $this->get_option('2factor', 'api_key');
        $this->otp_template_name   = $this->get_option('2factor', 'otp_template_name');

        $this->add_gateway('2factor', [
            'name'    => '2 Factor',
            'info'    => '',
            'options' => [
                [
                    'name'  => 'api_key',
                    'label' => 'API KEY',
                ],
                [
                    'name'  => 'otp_template_name',
                    'label' => 'OTP SMS Template Name',
                ]
            ],
        ]);
    }

    public function send_otp($phone, $otp)
    {
        $otpSendURL = $this->requestURL . $this->api_key . '/SMS/' . $phone . '/' . $otp . '/' . $this->otp_template_name;
        try {
            $response = $this->client->request('GET', $otpSendURL, [
                'headers' => [
                    'X-Version' => '1',
                ],
            ]);
            $result = json_decode($response->getBody());
            if (isset($result->Status)) {
                return $result;
            }
        } catch (\Exception $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents(), true);
            return $response;
        }
        return false;
    }

    public function verify_otp($phone, $user_otp)
    {
        $otpSendURL = $this->requestURL . $this->api_key . '/SMS/VERIFY3/' . $phone . '/' . $user_otp;
        try {
            $response = $this->client->request('GET', $otpSendURL, [
                'headers' => [
                    'X-Version' => '1',
                ],
            ]);
            $result = json_decode($response->getBody());
            if (isset($result->Status)) {
                return $result;
            }
        } catch (\Exception $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents(), true);
            return $response;
        }
        return false;
    }
}
