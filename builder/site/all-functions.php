<?php
function contains2($haystack, $needle) { return stripos($haystack, $needle) !== false; }

//Added 15 Jan 2025 to 3-files.php
function _makeSlashesConsistent2($path) {
	$fromTo = DIRECTORY_SEPARATOR == '/' ? ['\\', '/'] : ['/', '\\'];
	return str_replace($fromTo[0], $fromTo[1], $path);
}

DEFINE('ALLREGISTRY', [
	'public_html' => ['local' => 'http://localhost/all/%s/', 'live' => 'https://%s.joyfulearth.org/'],
]);

function allInfo($absFol) : array | bool {
	$slashSafe = _makeSlashesConsistent2($absFol);
	if (!contains2($slashSafe, DIRECTORY_SEPARATOR . 'all' . DIRECTORY_SEPARATOR)) return false;
	$site = pathinfo($absFol, PATHINFO_FILENAME);
	$allIn = pathinfo(dirname($absFol, 2), PATHINFO_FILENAME);
	return ['name' => $site, 'in' => $allIn];
}

function enhanceAllSite(&$vars, $name = false, $in = false) {
	if (!$name) $name = ALLNAME;
	if (!$in) $in = ALLIN;
	$all = ALLREGISTRY[$in];
	$vars['local-url'] = sprintf($all['local'], $name);
	$vars['live-url'] = sprintf($all['live'], $name);
	$vars['safeName'] = $name;
}
