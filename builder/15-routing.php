<?php
DEFINE('OURNETWORK', '');
DEFINE('DOMAINKEY', 'showIn');

function sluggize($relPath) {
	if (!contains($relPath, '/')) return $relPath;
	$slugs = explode('/', $relPath);
	return end($slugs);
}

DEFINE('SITEURLKEY', 'site-url-key'); //typo proof

function _getUrlKeySansPreview() {
	return (is_local() ? VARLocal : VARLive) . '-url';
}

function getSiteUrlKey() {
	$usePreview = variableOr(VARUsePreview, false);
	$local = is_local(); //this is now in before_bootstrap

	//NOTE: tests preview urls locally
	//$local = false; $preview = true;

	if (!$usePreview) {
		$result = ($local ? VARLocal : VARLive) . '-url';
		variable(SITEURLKEY, $result);
		return $result;
	}

	$live = is_live();
	$testSafeHost = variableOr('testingHost', $_SERVER['HTTP_HOST']);
	$preview = hasVariable('preview') ? variable('preview') :
		($local ? !$live : contains($testSafeHost, 'preview'));

	$result = ($local ? 'local-' : 'live-') . ($preview ? 'preview-' : '') . 'url';
	//showDebugging('ROUTING', ['key' => $result, VARLive => $live, VARLocal => $local, 'preview' => $preview ]);

	variable('preview', $preview);
	variable(SITEURLKEY, $result);
	return $result;
}

function wants_only_content($ctaCheck = true) {
	$result = getQueryParameter('content');
	if ($ctaCheck AND getQueryParameter('cta'))
		$result = false;
	return $result;
}

DEFINE('CDNAUTO', 'auto');
DEFINE('CDNLIVESUBDOMAIN', 'cdn.');
function setup_cdn($fol = CDNAUTO, $local = true, $live = true) {
	$cdn = 'https://cdn.joyfulearth.org/';
	if ($fol == CDNAUTO) $fol = pathinfo(SITEPATH, PATHINFO_FILENAME) . '/';
	if (is_local()) {
		if ($local === false) return;
		$cdn = 'http://localhostcdn/';
		$cdn .= $fol;
		define('ROOTCDNPATH', realpath(ALLSITESROOT . '../../cdn') . '/');
	} else {
		if ($live === false) return;
		if ($live === true)
			$cdn .= $fol;
		else
			$cdn = str_replace($prefix = 'https://', $prefix . $live, variable(VARLive . '-url'));
		define('ROOTCDNPATH', ALLSITESROOT . '_cdn/');
	}
	DEFINE('SITECDNPATH', ROOTCDNPATH . $fol);
	variable('cdn', $cdn);
}

DEFINE('MENUNAME', 'menu_name');
DEFINE('FILELOOKUP', 'file_lookup');
DEFINE('MENUITEMS', 'menu_items');
function getSectionKey($slug, $for) {
	return 'this_' . $slug . '_' . $for;
}

function getSectionFrom($dir) {
	return pathinfo($dir, PATHINFO_FILENAME);
}

DEFINE('LASTPARAM', 'last-page');
DEFINE('NODEVAR', VARNode);
DEFINE('SITEHOME', 'index');
function nodeValue() { return variable(NODEVAR); }
function nodeIs($what) { return nodeValue() == $what; }
function nodeIsNot($what) { return nodeValue() != $what; }
function nodeIsOneOf($whatAll) { return in_array(nodeValue(), $whatAll); }
function lastParamIs($what) { return lastParam() == $what; }
function lastParam() { return getPageParameterAt(variable(LASTPARAM)); }

DEFINE('SECTIONVAR', 'section');
function sectionValue() { return variable(SECTIONVAR); }
function sectionIs($what) { return sectionValue() == $what; }
function nodeIsSection() { return nodeValue() == sectionValue(); }


DEFINE('SAFENODEVAR', 'safeNode');

DEFINE('USEDNODEVAR', 'usedNodeVars');
variable(USEDNODEVAR, []);
function nodeVarsInUse($append = false) {
	$vars = variable(USEDNODEVAR);
	if (!$append) return $vars;

	$vars[] = $append;
	sort($vars);
	variable(USEDNODEVAR, $vars);
}

class nodeSettings extends builderBase {
	const one_page = '';

	const two_page = 'level2';

	static function factory($where, $const = self::one_page, $settings = []) {
		if ($where == SITEPATH) {
			variables($settings);
			return;
		}

		if (contains($const, 'level2')) $level = 2;
		else $level = 1;
		autoSetNode($level, $where, $settings);
	}

	static function create($where, $const = self::one_page) {
		return new nodeSettings($where, $const);
	}

	private $where;
	private $const;

	function __construct($where, $const)
	{
		$this->where = $where;
		$this->const = $const;
	}

	function nodeHome($clear = false) {
		return $this->setValue(VARLinkToNodeHome, !$clear);
	}

	function subNodeHome($clear = false) {
		return $this->setValue(VARLinkToSubnodeHome, !$clear);
	}

	function sectionHome($clear = false) {
		return $this->setValue(VARLinkToSectionHome, !$clear);
	}

	function sectionsHaveFiles($clear = false) {
		return $this->setValue(VARSectionsHaveFiles, !$clear);
	}

	function logo($overwrite = true) {
		return $this->setValue(DontOverwriteLogo, !$overwrite);
	}

	function apply($settings = []) {
		$this->settings = array_merge($this->settings, $settings);
		self::factory($this->where, $this->const, $this->settings);
	}
}

function autoSetNode($level, $where, $overrides = []) {
	$section = variable('section');

	nodeVarsInUse($level);
	if (nodeIs(SITEHOME) OR nodeIsSection()) return;

	$relPath = $level == 0 ? nodeValue() : str_replace('\\', '/', 
		substr($where, strlen(SITEPATH . '/' . $section) + 1));
	$endSlug = nodeValue();
	if ($level > 1) { $bits = explode('/', $relPath); $endSlug = array_pop($bits); }

	$prefix = valueIfSet($overrides, 'prefix-safeName') ? variable(VARSafeName) . '-' : '';
	if ($prefix && isset($overrides[VARNodeSafeName]))
		$overrides[VARNodeSafeName] = $prefix . $overrides[VARNodeSafeName];

	$vars = array_merge([
		'nodeSlug' => $relPath,
		assetKey(NODEASSETS) => fileUrl($section . '/' . $relPath . '/assets/'),
		VARNodeSiteName => humanize($endSlug),
		VARNodeSafeName => $prefix . $endSlug,
		VARSubmenuAtNode => true,
		VARNodesHaveFiles => true,
		'nodepath' => $where,
	], $overrides);

	//TODO: develop this
	if ($engage = valueIfSet($vars, 'engage-from')) {
		$source = valueIfSet($engage, 'source', 'opus');
		$folder = valueIfSet($engage, 'folder', );
		$files = valueIfSet($engage, 'files', $endSlug);
		if (!is_array($files)) $files = [$files];
	}

	variable('NodeVarsAt' . $level, $vars);
}

function autosetPageMenu() {
	$section = sectionValue();
	$node = nodeValue();
	if (!$section) return;
	if ($section == $node) return;

	DEFINE('NODEPATH', SITEPATH . '/' . sectionValue() . '/' . $node);
	variables([
		VARNodeSiteName => humanizeThis(),
		VARNodeSafeName => $node,
		VARSubmenuAtNode => true,
		VARNodesHaveFiles => true,
		VARDontOverwriteLogo => $section == 'ideas',
	]);
}

function lastNodeVarsIndex() {
	return count(nodeVarsInUse());
}

function ensureNodeVar() {
	if (count($indices = nodeVarsInUse())) {
		$vars = variable('NodeVarsAt' . end($indices));
		variables($vars);
		$slug = $vars['nodeSlug'];
		variable(assetKey(LEAFNODEASSETS), $vars[assetKey(NODEASSETS)]); //assume required as its always set above
		DEFINE('NODEPATH', $vars['nodepath']);
	} else {
		$slug = nodeValue();
	}
	variable(SAFENODEVAR, $slug);
}
