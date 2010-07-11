<?
	namespace Skeet;
	$loginStyle = 'style="display: none;"';
	$registerStyle = 'style="display: none;"';
	
	if(getRequestValue("do_register")) {
		$registerStyle = "";
	}
	
	if(getRequestValue("do_login")) {
		$loginStyle = "";
	}
?>

	<div>
		<a href="#" onclick="$('login').appear({duration: .3}); return false;">log in</a>
		&nbsp;&nbsp;
		<a href="#" onclick="$('register').appear({duration: .3}); return false;">register</a>
	</div>
<?
	if(count($this->getPage()->getErrorMessages())) {
?>
	<ul>
	<?
		foreach($this->getPage()->getErrorMessages() as $errorMessage) {
	?>
		<li class="<?= $this->getPage()->getBadClass() ?>"><?= $errorMessage ?></li>
	<?
		}
	?>
	</ul>
<?
	}
?>	
	<div id="login" <?= $loginStyle ?>>
		<h1>Login</h1>
		<?= LinkFactory::getLink("Login")->getLinkAsForm() ?>
		<input type="hidden" name="do_login" value="1">
		<table width="400" cellspacing=0 cellpadding=0 border=0>
			<tr>
				<td class="input_field <?= $this->getPage()->getErrorClass("username") ?>">Username:</td>
				<td>
					<input type="text" name="username" id="username" value="<?= getRequestValue("username") ?>">
				</td>
			</tr>
			<tr>
				<td class="input_field <?= $this->getPage()->getErrorClass("password") ?>">Password:</td>
				<td>
					<input type="password" name="password" id="password" value="<?= getRequestValue("password") ?>">
				</td>
			</tr>
		</table>
		<a href="<?= LinkFactory::getLink("PasswordReset")->getLink() ?>">I forgot my password.</a>
		<div class="right small_pad">
			<input type="submit" value="login">
			&nbsp;
			<a href="#" onclick="$('login').fade({duration: .3}); return false;">cancel</a>
		</div>
		
		</form>
	</div>
	<div id="register" <?= $registerStyle ?>>
		<?= LinkFactory::getLink("Login")->getLinkAsForm() ?>
		<input type="hidden" name="do_register" value="1">
		<h1>Register</h1>
		<table width="400" cellspacing=0 cellpadding=0 border=0>
			<tr>
				<td class="input_field <?= $this->getPage()->getErrorClass("username") ?>">Username:</td>
				<td>
					<input type="text" name="username" id="username" value="<?= getRequestValue("username") ?>">
				</td>
			</tr>
			<tr>
				<td class="input_field <?= $this->getPage()->getErrorClass("password") ?>">Password:</td>
				<td>
					<input type="password" name="password" id="password" value="<?= getRequestValue("password") ?>">
				</td>
			</tr>
			<tr>
				<td class="input_field <?= $this->getPage()->getErrorClass("password_confirm") ?>">Password Confirm:</td>
				<td>
					<input type="password" name="password_confirm" id="password_confirm" value="<?= getRequestValue("password_confirm") ?>">
				</td>
			</tr>
			<tr>
				<td class="input_field <?= $this->getPage()->getErrorClass("email_address") ?>">Email Address:</td>
				<td>
					<input type="text" name="email_address" id="email_address" value="<?= getRequestValue("email_address") ?>">
				</td>
			</tr>
			<tr>
				<td class="input_field <?= $this->getPage()->getErrorClass("first_name") ?>">First Name:</td>
				<td>
					<input type="text" name="first_name" id="first_name" value="<?= getRequestValue("first_name") ?>">
				</td>
			</tr>
			<tr>
				<td class="input_field <?= $this->getPage()->getErrorClass("last_name") ?>">Last Name:</td>
				<td>
					<input type="text" name="last_name" id="last_name" value="<?= getRequestValue("last_name") ?>">
				</td>
			</tr>						
			
		</table>
		<div class="right small_pad">
			<input type="submit" value="register">
			&nbsp;
			<a href="#" onclick="$('register').fade({duration: .3}); return false;">cancel</a>
		</div>
		</form>
	</div>