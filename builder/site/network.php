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
	$urlKey = _getUrlKeySansPreview();
	$showIn = variable(DOMAINKEY);

	$items = [
		//$showIn && !$return ? makeLink('Back to ' . humanize($showIn), getSiteUrl(SITEROOT) : null,
		makeLink(NETWORKNAME . ' Root', getSiteUrl(SITEROOT)),
	];

	$paths = textToList(disk_file_get_contents(NETWORKSDEFINEDAT . 'Webring.txt'));
	$items[] = NETWORKNAME . 'Core';
	foreach ($paths as $item)
		$items[] = [$urlKey => getSiteUrl($item), 'name' => 'JE ' . humanize($item)];

	//TODO: categories

	flatMenu($items, NETWORKABBR);
}

function setupNetwork($noNetwork) {
	$networkSites = [];

	$networkName = urldecode(getQueryParameter(VARNetwork, variable(VARNetwork)));

	$urlKey = _getUrlKeySansPreview();

	$items = [];

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
	} else if (!$noNetwork && $networkName != 'dawn-only') {
		if (disk_file_exists($txt = NETWORKSDEFINEDAT . $networkName . '.txt')) {
			$items = textToList(disk_file_get_contents($txt));
		} else {
			$sheet = getSheet(NETWORKSDEFINEDAT . $networkName . '.tsv', false);
			$items = $sheet->rows;
		}
	}

	foreach ($items as $key => $row) {
		$plain = is_string($row);
		$key = $plain ? $row : $sheet->getValue($row, 'key');
		if (startsWith($key, '~')) {
			$networkSites[] = $key;
			continue;
		}

		$item = _getOrWarn($plain ? $row : $sheet->getValue($row, 'path'));
		if ($item === false) continue;
		$networkSites[] = $item;
	}

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
echo $relativePath . BRNL;
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
