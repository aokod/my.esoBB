<?php
if(!defined("IN_ESO"))exit;
?>

<form action='' method='post'>

<?php

switch ($this->step) {


// Fatal checks.
case "fatalChecks": ?>
<h1><img src='logo.svg' data-fallback='logo.png' alt='Forum logo'/>Uh oh, something's not right!</h1>
<hr/>
<p>The following errors were found with myesoBB's setup. They must be resolved before you can use the installer.</p>
<ul>
<?php foreach ($this->errors as $error) echo "<li>$error</li>"; ?>
</ul>
<p>If you run into any other problems, feel free to join the <a href='https://forum.geteso.org'>esoBB support forum</a> where a bunch of friendly people will be happy to help you out.</p>
<p id='footer'><input class='button' value='Try again' type='submit'/></p>
<hr/>
<p id='version'>Installer version <?php echo $this->getVersion(); ?></p>
<?php break;


// Warning checks.
case "warningChecks": ?>
<h1><img src='logo.svg' data-fallback='logo.png' alt='Forum logo'/>Warning!</h1>
<hr/>
<p>The following errors were found with myesoBB's setup. You can use the installer without resolving them, but some functionality may be limited.</p>
<ul>
<?php foreach ($this->errors as $error) echo "<li>$error</li>"; ?>
</ul>
<p>If you run into any other problems, feel free to join the <a href='https://forum.geteso.org'>esoBB support forum</a> where a bunch of friendly people will be happy to help you out.</p>
<p id='footer'><input class='button' value='Next step &#155;' type='submit' name='next'/></p>
<hr/>
<p id='version'>Installer version <?php echo $this->getVersion(); ?></p>
<?php break;


// Specify setup information.
case "info": ?>
<h1><img src='logo.svg' alt=''/>Create a new forum</h1>
<p class='lead'>Welcome to the myesoBB forum creation wizard.  We need a few details from you so we can get your forum ready to go.
<br/>If you have any trouble, you may get help on the <a href='https://forum.geteso.org'>esoBB support forum</a>.</p>

<fieldset id='basicDetails'><legend>Specify basic details</legend>
<ul class='form'>
<li><label>Forum title</label> <input id='forumTitle' name='forumTitle' tabindex='1' type='text' class='text' placeholder="e.g. Simon's Krav Maga Forum" value='<?php echo @$_POST["forumTitle"]; ?>'/>
<?php if (isset($this->errors["forumTitle"])): ?><div class='warning msg'><?php echo $this->errors["forumTitle"]; ?></div><?php endif; ?></li>

<li><label>Forum description</label> <input id='forumDescription' name='forumDescription' tabindex='2' type='text' class='text' placeholder="e.g. Learn about Krav Maga." value='<?php echo @$_POST["forumDescription"]; ?>'/>
<?php if (isset($this->errors["forumDescription"])): ?><div class='warning msg'><?php echo $this->errors["forumDescription"]; ?></div><?php endif; ?></li>

<li><label>Forum URL</label> <input id='forumURL' name='forumURL' tabindex='2' type='text' class='text' placeholder="kravmaga" value='<?php echo @$_POST["forumURL"]; ?>'/>
<p id='forumDomain'>.myeso.org</p>
<?php if (isset($this->errors["forumURL"])): ?><div class='warning msg'><?php echo $this->errors["forumURL"]; ?></div><?php endif; ?></li>

<li><label>Default language</label> <div><select id='language' name='language' tabindex='3'>
<?php foreach ($this->languages as $language) echo "<option value='$language'" . ((!empty($_POST["language"]) ? $_POST["language"] : "English (casual)") == $language ? " selected='selected'" : "") . ">$language</option>"; ?>
</select><br/>
</div></li>
</ul>
</fieldset>

<fieldset id='adminConfig'><legend>Administrator account</legend>
<p>myesoBB will use the following information to set up the administrator account on your forum.</p>

<ul class='form'>
<li><label>Administrator username</label> <input id='adminUser' name='adminUser' tabindex='15' type='text' class='text' placeholder='Simon' autocomplete='username' value='<?php echo @$_POST["adminUser"]; ?>'/>
<?php if (isset($this->errors["adminUser"])): ?><div class='warning msg'><?php echo $this->errors["adminUser"]; ?></div><?php endif; ?></li>
	
<li><label>Administrator email</label> <input id='adminEmail' name='adminEmail' tabindex='16' type='text' class='text' placeholder='simon@example.com' autocomplete='email' value='<?php echo @$_POST["adminEmail"]; ?>'/>
<?php if (isset($this->errors["adminEmail"])): ?><span class='warning msg'><?php echo $this->errors["adminEmail"]; ?></span><?php endif; ?></li>
	
<li><label>Administrator password</label> <input id='adminPass' name='adminPass' tabindex='17' type='password' class='text' autocomplete='new-password' value='<?php echo @$_POST["adminPass"]; ?>'/>
<?php if (isset($this->errors["adminPass"])): ?><span class='warning msg'><?php echo $this->errors["adminPass"]; ?></span><?php endif; ?></li>
	
<li><label>Confirm password</label> <input id='adminConfirm' name='adminConfirm' tabindex='18' type='password' class='text' autocomplete='off' value='<?php echo @$_POST["adminConfirm"]; ?>'/>
<?php if (isset($this->errors["adminConfirm"])): ?><span class='warning msg'><?php echo $this->errors["adminConfirm"]; ?></span><?php endif; ?></li>
</ul>
</fieldset>

<fieldset id='verifyTurnstile'><legend>Are you human?</legend>

<ul class='form'>
<li><div class='cf-turnstile' data-sitekey='<?php echo $this->captchaKey; ?>'></div>
<?php if (isset($this->errors["validateCaptcha"])): ?><div class='warning msg'><?php echo $this->errors["validateCaptcha"]; ?></div><?php endif; ?></li>
</ul>
</fieldset>

<script type='text/javascript'>Settings.hideFieldset("advancedOptions")</script>

<p id='footer' style='margin:0'><input type='submit' tabindex='26' value='Next step &#155;' class='button'/></p>
<hr class='separator'/>
<p id='version'>Installer version <?php echo $this->getVersion(); ?></p>
<?php break;


// Show an installation error.
case "install": ?>
<h1><img src='logo.svg' alt=''/>Uh oh! It's a fatal error...</h1>
<hr class='separator'/>
<p class='warning msg'>The forum installer encountered an error.</p>
<p>The myesoBB installer has encountered a nasty error which is making it impossible to create your forum. But don't feel down, <strong>here are a few things you can try</strong>:</p>
<ul>
<li><strong>Try again.</strong> Everyone makes mistakes: maybe the computer made one this time.</li>
<li><strong>Go back and check your settings.</strong> In particular, make sure your database information is correct.</li>
<li><strong>Get help.</strong> Go on the <a href='https://forum.geteso.org'>esoBB support forum</a> to see if anyone else is having the same problem as you are. If not, open a new issue, including the error details below.</li>
</ul>

<a href='#' onclick='toggleError();return false'>Show error information</a>
<hr class='aboveToggle'/>
<div id='error'>
<?php echo $this->errors[1]; ?>
</div>
<script type='text/javascript'>
// <![CDATA[
function toggleError() {
	toggle(document.getElementById("error"), {animation: "verticalSlide"});
}
hide(document.getElementById("error"));
// ]]>
</script>
<p id='footer' style='margin:0'>
<input type='submit' class='button' value='&#139; Go back' name='back'/>
<input type='submit' class='button' value='Try again'/>
</p>
<hr/>
<p id='version'>Installer version <?php echo $this->getVersion(); ?></p>
<?php break;


// Finish!
case "finish": ?>
<h1><img src='logo.svg' alt=''/>Congratulations!</h1>
<hr class='separator'/>
<p>Your forum has been installed, and it should be ready to go.</p>
<p>Just go to the forum URL and log in with the administrator account you just created.</p>
<p style='text-align:center' id='footer'><input type='submit' class='button' value='Take me to my forum!' name='finish'/></p>
<hr class='separator'/>
<p id='version'>Installer version <?php echo $this->getVersion(); ?></p>
<?php break;

}
?>

</form>
