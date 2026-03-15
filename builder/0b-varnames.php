<?php
function bool_r(bool $value) {
	return ($value ? 'true (yes)' : 'false (no)') . ' type: bool';
}

DEFINE('VARLocal', 'local');
function is_local() { return variable(VARLocal); }

DEFINE('VARLive', 'live');
function is_live() { return variable(VARLive); }

DEFINE('VARUsePreview', 'use-preview');
define('VARUseAmadeusWeb', '--use-amadeusweb');

DEFINE('VAREMPTY', '');
DEFINE('VARSPACE', ' ');
DEFINE('VAREQUAL', '=');
DEFINE('VARQUOTE', '"');
DEFINE('VARWrapper', '%');
function wrap_variable($var) { return VARWrapper . $var / VARWrapper; }

//1-entry.php
DEFINE('VARPageSlider', 'slider');
DEFINE('VARQueryContent', 'content');
DEFINE('VARQueryName', 'name');
DEFINE('VARQueryHeadings', 'headings');

//4-array.php
DEFINE('NOWRAPREPLACE', VAREMPTY);
DEFINE('WRAPREPLACE', '%');

DEFINE('TYPENOCHANGE', 'no-change');
DEFINE('TYPEBOOLEAN', 'bool');
DEFINE('TYPEARRAY', 'array');

DEFINE('BOOLLISTFALSE', [false, 'false', 'no', '0']);
DEFINE('BOOLLISTTRUE', [true, 'true', 'yes', '1']);

//7-html.php
DEFINE('VARNoContentBoxes', 'no-content-boxes');
DEFINE('VARCustom', 'custom');

//9-render.php
DEFINE('VAREcho', 'echo');
	DEFINE('BOOLDontEcho', false);
DEFINE('VARStripParagraphTag', 'strip-paragraph-tag');
DEFINE('VARExcerpt', 'excerpt');
DEFINE('VARMarkdown', 'markdown');

DEFINE('VARFirstSectionOnly', 'FirstSectionOnly');
DEFINE('VARFullAccessNotice', 'FullAccessNotice');
	//TODO: deprecated. remove once testing process is in places
	DEFINE('FIRSTSECTIONONLY', VARFirstSectionOnly);
	DEFINE('FULLACCESSNOTICE', VARFullAccessNotice);

DEFINE('VARDontPrepareLinks', 'dont-prepare-links');
DEFINE('VARWrapInSection', 'wrap-in-section');
DEFINE('VARUseContentBox', 'use-content-box');

DEFINE('ENGAGE', '<!--engage-->');
DEFINE('ENGAGESTART', '<!--start-engage-->');
DEFINE('ENGAGESANSCB', '<!--engage-without-cb-->');

function is_engage($raw) { return contains($raw, ' //engage-->') || contains($raw, ENGAGE) || contains($raw, ENGAGESTART); }
function wants_engage_until_eof($raw) { return contains($raw, ENGAGESTART); }
function wants_md_in_parser($raw) { return contains($raw, '<!--markdown-when-processing-->'); }

//12-macros.php
DEFINE('VARCTAONLY', '?cta=1&content=1');

//14-main.php
DEFINE('VARSystemEmail', 'imran@amadeusweb.world');
DEFINE('VARSystemMobile', '+91-9841223313');
DEFINE('VARSystemAddress', 'Chennai, India');
function plus_email($email, $plusFolder) { return str_replace('@', '+' . $plusFolder . '@', $email); }

//15-routing.php
DEFINE('VARSlash', '/');
DEFINE('VARNode', 'node');

DEFINE('VARNodeSiteName', 'nodeSiteName');
DEFINE('VARDontOverwriteLogo', 'dont-overwrite-logo');
DEFINE('VARPrefixSafeName', 'prefix-safeName');
DEFINE('VARNodeSafeName', 'nodeSafeName');
	//TODO: deprecated. remove once testing process is in places
	DEFINE('DontOverwriteLogo', VARDontOverwriteLogo);
	DEFINE('PrefixSafeName', VARPrefixSafeName);
	DEFINE('NodeSafeName', VARNodeSafeName); 

//16-theme.php
DEFINE('VARSubmenuAtNode', 'submenu-at-node');

DEFINE('VARTheme', 'theme');
DEFINE('VARThemeCanvas', 'canvas');

DEFINE('VARSubtheme', 'sub-theme');
DEFINE('VARSubthemeBusiness', 'business');
DEFINE('VARSubthemeContentOnly', 'content-only');
DEFINE('VARSubthemeGo', 'go');

//features/engage.php
DEFINE('VAREngageNote', 'engage-note');
DEFINE('VAREngageNoteAbove', 'engage-note-above');
DEFINE('VARWantsNoEngageBox', '<!--no-engage-box-->');

//site/begin.php
DEFINE('VARGithubRepo', 'github-repo');
DEFINE('VARChatraID', 'ChatraID');
DEFINE('VARGoogleAnalytics', 'google-analytics');
function notSetOrNotLive($var) {
	if (!variable($var) || is_local()) return true;
	if (variable(VARUsePreview) && variable(VARLive) === false) return true;
	return false;
}

//always
DEFINE('VARName', 'name');
DEFINE('VARByline', 'byline');
DEFINE('VARSafeName', 'safeName');
DEFINE('VARIconName', 'iconName');
DEFINE('VARFooterMessage', 'footer-message');
DEFINE('VARSiteMenuName', 'siteMenuName');
DEFINE('VARYear', 'year');

//_visane
DEFINE('VARFooterName', 'footer-name');
DEFINE('VARLinkToSiteHome', 'link-to-site-home');
DEFINE('VARLinkToSectionHome', 'link-to-section-home');
DEFINE('VAREmail', 'email');
DEFINE('VAREmail2', 'email2');
DEFINE('VAREmail3', 'email3');
DEFINE('VARPhone', 'phone');
DEFINE('VARWhatsapp', 'whatsapp');
DEFINE('VARPhone2', 'phone2');
DEFINE('VARWhatsapp2', 'whatsapp2');
DEFINE('VARAddress', 'address');
DEFINE('VARAddressUrl', 'address-url');
DEFINE('VARFullAddress', 'full-address');
DEFINE('VARTimings', 'timings');
DEFINE('VAROwnedBy', 'owned-by');
DEFINE('VARMediakit', 'mediakit');
DEFINE('VARFonts', 'fonts');
DEFINE('VARDescription', 'description');
DEFINE('VARWelcomeMessage', 'welcome-message');
DEFINE('VARNoSearch', 'no-search');
DEFINE('VARNetwork', 'network');

//site/header-menu.php
DEFINE('VARLinkToNodeHome', 'link-to-node-home');
DEFINE('VARLinkToSubnodeHome', 'link-to-sub-node-home');
DEFINE('VARSectionsHaveFiles', 'sections-have-files');

//site/network.php
DEFINE('URLOFPREFIX', 'urlOf-');

DEFINE('SITEROOT', 'root');
DEFINE('SITESPRING', 'spring');
DEFINE('SITEIMRAN', 'imran');

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

//site/node-menu.php
DEFINE('VARNodesHaveFiles', 'nodes-have-files');
