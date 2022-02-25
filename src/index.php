<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

require_once('vendor/autoload.php');
$jsonUrl = 'REDACTED';
$client = new Client();

try {
    $response = $client->request('GET', $jsonUrl);
} catch (GuzzleException $e) {
    die('Could not fetch pages');
}

$pages = json_decode($response->getBody()->getContents(), true);

/**
 * @param array $pages
 * @param array $results
 * @param string|null $parentPath
 * @return array
 */
function getPagesByPath(array $pages, array &$results = [], string $parentPath = null): array {
    foreach ($pages as $path => $page) {
        $pagePath = $parentPath ? $parentPath . $path : $path;
        if (!empty($page['Children'])) {
            getPagesByPath($page['Children'], $results, $pagePath);
        }

        if ($page['Page']) {
            $results[$pagePath] = $page['Page'];
        }
    }

    return $results;
}

$processedPages = getPagesByPath($pages);

// Sort by the page load duration
uasort($processedPages, function($a, $b) {
    return $a['Stats']['duration'] <=> $b['Stats']['duration'];
});

$top5PerformantPages = array_slice($processedPages, 0, 5);
?>

<!-- Typically, obviously I'd use a templating layer, or this would all be spat out via API and consumed elsewhere -->
<h1>Our top 5 most performant pages are... </h1>
<ul>
    <?php foreach ($top5PerformantPages as $path => $page): ?>
        <li><?php echo $path; ?> - <?php echo $page['Stats']['duration']; ?>ms</li>
    <?php endforeach; ?>
</ul>


