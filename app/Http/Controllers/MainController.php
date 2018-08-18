<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class MainController extends Controller
{

    private $auth_token = '82e042f14ab2db018f61bb3fd913ef54';
    public  $zoho_url = 'https://www.zohoapis.com/crm/v2/';
    public $who_id = '3463172000000193155';

    // Запрос к API с помощью CURL
    public function curl_connect($moduleName,$fields=""){
        $ch = curl_init($this->zoho_url.$moduleName);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type:application/json',
                "Authorization:".$this->auth_token,
            ]
        );

        if ($fields) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }
        $result = curl_exec($ch);

        return $result;
    }

    //Добавление задачи к сделке
    public function addTask($who_id, $module,  $what_id){
        $data=[
            'data' => [
                [
                    'Subject' => 'Call',
                    'Due_Date' =>  Carbon::now()->addDays(3)->toDateString(),
                    'Status' => 'Deferred',
                    'Who_Id' => $who_id,
                    '$se_module' => $module,
                    'What_Id' => $what_id
                ]
            ]
        ];

        $fields = json_encode($data);
        $task_result =  $this->curl_connect("Tasks",$fields);


    }

    // Создание сделки
    public function addDeal(){

     $data=[
         'data' =>
             [
               [
                 'Owner' =>
                   [
                     'id' => $this->getOwnerid(),
                   ],
                    'Closing_Date' => Carbon::now()->toDateString(),
                    'Deal_Name' => 'testdeal',
                    'Expected_Revenue' => 50000,
                    'Stage' => 'Negotiation_Review',
                    'Account_Name' =>
                     [
                       'id' => '3463172000000182005',
                     ],
                     'Amount' => 50000,
                     'Probability' => 75,
                     ],
                ],
          ];

        $fields = json_encode($data);

        $deal_result =  $this->curl_connect("Potentials",$fields);
        $deal_result_array = json_decode($deal_result,true);

        $deal_insert_id = $deal_result_array['data'][0]['details']['id'];

        $this->addTask($this->who_id,'Deals',$deal_insert_id);

        return redirect()->back()->with('message','Deal and Task created');
    }

    public function getOwnerid(){
            $users = $this->curl_connect('users?type=AdminUsers');
            $users_array=json_decode($users,true);

            return $users_array["users"][0]["id"];
    }
}


