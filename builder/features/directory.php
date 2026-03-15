<?php
if (variable('skip-directory')) return;
$where = variableOr('directory_of', variable('section'));

$folder = sectionBaseOrSitePath() . '/' . $where . '/';
if (disk_file_exists($php = $folder . 'home.php')) {
	disk_include_once($php);
	return;
}

variable('omit-long-keywords', true);

sectionId('directory', 'container');
function _sections($current) {
	if (!nodeIsSection()) return;

	contentBox('', 'toolbar text-align-left');
	echo 'Section: ' . variable('nl');
	foreach (variable('sections') as $item) {
		//TODO: reinstate - if (cannot_access($item)) continue;
		echo sprintf(variable('nl') . '<a class="btn btn-%s" href="%s">%s</a> ',
			$item == $current ? 'primary' : 'secondary',
			pageUrl($item),
			humanize($item)
		);
	}
	contentBox('end');
}

$file = hasVariable('file') ? false : variableOr('directory-file', $folder . 'home.md');
_renderMenu($file, $folder, $where);

function _renderMenu($home, $folder, $where) {
	$breadcrumbs = variable('breadcrumbs');

	if (!$breadcrumbs && !variable('in-node'))
		h2(humanize($where), 'amadeus-icon');

	if ($home) {
		$wantsNoCB = contains(disk_file_get_contents($home), WANTSNOCONTENTBOX);
		if (!$wantsNoCB) contentBox('home');
		renderAny($home);
		if (!$wantsNoCB) contentBox('end');
	}

	echo GOOGLEOFF;
	contentBox('nodes', variable('directory_use_excerpts') ? '' : 'after-content mb-5');

	if (!$breadcrumbs)
		_sections($where);

	variable('seo-handled', false);


	$ix = 1;
	$sectionItems = [];

	if ($breadcrumbs) {
		$clone = array_merge($breadcrumbs);
		if (count($clone) > 1)
			$first = array_shift($clone);
		$last = end($clone);
		$sectionItems[] = getFolderMeta($folder, false, '__' . $last, $ix++);
	} else if (variable('link-to-node-home') || variable('link-to-node-home-only-in-directory')) {
		$sectionItems[] = getFolderMeta($folder, true, '__' . getHtmlVariable('nodeName'), $ix++);
		//echo '<li class="' . $itemClass . '"><a href="' . pageUrl(variable(SAFENODEVAR)) . '" class="' . $anchorClass . '">' . getHtmlVariable('nodeName') . '</a>';
	}

	//doesnt need / (copied from node-menu)
	$namesOfFiles = false;
	if (disk_file_exists($folder . '_menu-items.tsv')) {
		$tsvInfo = menu('/' . $where . '/', ['return-tsv-info' => true]);
		$files = $tsvInfo['files'];
		$namesOfFiles = $tsvInfo['namesOfFiles'];
	} else {
		$files = disk_scandir($folder);
		natsort($files);
	}
	$nodes = _skipNodeFiles($files);

	$lastName = false;
	foreach ($nodes as $fol) {
		$item = getFolderMeta($folder, $fol, '', $ix++);
		if ($lastName == $item['name_urlized'] || $item['name_urlized'] == 'home') { $ix--; continue; }
		$lastName = $item['name_urlized'];
		if ($namesOfFiles && isset($namesOfFiles[$lastName]))
			$item['name_humanized'] = $namesOfFiles[$lastName];
		$sectionItems[] = $item;
	}

	$relativeUrl = (nodeIsNot(variable('section')) ? nodeValue() . '/' : '') . ($breadcrumbs ? implode('/', $breadcrumbs) . '/' : '');

	if (hasPageParameter('generate-index')) {
		addScript(features::engage, COREASSETS);
		echo '<textarea class="autofit">' . NEWLINE;
		echo '<!--use-blocks-->' . NEWLINES2;
		foreach ($sectionItems as $item) {
			echo '## ' . humanize($item['name_urlized']) . NEWLINE;
			echo 'Keywords ' . $item['tags'] . NEWLINES2;
			echo $item['about'] . NEWLINES2;
		}

		echo '</textarea>' . NEWLINE;
	} else if (variable('directory_use_excerpts')) {
		$last = count($sectionItems);
		foreach ($sectionItems as $ix => $item) {
			$slug = $item['name_urlized'];
			$extn = disk_one_of_files_exist($file = $folder . $slug . '/home.', 'md');
			if (!$extn) showDebugging('md files only allowed for "excerpt"!!', 'Expected: ' . $file . 'md', true, true);
			if ($ix > 0) echo cbCloseAndOpen('mt-3 container' . ($ix == count($sectionItems) - 1 ? ' mb-5' : ''));
			renderExcerpt($file . 'md', pageUrl($slug), h2(makeLink($item['name_humanized'], pageUrl($slug)), '', true));
		}
	} else {
		features::ensureTables();
		$template = '<tr><td><a href="%url%' . $relativeUrl .
			'%name_urlized%">%name_humanized%</a></td><td>%about%</td><td>%tags%</td><td>%size%</td></tr>';
		$params = ['use-datatables' => count($sectionItems) > 5];
		(new tableBuilder(INPAGETABLE, $sectionItems, 'name_urlized, about, tags, size', $template, $params))->render();
	}

	contentBox('end');
	echo GOOGLEON;
}

sectionEnd();
