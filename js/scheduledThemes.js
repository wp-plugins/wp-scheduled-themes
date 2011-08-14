var scheduledThemesnewThemeNumber=0;

function cloneBlankItemTemplate(e){
	e.preventDefault();
	scheduledThemesnewThemeNumber=scheduledThemesnewThemeNumber+1;
	
	var html = jQuery('#newThemeTemplate').html();
	html = html.replace(/NUMZ/g,scheduledThemesnewThemeNumber);
	jQuery('#scheduledThemes').append(html);

	jQuery('#newTheme' + scheduledThemesnewThemeNumber + ' .datePicker').datepicker({ dateFormat: 'yy-mm-dd' });
	jQuery('#newTheme' + scheduledThemesnewThemeNumber + ' .deleteLink').click(removeScheduledItemFromDOM);
}

function markScheduledItemForDeletion(e){
	e.preventDefault();
	jQuery(this).prev().prev().attr('checked',true);
	jQuery(this).parent().parent().hide('slow');
}

function removeScheduledItemFromDOM(e){
	e.preventDefault();
	jQuery(this).parent().parent().hide('slow',function(){jQuery(this).remove();});
}

function initializeScheduledThemes(){
	jQuery('#scheduledThemes .datePicker').datepicker({ dateFormat: 'yy-mm-dd' });
	jQuery('#ui-datepicker-div').css('clip', 'auto');
	jQuery('.addScheduledTheme').click(cloneBlankItemTemplate);
	jQuery('#scheduledThemes .deleteLink').click(markScheduledItemForDeletion);
	jQuery('#scheduledThemesForm').validate();
}

jQuery(document).ready(initializeScheduledThemes);