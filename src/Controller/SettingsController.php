<?php

namespace App\Controller;

use App\Security\User\WebserviceUser;
use CURLFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

/**
 * Class ActivationController
 * @package App\Controller
 */
class SettingsController extends AbstractController
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
    private $urlGenerator;
    private $security;
    private $create_action="create";

    /**
     * ActivationController constructor.
     * @param UrlGeneratorInterface $urlGenerator
     * @param HttpClientInterface $client
     * @param Security $security
     */
    public function __construct(UrlGeneratorInterface $urlGenerator,HttpClientInterface $client,Security $security)
    {
        $this->client = $client;
        $this->urlGenerator = $urlGenerator;
        $this->security=$security;



    }
    public function testing(){
        var_dump($this->get('session')->get("requested_url"));
        return new Response(json_encode($this->get('session')->get("requested_url")), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     */
    public function viewAction(Request $request)
    {




        $data=$request->query->all();

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


        return $this->render('pages/main_settings.html.twig',[
                "account_types"=>$response
            ]
        );
        // return new RedirectResponse($this->urlGenerator->generate('home'));

    }

    private function getRoles($user)
    {
        $accounts=$user["accounts"];

        $roles=[];
        foreach ($accounts as $account) {
            foreach ($account["roles"] as $role) {
                if(!in_array($role,$roles)) array_push($roles,$role);
            }
        }
        $user_roles=$user["roles"];
        return array_merge($roles, $user_roles);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     */

    public function uploadAction(Request $request,$id)
    {


        if($request->getMethod()=="POST"){

            $file = $request->files->get('avatar');
            $file=$file->move("data");
            $this->site_url = $this->getParameter('app.site_url');
            $this->apikey = $this->getParameter('app.apikey');
            $header["apikey"]=$this->apikey;
            $url = $this->site_url."user/".$id."/profile-picture-edit";


            $headers=[
                'Accept' => 'application/json',
                'Content-Type: multipart/form-data;',
                "apikey:".$this->apikey,

            ];

            $params=[];


            $response=$this->make_test_post_request($params,$headers,$url,$file);
            return new Response(json_encode($response), Response::HTTP_OK,[
                "Content-Type"=>'application/json'
            ]);
        }
        return new Response(json_encode(["code"=>"pas post"]), Response::HTTP_CREATED,[
            "Content-Type"=>'application/json'
        ]);



    }


    public function testAction(Request $request ) {


        if($request->getMethod()=="POST") {

            $file = $request->files->get('avatar');
            return new Response(json_encode([
                "file"=>$file->getClientOriginalName()]), Response::HTTP_CREATED,[
                "Content-Type"=>'application/json'
            ]);

        }

        return new Response(json_encode(["code"=>" post Nok"]), Response::HTTP_CREATED,[
            "Content-Type"=>'application/json'
        ]);
    }

    public function activatedAction(Request $request)
    {


        $activated=$request->query->get("activated");
        if (true==$activated){
            $this->addFlash(
                'activated',
                'Compte activé'
            );
        }else{
            $this->addFlash(
                'inapropriate',
                'merci de votre confiance.'
            );
        }
        return $this->render('pages/activations.html.twig',
            ["activated"=>$activated
            ]);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function send_sms($id)
    {
        $endpoint="user/".$id."/send_sms";
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
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @param $body
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
                    'body' => $body


                ]
            );

            if($response->getStatusCode()==201 | $response->getStatusCode()==200){

                return $response->toArray();
            }else {


                return [
                    "code"=>$response->getStatusCode()
                ];
            }




        } catch (TransportExceptionInterface | ClientExceptionInterface |ServerExceptionInterface| DecodingExceptionInterface | RedirectionExceptionInterface $e) {

            $this->addFlash(
                'error',
                'An internal error has occurred please try again later!'
            );
            return [
                "code"=>0
            ];
        }



    }

    /**
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @param $file
     * @return array|mixed|\Symfony\Contracts\HttpClient\ResponseInterface
     */
    public function make_test_post_request(array $parameters, array $header, $endpoint,File $file)
    {
        // var_dump($file->getPathname());
        /* $curl = curl_init();



         $data=['avatar'=>new CURLFILE('C:/Users/GOOGLE/Desktop/imgs/astro.png')];

         curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
         curl_setopt($curl, CURLOPT_POST, 1);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

         curl_setopt($curl, CURLOPT_POSTFIELDS,
             array('avatar'=> new CURLFILE('/C:/Users/GOOGLE/Desktop/imgs/images.jpg')));
         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
         curl_setopt($curl, CURLOPT_HTTPHEADER,
             array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15',
                 'Content-Type: multipart/form-data',"apikey:".$this->apikey));

         $response = curl_exec($curl);

         curl_close($curl);
         var_dump($response);

         echo $response;*/

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.daticash.com/api/user/14/profile-picture-edit",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array('avatar'=> new CURLFILE('/C:/Users/GOOGLE/Desktop/imgs/images.jpg')),
            CURLOPT_HTTPHEADER => array(
                "apikey: sec_5ed93806dd2ca"
            ),
        ));
        $file=new CURLFile('C:/Users/GOOGLE/Desktop/imgs/images.jpg');


        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15',
                'Content-Type: multipart/form-data',"apikey:".$this->apikey));
        $postfields = array(
            'files[avatar]' => new CURLFile('C:/Users/GOOGLE/Desktop/imgs/images.jpg'),
            'files["avatar"]' => new CURLFile("@/C:/Users/GOOGLE/Desktop/imgs/images.jpg"),
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS,
            $postfields);


        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;


        $formFields = [
            'regular_field' => 'some value',
            'avatar' => DataPart::fromPath("C:/Users/GOOGLE/Desktop/imgs/images.jpg"),
        ];
        $formData = new FormDataPart($formFields);
        try {
            $this->client->request('POST', $endpoint, [
                'headers' => $header,
                //'body' => $formData->bodyToIterable(),
                'body' => fopen("C:/Users/GOOGLE/Desktop/imgs/images.jpg", 'r')
            ]);
        } catch (TransportExceptionInterface $e) {
            var_dump($e);
        }

        echo("++++++++++++++++++++");

        $data=['avatar'=>new CURLFILE('C:\sers\images.JPG')];

        /*       $formFields = [
                   'regular_field' => 'some value',
                   'avatar' => DataPart::fromPath('C:\sers\images.jpg'),
               ];
               $formData = new FormDataPart($formFields);
               $headers=$formData->getPreparedHeaders()->toArray();

               try {
                   $this->client->request('POST', $endpoint, [
                       'headers' => $header,
                       'body' => $formData->bodyToIterable(),
                   ]);
               } catch (TransportExceptionInterface $e) {
               }*/


        return "";
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

            if($response->getStatusCode()==201 | $response->getStatusCode()==200){

                return $response->toArray();
            }else {

                return [
                    "code"=>$response->getStatusCode()
                ];
            }




        } catch (TransportExceptionInterface | ClientExceptionInterface |ServerExceptionInterface| DecodingExceptionInterface | RedirectionExceptionInterface $e) {

            $this->addFlash(
                'error',
                'An internal error has occurred please try again later!'
            );
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

    private function saveToSession($text,$transaction)
    {
        $this->get('session')->set($text,$transaction);

    }
    private function removeToSession($key){
        $this->get('session')->remove($key);
    }
}
