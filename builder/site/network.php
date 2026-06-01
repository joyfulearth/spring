<?php
DEFINE('NETWORKSDEFINEDAT', AMADEUSSITEROOT . 'data/networks/');
DEFINE('NETWORKNAME', '~JoyfulEarth\'s ');
DEFINE('NETWORKABBR', 'JE');

//url of only of webring
if (defined('SHOWSITESAT')) {
	setupNetwork(null);
	return;
}

//setup continues
$networkName = variable(VARNetwork);
$noNetwork = in_array($networkName, BOOLLISTFALSE);
setupNetwork($noNetwork);

if (!$noNetwork) {
	function network_menu() {
		if (variable(VARNetwork) != 'dawn-only')
			flatMenu(variable('networkSites'), variable(VARNetwork));
		dawn_menu();
	}
}

function dawn_menu() {
	$items = setupNetwork(false, getSheet(NETWORKSDEFINEDAT . 'Core.tsv', false));
	flatMenu($items, NETWORKABBR);
}

function setupNetwork($noNetwork, sheet | null $sheet = null) {
	$networkSites = [];

	$networkName = urldecode(getQueryParameter(VARNetwork, variable(VARNetwork)));

	$items = [];
	$urlKey = _getUrlKeySansPreview();
	$returnArray = false;

	if (defined('SHOWSITESAT')) {
		define('SITELISTNAME', humanize($folPrefix = '/' . pathinfo(SHOWSITESAT, PATHINFO_FILENAME)));

		$files = getSitesToShow($folPrefix, $urlKey);

		$folPrefix .= '/';
		foreach ($files as $file) {
			if (startsWith($file, '~')) {
				$items[] = $file;
				continue;
			}

			if (startsWith($file, '==') || !disk_file_exists($tsv = ALLSITESROOT . $file . '/data/site.tsv')) {
				if (is_local()) echo '<!-- missing tsv: ' . $tsv . '-->' . NEWLINE;
				continue;
			}
			$items[$file] = $file;
		}
	} else if ($sheet) {
		$items = $sheet->rows;
		$returnArray = true;
	} else if (!$noNetwork && $networkName != 'dawn-only') {
		if (disk_file_exists($txt = NETWORKSDEFINEDAT . $networkName . '.txt')) {
			$items = textToList(disk_file_get_contents($txt));
		} else {
			$sheet = getSheet(NETWORKSDEFINEDAT . $networkName . '.tsv', false);
			$items = $sheet->rows;
		}
	}

	$hasNode = isset($sheet) && $sheet->hasColumn('node');
	foreach ($items as $key => $row) {
		$plain = is_string($row);
		$key = $plain ? $row : $sheet->getValue($row, 'key');
		if (startsWith($key, '~')) {
			$networkSites[] = $key;
			continue;
		}

		$item = _getOrWarn($plain ? $row : $sheet->getValue($row, 'path'), $urlKey);
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

function getSitesToShow($folSuffix) {
	$fols = _skipNodeFiles(scandir(ALLSITESROOT . $folSuffix), ONLYFOLDERS);

	$op = [];
	foreach ($fols as $relativePath) {
		$file = ALLSITESROOT . $folSuffix . $relativePath . '/data/site.tsv';
		if (!sheetExists($file)) {
			if (is_local()) debug(__FILE__, 'getSitesToShow', ['skipping' => $relativePath, 'TSV missing' => $file, 'hint' => 'IS NETWORK / Site Grouping?'], DEBUGVERBOSE);
			continue;
		}

		$item = _getOrWarn($relativePath);

		$op[] = $item;
	}

	return $op;
}

function _getOrWarn($relativePath, $urlKey = false) {
	$key = 'siteInfo_' . $relativePath;
	$result = variable($key);
	if ($result) return $result; //showDebugging(218, $result, PleaseDie);

	$file = ALLSITESROOT . $relativePath . '/data/site.tsv';
	//need the check again as it may be called from subsites/
	if (!sheetExists($file)) {
		if (is_local()) debug(__FILE__, '_getOrWarn', ['missing for' => $relativePath, 'TSV missing' => $file], DEBUGSPECIAL);
		return false;
	}

	$site = getSheet($file, 'key');

	$showInConfig = $site->firstOfGroup(DOMAINKEY, false, false);
	$showIn = $showInConfig ? $site->getValue($showInConfig, 'value') : 'misc';

	$result = [
		'key' => $site->getValue($site->firstOfGroup(VARSafeName), 'value'),
		'name' => $site->getValue($site->firstOfGroup(VARIconName), 'value'),

		'siteName' => $site->getValue($site->firstOfGroup('name'), 'value'),
		VARByline => $site->getValue($site->firstOfGroup(VARByline), 'value'),

		'local-url' => $site->getValue($site->firstOfGroup('local-url'), 'value'),
		'live-url' => $site->getValue($site->firstOfGroup('live-url'), 'value'),

		'path' => $relativePath,
		'category' => $showIn, //TODO: HI: cleanup and tags / articles
	];

	if ($urlKey) addNetworkUrl($relativePath, $result[$urlKey]); //if a / is there, lets leave it so %urlOf-imran/writing%
	variable($key, $result);
	return $result;
}
