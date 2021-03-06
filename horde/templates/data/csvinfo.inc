<form method="post" name="import_form" action="data.php">
<?php echo Horde_Util::formInput() ?>
<input type="hidden" name="actionID" value="<?php echo (int)$next_step ?>" />
<input type="hidden" name="import_format" value="csv" />
<input type="hidden" name="import_step" value="<?php echo (int)$import_step ?>" />
<h1 class="header">
 <?php printf(_("Import, Step %d"), (int)$import_step) ?>
</h1>
<div class="headerbox text">
    <?php echo _("Here is the beginning of the file:") ?><br />
    <pre><?php echo htmlspecialchars($GLOBALS['session']->get('horde', 'import_data/first_lines')) ?></pre><br />
    <?php echo Horde::label('header', _("Does the first row contain the field names? If yes, check this box:")) ?>
    <input id="header" type="checkbox" name="header" value="1" checked="checked" /><br />
    <?php echo Horde::label('sep', _("What is the delimiter character?")) ?>
    <input type="text" id="sep" name="sep" size="1" value="," /><br />
    <?php echo Horde::label('quote', _("What is the quote character?")) ?>
    <input type="text" id="quote" name="quote" size="1" value="&quot;" /><br />
    <?php echo Horde::label('fields', _("How many fields (columns) are there?")) ?>
    <input type="text" id="fields" name="fields" size="2" value="<?php echo htmlspecialchars($GLOBALS['session']->get('horde', 'import_data/fields')) ?>" /><br />
</div>
<input type="submit" value="<?php echo _("Next") ?>" class="button" />
</form>
