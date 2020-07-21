<?php declare(strict_types=1);

use Alfred\Workflows\Workflow;
use Algolia\AlgoliaSearch\SearchClient;

require __DIR__.'/vendor/autoload.php';

$query = $argv[1];

$workflow = new Workflow();
$algolia  = SearchClient::create('BH4D9OD16A', '1a246c2737d8e8c38198dd7ea530d123');

$index   = $algolia->initIndex('symfony');
$search  = $index->search($query, ['facetFilters' => 'version:current']);
$results = $search['hits'];

if (empty($results))
{
    $workflow->result()
             ->title('No matches')
             ->icon('google.png')
             ->subtitle("No match found in the docs. Search Google for: \"Symfony+{$query}\"")
             ->arg("https://www.google.com/search?q=symfony+{$query}")
             ->quicklookurl("https://www.google.com/search?q=symfony+{$query}")
             ->valid(true);

    echo $workflow->output();
    exit;
}

foreach ($results as $hit)
{
    $highestLvl = $hit['hierarchy']['lvl6'] ? 6 : (
        $hit['hierarchy']['lvl5'] ? 5 : (
            $hit['hierarchy']['lvl4'] ? 4 : (
                $hit['hierarchy']['lvl3'] ? 3 : (
                    $hit['hierarchy']['lvl2'] ? 2 : (
                        $hit['hierarchy']['lvl1'] ? 1 : 0
                    )
                )
            )
        )
    );

    $title      = $hit['hierarchy']['lvl'.$highestLvl];
    $currentLvl = 0;
    $subtitle   = $hit['hierarchy']['lvl0'];
    while ($currentLvl < $highestLvl)
    {
        $currentLvl = $currentLvl + 1;
        $subtitle   = $subtitle.' » '.$hit['hierarchy']['lvl'.$currentLvl];
    }

    $workflow->result()
             ->uid($hit['objectID'])
             ->title($title)
             ->autocomplete($title)
             ->subtitle($subtitle)
             ->arg($hit['url'])
             ->quicklookurl($hit['url'])
             ->valid(true);
}

echo $workflow->output();
