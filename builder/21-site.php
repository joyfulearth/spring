<?php
global $networkUrls;
$networkUrls = [];

function addNetworkUrl($site, $url) {
	global $networkUrls;
	$networkUrls[URLOFPREFIX . $site] = $url;
}

function replaceNetworkUrls($html) {
	global $networkUrls;
	if (empty($networkUrls)) return $html; //assumes will be called again in render
	if ($html === PleaseDie) showDebugging(22, $networkUrls, true);
	if (!contains($html, URLOFPREFIX) || empty($networkUrls)) return $html;
	//if (endsWith($html, '%')) showDebugging(23, [$html, $networkUrls], PleaseDie);
	return replaceItems($html, $networkUrls, WRAPREPLACE);
}

function getSiteKey($site, $suffix = '') { return '%' . URLOFPREFIX . $site . '%' . $suffix; }
function getSiteUrl($site, $suffix = '') { return replaceNetworkUrls(getSiteKey($site)) . $suffix; }

function getSpecialUrl($name) {
	if ($name == 'root')
		return getSiteUrl(SITEROOT);
	else if ($name == 'signup')
		return getSiteUrl(SITESPRING, NODEOPUS . '/services/signup/');
	else if ($name == 'smithy')
		return getSiteUrl(SITESPRING, NODESMITHY . '/');
	else throw new Error('Unknown SpecialUrl: ' . $name);
}

function getSiteInfo($relativePath, $urlKey = false) {
	$key = 'siteInfo_' . $relativePath;
	$result = variable($key);
	if ($result) return $result; //showDebugging(218, $result, PleaseDie);

	$file = ALLSITESROOT . $relativePath . '/data/site.tsv';
	//need the check again as it may be called from subsites/
	if (!sheetExists($file)) {
		if (is_local()) debug(__FILE__, 'getSiteInfo', ['missing for' => $relativePath, 'TSV missing' => $file], DEBUGSPECIAL);
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

	if ($urlKey) addNetworkUrl($relativePath, $result[$urlKey]);
	variable($key, $result);
	return $result;
}

function getUrlFrom($relativePath, $urlKey = false) {
	if (!$urlKey) $urlKey = _getUrlKeySansPreview();
	$result = getSiteInfo($relativePath);
	return $result[$urlKey];
}
