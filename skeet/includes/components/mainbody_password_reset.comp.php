To change your password, please enter your username below.  An email will be sent 
to the address you registered with a link that you must follow.  Please note, until
you click this link, your password will not change.
<?
	if(!getRequestValue("do_reset_password") || count($this->getPage()->getErrorMessages()) > 0) {
?>
<div>
	<?= LinkFactory::getLink("PasswordReset")->getLinkAsForm() ?>
	<span class="input_field <?= $this->getPage()->getErrorClass("username") ?>">Username:</span>
	<input type="text" name="username" value="<?= getRequestValue("username") ?>">
</div>
<?
	}
	else {
?>
<div>
	An email has been sent to the address associated with the username "<?= $user->getUsername() ?>". 
	Please make sure that <?= DOMAIN ?> is whitelisted by your spam filter.
</div>
<?
	}
?>	