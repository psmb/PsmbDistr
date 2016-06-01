<?php
namespace Psmb\PsmbImport\DataProvider;

use Ttree\ContentRepositoryImporter\DataProvider\DataProvider;
use Ttree\ContentRepositoryImporter\DataType\StringValue;

class NewsContentDataProvider extends DataProvider {
  /**
   * @return array
   */
  public function fetch() {
    $result = [];
    $query = $this->createQuery()
    ->select('*')
    ->from('tx_news_domain_model_news', 'n')
    ->where('n.hidden=0 AND n.deleted=0')
    ->orderBy('n.datetime');
    $statement = $query->execute();
    while ($record = $statement->fetch()) {
      $newItem = $this->getContent($record);
      $newItem['__externalIdentifier'] = (int)$record['uid'];
      $result[] = $newItem;
    }
    $this->count = count($result);
    return $result;
  }

	protected function getContent($record) {
		$media = $this->getMedia($record['uid']);
    if (isset($media[0]['dontShowInside'])) {
      $innerImages = array_filter($media, function ($i) {
        return $i['dontShowInside'];
      });
      $coverImage = reset($innerImages);
    } else {
      $coverImage = null;
    }

    $bodytext = $this->parseBodytext($record['bodytext']);
    $contentArray = preg_split(
      '/
        (<p>\[\[MEDIA\d+\]\]<\/p>)|
        (<blockquote>.+?<\/blockquote>)|
        (<aside>.+?<\/aside>)|
        (<section>.+?<\/section>)|
        (<p\s+class="epigraph">.+?<\/p>)|
        (<p\s+class="author">.+?<\/p>)
      /x',
      $bodytext, -1, PREG_SPLIT_DELIM_CAPTURE
    );

    $contentArray = array_filter(array_map('trim', $contentArray));
    $lastMediaIndex = 0;
    $author = '';
    $contentArray = array_map(function ($i) use ($media, &$lastMediaIndex, &$author) {
      if (preg_match('/^<p>\[\[MEDIA(\d+)\]\]<\/p>$/', $i, $matches)) {
        $lastMediaIndex = $matches[1] - 1;
        if (isset($media[$lastMediaIndex])) {
          return $media[$lastMediaIndex];
        } else {
          return null;
        }
      } else if (preg_match('/^<blockquote>(.+?)<\/blockquote>$/', $i, $matches)){
        return [
          '_type' => 'Psmb.NodeTypes:Blockquote',
          'text' => $matches[1]
        ];
      } else if (preg_match('/^<section>(.+?)<\/section>$/', $i, $matches)){
        return [
          '_type' => 'Psmb.NodeTypes:Section',
          'text' => $matches[1]
        ];
      } else if (preg_match('/^<aside>(.+?)<\/aside>$/', $i, $matches)){
        return [
          '_type' => 'Psmb.NodeTypes:Aside',
          'text' => $matches[1]
        ];
      } else if (preg_match('/^<p\s+class="epigraph">(.+?)<\/p>$/', $i, $matches)){
        return [
          '_type' => 'Sfi.Site:Epigraph',
          'text' => $matches[1]
        ];
      } else if (preg_match('/^<p\s+class="author">(.+?)<\/p>$/', $i, $matches)){
        $author .= $matches[1] . "\n";
        return null;
      } else {
        return $i ? [
          '_type' => 'Psmb.NodeTypes:Text',
          'text' => $i
        ] : null;
      }
    }, $contentArray);
    $contentArray = array_filter($contentArray);

    // Get all unused images for gallery
    $lastImageOffset = array_search($lastMediaIndex, array_keys($media));
    if ($lastImageOffset !== false) {
      $gallery = array_slice($media, $lastImageOffset + 1, null, true);
    } else {
      $gallery = null;
    }

    return [
      'main' => $contentArray,
      'coverImage' => $coverImage,
      'credit' => $author,
      'gallery' => $gallery
    ];
	}

	protected function getMedia($parent) {
		$result = [];
		$query = $this->createQuery()
      ->select('*')
      ->from('tx_news_domain_model_media', 'm')
      ->where('m.parent = :parent AND m.hidden=0 AND m.deleted=0')
  		->setParameter(':parent', $parent)
      ->orderBy('m.sorting');
    $statement = $query->execute();
    while ($record = $statement->fetch()) {
      if ($record['type'] == 0) {
        $mediaItem = [
          '_type' => 'Psmb.NodeTypes:Image',
          'caption' => $record['caption'],
          'filename' => $record['image'],
          'dontShowInside' => $record['showinpreview']
        ];
      } else if ($record['type'] == 1) {
  			$mediaItem = [
  				'_type' => 'Sfi.YouTube:YouTube',
  				'videoUrl' => $record['multimedia'],
  				'caption' => $record['caption'],
  				'dontShowInside' => $record['showinpreview']
  			];
      }
			$result[] = $mediaItem;
		}
		return $result;
	}

	protected function parseBodytext($bodytext) {
    // Remove empty tags
		$bodytext = preg_replace('@<(p|div|span|i|b|strong|em)[^>]*></\1>@ui', '', $bodytext);
    // b -> strong
		$bodytext = preg_replace('/<b>(.*?)<\/b>/i', '<strong>$1</strong>', $bodytext);
    // i -> em
		$bodytext = preg_replace('/<i>(.*?)<\/i>/i', '<em>$1</em>', $bodytext);
    // Wrap empty lines with paragraph tag
		$bodytext = preg_replace('/^((<em>|<strong>|<a|\w|\[\[).*?)([\r\n]|$)/mui', '<p>$1</p>', $bodytext);
		// Handle link tags
		$bodytext = preg_replace_callback(
			'@<link\s+(\S*)[^>]*>([^<]*)</link>@ui',
			function ($matches) {
				// If link to page, we drop that link, as they have changed anyways
				if (is_numeric($matches[1])) {
					return $matches[2];
				} else if (preg_match('@http@ui', $matches[1], $matches2)) { //If url, then turn into normal link
					return '<a href="' . $matches[1] . '">' . $matches[2] . '</a>';
				} else if (preg_match('@(record:tt_news:)([\d]+)@ui', $matches[0], $matches2)) { //Remove links to news record:tt_news:2806
					return $matches[2];
				} else { //just in case...
					return '<a href="' . $matches[1] . '">' . $matches[2] . '</a>';
				}
			},
			$bodytext
		);
		return $bodytext;
	}
}
