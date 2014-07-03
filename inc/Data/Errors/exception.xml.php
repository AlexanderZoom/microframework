<?php echo sprintf('<?xml version="1.0" encoding="%s" ?>', Config::getVar('app_charset'))."\n" ?>
<error code="<?php echo $code ?>" message="<?php echo $text ?>">
  <debug>
    <name><?php echo $name ?></name>
    <message><?php echo htmlspecialchars($message, ENT_QUOTES, Lib_Config::getVar('app_charset')) ?></message>
    <traces>
<?php foreach ($traces as $trace): ?>
        <trace><?php echo $trace ?></trace>
<?php endforeach; ?>
    </traces>
  </debug>
</error>
