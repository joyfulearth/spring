<?php
class socialBuilder {
	const variableName = 'social';
	const HR = '----';
	const shareBtn = 'bi bi-send-plus bg-success';
	const dawnBtn = 'bi bi-heart-pulse bg-danger';
	const imranBtn = 'bi bi-heart-pulse bg-warning';
	const springBtn = 'bi bi-tools bg-warning';

	private $items = [];
	function getItems() { return $this->items; }

	static function default($builtin = true, $hr = 'after') {////$hr = before|after|both
		return self::create()
			->setSocialCaption()
			->addHR($hr == 'after')
			->addBuiltin($builtin ? self::readme : false, $builtin ? self::license : false)
			->addSignup()
			->addSmithy()
			->addHR($hr == 'before')
			->getItems();
	}

	static function create($items = []) {
		$r = new socialBuilder();
		if (!empty($items)) {
			foreach ($items as &$item) {
				if ($item === self::HR) continue;
				$url = $item['url'];
				if (
					startsWith($url, self::url_instagram) OR
					startsWith($url, self::url_linkedin) OR
					startsWith($url, self::url_youtube) OR
					startsWith($url, self::url_github)
				) $item['url'] = $item['url'] . NOFOLLOWSUFFIX;
			}
			//showDebugging(25, $items);
			$r->items = $items;
		}
		return $r;
	}

	function setSocialCaption($text = 'Social and Quick Links') {
		variable('social-caption', $text);
		return $this;
	}

	function addHR($skip = false) {
		if (!$skip) $this->items[] = self::HR;
		return $this;
	}

	private function add($type, $url, $name) {
		$this->items[] = [ 'type' => $type, 'url' => $url, 'name' => $name ];
		return $this;
	}

	//#region begin
	function addSpecial($url, $name, $type) {
		extract(specialLinkVars(compact('url', 'name', 'type')));
		return $this->add($type, $url, $name);
	}

	function addExternal($type, $absUrl, $name, $nofollow = true) {
		if ($nofollow) $absUrl .= NOFOLLOWSUFFIX;
		return $this->add($type, $absUrl, $name);
	}

	function addInternal($relUrl, $name, $type, $skip = false) {
		if ($skip) return $this;
		if (!contains($relUrl, 'http') AND !isSpecialLink($relUrl))
			$relUrl = pageUrl($relUrl);
		return $this->add($type, $relUrl, $name);
	}

	//#region begin
	const readme = 'fa fa-book-reader bg-success';
	const license = 'fa fa-legal bg-danger';
	function addBuiltin($readme = self::readme, $license = self::license) {
		return $this
			->addInternal('readme', 'ReadMe', $readme, !$readme)
			->addInternal('license', 'License', $license, !$license);
	}

	function addSignup($class = 'fa fa-door-open bg-info') {
		return $this->addExternal($class, getSpecialUrl('signup'), 'AW Signup', false);
	}

	function addSmithy($class = 'bi bi-tools bg-secondary') {
		return $this->addExternal($class, getSpecialUrl('smithy'), 'AW Smithy', false);
	}

	//#region begin
	const instagram = 'instagram';
	private const url_instagram = 'https://www.instagram.com/';
	function addInstagram($relUrl, $name, $type = self::instagram, $skip = false) {
		if ($skip) return $this;
		return $this->addExternal($type, self::url_instagram . $relUrl, $name);
	}

	const linkedin = 'linkedin';
	private const url_linkedin = 'https://www.linkedin.com/';
	function addLinkedIn($relUrl, $name, $type = self::linkedin, $skip = false) {
		if ($skip) return $this;
		return $this->addExternal($type, self::url_linkedin . $relUrl, $name);
	}

	const youtube = 'youtube';
	private const url_youtube = 'https://www.youtube.com/';
	function addYoutube($relUrl, $name, $type = self::youtube, $skip = false) {
		if ($skip) return $this;
		return $this->addExternal($type, self::url_youtube . $relUrl, $name);
	}

	const github = 'github';
	private const url_github = 'https://www.github.com/';
	function addGithub($relUrl, $name, $skip = false, $type = self::github) {
		if ($skip) return $this;
		return $this->addExternal($type, self::url_github . $relUrl, $name);
	}

	//#region begin
	function addShare() {
		return $this
			->addInternal(getPageParameters(VARSlash . features::shareQS), 'Share Via&hellip;', self::shareBtn);
	}

	function addImranPersonal($who = true, $technologist = true, $builder = true) {
		$base = getSiteUrl(SITEIMRAN);
		return $this
			->addInternal($base . 'whoami/on-linkedin/', 'Who Is Imran', 'fa-brands fa-redhat bg-danger', !$who)
			->addInternal($base . 'whoami/the-technologist/', 'The IT Guy', 'fa-brands fa-linkedin bg-linkedin text-light', !$technologist)
			->addInternal($base . '#dare-i-build', 'Darfe I Build', self::imranBtn, !$builder)
			;
	}

	function addDawn($linkedIn = true, $youtube = true) {
		return $this
			->addLinkedIn('company/amadeusweb/', 'Amadeus Web', self::linkedin, !$linkedIn)
			->addYoutube('@imran-thrives', 'Imran from DAWN', self::youtube, !$youtube);
	}

	function addThisSitesGithub() {
		return $this
				->addGithub(variable('github-repo'), 'This Site', !variable('github-repo'));
	}

	function addGithubGroup() {
		return $this
			->addHR()
			->addGithub('amadeus-web-world/', 'AW World')
			->addGithub('amadeus-web-world/spring', 'AW Spring')
			->addGithub(variable('github-repo'), 'This Site', !variable('github-repo'))
			;
	}

	function addUtilityGroup() {
		return $this
			->addHR()
			->addShare()
			->addInternal(getSiteUrl(SITEROOT), 'DAWN', self::dawnBtn)
			->addInternal(getSiteUrl(SITESPRING), 'AW Spring', self::springBtn)
			;
	}
}
