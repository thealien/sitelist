<form action="<?php echo $this->controller->createUrl('user/openid'); ?>" method="get" id="openid_form" class='forms' style='width: auto;'>
	<div id="openid_choice">
	    <div id="openid_btns">&nbsp;</div>
	</div>
	<div id="openid_input_area">
	    <input id="openid_identifier" name="openid_identifier" type="text" value="http://" />
		&nbsp;
	    <input id="openid_submit" type="submit" value="Войти"/>
	</div>
	<noscript>
	    <p>OpenID это сервис который позволяет вам входить в разные веб-сайты пользуясь одной учетной записью.
			Узнайте <a href="http://openid.net/what/">больше о OpenID</a> и <a href="http://openid.net/get/">как получить OpenID аккаунт</a>.</p>
	</noscript>
</form>