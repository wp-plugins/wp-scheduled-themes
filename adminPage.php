<div id="scheduledThemesAdminPage" class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<h2>Scheduled Themes</h2>
<p>This plugin allows a wordpress administrator to schedule a different theme to display on the website for holidays or special events for all visitors.</p>
<p>This plugin brought to you for free by <a href="http://www.itegritysolutions.ca/community/wordpress/scheduled-themes/" target="_blank">ITegrity Solutions</a>.</p>
<?php 
$theme_names = array_keys($this->activeThemes);
natcasesort($theme_names);

$themeSchedule = $this->read_schedule('active');

if($_POST['submit'])
{
?>
<div id="message">New schedule saved successfully.</div>
<?php }?>

<div id='scheduledThemesHeader'>
	<div class='themeCol'>Theme</div>
	<div class='startTimeCol'>Start Date</div>
	<div class='endTimeCol'>End Date</div>
	<div class='repeatYearlyCol'>Yearly</div>
</div>

<!-- This div contains a blank record that will be used by jQuery as a template for adding new records -->
<div id='newThemeTemplate'>
	<div class='scheduledItem' id='newThemeNUMZ'>
		<div class='themeCol'>
			<select name='newThemeNUMZ-themeName' class='themeSelector required'>
				<option value=''>Select Theme</option>
				<?php foreach($theme_names as $theme_name){ ?>
						<option value="<?php echo $theme_name; ?>"><?php echo $theme_name; ?></option>					
				<?php }?>
			</select>
		</div>
		<div class='startTimeCol'>
			<input name='newThemeNUMZ-startTime' type='text' size='11' class='datePicker required' maxlength="10" />
		</div>
		<div class='endTimeCol'>
			<input name='newThemeNUMZ-endTime' type='text' size='11' class='datePicker required' maxlength="10" />
		</div>
		<div class='repeatYearlyCol'>
			<input type='checkbox' name='newThemeNUMZ-repeatYearly' />
		</div>
		<div class='miscCol'>
			<input type='checkbox' name='newThemeNUMZ-delete' class='hiddenInput' />
			<input type="hidden" name='newThemeKeys[]' class='hiddenInput' value='NUMZ' />
			<a class='deleteLink' href='#'>Delete</a>
		</div>
	</div>
</div>
<form method="post" id="scheduledThemesForm">
<div id="scheduledThemesFormError"></div>
    
<div id="scheduledThemes">
	<?php
	wp_nonce_field('scheduledThemesNonceField');
	//loop through each scheduled item, displaying a record to the admin
	foreach($themeSchedule as $scheduledItem)
	{
		$id=$scheduledItem->id;
		$startTime= $scheduledItem->startTime;
		$endTime=$scheduledItem->endTime;
		$themeName=$scheduledItem->themeName;
		$repeatedYearly= '';
		if($scheduledItem->repeatYearly ==1)
			$repeatedYearly= " checked";
		?>
		<div class='scheduledItem' id='scheduledItem-<?php echo $id; ?>'>
			<div class='themeCol'>
				<select name='items<?php echo $id; ?>-themeName' class='themeSelector required'>
					<option value="" >Select Theme</option>
					<?php
					foreach($theme_names as $theme_name){
						$themeSelected="";
						if($theme_name==$themeName)
							$themeSelected='" selected="selected"';
						?>
						<option value="<?php echo $theme_name; ?>" <?php echo $themeSelected?> ><?php echo $theme_name; ?></option>					
						<?php 
					}?>
				</select>
			</div>
			<div class='startTimeCol'>
				<input name='items<?php echo $id; ?>-startTime' type='text' size='11' class='datePicker required' value='<?php echo $startTime; ?>' maxlength="10" />
			</div>
			<div class='endTimeCol'>
				<input name='items<?php echo $id; ?>-endTime' type='text' size='11' class='datePicker required' value='<?php echo $endTime; ?>' maxlength="10" />
			</div>
			<div class='repeatYearlyCol'>
				<input type='checkbox' name='items<?php echo $id; ?>-repeatYearly' <?php echo $repeatedYearly?> />
				<?php //TODO UI get Checkbox centered ?>
			</div>
			<div class='miscCol'>
				<input type='checkbox' name='items<?php echo $id; ?>-delete' class='hiddenInput' />
				<input type='hidden' name='itemKeys[]' class='hiddenInput' value='<?php echo $id; ?>' />
				<a class='deleteLink' href='#'>Delete</a>
			</div>
		</div><?php
	}?>
	</div>
	<div id='bottomControls'>
		<div id='addItem'><a class='addScheduledTheme' href='#'>Add New Scheduled Theme</a></div>
		<div id='submitButton'>
			<input id='submit' class='button-primary' type='submit' value='Save Changes' name='submit' />
		</div>
	</div>
</form>
</div>