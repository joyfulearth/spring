<?php
function getThemeBaseUrl() {
	$themeName = variable('theme');
	$themeUrl = variable('app-themes') . "$themeName/assets/";
	variable('themeUrl', $themeUrl);
	return $themeUrl;
}

function getThemeFile($file, $folder = false) {
	$themeName = variable('theme');
	return concatSlugs([($folder ? $folder : AMADEUSTHEMESFOLDER) . $themeName, $file]);
}

function setTheme($name = VARThemeCanvas) {
	variable(VARTheme, $name);
}

function setSubTheme($name) {
	variable(VARSubtheme, $name);
}

function renderThemeFile($file, $themeName = false) {
	if (variable('site-has-theme')) {
		disk_include_once(SITEPATH . '/theme/' . $file . '.php');
		return;
	}

	if (!$themeName) $themeName = variable('theme');
	$themeFol = concatSlugs([AMADEUSTHEMESFOLDER, $themeName, '']);

	disk_include_once($themeFol . $file . '.php');
}

function getThemeTemplate($end = '-rich-page.php') {
	return getThemeFile(variable('sub-theme') . $end);
}

function getTemplateFrom($file) {
	$file = getThemeFile($file . '.html');
	$bits = explode('##content##', disk_file_get_contents($file));
	return ['header' => $bits[0], 'footer' => $bits[1]];
}

function getThemeBlock($name, $location = false) {
	$file = getThemeFile('blocks/' . $name . '.html', $location);
	$bits = explode('<!--part-separator-->', disk_file_get_contents($file));
	return ['start' => $bits[0], 'item' => $bits[1], 'end' => $bits[2]];
}

function getThemeSection($name, $section, $location = false) {
	$file = getThemeFile('rich-pages/' . $name . '/' . $section . '.html', $location);
	return disk_file_get_contents($file);
}

function getThemeSnippet($name, $location = false) {
	$file = getThemeFile('snippets/' . $name . '.html', $location);
	$html = renderAny($file, ['echo' => false, 'strip-paragraph-tag' => true]);
	$vars = [
		'##theme##' => getThemeBaseUrl(),
		'<br />' => '',
	];
	return NEWLINES2 . replaceItems($html, $vars) . NEWLINE;
}

function includeThemeManager() {
	$mgr = getThemeFile('manager.php');
	disk_include_once($mgr);
}

DEFINE('WIDGETSBELOW', 'widgets-below');
function _useAltFooterDesign() {
	return variableOr('footer-variation', WIDGETSBELOW) == WIDGETSBELOW && !variable('no-footer-alt-design');
}

function runThemePart($what) {
	if (!($content = variable('theme-template'))) {
		$file = getThemeFile(variable('sub-theme') . '.html');
		$bits = explode('##content##', disk_file_get_contents($file));
		$content = ['header' => $bits[0], 'footer' => $bits[1]];
		$content['footer-widgets'] = variable('custom-footer') ? getSnippet('footer') : disk_file_get_contents(getThemeFile('footer/' . variableOr('footer-variation', WIDGETSBELOW) . '.html'));
		variable('theme-template', $content);
	}

	$siteIcon = getLogoOrIcon('icon', 'site');
	$nodeIcon = getLogoOrIcon('icon', 'node');

	if ($what == 'header') {
		$vars = [
			'theme' => getThemeBaseUrl(), //TODO: /version can be maintained on the individual file?
			'optional-page-menu' => '',
			'optional-slider' => '', //this could be a page title too
			'optional-right-button' => '',
			'optional-after-menu' => '',
			'optional-search-trigger' => '',
			'optional-search' => '',
			//deprecate! 'header-align' => '', //an addon class needed if video page title has an image and wants content on right
			'search-url' => searchUrl(),
			//deprecate! 'app-static' => assetMeta(COREASSETS)['location'],
		];

		$icon = '<link rel="icon" href="' . $nodeIcon . '" sizes="192x192">';

		if (wants_only_content())
			add_body_class('wants-only-content');

		$vars['head-includes'] = '<title>' . title() . '</title>' . NEWLINE . '	' . $icon . NEWLINE . main::runAndReturn();
		$vars['seo'] = seo_tags(true);
		$vars['body-classes'] = body_classes(true);

		//TODO: icon link to node home, should have 2nd menu & back to home
		$baseUrl = hasVariable(VARNodeSafeName) && !variable(VARDontOverwriteLogo) ? pageUrl(nodeValue()) : pageUrl();
		$logo2x = getLogoOrIcon('logo', 'node');
		$vars['logo'] = concatSlugs(['<a href="', $baseUrl . variableOr('nodeChildSlug', ''), '">' . NEWLINE
			. '								<img src="', $logo2x, '" class="img-fluid img-max-',
			variableOr('footer-logo-max-width', '500'), '" alt="', variableOr(VARNodeSiteName, variable('name')), '">' . NEWLINE
			. '							</a><br>'], '');

		$vars['optional-page-css'] = [];
		$vars['optional-page-menu'] = _page_menu($siteIcon, $nodeIcon);

		if (!variable(VARNoSearch)) {
			$vars['optional-search-trigger'] = getThemeSnippet('search-trigger');
			$vars['optional-search'] = replaceItems(getThemeSnippet('search'), ['search-url' => searchUrl()], '##');
		}

		$header = _substituteThemeVars($content, 'header', $vars);

		$bits = explode('##menu##', $header);

		echo _renderRaw($bits[0]);
		if (isset($bits[1])) {
			setMenuSettings();
			runFrameworkFile('site/header-menu');
			echo _renderRaw($bits[1]);
		}
		setMenuSettings(true);
	} else if ($what == 'footer') {
		$vars = [
			'theme' => getThemeBaseUrl(), //TODO: /version can be maintained on the individual file?
		];

		if (!variable('footer-widgets-in-enrich')) {
			$logo2x = getLogoOrIcon('logo', 'site');
			$logo = NEWLINE . '			' . concatSlugs(['<a href="', pageUrl(), '">' . NEWLINE .
				'				<img src="', $logo2x, '" style="border-radius: 8px;" class="img-fluid img-logo img-max-500" alt="', variable('name'), '">' . NEWLINE . '			</a><br>'], '');

			$message = !variable(VARFooterMessage) ? '' : (NEWLINE . '			<span class="btn btn-secondary mb-2">' . returnLine(variable(VARFooterMessage)) . '		</span>');

			$contact = getSnippet('contact');
			if (!$contact) $contact = getSnippet('contact', CORESNIPPET);

			//https://www.toptal.com/designers/htmlarrows/arrows/
			$rightArrow = '<span class="h4">&#8608;</span> '; $leftArrow = ' <span class="h4">&#8606;</span>';
			$nodeLink = makeLink(variableOr(VARNodeSiteName, humanizeThis()), pageUrl(nodeIs(SITEHOME) ? '' : nodeValue()), true, false, 'btn-has-icon');
			$nodeName = nodeIs(SITEHOME) ? '' : NEWLINE .
				'				<span class="btn btn-success" style="letter-spacing: 2px;"> ' . $rightArrow
				. $nodeLink . $leftArrow . '</span>' . NEWLINE;

			$altDesign = _useAltFooterDesign();
			$nodeName = NEWLINE . '			<h4 class="' . ($altDesign ? '' : 'mt-sm-4 ') . 'mb-0">' . variableOr(VARFooterName, variable('name')) . '</h4>' . $nodeName;

			$fwVars = [
				'footer-logo' => $logo . NEWLINE . '			<div class="text-center mt-2">' . ($altDesign ? '' : $nodeName) . '</div>',
				'site-widgets' => siteWidgets(),
				VARFooterMessage => '<div class="mt-3">' . ($altDesign ? $nodeName . '<hr class="my-2">' . $message : $message) . '</div>',
				'footer-contact' => $contact,
				'copyright' => _copyright(true),
				'credits' => _credits('', true),
			];

			$vars['footer-widgets'] = _substituteThemeVars($content, 'footer-widgets', $fwVars);
		}

		$footer = _substituteThemeVars($content, 'footer', $vars);

		$atBody = !contains($footer, '##footer-includes##');
		$bits = explode($atBody ? '</body>' : '##footer-includes##', $footer);

		if (wants_only_content()) {
			//noop
		} else if ($after = variable('after-wrapper')) {
			if (!contains($bits[0], $sep = '<!-- #wrapper end -->'))
				showDebugging('expected template to have a wrapper close comment!', $after, true);

			$wabbits = explode($sep, $bits[0]);
			echo _renderRaw($wabbits[0]);
			$tpl = getTemplateFrom($after['template']);
			echo _renderRaw($tpl['header']);
			builtinOrRender($after['file']);
			echo _renderRaw($tpl['footer']);
			echo $sep . _renderRaw($wabbits[1]);
		} else {
			echo _renderRaw($bits[0]);
		}

		print_stats(); //returns if not needed
		if (function_exists('before_footer_assets')) before_footer_assets();
		styles_and_scripts();
		if (function_exists('after_footer_assets')) after_footer_assets();

		if ($atBody) echo '</body>';
		echo _renderRaw($bits[1]);
	}
}

function site_and_node_icons($siteIcon = null, $nodeIcon = null, $nodeSuffix = '') {
	if (!$siteIcon) $siteIcon = getLogoOrIcon('icon', 'site');
	if (!$nodeIcon) $nodeIcon = getLogoOrIcon('icon', 'node' . $nodeSuffix); //todo - remove!

	$breadcrumbs = [_iconLink($siteIcon)];
	$nodeLink = '';
	foreach (nodeVarsInUse() as $index) {
		$vars = variable('NodeVarsAt' . $index);
		$breadcrumbs[] = _iconLink(getLogoOrIcon('icon', $vars), $nodeLink . $vars['nodeSlug']);
		if (!nodeIs($vars['nodeSlug'])) $nodeLink = ($nodeLink ? $nodeLink . '/' : '') . $vars['nodeSlug'] . '/';
	}

	return implode(BREADCRUMBSEPARATOR . NEWLINE, $breadcrumbs);
}

function _iconLink($icon, $slug = '') {
	return '<a href="' . pageUrl($slug) . '">' . NEWLINE . '		<img height="40" src="' . $icon . '" /></a>' . NEWLINE;
}

function _iconImage($src) {
	return NEWLINE . '		<img height="90" src="' . $src . '" /></a></li>' . NEWLINE;
}

function _page_menu($siteIcon, $nodeIcon) {
	if (!variable(VARSubmenuAtNode)) return '<!--no-page-menu-->';

	$menuFile = getThemeFile('snippets/page-menu.html');
	$menuContent = disk_file_get_contents($menuFile);

	$siteOnly = variable(VARDontOverwriteLogo) && lastNodeVarsIndex() < 2;
	$name = humanize(variable(VARNodeSiteName));
	$menuVars = $siteOnly ? [
		'menu-title' => NEWLINE . _iconLink($siteIcon) . BREADCRUMBSEPARATOR
		 . getLink($name, pageUrl(variable('nodeSlug')), 'btn btn-site') . NEWLINE,
	] : [
		'menu-title' => NEWLINE . site_and_node_icons($siteIcon, $nodeIcon)
			 . $name . NEWLINE,
	];
	$menuContent = replaceItems($menuContent, $menuVars, '##');

	$menuBits = explode('##page-menu##', $menuContent);

	doToBuffering(1);

	echo _renderRaw($menuBits[0]);
	setMenuSettings('page-menu');
	runFrameworkFile('site/node-menu');
	echo _renderRaw($menuBits[1]);

	$result = doToBuffering(2);
	doToBuffering(3);
	return $result;
}

function _substituteThemeVars($content, $what, $vars) {
	if (function_exists('enrichThemeVars'))
		$vars = enrichThemeVars($vars, $what);

	if ($what == 'header') {
		$vars['optional-page-css'] = implode($vars['optional-page-css']);
		if ($vars['optional-slider'] == '')
			$vars['body-classes'] = $vars['body-classes'] . ' no-slider';
	}
	return replaceItems($content[$what], $vars, '##');
}

function _renderRaw($html) {
	return renderAny($html, ['raw' => true, 'echo' => false]);
}

function setMenuSettings($after = false) {
	if ($after === true) {
		variable('menu-settings', false);
		return;
	}

	$pm = $after == 'page-menu';
	$prefix = $pm ? 'page-' : '';
	//same as non-profit header
	variable('menu-settings', [
		'isPageMenu' => $pm,
		'noOuterUl' => false,
		'groupOuterUlClass' => $prefix . 'menu-container',
		'outerUlClass' => 'menu-container',
		'ulClass' => $pm ? 'page-menu-sub-menu' : 'sub-menu-container',
		'itemClass' => $prefix . 'menu-item',
		'subMenuClass' => $pm ? 'page-menu-sub-menu' : 'sub-menu',
		'itemActiveClass' => 'current',
		'anchorClass' => $pm ? '' : 'menu-link',
		'wrapTextInADiv' => true,
		'topLevelAngle' => $pm ? '<i class="sub-menu-indicator fa-solid fa-caret-down"></i>' : '<i class="icon-angle-down"></i>',
	]);
}

function siteWidgets() {
	//Do Better - if (variable('node-alias')) return '';

	$colsInUse = 0;

	$showSections = variable(VARLinkToSectionHome) && !variable('no-sections-in-footer');
	if ($showSections) $showSections = count($sections = variableOr('sections', []));
	if ($showSections) $colsInUse += 1;

	$showNetwork = !variable('no-network-in-footer') && variable('network');
	if ($showNetwork) $showNetwork = count($sites = variableOr('networkSites', []));
	if ($showNetwork) $colsInUse += 1;

	$showSocial = !variable('no-social-in-footer');
	if ($showSocial) $showSocial = count($social = variableOr(socialBuilder::variableName, main::defaultSocial()));
	if ($showSocial) $colsInUse += 1;

	if ($colsInUse == 0) return '';

	//adjust
	$grid = [1 => 12, 2 => 6, 3 => 4];
	$colspan = $grid[$colsInUse];

	$start = sprintf('<div id="footer-[WHAT]" class="col-md-%s mt-sm-2 pt-xs-3"><hr class="d-sm-none">', $colspan) . NEWLINE;

	//TODO: Showcase + Misc
	$op = [];

	if ($showSections) {
		$op[] = str_replace('[WHAT]', 'sections', $start);
		$op[] = '<h4 class="mb-1">Sections</h4>';
		$class = variableOr('sections-class', '');
		foreach ($sections as $slug)
			$op[] = getLink(humanize($slug), pageUrl($slug), $class) . ($class ? '' : BRNL);
		$op[] = '</div>'; $op[] = '';
	}

	if ($showNetwork) {
		$op[] = str_replace('[WHAT]', 'network', $start);
		$op[] = '<h4 class="mb-1">' . networkLink() . '</h4>';

		$urlKey = _getUrlKeySansPreview();
		$brYes = _useAltFooterDesign() ? NEWLINE : BRNL;
		foreach ($sites as $ix => $site)
			$op[] = is_string($site) ? ($ix > 0 ? BRNL : '') . '<u class="m-1 ms-3">' . substr($site, 1) . '</u>'
				: getLink('<img src="' . $site[$urlKey] . $site['key'] . '-icon.png" height="28" class="me-2" /> ' . $site['name'], $site[$urlKey],
					'btn bg-light btn-outline-success m-1', true) . $brYes;
		$op[] = '</div>'; $op[] = '';
	}

	if ($showSocial) {
		$op[] = str_replace('[WHAT]', socialBuilder::variableName, $start);
		$op[] = '<h4 class="mb-1">' . variableOr('social-caption', 'Social') . '</h4>';
		appendSocial($social, $op);
		$op[] = '</div>'; $op[] = '';
	}

	return implode(NEWLINE, $op);
}

function networkLink($class= '', $prefix = '') {
	if (!DEFINED('SITENETWORK')) return 'Network';
	return $prefix . getLink('Our Network', subVariableOr('networkHome', 'url', '#todo/') . 'our-network/', $class, true);
}

function appendSocial($social, &$op) {
	if (empty($social)) return;

	$separatorType = variableOr('social-separator', 'pipe');
	$separator = $separatorType == 'pipe' ? PIPEWS : '<hr class="'. ($separatorType == 'newline-with-hr' ? '' : 'invisible ') .'mt-3 mb-0 w-50 mx-auto" />';
	$lastIndex = count($social) - 1;
	$class = variableOr('social-class', 'text-light');
	foreach($social as $ix => $item) {
		if ($item == socialBuilder::HR) { $op[] = '<hr class="mt-3 mb-0 w-50 mx-auto" />'; continue; }

		$wantsItalics = !contains($item['type'], 'png-icon');
		$nextIsSpacer = $ix < count($social) - 1 && $social[$ix + 1] == '----';
		$op[] = '<a target="_blank" href="' . $item['url'] . '"' . (!$wantsItalics ? ' class="' . $item['type'] . '"' : '') . '>';
		$op[] = ($wantsItalics ? '	<i class="social-icon si-mini rounded-circle lh-3 ' . $class . ' ' . (contains($item['type'], ' ')
			? $item['type'] : 'fa-brands fa-'. $item['type'] . ' bg-' . $item['type']) . '"></i> ' : '') . $item['name'] . '</a>'
			. ($ix < $lastIndex && !$nextIsSpacer ? $separator : '');
		$op[] = '';
	}
}

function getBreadcrumbs($items) {
	$op = [];
	foreach ($items as $slug => $text)
		$op[] = '<li class="breadcrumb-item">' . getLink($text, replaceHtml($slug)) . '</li>';
	return implode(NEWLINE . '			', $op);
}
