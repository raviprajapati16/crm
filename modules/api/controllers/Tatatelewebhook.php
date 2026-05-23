<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require __DIR__.'/REST_Controller.php';

class Tatatelewebhook extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tatatel_model');
        $this->load->model('Api_model');
        $this->load->model('Leads_model');
    }


    public function call_hangup_missed_or_answered_post(){
        log_activity("call_hangup_missed_or_answered_post request received.");
        $data                          = json_decode(file_get_contents("php://input"));
        if(isset($data)) {
            log_activity(json_encode($data));
        }

        $uuid                          = isset($data->uuid) ? $data->uuid : "";
        $call_to_number                = isset($data->call_to_number) ? $data->call_to_number : "";
        $caller_id_number              = isset($data->caller_id_number) ? $data->caller_id_number : "";
        $start_stamp                   = isset($data->start_stamp) ? $data->start_stamp : "";
        $answer_stamp                  = isset($data->answer_stamp) ? $data->answer_stamp : "";
        $end_stamp                     = isset($data->end_stamp) ? $data->end_stamp : "";
        $hangup_cause                  = isset($data->hangup_cause) ? $data->hangup_cause : "";
        $billsec                       = isset($data->billsec) ? $data->billsec : "";
        $digits_dialed                 = isset($data->digits_dialed) ? $data->digits_dialed : "";
        $direction                     = isset($data->direction) ? $data->direction : "";
        $duration                      = isset($data->duration) ? $data->duration : "";
        // $answered_agent                = isset($data->answered_agent) ? $data->answered_agent : "";
        $answered_agent                = json_encode(isset($data->answered_agent) ? $data->answered_agent : "");
        $answered_agent_name           = isset($data->answered_agent_name) ? $data->answered_agent_name : "";
        $answered_agent_number         = isset($data->answered_agent_number) ? $data->answered_agent_number : "";
        // $missed_agent                  = isset($data->missed_agent  ) ? $data->missed_agent   : "";
        $missed_agent                  = json_encode(isset($data->missed_agent) ? $data->missed_agent : "");
        // $call_flow                     = isset($data->call_flow) ? $data->call_flow : "";
        $call_flow                     = json_encode(isset($data->call_flow) ? $data->call_flow : "");
        $broadcast_lead_fields         = isset($data->broadcast_lead_fields) ? $data->broadcast_lead_fields : "";
        $recording_url                 = isset($data->recording_url) ? $data->recording_url : "";
        $call_status                   = isset($data->call_status) ? $data->call_status : "";
        $call_id                       = isset($data->call_id) ? $data->call_id : "";
        $outbound_sec                  = isset($data->outbound_sec) ? $data->outbound_sec : "";
        $agent_ring_time               = isset($data->agent_ring_time) ? $data->agent_ring_time : "";
        // $billing_circle                = isset($data->billing_circle) ? $data->billing_circle : "";
        $billing_circle                = json_encode(isset($data->billing_circle) ? $data->billing_circle : "");
        $call_connected                = isset($data->call_connected) ? $data->call_connected : "";
        $aws_call_recording_identifier = isset($data->aws_call_recording_identifier) ? $data->aws_call_recording_identifier : "";
        $customer_no_with_prefix       = isset($data->customer_no_with_prefix) ? $data->customer_no_with_prefix : ""; //single number
        $leadid                        = "";
        if ($customer_no_with_prefix) {
            $last_10_digits = substr($customer_no_with_prefix, -10);
            $lead_ids = $this->Leads_model->get_lead_id_by_phone_numbers($last_10_digits);

            if (!empty($lead_ids)) {
                $leadid = $lead_ids[0];
            }
        }

        if(!empty($uuid)){
            $call = array(
                "leadid"=>$leadid,
                "uuid"=>$uuid,
                "call_to_number"=>$call_to_number,
                "caller_id_number"=>$caller_id_number,
                "start_stamp"=>$start_stamp,
                "answer_stamp"=>$answer_stamp,
                "end_stamp"=>$end_stamp,
                "hangup_cause"=>$hangup_cause,
                "billsec"=>$billsec,
                "digits_dialed"=>$digits_dialed,
                "direction"=>$direction,
                "duration"=>$duration,
                "answered_agent"=>$answered_agent,
                "answered_agent_name"=>$answered_agent_name,
                "answered_agent_number"=>$answered_agent_number,
                "missed_agent"=>$missed_agent,
                "call_flow"=>$call_flow,
                "broadcast_lead_fields"=>$broadcast_lead_fields,
                "recording_url"=>$recording_url,
                "call_status"=>$call_status,
                "call_id"=>$call_id,
                "outbound_sec"=>$outbound_sec,
                "agent_ring_time"=>$agent_ring_time,
                "billing_circle"=>$billing_circle,
                "call_connected"=>$call_connected,
                "aws_call_recording_identifier"=>$aws_call_recording_identifier,
                "customer_no_with_prefix"=>$customer_no_with_prefix,
            );
            if($this->Tatatel_model->insert_calldata($call)){
               $this->response(array(
                "status" =>1,
                "messages" =>"created",),REST_Controller::HTTP_OK);
            }else{
                $this->response(array(
                    "status" =>0,
                    "messages" =>"failed",),REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            $this->response(array(
                "status" =>0,
                "messages" =>"Missing Call uuid.",
            ),REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
