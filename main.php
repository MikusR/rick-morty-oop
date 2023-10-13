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
    private ?int $rating;

    public function __construct(stdClass $episodeData, int $rating = null)
    {
        foreach ($episodeData as $propertyName => $value) {
            if (property_exists(__CLASS__, $propertyName)) {
                $this->$propertyName = $value;
            }
            $this->rating = $rating;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "Season {$this->getSeason()}, Episode {$this->getEpisodeNumber()}, Title: {$this->getName()}";
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getEpisodeNumber(): int
    {
        return (int)substr($this->episode, 4, 2);
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

    /**
     * @param int $rating
     */
    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }

    /**
     * @return int|null
     */
    public function getRating(): ?int
    {
        return $this->rating;
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

    public function listEpisodes(string $type = 'all'): array
    {
        switch ($type) {
            case 'all':
                return $this->episodes;
            case 'unrated':
                $return = [];
                foreach ($this->episodes as $episode) {
                    if (is_null($episode->getRating())) {
                        $return[] = $episode;
                    }
                }
                return $return;
            case'rated':
                $return = [];
                foreach ($this->episodes as $episode) {
                    if ($episode->getRating()) {
                        $return[] = $episode;
                    }
                }
                return $return;
            default:
                return [];
        }
    }

    public function get(int $episodeID): Episode
    {
        return $this->episodes[$episodeID];
    }
}

class Application
{
    private ListOfEpisodes $listOfEpisodes;
    private array $ratings = [];

    public function __construct()
    {
//        $this->ratings = $this->loadRatings();
        $this->listOfEpisodes = new ListOfEpisodes();
    }

    public function list(string $type = 'all'): void
    {
        foreach ($this->listOfEpisodes->listEpisodes($type) as $episode) {
            if ($episode->getEpisodeNumber() === 1) {
                echo "Season {$episode->getSeason()}\n";
            }
            echo "ID:({$episode->getId()}) {$episode->getEpisodeNumber()}. {$episode->getName()}.";
            echo ($episode->getRating()) ? " Rating: {$episode->getRating()}\n" : "\n";
        }
    }

    public function rate(): void
    {
        echo "Enter episode ID to rate\n";
        $choice = (int)readline();
        echo "Enter rating for {$this->listOfEpisodes->get($choice)}\n";
        $rating = (int)readline();

        $this->listOfEpisodes->get($choice)->setRating($rating);
    }

    public function run(): void
    {
        while (true) {
            echo "1. list episodes\n";
            echo "2. rate episodes\n";
            echo "3. list rated episodes\n";
            echo "4. list unrated episodes\n";
            echo "any other key to quit\n";
            $choice = (int)readline();

            switch ($choice) {
                case 1:
                    $this->list();
                    break;
                case 2:
                    $this->rate();
                    break;
                case 3:
                    $this->list('rated');
                    break;
                case 4:
                    $this->list('unrated');
                    break;
                default:
                    die;
            }
        }
    }
}

$app = new Application();
$app->run();
//file_put_contents('test.json', json_encode($test));
