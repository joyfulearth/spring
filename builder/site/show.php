<?php
getSiteUrlKey();
runFrameworkFile('site/network');

setTheme();
setSubTheme(VARSubthemeGo);

variables([
	VARMediakit => '?palette=1',
	VARNode => SITEHOME,
	'name' => substr(NETWORKNAME . SITELISTNAME, 1),
	VARFooterMessage => 'Proud Member of "' . NETWORKABBR . VARQUOTE,

	VARChatraID => VARUseAmadeusWeb,
	VARGoogleAnalytics => VARUseAmadeusWeb,
	VAREmail => VARSystemEmail,
	VARPhone => $ph1 = VARSystemMobile,
	VARWhatsapp => $ph1,
	VARAddress => VARSystemAddress,
	VARNetwork => 'Webring',
]);

add_body_class('showing-sites');
addStyle('v9-spring', COREASSETS);
addStyle('v9-features', COREASSETS);

DEFINE('SITEPATH', SHOWSITESAT);
runThemePart('header');
runFrameworkFile('site/listing');
runThemePart('footer');
