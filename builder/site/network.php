<?php
DEFINE('NETWORKDEFINEDAT', DEFINED('NETWORKPATH') ? NETWORKPATH . '/' : AMADEUSSITEROOT . 'data/networks/');
DEFINE('NETWORKNAME', '~JoyfulEarth\'s ');
DEFINE('NETWORKABBR', 'JE');

setupNetwork();

function network_menu() {
	if (in_array(variable(VARDAWNMenu), BOOLLISTFALSE)) return;

	if (!in_array(variable(VARNetwork), BOOLLISTFALSE))
		flatMenu(variable('networkSites'), variable(VARNetwork));

	$urlKey = _getUrlKeySansPreview();
	$dawnFols = ['joyfulearth', 'spring', 'federated/imran'];
	$dawn = [];
	foreach ($dawnFols as $slug) {
		if (!is_dir(ALLSITESROOT . $slug)) continue;
		$dawn[] = getSiteInfo($slug, $urlKey);
	}

	$items = ['DAWN' => $dawn];

	$folders = [
		'federated',
		'networks',
		'others',
		//TODO: HI: when doing 'all',
		//TODO: 'for/vidya'
	];
	foreach ($folders as $slug) {
		if (!is_dir(ALLSITESROOT . $slug)) continue;
		$items[humanize($slug)] = setupNetwork($slug);
	}
	twoLevelMenu($items, NETWORKABBR);
}

function setupNetwork(sheet | null | string $sheet = null) {
	$networkSites = [];

	$networkName = variable(VARNetwork);

	$items = [];
	$urlKey = _getUrlKeySansPreview();
	$returnArray = false;

	if (is_string($sheet) && $sheet != null) {
		$fols = _skipNodeFiles(scandir(ALLSITESROOT . $sheet), ONLYFOLDERS);
		foreach ($fols as $fol) $items[] = $sheet . '/' . $fol;
		$returnArray = true;
	} else if ($sheet) {
		$items = $sheet->rows;
		$returnArray = true;
	} else {
		if (disk_file_exists($txt = NETWORKDEFINEDAT . $networkName . '.txt')) {
			$items = textToList(disk_file_get_contents($txt));
		} else {
			$sheet = getSheet(NETWORKDEFINEDAT . $networkName . '.tsv', false);
			$items = $sheet->rows;
		}
	}

	$hasNode = !is_string($sheet) && isset($sheet) && $sheet->hasColumn('node');
	foreach ($items as $key => $row) {
		$plain = is_string($row);
		$key = $plain ? $row : $sheet->getValue($row, 'key');
		if (startsWith($key, '~')) {
			$networkSites[] = $key;
			continue;
		}

		$item = getSiteInfo($plain ? $row : $sheet->getValue($row, 'path'), $urlKey);
		if ($item === false) continue;
		if ($hasNode && $node = $sheet->getValue($row, 'node')) {
			$item[$urlKey] .= $node . '/';
			$item['key'] .= '/' . $key;
			$item['name'] = humanize($node) . ' &larr; ' . $item['name'];
		}
		$networkSites[] = $item;
	}

	if ($returnArray) return $networkSites;
	variable('networkSites', $networkSites);
}
