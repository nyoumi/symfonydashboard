<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;



class DatiController extends Controller
{
    private $client;
    private $site_url ;
    private $apikey ;



    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');
    }
    public function make_get_request(array $parameters,array $header){
        foreach($parameters as $key=>$value)
        {
            echo $key;
            echo $value;
        }
        $response = $this->client->request(
            'GET',
            $this->site_url,
            [
                'headers' => $header,
            ]
        );
        var_dump($response);
    }
    public function number(): Response
    {
        $number = random_int(0, 100);

        $headers=[
            'Accept' => 'application/json',
            'apikey' => $this->apikey
        ];
        $this->make_get_request()


        var_dump($apikey);
        var_dump($site_url);


        $response = $this->client->request(
            'GET',
            'https://restcountries.eu/rest/v2/all'
        );

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        var_dump($content[0]["name"]);
         return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        ); 
 
   
/*         return $this->render('dashboard/dashboard.html.twig', [
            '_total_balance' => $total_balance,
            '_withdraw_amount' =>$withdraw_amount,
            '_deposit_amount' =>$deposit_amount,
            '_accounts' =>$accounts


        ]); */
        
    }
}