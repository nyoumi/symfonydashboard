<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;



class DatiController extends Controller
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    public function number(): Response
    {
        $number = random_int(0, 100);

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
/*         return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        ); */
 
        return $this->render('dashboard/dashboard.html.twig', [
    


        ]);
        
    }
}