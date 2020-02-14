<?php

namespace Psmb\PsmbImport\DataProvider;

use Ttree\ContentRepositoryImporter\DataProvider\AbstractDatabaseDataProvider;
use Ttree\ContentRepositoryImporter\DataType\StringValue;

class SermonDataProvider extends AbstractDatabaseDataProvider
{
	/**
	 * @return array
	 */
	public function fetch()
	{
		$result = [];
		$query = $this->createQuery()
			->select('*')
			->from('tx_news_domain_model_news', 'n')
			->where('n.hidden=0 AND n.deleted=0 AND pid=125')
			->orderBy('n.datetime');
		$statement = $query->execute();
		while ($record = $statement->fetch()) {
			$date = new \DateTime();
			$date->setTimestamp($record['datetime']);
			if (isset($record['archive'])) {
				$dateStart = new \DateTime();
				$dateStart->setTimestamp($record['archive']);
			} else {
				$dateStart = null;
			}
			$result[] = [
				'__externalIdentifier' => (int) $record['uid'],
				'__parentPageIdentifier' => 'p' . $record['pid'],
				'title' => StringValue::create($record['title'])->getValue(),
				'teaser' => StringValue::create($record['teaser'])->getValue(),
				'bodytext' => $this->parseBodyText(StringValue::create($record['bodytext'])->getValue()),
				'date' => $date,
				'dateStart' => $dateStart,
				'author' => $record['author'],
				'magicDate' => $record['author_email'] // Don't ask me why...
			];
		}
		$this->count = count($result);
		return $result;
	}

	protected function parseBodytext($bodytext)
	{
		// Remove empty tags
		$bodytext = preg_replace('@<(p|div|span|i|b|strong|em)[^>]*></\1>@ui', '', $bodytext);
		// b -> strong
		$bodytext = preg_replace('/<b>(.*?)<\/b>/i', '<strong>$1</strong>', $bodytext);
		// i -> em
		$bodytext = preg_replace('/<i>(.*?)<\/i>/i', '<em>$1</em>', $bodytext);
		// Wrap empty lines with paragraph tag
		$bodytext = preg_replace('/^((<em|<span|<strong|<a|[^<]).*?)([\r\n]|$)/mui', '<p>$1</p>', $bodytext);
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
