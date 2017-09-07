<?php

namespace Microdata;


class Ratings {
    public $average_rating;
    public $rating_count;

    public function __construct(float $average_rating, int $rating_count) {
        $this->average_rating = $average_rating;
        $this->rating_count = $rating_count;

        var_dump($this->average_rating);
        var_dump($this->rating_count);
    }
}

interface iMicrodataParser {
    public function parseRatings(string $url): Ratings;
}

class DocumentParser implements iMicrodataParser {
  public $doc;
  public $nodes;

  public function __construct(string $url) {
    $c = curl_init($url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_URL, $url);

    $html = curl_exec($c);

    if (curl_error($c))
      die(curl_error($c));

    curl_close($c);

    $this->doc = new \DOMDocument();
    $this->doc->loadHTML($html);

    $this->parseRatings($url);
  }

  public function parseRatings(string $url): Ratings {
    $finder = new \DOMXPath($this->doc);

    $movie_nodes = $finder->query('//*[@itemscope and not(@itemprop) and (contains(concat(" ", normalize-space(@itemtype), " "), " ' . htmlspecialchars(trim('http://schema.org/Movie')) . ' "))]');
    $movie_item = $movie_nodes->item(0);

    $rating_nodes = $finder->query($movie_item->getNodePath() . '//*[@itemprop="aggregateRating"]');

    $this->getAllChildren($rating_nodes->item(0), $finder);

    foreach ($this->nodes as $child) {
      if($child->tagName == 'meta') {
        $value = $child->getAttribute('content');
      }
      else {
        $value = $child->textContent;
      }

      switch ($child->getAttribute('itemprop')) {
        case 'reviewCount': $rating_count = $value; break;
        case 'ratingValue': $average_rating = $value; break;
      }

    }

    return new Ratings($average_rating, $rating_count);

  }

  public function getAllChildren($node, $finder) {
    $children = $finder->query($node->getNodePath() . '//*[@itemprop="reviewCount" or @itemprop="ratingValue"]');

    foreach ($children as $child) {
      $this->nodes[] = $child;
      if($child->hasChildNodes()) {
        $this->getAllChildren($child, $finder);
      }
    }
  }
}