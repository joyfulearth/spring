<?php
/**
 * This php framework is Proprietary, Source-available software!
 * It is licensed for distribution at the sole discretion of it's owner Imran.
 * Copyright Oct 2019 -> 2026, JoyfulEarth.org, All Rights Reserved!
 *     
 * Author:    Imran Ali Namazi <imran@joyfulearth.org>
 * Architect: https://imran.joyfulearth.org/
 * Website:   https://spring.joyfulearth.org/
 * Source:    https://github.com/joyfulearth/spring
 * License:   https://github.com/joyfulearth/spring#License-1-ov-file
 * Note: AmadeusWeb Spring v9.4 is based on 25 years of Imran's programming experience.
 * You MUST agree to the "proprietary" nature and Imran's PULL PLUG RIGHTS
 * Rights:    https://spring.joyfulearth.org/dawn/proprietariness/with-ai/2025-12--16th-chat/
 */

DEFINE('AMADEUSROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
DEFINE('AMADEUSSITEROOT', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR);
DEFINE('ALLSITESROOT', dirname(AMADEUSROOT) . DIRECTORY_SEPARATOR);
DEFINE('AMADEUSTHEMESFOLDER', AMADEUSROOT . 'themes/');

DEFINE('AMADEUSBUILDER', __DIR__ . DIRECTORY_SEPARATOR);
	DEFINE('AMADEUSFRAMEWORK', __DIR__ . DIRECTORY_SEPARATOR);
	DEFINE('AMADEUSCORE', __DIR__ . DIRECTORY_SEPARATOR);

DEFINE('AMADEUSFEATURES', AMADEUSBUILDER . 'features/');
DEFINE('AMADEUSMODULES', AMADEUSBUILDER . 'modules/');
DEFINE('AMADEUSDATA', AMADEUSBUILDER . 'data/');

function handoverSansStats() {
	$critical = [
		AMADEUSBUILDER . '0a-process.php', //for parsing when ?process=1
		AMADEUSBUILDER . '2-stats.php', //start time, needed to log disk load in files.php
		AMADEUSBUILDER . '3-files.php', //disk_calls, needed first to measure include times
	];

	foreach ($critical as $file)
		include_once($file);

	foreach ($critical as $file)
		processComments($file);
}

handoverSansStats();

function runFrameworkFile($name) {
	$file = AMADEUSBUILDER . $name . '.php';
	disk_include_once($file);
	processComments($file);
}

function runModule($name) {
	runFrameworkFile('modules/' . $name);
}

function runFeature($name, $variables = []) {
	runFrameworkFile('features/' . $name, $variables);
}

function runFeatureMultiple($name, $variables = []) {
	$file = AMADEUSBUILDER . 'features' . $name . '.php';
	disk_include($file, $variables);
	processComments($file);
}

runFrameworkFile('0b-varnames');
runFrameworkFile('0c-builder-base');

runFrameworkFile('4-array');
runFrameworkFile('5-vars');
runFrameworkFile('6-text'); //needs vars
runFrameworkFile('7-html');
runFrameworkFile('8-menu');

//New from 4.1 to 8.5
runFrameworkFile('9-render');
runFrameworkFile('10-seo');
runFrameworkFile('11-assets');
runFrameworkFile('12-macros');
runFrameworkFile('13-builtin'); //was special
runFrameworkFile('14-main');
runFrameworkFile('15-routing');
runFrameworkFile('16-theme');

//New in v9.3
runFrameworkFile('17-pollen');
runFrameworkFile('18-related');
runFrameworkFile('19-spring');
runFrameworkFile('20-social-builder');

//New in v9.4
runFrameworkFile('21-site');

class features {
	const blurbs = 'blurbs';
	const deck = 'deck';
	const directory = 'directory';
	const engage = 'engage';
	const familyTree = 'family-tree';
	const pollen = 'pollen';
	const share = 'share';
	const underConstruction = 'under-construction';
	const tables = 'tables';

	const shareQS = '?share=1&content=1';

	static function ensureDirectory() { runFeature(self::directory); } //call either this, OR runMultiple for sitemap
	static function ensureEngage() { runFeature(self::engage); }
	static function ensureTables() { runFeature(self::tables); }
	static function runPage($what) { runFrameworkFile('pages/' . $what); }
	static function runMultiple($what, $vars = []) { runFeatureMultiple($what, $vars); }
	static function runWithFile($what, $file) { self::runMultiple($what, ['file' => $file]); }
	static function runPollen($items = []) { runFeature(self::pollen, ['items' => $items]); }
}

function before_bootstrap() {
	$port = $_SERVER['SERVER_PORT'];

	$testMobile = 80; //80 for normal, 8000 to simulate mobile/no-url-rewrite
	$isMobile = $testMobile != 80 || startsWith(__DIR__, '/storage/');
	
	variable('port', $port != $testMobile ? ':' . $port : '');

	variable(VARLocal, $local = startsWith($_SERVER['HTTP_HOST'], 'localhost'));

	variable('app', $spring = $local && !$isMobile ? getUrlFrom('spring') : getUrlFrom('spring', 'live-url'));

	addNetworkUrl(SITEROOT, getUrlFrom('joyfulearth'));
	addNetworkUrl(SITESPRING, getUrlFrom('spring'));
	//NOTE: no more self hosted. //TODO: HI: allow
	variable('app-themes', $spring . 'themes/');

	variable(assetKey(COREASSETS, ASSETFOLDER), AMADEUSROOT . 'assets/');
	variable(assetKey(COREASSETS), $spring . 'assets/');

	$php = contains($_SERVER['DOCUMENT_ROOT'], 'magique') || contains($_SERVER['DOCUMENT_ROOT'], 'Magique');
	variable('is-mobile', $isMobile || $php);

	variable('no_url_rewrite', $isMobile || $php);
	if ($isMobile || $php) variable('scriptNameForUrl', 'index.php/'); //do here so we can simulate usage in site.php

	runModule('markdown');
	runModule('wordpress');
}

if (!DEFINED('AMADEUSPRODUCT'))
	before_bootstrap();

//Now this only sets up the node and page parameters - rest moved to before_bootstrap()
function bootstrap($config) {
	variables($config);

	$noRewrite = variable('no_url_rewrite');
	if ($noRewrite) $node = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	else $node = getQueryParameter(VARNode, '');

	$node = removeSlash($node, 'both');

	if ($node == '') $node = SITEHOME;
	variable('all_page_parameters', $node); //let it always be available

	if (strpos($node, '/') !== false) {
		$slugs = explode('/', $node);
		$node = array_shift($slugs);
		variable('page_parameters', $slugs);
		foreach ($slugs as $ix => $slug) variable('page_parameter' . ($ix + 1), $slug);
		variable(LASTPARAM, $ix + 1);
	}

	variable(NODEVAR, variableOr('node-alias', $node));
}

function getPageParameterAt($index = 1, $or = false) {
	return variableOr('page_parameter' . $index, $or);
}

function removeSlash($node, $where) {
	if (in_array($where, ['both', 'end']) AND endsWith($node, '/')) $node = substr($node, 0, strlen($node) - 1);
	if (in_array($where, ['both', 'start']) AND startsWith($node, '/')) $node = substr($node, 1);
	return $node;
}

function getPageParameters($trail = '/', $baseUrl = true) {
	$base = $baseUrl ? getHtmlVariable('url') : '';
	$all = variable('all_page_parameters');
	if ($all == SITEHOME) {
		$all = '';
		$trail = removeSlash($trail, 'start');
	}
	return $base . ($all ? $all : '') . $trail;
}

function hasPageParameter($param) {////use: VARPage[$param]
	return in_array($param, variableOr('page_parameters', [])) || isset($_GET[$param]);
}

function getQueryParameter($param, $or = false) {////use: VARQuery[$param]
	return isset($_GET[$param]) ? $_GET[$param] : $or;
}

function render() {
	if (function_exists('before_render')) before_render();
	ob_start();

	$theme = variable('theme');
	$embed = variable('embed');

	$fileWanted = $rootFile = in_array(nodeValue(), ['readme', 'license']) ? SITEPATH . '/' . strtoupper(nodeValue()) . '.md' : false;
	$folder = SITEPATH . '/' . (variable('folder') ? variable('folder') : '');
	if (!$rootFile) {
		//asumes logic below will never go to else part
		$contentExt = disk_one_of_files_exist($contentFWE = $folder . nodeValue() . '.', CONTENTFILES);
		$fileWanted = $contentFWE . $contentExt;
	}

	if ($fileWanted) {
		read_seo($fileWanted, true); //so rootFile supports seo
	}

	if (!$embed) {
		renderThemeFile('header', $theme);
		if (function_exists('network_before_file')) network_before_file();
		if (function_exists('before_file')) before_file();
	}

	if (variable(features::underConstruction)) {
		runFeature(features::underConstruction);
		$rendered = true;
	} else if (isset($_GET[features::share])) {
		features::runPage(features::share);
		$rendered = true;
	} else if (isset($_GET['cta'])) {
		h2(title(FORHEADING), cssUX::CenterContainer);
		echo getCodeSnippet('cta-or-engage', CORESNIPPET);
		$rendered = true;
	} else if (hasPageParameter(VARPageSlider)) {
		$rendered = true; //dont want to render content. and needed here as it shouldnt support "content" menu pages
	} else if (variable('skip-content-render')) {
		$rendered = false;
	} else if ($rootFile) {
		h2(variable('name') . ' &mdash; ' . humanizeThis(), cssUX::CenterContainer);
		contentBox('root-file', 'container content-box');
		renderAny($rootFile);
		contentBox('end');
		$rendered = true;
	} else {
		$rendered = false;
		if ($contentExt) {
			$rendered = true;
			builtinOrRender($file = $contentFWE . $contentExt, false, !variable('skip-container-for-this-page'));
			pageMenu($file);
		}
	}

	if (!$rendered) {
		if (function_exists('did_render_page') && did_render_page()) {
			//noop
		} else if ($missing = getSnippet('missing-page')) {
			if (!hasVariable('showing-media'))
				h2(title(FORHEADING), cssUX::CenterContainer);
			contentBox('missing-page', cssUX::container);
			renderMarkdown($missing);
			contentBox('end');
		} else {
			//NOTE: Uses output buffering magic methods to delay sending of output until 404 header is sent 
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
			ob_flush();

			if (isset($_GET['debug'])) {
				echo 'NOTE: Turning on stats so you can additionally see what files are included! This appears below the footer' . variable('brnl') . variable('brnl');

				$verbose = $_GET['debug'] == 'verbose';
				if ($verbose) {
					global $cscore;
					showDebugging('ALL AMADEUS VARS - global $cscore;', $cscore);
				}
			}

			$breadcrumbs = variable('breadcrumbs');
			$file = nodeValue(); $message = 'at level 1 (content / section / node)';
			if ($breadcrumbs) {
				$file = BRNL . variableOr('all_page_parameters', 'node/section missing?');
				$message = 'found only these valid params:' . BRNL . BRNL . '<strong>' . implode(' &mdash; ', $breadcrumbs) . '</strong>';
			}
			error('<h1 class="alert alert-danger rounded-pill mt-3 mb-0">Couldn\'t find page:</h1>'
				. '<h2 class="mt-3 mb-3">' . $file . '</h2>' . BRNL . NEWLINE . '<p class="rounded-pill alert alert-secondary">' . $message . '</p>');
		}
	}

	ob_end_flush();

	if (!$embed) {
		if (function_exists('pollenAt')) pollenAt('embed');
		if (function_exists('after_file')) after_file();
		renderThemeFile('footer', $theme); //theme.php is now responsible for calling stats before styles+scipts as the table feature requires its usage and it will be before </body>
	}

	if (function_exists('after_render')) after_render();
}

function copyright_and_credits($separator = '<br>', $return = false) {
	$copy = _copyright(true);
	$cred = _credits('', true);
	$result = $copy . $separator . $cred;
	if ($return) return $result;
	echo $result;
}

function _copyright($return = false) {
	if (variable('dont_show_copyright')) return '';

	$year = date('Y');
	$start = variable('start_year');
	$from = ($start && ($start != $year)) ? $start . ' - ' : '';

	$before = variable(VAROwnedBy) ? '<strong>' . variable('name') . '</strong>, ' : '';
	$after = variable(VAROwnedBy) ? variable(VAROwnedBy) : variable('name');

	$result = '&copy; ' . $before . 'Copyright <strong><span>' . $after . '</span></strong>. ' . $from . $year . ' All Rights Reserved.';
	if ($return) return $result; else echo $result;
}

function _credits($pre = '', $return = false) {
	if (variable('dont_show_credits')) return '';

	$utm = '?utm_content=site-credits&utm_referrer=' . variable(VARSafeName);

	$img = '<img src="' . getSiteUrl(SITESPRING) . 'amadeusweb-work-logo.png" height="40" alt="AW Spring" class="m-2 align-middle rounded-2">';

	$skipBranding = in_array(variable(VARDAWNMenu), BOOLLISTFALSE);

	$result = $pre . 'Powered by' . getLink($img, getSpecialUrl('root') . $utm, 'd-inline-block', true) . NEWLINE;
	if (!$skipBranding)
		$result .= getLink('Request a Service', getSpecialUrl('signup') . $utm, bootstrapAndUX::button(bootstrapAndUX::primary));

	if ($return) return $result; else echo $result;
}
