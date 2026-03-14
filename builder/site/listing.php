<?php
$sites = variable('networkSites');
$prefix = substr(NETWORKNAME, 1);

sectionId('network-sites', 'container');

function _is_heading($item) { return is_string($item) && startsWith($item, '~'); }

echo _excludeFromGoogleSearch(
	contentBox('root-header', 'text-center mt-5', true)
	. getSnippet('root-header', CORESNIPPET)
	. contentBox('end', '', true)
);

$op = ['ALLARTICLES'];
foreach ($sites as $ix => $item) {
	if (_is_heading($item)) {
		if ($ix == count($sites) -1) continue;
		if ($ix < count($sites) -1 && _is_heading($sites[$ix + 1])) continue; //Hence ThisIsEmpty

		$text = substr($item, 1);
		$link = makeLink($prefix . humanize($text), getDomainLink('', urlize($text), '', true), false, false, $ix == 0 ? 'btn btn-info' : 'btn btn-success');
		$op[] = h2($link, 'bg-light', true);
		continue;
	}

	if (!isset($item[variable(SITEURLKEY)]))
		showDebugging('7 url-key-missing', [variable(SITEURLKEY), $item], true);

	$item['url'] = $item[variable(SITEURLKEY)];
	$item[VARSafeName] = $item['key'];

	$op[] = 'ARTICLE-3COL-BOX';
	$op[] = replaceItems('<a href="%url%"><h3 class="h3 m-0 mb-1">%name%</h3><img src="%url%%safeName%-logo.png" class="img-fluid mb-2" />%byline%</a>', $item, '%');
	$op[] = 'ARTICLE-CLOSE';
	$op[] = ''; $op[] = '';
}
$op[] = 'ALLARTICLES-CLOSE';

echo returnLinesNoParas(implode(NEWLINE, $op));

sectionEnd();
