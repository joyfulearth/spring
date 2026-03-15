<?php
DEFINE('MORETAG', '<!--more-->');

DEFINE('EXCERPTSTART', '<!--start-excerpt-->');
DEFINE('WANTSNOPARATAGS', '<!--no-p-tags-->');
DEFINE('WANTSNOPROCESSING', '<!--no-processing-->');
DEFINE('WANTSNOCONTENTBOX', '<!--no-content-box-->');
DEFINE('NOREPLACES', '<!--no-replaces-->');

DEFINE('WANTSMARKDOWN', '<!--markdown-->' . NEWLINE); //NOTE: to detect content which doesnt start with a heading
DEFINE('WANTSAUTOPARA', '<!--autop-->');

DEFINE('TAGSECTION', '<section>');
DEFINE('TAGSECTIONEND', '</section>');
DEFINE('TAGDIVEND', '</div>');
DEFINE('TAGBOLD', '<strong class="bg-info py-2 px-4 rounded-pill my-2 d-inline-block">');
DEFINE('TAGBOLDEND', '</strong>');

///Tag Helpers
function currentUrl() {
	return pageUrl(variable('all_page_parameters'));
}

function printNodeHeading($noEnd = false) {
	sectionId('node', 'container text-center');
	h2(humanizeThis(), 'amadeus-icon');
	if (!$noEnd)
		sectionEnd();
}

function pageUrl($relative = '') {
	if ($relative == '') return variable('page-url');
	$hasQuerysting = contains($relative, '?');
	$hasHash = contains($relative, '#');
	if (!endsWith($relative, '/') && !$hasHash && !$hasQuerysting)
		$relative .= '/';
	return variable('page-url') . stripHomeFromUrl($relative);
}

function stripHomeFromUrl($slug) {
	return str_replace('/home', '', $slug);
}

function scriptSafeUrl($url) {
	return $url . variableOr('scriptNameForUrl', '');
}

function fileUrl($relative = '') {
	return variable('assets-url') . $relative;
}

function searchUrl() {
	return variable('page-url') . 'search/';
}

function cssClass($items) {
	if (!count($items)) return '';
	return ' class="' . implode(' ', $items) . '"';
}

function sectionId($id, $class = '', $echo = true) {
	$attrs = '';
	if ($id) $attrs .= ' id="' . $id . '"';
	if ($class) $attrs .= ' class="' . $class . '"';
	$r = NEWLINE . '<section' . $attrs . '>' . NEWLINE;
	if (!$echo) return $r; else echo $r;
}

function sectionEnd($echo = true) {
	$r = '</section>' . NEWLINES2;
	if (!$echo) return $r; else echo $r;
}

function iframe($url, $wrapContainer = true) {
	if ($wrapContainer) echo '<div class="video-container">';
	echo '<iframe src="' . $url . '" style="width: 100%; height: 90vh;"></iframe>';
	if ($wrapContainer) echo TAGDIVEND;
}

function cbWrapAndReplaceHr($raw, $class = '') {
	if (variable(VARNoContentBoxes)) return $raw;

	$closeAndOpen = ($end = contentBox('end', '', true)) . ($start = contentBox('', $class, true));
	//TODO: asap! if (substr_count($raw, HRTAG) > 3) runFeature('page-menu');
	return $start . str_replace(HRTAG, $closeAndOpen, $raw) . $end;
}

function cbCloseAndOpen($class = '') {
	return contentBox('end', '', true) . contentBox('', $class, true);
}

function _getCBClassIfWanted($additionalClass) {
	$no = variable(VARNoContentBoxes);
	if ($no && $additionalClass == '') return '';
	$classes = [];
	if ($additionalClass) $classes[] = $additionalClass;
	if (!$no) $classes[] = 'content-box';
	return implode(' ', $classes);
}

function contentBox($id, $class = '', $return = false) { return tagUX::contentBox($id, $class, $return); }
function h2($text, $class = '', $return = false) { return tagUX::heading($text, $class, $return); }
function listItem($html) { return tagUX::listItem($html); }

///Internal Variables & its replacements

function pipeToBR($raw) {
	return replaceItems($raw, [ '|' => BRNL, 'NEWLINE' => NEWLINE ]);
}

function pipeToNL($raw) {
	return replaceItems($raw, [ '|' => NEWLINE ]);
}

function csvToHashtags($raw) {
	if (!contains(',', $raw)) $raw = ', ' . $raw;

	$begin = '<a class="hashtag">#';
	$end = '</a>';
	$replaces = [
		', ' => $end . NEWLINE . ' ' . $begin,
	];

	$offset = strlen('#">' . $end);
	$result = substr(replaceItems($raw, $replaces), $offset);
	if (startsWith($result, $begin . $begin)) $result = substr($result, strlen($begin));
	return $result;
}

function replaceSpecialChars($html) {
	$replaces = [
		'|' => NEWLINE,
		'–' => ' &mdash; ',
		'’' => '\'',
		'“' => '"',
		'”' => '"',
		'®' => '&reg;',
	];
	return replaceItems($html, $replaces);
}

function getHtmlVariable($key) {
	return subVariable('htmlSitewideReplaces', '%' . $key . '%');
}

function replaceHtml($html) {
	//TODO: MEDIUM: Warning if called before bootstrap!
	$key = 'htmlSitewideReplaces';
	$replaces = variable($key);
	if (!$replaces) {
		$node = nodeValue();
		variable($key, $replaces = [
			//Also, we should incorporate dev tools like w3c & broken link checkers
			'%url%' => variable('page-url'),
			getSiteKey(SITESPRING) => getSiteUrl(SITESPRING),

			'%node-assets%' => _resolveFile('', STARTATNODE),
			'%section-assets%' => _resolveFile('', STARTATSECTION),
			'%site-base%' => variable('assets-url'),
			'%site-assets%' => _resolveFile('', STARTATSITE),
			'%core-assets%' => _resolveFile('', STARTATCORE),
			'##theme##' => getThemeBaseUrl(),

			'%cdn%' => variableOr('cdn', variable(variable('is-mobile') || variable('live-cdn') ? 'live-url' : 'assets-url') . 'assets/cdn/'),

			'%currentUrl%' => currentUrl(),
			'%nodeSlug%' => $node,
			'%nodeName%' => humanizeThis(),
			'%nodeUrl%' => pageUrl($node),
			'%nodeItem%' => $ni = getPageParameterAt(1, ''),
			'%nodeItem_r%' => humanize($ni),
			'%nodeItem2%' => $ni = getPageParameterAt(2, ''),
			'%nodeItem2_r%' => humanize($ni),
			'%nodeFullUrl%' => pageUrl(variableOr('nodeSlug', '##no-nodeSlug')),
			'%leafNodeAssets%' => variableOr(assetKey(LEAFNODEASSETS), ''),

			'%admin-email%' => variableOr('systemEmail', variableOr('assistantEmail', '#error--no-email-configured')),
			'%email%' => variableOr(VAREmail, ''),
			'%email2%' => variableOr(VAREmail2, ''),
			'%email3%' => variableOr(VAREmail3, ''),
			'%phone%' => variableOr(VARPhone, ''),
			'%phone2%' => variableOr(VARPhone2, ''),
			'%whatsapp-number%' => $wa = variable(VARWhatsapp), //guaranteed
			'%whatsapp%' => $wame = whatsapp_me($wa), //var used below
			'%whatsapp2-number%' => $wa2 = variable(VARWhatsapp2),
			'%whatsapp2%' => whatsapp_me($wa2),

			'%address%' => variableOr(VARAddress, '[no-address]'),
			'%address2%' => variableOr('address2', '[no-address2]'),
			'%timings%' => variableOr('timings', '[no-timings]'),
			'%address-url%' => variableOr('address-url', '#no-link'),

			'%welcomeMessage%' => markdown(pipeToNL(variable(VARWelcomeMessage))), //links will get picked up
			//TODO:
			//'%network-link%' => networkLink('btn btn-success', '<hr class="mt-5" />'),
			//'%networkName%' => DAWN_NAME,
			'%siteName%' => $sn = variable('name'),
			'%siteName_subject%' => urlencode($sn),
			'%byline%' =>  variable(VARByline),
			'%safeName%' =>  variable(VARSafeName),
			'%section%' => sectionValue(),
			'%section_r%' => humanizeThis(SECTIONVAR),

			'%network-signup%' => getSiteUrl(SITEROOT, VARCTAONLY),
			'%network-helping%' => getSiteUrl(SITEROOT, 'helping/'),
			'%work-signup%' => getSiteUrl(SITEROOT, 'signup/'),
			'%gmail-reponses%' => 'https://mail.google.com/mail/u/0/?ogbl#advanced-search/subject=responds+on+website',
			'%site-engage-btn%' => engageButton('Engage With Us', 'btn btn-lg btn-site'),

			'%nodeUrlUptoLeaf%' => $loc = variable('all_page_parameters'), //experimental
			'%enquiry%' => str_replace(' ', '+', 'enquiry (for) ' . $sn . ' (at) ' . $loc),
			'%optional-content-box-class%' => _getCBClassIfWanted(''),
			'<marquee>' => variable('_marqueeStart'),

			'--large-list--' => cbCloseAndOpen('large-list'),
		]);
		variable('whatsapp-txt-start', $wame);
	}

	if ($hr = variable('htmlReplaces'))
		$html = replaceItems($html, $hr, '%');

	$html = replaceNetworkUrls($html);

	return replaceItems($html, $replaces);
}

function replaceIfContained($html, $variable) {
	if (!contains($html, '%' . $variable . '%'))
		return $html;

	return replaceItems($html, [$variable => markdown(pipeToNL(variable($variable)))], '%');
}

variable('_marqueeStart', '<marquee onmouseover="this.stop();" onmouseout="this.start();">');

variable('_engageButtonFormat', '<a href="javascript: void(0);" class="btn btn-primary btn-%class% toggle-engage" data-engage-target="engage-%id%">%name%</a>');

function engageButton($name, $class, $scroll = false) {
	if ($scroll) $class .= ' engage-scroll';
	//$class .= ' btn-fill';

	return replaceItems(variable('_engageButtonFormat'), [
		'id' => urlize($name),
		'name' => $name,
		'class' => $class],
	'%') . NEWLINE;
}

/// Expects the whole link(s) html to be provided so href to target blank and mailto can be substituted.
function prepareLinks($output) {
	$output = str_replace(pageUrl(), '%url%', $output); //so site urls dont open in new tab. not sure when this became a problem. maybe a double call to prepareLinks as the render methods got more complex.

	$output = str_replace('<a ' . ($find = 'href="' . WAME), '<a ' . NOFOLLOWPREFIX . $find, $output);

	$output = str_replace('href="http', 'target="_blank" href="http', $output); //yea, baby! no need a js solution!
	$output = str_replace('href="mailto', 'target="_blank" href="mailto', $output); //if gmail in chrome is the default, it will hijack current window
	$output = str_replace('~~TARGETNEW', '" target="_blank', $output); //pdf links

	$output = str_replace('%url%', pageUrl(), $output);

	//undo wrongly added blanks
	$output = str_replace('rel="preconnect" target="_blank" ', 'rel="preconnect" ', $output); //new nuance
	$output = str_replace('target="_blank" href="https://fonts.googleapis.com', 'href="https://fonts.googleapis.com', $output);
	$output = str_replace('target="_blank" target="_blank" ', 'target="_blank" ', $output);

	//TODO: " class="analytics-event" data-payload="{clickFrom:'%safeName%' //leave end " as a hack to pile on attributes
	$campaign = isset($_GET['utm_campaign']) ? '&utm_campaign=' . $_GET['utm_campaign'] : '';
	$output = str_replace('#utm', '?utm_source=' . variable(VARSafeName) . $campaign, $output);

	$output = bootstrapAndUX::toButtons($output);
	$output = replaceHtmlShortcuts($output);

	$output = replaceItems($output, ['/class' => '', 'class' => '" class="', ], '~');
	$output = str_replace('NBSP', ' ', $output);

	return $output;
}

function replaceHtmlShortcuts($output) {
	return htmlUX::replaceAll($output);
}

class tagUX {
	const HorizontalRule = 'hr';

	static function selfClosetag($name, $classes, $attributes = []) {
		$attrs = '';
		$attributes['class'] = $classes;
		foreach ($attributes as $attr => $value)
			$attrs .= concatStrings(VAREMPTY, VARSPACE, $attr, VAREQUAL, VARQUOTE, $value, VARQUOTE);
		return "<$name$attrs />";
	}

	static function tag($name, $classes, $id = '', $innerHtml = '') {
		$attrs = ' class="' . $classes . '"';
		if ($id) $attrs = ' id="' . $id . '"' . $attrs;
		return "<$name$attrs>$innerHtml</$name>";
	}

	static function contentBoxClasses($id, $class1, $class2) {
		$classes = func_get_args();
		unset($classes[0]);
		return self::contentBox($id, cssUX::concat($classes), true);
	}

	static function startPhpFile($at = 0) {
		$page = $at == 0 ? nodeValue() : getPageParameterAt($at);
		self::heading(humanize($page), cssUX::CenterContainer);
		tagUX::contentBox($page, cssUX::concat(cssUX::container, cssUX::m2, cssUX::mauto));
	}

	static function contentBoxEnd($return = false) {
		$result = NEWLINE . TAGDIVEND . NEWLINES2;
		if ($return) return $result;
		echo $result;
		return;
	}

	static function contentBox($id, $class = '', $return = false) {
		if ($id == 'end') return self::contentBoxEnd($return);

		$attrs = '';
		if ($id) $attrs .= ' id="' . $id . '"';

		$all = _getCBClassIfWanted($class);
		if ($all) $attrs .= ' class="' . $all . '"';

		$result = NEWLINE . '<div' . $attrs . '>' . NEWLINE;
		if ($return) return $result;
		echo $result;
	}

	static function heading($text, $class = '', $return = false, $level = 2) {
		if ($class) $class = ' class="' . $class . '"';
		$result = '<h' . $level . $class . '>';
		$result .= trim(renderSingleLineMarkdown($text, [VAREcho => BOOLDontEcho]));
		$result .= '</h' . $level . '>' . NEWLINE;
		if ($return) return $result;
		echo $result;
	}

	static function listItem($html) {
		return '	<li>' . $html . '</li>' . NEWLINE;
	}
}

class cssUX {
	//start with caps for prebuilt concatenations
	const CenterContainer = 'container text-center my-3';

	const container = 'container';
	const standout = 'standout';
	const pt4 = 'pt-4';
	const m2 = 'm-2';
	const mauto = 'm-auto';

	static function concat($param1, $param2 = null) {
		$params = is_array($param1) ? $param1 : func_get_args();
		return implode(' ', $params);
	}
}

class htmlUX {
	private static $vars = [];
	private static $names = [
		///note the order is important and should match exactly
		//1 - divs (5)
		self::divLargeListSep, self::divLargeListLA, self::divLargeListLR, self::divLargeList, self::divLargeList,
		//2 - divs (4)
		self::divContainer, self::divCenter500, self::divCenter, self::divRight,
		//3 - divs (5)
		self::divClear, self::divBox, self::divClose, self::divSFClose, self::divSF,
		//4 - bs grid (6)
		self::gridRow, self::grid3, self::grid4, self::grid5, self::grid6, self::grid7, self::grid8, self::grid9,
		//5 - articles / grid (4)
		self::artAllClose, self::artAllHAuto, self::artAll, self::artClose,
		//6 - articles / box (8)
		self::artHAuto3, self::artHAuto4, self::artHAuto6, self::artHAuto12, self::art3, self::art4, self::art6, self::art12,
		//7 - whitespace (4)
		self::wsNewLines2, self::wsNewLine, self::wsJustBR, self::wsCrLf,
		//8 - generic html (6)
		self::cbOPEN, self::cbCNO, self::cbOWC, self::cbCLOSE, self::tagDivStart, self::tagCloseGT,
	];

	private static function ensureVars() {
		$custom = [
			self::cbOPEN[0] => contentBox('', '', true),
			self::cbCNO[0] => cbCloseAndOpen('container my-5'),
			self::cbOWC[0] => contentBox('', 'container', true),
			self::cbCLOSE[0] => contentBox('end', 'mb-2', true),
		];

		if (empty(self::$vars)) {
			foreach (self::$names as $kv)
				self::$vars[$kv[0]] = $kv[1] == VARCustom ? $custom[$kv[0]] : $kv[1];

		}
		return self::$vars;
	}

	static function keyOf(array $array) {
		return $array[0];
	}

	static function valueOf(array $array) {
		$vars = self::ensureVars();
		return $vars[self::keyOf($array)];
	}

	static function replaceAll($output) {
		$vars = self::ensureVars();
		//showDebugging(359, $vars, PleaseDie);
		foreach ($vars as $search => $replace)
			if (contains($output, $search))
				$output = str_replace($search, $replace, $output);
		return $output;
	}

	//1 - divs (5)
	const divLargeListSep = ['DIV-LARGELISTWITHITEMSEPARATOR', '<div class="large-list item-separator">'];
	const divLargeListLA = [
	'DIV-LARGELISTLOWERALPHA', '<div class="large-list lower-alpha item-separator">'];
	const divLargeListLR = ['DIV-LARGELISTLOWERROMAN', '<div class="large-list lower-roman item-separator">'];
	const divLargeList = ['DIV-LARGELIST', '<div class="large-list">'];
	//2 - divs (4)
	const divContainer = ['DIV-PLAINCONTAINER', '<div class="container">'];
	const divCenter500 = ['DIV-MAX-500-CENTER', '<div class="m-auto img-max-500">'];
	const divCenter = ['DIV-CENTER', '<div class="text-center">'];
	const divRight = ['DIV-RIGHT', '<div class="float-right">'];
	//3 - divs (5)
	const divClear = ['DIV-CLEAR', '<div class="clearfix"></div>'];
	const divBox = ['DIV-WITHBOX', '<div class="content-box">'];
	const divClose = ['DIV-CLOSE', TAGDIVEND];
	const divSFClose = ['DIV-SPACEFIX-CLOSE', TAGDIVEND];
	const divSF = ['DIV-SPACEFIX', '<div>'];
	//4 - bs grid (6)
	const gridRow = ['DIV-ROW', '<div class="row">'];
	const grid3 = ['DIV-CELL3', '<div class="col-md-3 col-sm-12">'];
	const grid4 = ['DIV-CELL4', '<div class="col-md-4 col-sm-12">'];
	const grid5 = ['DIV-CELL5', '<div class="col-md-5 col-sm-12">'];
	const grid6 = ['DIV-CELL6', '<div class="col-md-6 col-sm-12">'];
	const grid7 = ['DIV-CELL7', '<div class="col-md-7 col-sm-12">'];
	const grid8 = ['DIV-CELL8', '<div class="col-md-8 col-sm-12">'];
	const grid9 = ['DIV-CELL9', '<div class="col-md-9 col-sm-12">'];
	//5 - articles / grid (4)
	const artAllClose = ['ALLARTICLES-CLOSE', TAGDIVEND];
	const artAllHAuto = ['ALLARTICLES-HAUTO', '<div class="row">'];
	const artAll = ['ALLARTICLES', '<div class="portfolio row grid-container">'];
	const artClose = ['ARTICLE-CLOSE', '</div></article>'];
	//6 - articles / box (8)
	const aSet3 = '-3COL'; const aSet4 = ''; const aSet50 = '-50'; const aSet100 = '-100'; const hAuto = '-HAUTO';
	static function article($aSet = 4, $hauto = true) { return 'ARTICLE' . $aSet . ($hauto ? self::hAuto : '') . '-BOX'; }

	const artHAuto3 = ['ARTICLE-3COL-HAUTO-BOX', '<article class="col-lg-4 col-md-6 col-xs-12 mb-4"><div class="content-box minh-100">'];
	const artHAuto4 = ['ARTICLE-HAUTO-BOX', '<article class="col-lg-3 col-md-6 col-xs-12 mb-4"><div class="content-box minh-100">'];
	const artHAuto6 = ['ARTICLE-50-HAUTO-BOX', '<article class="portfolio-item col-6 mb-4"><div class="grid-inner content-box minh-100">'];
	const artHAuto12 = ['ARTICLE-100-HAUTO-BOX', '<article class="portfolio-item col-12 mb-4"><div class="grid-inner content-box minh-100">'];

	const art3 = ['ARTICLE-3COL-BOX', '<article class="portfolio-item col-lg-4 col-md-6 col-xs-12 mb-4"><div class="grid-inner content-box">'];
	const art4 = ['ARTICLE-BOX', '<article class="portfolio-item col-lg-3 col-md-6 col-xs-12 mb-4"><div class="grid-inner content-box">'];
	const art6 = ['ARTICLE-50-BOX', '<article class="portfolio-item col-6 mb-4"><div class="grid-inner content-box">'];
	const art12 = ['ARTICLE-100-BOX', '<article class="portfolio-item col-12 mb-4"><div class="grid-inner content-box">'];
	//7 - whitespace (4)
	const wsNewLines2 = [' NEWLINES2', '<br /><br />' . NEWLINE];
	const wsNewLine = [' NEWLINE', '<br />' . NEWLINE];
	const wsJustBR = [' JUSTBR', '<br />'];
	const wsCrLf = [' CRLF', NEWLINE];
	//8 - generic html (6)
	const cbOPEN = ['[cb-open]', VARCustom];
	const cbCNO = ['[cb-close-and-open]', VARCustom];
	const cbOWC = ['[cb-open-with-container]', VARCustom];
	const cbCLOSE = ['[cb-close]', VARCustom];

	const tagDivStart = ['STARTDIV ', '<div '];
	const tagCloseGT = [' CLOSETAG', '>'];
}

function url_r($url, $domainOnly = false) {
	$url = replaceItems($url, [
		'preview.' => '',
		'https://' => '',
		'http://' => '',
		'www.' => '',
		'//' => '',
	]);
	if (endsWith($url, '/')) $url = substr($url, 0, strlen($url) - 1);
	return $domainOnly ? explode('/', $url)[0] : $url;
}

DEFINE('NOFOLLOWSUFFIX', '" rel="nofollow');
DEFINE('NOFOLLOWPREFIX', 'rel="nofollow" ');
function nofollowReplace($url, $what = 'src') { return [$find = $what . '="' . $url => NOFOLLOWPREFIX . $find]; }

DEFINE('WAME', 'https://wa.me/');
DEFINE('WAQS', '?text=');

function whatsapp_clean($mob) {
	return replaceItems($mob, ['+' => '', '-' => '', '.' => '']);
}

//nofollow is set below
function whatsapp_me($mob, $text = WAQS) {
	return WAME . whatsapp_clean($mob) . $text;
}

function isSpecialLink($link) {
	foreach (['tel:', WAME, 'mailto:'] as $needle)
		if (contains($link, $needle)) return true;
	return false;
}

function specialLinkVars($item) {
	extract($item);
	//$url, $name and $type sent

	if ($type == VAREmail) $classType = 'fa-classic amadeus-2x-icon rounded-circle bg-info fa-envelope';
	if ($type == VARPhone) $classType = 'fa-classic amadeus-2x-icon rounded-circle bg-info fa-solid fa-phone';

	$class = isset($classType) ? $classType : 'amadeus-2x-icon rounded-circle fa-brands fa-'. $type . ' bg-' . $type;

	if ($type == VARPhone)
		$url = 'tel:' . $url;

	if ($type == VARWhatsapp)
		$url = whatsapp_me($url);

	if ($type == VAREmail)
		$url = 'mailto:' . $url . '?subject=' . replaceItems($name, [' ' => '+']);

	$type = $class;
	return compact('url', 'name', 'type');
}

class bootstrapAndUX extends builderBase {
	const primary = 'primary'; const secondary = 'secondary'; const info = 'info';
	const success = 'success'; const warning = 'warning'; const danger = 'danger';
	const colors = [self::primary, self::secondary, self::info, self::success, self::warning, self::danger];

	const namedButtons = [
		'DOWNLOAD' => 'btn btn-lg btn-primary" target="_blank',
		'SITE' => 'btn btn-info',
		'SECURE' => 'btn btn-danger m-2 bi bi-shield-lock icon-2x',
		'TODO' => 'btn btn-warning bi bi-journal-check" target="_blank',
		'PHONE' => 'btn btn-has-icon btn-info bi bi-telephone ls-2" style="color: #fff;' . NOFOLLOWSUFFIX,
		'WHATSAPP' => 'btn btn-has-icon btn-success bi bi-whatsapp ls-2', //nofollow taken care of in prepareLinks
		'EMAIL' => 'btn btn-has-icon btn-danger bi bi-mailbox ls-2' . NOFOLLOWSUFFIX,
		'MAP' => 'btn btn-has-icon btn-warning bi bi-pin-map ls-2' . NOFOLLOWSUFFIX,
	];

	static $buttonVars = []; //static on demand for optimizing

	private static function buttonVars() {
		if (count(self::$buttonVars) == 0) {
			$btn = 'BTN'; $bigBtn = 'BTNLARGE';
			$start = '" class="m-1 ';
			foreach (self::colors as $color) {
				$colorUpper = strtoupper($color);
				self::$buttonVars[$btn . $colorUpper] = $start . self::button($color);
				self::$buttonVars[$btn . 'OUTLINE' . $colorUpper] = $start . self::buttonOutline($color);
				self::$buttonVars[$bigBtn . $colorUpper] = $start . self::buttonLarge($color);
			}

			foreach (self::namedButtons as $name => $class)
				self::$buttonVars[$btn . $name] = $start . $class;
		}

		return self::$buttonVars;
	}

	//NOTE: for now, supports single css class only
	static function toButtons($html) {
		if (!contains($html, 'BTN')) return $html;
		return replaceItems($html, self::buttonVars());
	}

	static function factory($yesColor, $noColor, $type = 'btn') {
		if (!in_array($type, ['btn', 'btn-lg', 'btn-outline'])) throw new ErrorException(__METHOD__ . ' has unsupported $type: ' . $type); //dbc on the way in
		return (new bootstrapAndUX())->setValue('yes', $yesColor)->setValue('no', $noColor)->setValue('type', $type);
	}

	function yesNobutton($condition, $suffix = '') {
		$type = $this->getSetting('type');
		$color = $this->getSetting($condition ? 'yes' : 'no');
		return self::button($color) . $suffix;
	}

	static function button($color) { return 'btn btn-' . $color; }
	static function buttonOutline($color) { return 'btn btn-outline-' . $color; }
	static function buttonLarge($color) { return 'btn btn-lg btn-' . $color; }
}

class linkBuilder extends builderBase {
	//NOTE: cant keep this in buttonsInText as thats an only when needed static class
	const usePageUrl = 'usePageUrl';
	const content = '/?content=1';

	const openFile = 'strip-extension text-suffix humanize outline-info margins lightbox noPageUrl';
	const openFileInline = self::openFile . ' inline';
	const openFileBlock = self::openFile . ' block';

	const localhostLink = 'new-tab localhost btn-secondary noPageUrl';

	const copyOnClick = 'copy noPageUrl btn btn-lg';
	const copyUrl = self::copyOnClick . ' outline-primary';
	const copyRelUrl = self::copyOnClick . ' outline-danger';

	const link = 'outline-primary margins noPageUrl';
	const selectedLink = 'btn-success margins noPageUrl';
	const innerLink = 'btn-secondary margins';

	static function factory($text, $href, $setting, $echo = false) {
		$do = explode(' ', $setting);

		if (in_array('strip-extension', $do))
			$text = pathinfo($text, PATHINFO_FILENAME);

		if (in_array('text-suffix', $do))
			$href = $href . '/' . $text;

		if (in_array('localhost', $do))
			$href = 'http://localhost/' . $text . '/';

		if (in_array('humanize', $do))
			$text = humanize($text);

		$attrs = '';
		if (in_array('copy', $do)) {
			$attrs = ' onclick="
			const val = new Blob([this.getAttribute(\'href\')], { type: \'text/plain\' });
			navigator.clipboard.write([new ClipboardItem({\'text/plain\': val})]);
			this.classList.add(\'text-decoration-underline\'); this.classList.add(\'fw-bolder\');
			return false;"';
		}

		$result = new linkBuilder($text, $href);

		if ($attrs)
			$result->attrs = $attrs;

		if (in_array('new-tab', $do))
			$result->target = true;

		foreach (bootstrapAndUX::colors as $color) {
			$break = true;
			if (in_array('btn-' . $color, $do))
				$result->btn($color);
			else if (in_array('outline-' . $color, $do))
				$result->btnOutline($color);
			else
				$break = false;
			if ($break) break;
		}

		if (in_array('margins', $do))
			$result->addClass('m-2');

		if (in_array('inline', $do))
			$result->addClass('d-inline-block');

		if (in_array('block', $do))
			$result->addClass('d-block me-3 mb-3');

		if (in_array('lightbox', $do)) {
			$result->attrs .= ' data-lightbox="iframe"';
			$result->href .= contains($result->href, '?') ? str_replace('/?', '&', self::content) : self::content;
			$result->href .= str_replace('?', '&', variable(VARMediakit));
		}

		if (in_array('noPageUrl', $do))
			$result->unset(self::usePageUrl);

		return $result->make($echo);
	}

	private $text, $href, $class, $target, $attrs = '';

	function __construct($text, $href, $class = '', $target = false, $settings = [])
	{
		$this->text = $text;
		$this->href = $href;
		$this->class = $class;
		$this->target = $target;
		$this->settings = $settings;
		$this->setDefault(self::usePageUrl, true);
	}

	function btn($color = 'success') {
		$this->addClass('btn btn-' . $color);
		return $this;	
	}

	function btnOutline($color = 'success') {
		$this->addClass('btn btn-outline-' . $color);
		return $this;	
	}

	function btnOrOutline($color = 'success', $outline = false) {
		if ($outline) $this->btnOutline($color);
		else $this->btn($color);
		return $this;	
	}

	private function addClass($class) {
		$this->class .= ($this->class ? ' ' : '') . $class;
		return $this;
	}

	function make($echo = true, $settings = []) {
		$this->set($settings);

		if ($this->settingIs(self::usePageUrl))
			$this->href = pageUrl($this->href);

		$result = getLink(
			$this->text,
			$this->href,
			$this->class,
			$this->target,
			$this->attrs,
		);

		if (!$echo) return $result;
		echo $result;
	}
}

function makeRelativeLink($text, $relUrl) {
	return '<a href="' . pageUrl($relUrl) . '">' . $text . '</a>';
}

DEFINE('EXTERNALLINK', 'external');

function makeLink($text, $link, $relative = true, $noLink = false, $class= '') {
	if ($noLink) return $text; //Used when a variable needs to control this, else it will be a ternary condition, complicating things
	if ($relative == EXTERNALLINK) $link .= '" target="_blank'; //hacky - will never 
	else if ($relative) $link = pageUrl($link);
	if ($class) $link .= '" class="' . $class;
	return prepareLinks('<a href="' . $link . '">' . $text . '</a>');
}

function urlFromSlugs() {
	return pageUrl(urlize(concatSlugs(func_get_args())));
}

function getLink($text, $href, $class = '', $target = false, $attrs = '') {
	$target = $target ? ' target="' . (is_bool($target) ? '_blank' : $target) . '"' : '';
	if ($class && !contains($class, 'class="')) $class = ' class="' .  $class . '"';
	$params = compact('text', 'href', 'class', 'target', 'attrs');
	return replaceItems('<a href="%href%"%class%%target%%attrs%>%text%</a>', $params, '%');
}

function getLinkWithCustomAttr($text, $href, $attr) {
	$href = ' href="' .  $href . '"';
	$params = compact('text', 'href', 'attr');
	return replaceItems('<a %href%%attr%>%text%</a>', $params, '%');
}

function getIconSpan($what = 'expand', $size = 'large') {
	$theme = variable('theme');
	if ($theme == 'biz-land') {
		$classes = [
			'expand' => 'icofont-expand',
			'expand-swap' => 'icofont-collapse',
			'toggle' => 'icofont-toggle-on',
			'toggle-swap' => 'icofont-toggle-off',
		];
		$sizes = ['large' => 'icofont-2x', 'normal' => 'icofont'];
		return '<span data-add="' . $classes[$what . '-swap'] . '" data-remove="' . $classes[$what] . '" class="icon ' . $classes[$what] . ' ' . $sizes[$size] . '"></span>';
	}
}

function getThemeIcon($id, $size = 'normal')  {
	return '<span class="icofont-1x icofont-' . $id . '"></span>';
}

DEFINE('BODYCLASSES', 'custom-body-classes');

function add_body_class($name) {
	$items = variableOr(BODYCLASSES, []);
	$items[] = $name;
	variable(BODYCLASSES, $items);
}

function body_classes($return = false) {
	$loadingPollen = false; //wantsPollen();
	$op = [];

	$op[] = 'site-' . variable(VARSafeName);
	$op[] = 'theme-' . variable('theme');
	if (hasVariable('sub-theme')) $op[] =  'sub-theme-' . variable('sub-theme');

	$op[] = 'node-' . nodeValue();
	$op[] = 'page-' . (isset($_GET['share']) ? 'share' : str_replace('/', '_', variable('all_page_parameters')));

	$op[] = 'mobile-click-to-expand'; //TODO: configurable!

	if (hasVariable(VARChatraID) && variable(VARChatraID) != 'none' && !is_local() && !$loadingPollen) $op[] = 'has-chatra';

	if (hasVariable(BODYCLASSES)) $op[] = implode(' ', variable(BODYCLASSES));

	$op = implode(' ', $op);
	if ($return) return $op;
	echo $op;
}

function error($html, $renderAny = false, $settings = []) {
	$settings['echo'] = false;
	if ($renderAny) $html = renderAny($html, $settings);
	echo VARErrorStart . $html . TAGDIVEND;
}

define('DEBUGPLAIN', '1');
define('DEBUGSPECIAL', 'special');
define('DEBUGVERBOSE', 'verbose');

function debug($file, $function, $vars, $type = DEBUGPLAIN) {
	if (getQueryParameter('debug') != $type) return;
	
	$file = shortPath($file);
	echo NEWLINE . '<!--' . NEWLINE
		. 'INFO:' . NEWLINE . '	' . implode(NEWLINE . '	', explode(PHP_EOL, printReadable($vars))) . NEWLINE
		. 'FILE: ' . $file . NEWLINE
		. 'FUNCTION: ' . $function . NEWLINE
		. '-->' . NEWLINES2;
}
