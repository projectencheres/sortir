<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class VilleService
{   private $client;
    public function __construct( HttpClientInterface $client)
    {
        $this->client = $client;
    }
    public function getVillesparCodePostal(string $codePostal): array
    {
        $url = "https://api-adresse.data.gouv.fr/search/?q=paris&type=street";
        $response = $this->client->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des villes');
            
        }
        return $response->toArray();
    }
}
