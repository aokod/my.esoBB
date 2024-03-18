<?php
if(!defined("IN_ESO"))exit;
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>

<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>

<title><?php echo ($this->controller->title?$this->controller->title." - ":"")."myesoBB";?></title>
<meta name='title' content='<?php echo ($this->controller->title?$this->controller->title." - ":"")."myesoBB";?>'>
<link type='text/css' rel='stylesheet' href='/install.css'/>

<?php echo $this->head();?> 
</head>

<body>
<?php $this->callHook("pageStart");?>

<div id='loading' style='display:none'>Loading</div>

<?php echo $this->getMessages();?>

<!-- HEADER -->
<div id='header'><ul>
	<li><a href='/'<?php if($this->action=="home"):?> class='active'<?php endif;?>>Home</a></li><li><a href='/create/'<?php if($this->action=="create"):?> class='active'<?php endif;?>>Create a forum</a></li><li><a href='/manage/'<?php if($this->action=="manage"):?> class='active'<?php endif;?>>Manage your forum</a></li><li><a href='/stats/'<?php if($this->action=="stats"):?> class='active'<?php endif;?>>Statistics</a></li>
</ul></div>

<div id='container'>
<?php $this->controller->render();?>
</div>

<?php $this->callHook("pageEnd");?>
</body>
</html>
