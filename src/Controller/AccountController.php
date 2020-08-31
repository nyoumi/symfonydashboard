<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class ActivationController
 * @package App\Controller
 */
class AccountController extends AbstractController
{
    /**
     * @var HttpClientInterface
     */
    private $client;
    /**
     * @var
     */
    private $site_url ;
    /**
     * @var
     */
    private $apikey ;

    /**
     * ActivationController constructor.
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

    }

    /**
     * retrieve a user account
     * @param $id
     * @return Response
     */
    public function retrieve($id)
    {

        $endpoint="account/".$id."/retrieve";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[
            'id' =>  $id,
        ];
        $response=$this->make_get_request($params,$headers,$endpoint);




        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }


    /**
     * retrieve a user account
     * @param $id
     * @return Response
     */
    public function listAccounts($id)
    {

        $endpoint="accounts/user/".$id;
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[
            'id' =>  $id,
        ];
        $response=$this->make_get_request($params,$headers,$endpoint);




        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }

    /**
     * retrieve a user account
     * @return Response
     */
    public function listAccountsTypes()
    {

        $endpoint="accounts/types";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[];
        $response=$this->make_get_request($params,$headers,$endpoint);




        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }



    /**
     * generateConfirmCode to be used to buying account process as confirmation of the transaction
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function generateConfirmCode($id,Request $request)
    {

        $endpoint="account/".$id."/generate-confirm-code";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];


        $params=["by"=>$request->get("by")];
        $response=$this->make_get_request($params,$headers,$endpoint);


        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }

    /**
     * retrieve a user account
     * @param $type
     * @return Response
     */
    public function estimate($type)
    {
        $endpoint="account/".$type."/estimate";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[];
        $response=$this->make_get_request($params,$headers,$endpoint);

        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }





    /**
     * retrieve a user account
     * @param Request $request
     * @return Response
     */
    public function createAccount(Request $request)
    {

        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $endpoint="account/new";
        $params=(array)$data;
        $response=$this->make_post_request($params,$headers,$endpoint,$params);

        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }







    public function editAccount(Request $request)
    {
        /*
         * lecture des données envoyées par post, submit ou dans la requête
         */

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }
        $endpoint="account/".$data->id."/edit";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];


        $params=(array)$data;

        $response=$this->make_put_request($params,$headers,$endpoint,(array)$data);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }

    public function setTrustedHostsList(Request $request)
    {
        /*
         * lecture des données envoyées par post, submit ou dans la requête
         */

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }

        $endpoint="account/".$data->id."/set-trusted-hosts-list";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=(array)$data;

        $response=$this->make_put_request($params,$headers,$endpoint,(array)$data);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }


    /**
     * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     *
     */
    public function viewAffiliateAction($type,$id)
    {



        $endpoint=$type."/".$id."/view";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[
            'id' =>  $id,
        ];
        $response=$this->make_get_request($params,$headers,$endpoint);




        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }

    public function listAffiliateAction($id, $type)
    {

        $endpoint=$type."/user/".$id."/list";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[
            'id' =>  $id,
        ];
        $response=$this->make_get_request($params,$headers,$endpoint);




        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }

    public function estimateCreatioinAffiliateAction($type)
    {

        $endpoint=$type."/estimate-creation";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[];
        $response=$this->make_get_request($params,$headers,$endpoint);




        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }

    public function estimateSubscriptionAffiliateAction($type)
    {
        $endpoint=$type."/estimate-subscription";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[];
        $response=$this->make_get_request($params,$headers,$endpoint);




        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }
    public function subscribeAffiliateAction($id, $type,Request $request)
    {


        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $endpoint=$type."/"."user/".$id."/subscribe";
        $params=(array)$data;
        $response=$this->make_post_request($params,$headers,$endpoint,$params);

        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }

    public function viewAction()
    {
        $endpoint="accounts/types";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[];
        $account_types=[];
        $response=$this->make_get_request($params,$headers,$endpoint);
        if(isset($response['code'])){
            $account_types["account_types"]=$response;
        }


        return $this->render('pages/accounts.html.twig',[
                "account_types"=>$response
            ]
        );
    }

    public function newAffiliateAction($type,Request $request)
    {


        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $endpoint=$type."/new";
        $params=(array)$data;
        $response=$this->make_post_request($params,$headers,$endpoint,$params);

        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }


    public function editAffiliateAction($type,$id,Request $request)
    {
        /*
         * lecture des données envoyées par post, submit ou dans la requête
         */

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }
        $endpoint=$type."/".$id."/edit";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];


        $params=(array)$data;

        $response=$this->make_put_request($params,$headers,$endpoint,(array)$data);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }



    public function linkMerchantActivityAffiliateAction($type,$id,Request $request)
    {
        /*
         * lecture des données envoyées par post, submit ou dans la requête
         */

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }
        $endpoint=$type."/".$id."/link-merchant-activity";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];


        $params=(array)$data;

        $response=$this->make_put_request($params,$headers,$endpoint,(array)$data);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }
    public function setTrustedHostsListAffiliateAction($type,$id,Request $request)
{
    /*
     * lecture des données envoyées par post, submit ou dans la requête
     */

    $data = $request->getContent();
    $data=json_decode($data);


    if(!is_object($data)){
        parse_str($request->getContent(),$output);
        $data=(object) $output;
    }

    if($this->emptyObj($data) ){
        $data=$request->query->all();
        $data=(object) $data;
    }
    $endpoint=$type."/".$id."/link-merchant-activity";
    $headers=[
        'Accept' => 'application/json',
        "apikey"=> $this->apikey
    ];


    $params=(array)$data;

    $response=$this->make_put_request($params,$headers,$endpoint,(array)$data);
    return new Response(json_encode($response), Response::HTTP_OK,[
        "Content-Type"=>'application/json'
    ]);

}


    public function deleteAffiliateAction($type,$id)
    {


        $endpoint=$type."/".$id."/delete";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $params=['id'=>$id];


        $response=$this->make_delete_request($params,$headers,$endpoint);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }


    public function renewSecurityToken(Request $request)
    {
        /*
         * lecture des données envoyées par post, submit ou dans la requête
         */

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }

        $endpoint="account/".$data->id."/renew-security-token";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=(array)$data;

        $response=$this->make_put_request($params,$headers,$endpoint,(array)$data);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }

    public function promote(Request $request)
    {
        /*
         * lecture des données envoyées par post, submit ou dans la requête
         */

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }

        $endpoint="account/".$data->id."/promote";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=(array)$data;

        $response=$this->make_put_request($params,$headers,$endpoint,(array)$data);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }


    public function promoteAccount(Request $request)
    {
        /*
         * lecture des données envoyées par post, submit ou dans la requête
         */

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }

        $endpoint="account/".$data->id."/promote";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=(array)$data;

        $response=$this->make_put_request($params,$headers,$endpoint,(array)$data);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }

    public function deleteAccount($id)
    {

        /*
 * lecture des données envoyées par post, submit ou dans la requête
 */


        $endpoint="account/".$id."/delete";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $params=['id'=>$id];


        $response=$this->make_delete_request($params,$headers,$endpoint);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }




    /**
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @return null
     */
    public function make_put_request(array $parameters, array $header, $endpoint,$body)
    {

        $this->site_url = $this->getParameter('app.site_url');
        $this->apikey = $this->getParameter('app.apikey');
        $header["apikey"]=$this->apikey;

        $params = "";
        foreach ($parameters as $key => $value) {
            $params = $params . $key . "=" . $value . "&";

        }
        $params = substr($params, 0, -1);
        //$params = *

        try {
            $response = $this->client->request(
                'PUT',
                $this->site_url . $endpoint . "?" . $params,
                [
                    'headers' => $header,
                    'json' => $body


                ]
            );

            if($response->getStatusCode()==200 |$response->getStatusCode()==208|$response->getStatusCode()==201 ){

                return $response->toArray();
            }else {

                return [
                    "code"=>$response->getStatusCode()
                ];
            }

        } catch (TransportExceptionInterface | ClientExceptionInterface |ServerExceptionInterface| DecodingExceptionInterface | RedirectionExceptionInterface $e) {


            return [
                "code"=>0
            ];
        }



    }



    /**
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @return null
     */
    public function make_post_request(array $parameters, array $header, $endpoint,$body)
    {

        $this->site_url = $this->getParameter('app.site_url');
        $this->apikey = $this->getParameter('app.apikey');
        $header["apikey"]=$this->apikey;

        $params = "";
        foreach ($parameters as $key => $value) {
            $params = $params . $key . "=" . $value . "&";

        }
        $params = substr($params, 0, -1);
        //$params = *

        try {
            $response = $this->client->request(
                'POST',
                $this->site_url . $endpoint . "?" . $params,
                [
                    'headers' => $header,
                    'json' => $body


                ]
            );

            if($response->getStatusCode()==200 |$response->getStatusCode()==201 ){

                return $response->toArray();
            }else {

                return [
                    "code"=>$response->getStatusCode()
                ];
            }

        } catch (TransportExceptionInterface | ClientExceptionInterface |ServerExceptionInterface| DecodingExceptionInterface | RedirectionExceptionInterface $e) {


            return [
                "code"=>0
            ];
        }



    }
    /**
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @return null
     */
    public function make_get_request(array $parameters, array $header, $endpoint)
    {

        $this->site_url = $this->getParameter('app.site_url');
        $this->apikey = $this->getParameter('app.apikey');
        $header["apikey"]=$this->apikey;

        $params = "";
        foreach ($parameters as $key => $value) {
            $params = $params . $key . "=" . $value . "&";

        }
        $params = substr($params, 0, -1);
        //$params = *

        try {
            $response = $this->client->request(
                'GET',
                $this->site_url . $endpoint . "?" . $params,
                [
                    'headers' => $header,

                ]
            );

            if($response->getStatusCode()==201 | $response->getStatusCode()==200
                | $response->getStatusCode()==202 ){
                $code=$response->getStatusCode();
                $response=$response->toArray();

                $response["code"]=$code;

                return $response;
            }else {


                return [
                    "code"=>$response->getStatusCode()
                ];
            }




        } catch (TransportExceptionInterface | ClientExceptionInterface |ServerExceptionInterface| DecodingExceptionInterface | RedirectionExceptionInterface $e) {

            return [
                "code"=>0
            ];
        }



    }
    /**
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @return null
     */
    public function make_delete_request(array $parameters, array $header, $endpoint)
    {

        $this->site_url = $this->getParameter('app.site_url');
        $this->apikey = $this->getParameter('app.apikey');
        $header["apikey"]=$this->apikey;

        $params = "";
        foreach ($parameters as $key => $value) {
            $params = $params . $key . "=" . $value . "&";

        }
        $params = substr($params, 0, -1);
        //$params = *

        try {
            $response = $this->client->request(
                'DELETE',
                $this->site_url . $endpoint . "?" . $params,
                [
                    'headers' => $header,

                ]
            );

            if($response->getStatusCode()==201 | $response->getStatusCode()==200
                | $response->getStatusCode()==202 ){
                $code=$response->getStatusCode();
                $response=$response->toArray();

                $response["code"]=$code;

                return $response;
            }else {


                return [
                    "code"=>$response->getStatusCode()
                ];
            }




        } catch (TransportExceptionInterface | ClientExceptionInterface |ServerExceptionInterface| DecodingExceptionInterface | RedirectionExceptionInterface $e) {

            return [
                "code"=>0
            ];
        }



    }

    private function emptyObj( $obj ) {
        foreach ( $obj AS $prop ) {
            array($prop);
            return FALSE;
        }

        return TRUE;
    }

}
