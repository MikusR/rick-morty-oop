<?php

declare(strict_types=1);


class Episode
{
    private int $id;
    private string $name;
    private string $air_date;
    private string $episode;
    //private array $characters;
    //private string $url;
    //private string $created;
    private ?int $ratining;

    public function __construct(stdClass $episodeData, int $rating = null)
    {
        foreach ($episodeData as $propertyName => $value) {
            if (property_exists(__CLASS__, $propertyName)) {
                $this->$propertyName = $value;
            }
            $this->ratining = $rating;
        }
    }

    public function __toString(): string
    {
        return;
    }

    /**
     * @return string
     */
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getEpisode(): int
    {
        return (int)substr($this->episode, 3, 2);
    }

    public function getSeason(): int
    {
        return (int)substr($this->episode, 1, 2);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}

class ListOfEpisodes
{

    private function prepareListOfEpisodes(): array
    {
        //salīmē vienā array visus result no vairākām lapām;
        $listOfEpisodes = [];
        $apiUrl = 'https://rickandmortyapi.com/api/episode/';
        $jsonFile = file_get_contents($apiUrl);
        $json = json_decode($jsonFile);
        $numberOfPages = (int)$json->info->pages;
        $currentPage = 1;
        while ($currentPage <= $numberOfPages) {
            if ($currentPage != 1) {
                $jsonFile = file_get_contents($apiUrl . "?page=$currentPage");
                $json = json_decode($jsonFile);
            }
            foreach ($json->results as $result) {
                $listOfEpisodes[$result->id] = $result;
            }
            $currentPage++;
        }
        return $listOfEpisodes;
    }

    /**
     * @var Episode[]
     */
    private array $episodes;

    public function __construct()
    {
        $listOfEpisodes = $this->prepareListOfEpisodes();
        foreach ($listOfEpisodes as $key => $episode) {
            $this->episodes[$key] = new Episode($episode);
        }
    }


    public function get(int $episodeID): Episode
    {
        return $this->episodes[$episodeID];
    }
}

$test = new ListOfEpisodes();
$test->get(1)->toString();
//file_put_contents('test.json', json_encode($test));
