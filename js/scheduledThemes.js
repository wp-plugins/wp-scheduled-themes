var scheduledThemesNewItemNumber=0;

function cloneBlankItemTemplate(e){
	e.preventDefault();
	scheduledThemesNewItemNumber=scheduledThemesNewItemNumber+1;
	
	var html = jQuery('#newItemTemplate').html();
	html = html.replace(/NUMZ/g,scheduledThemesNewItemNumber);
	jQuery('#scheduledItems').append(html);

	jQuery('#newItem' + scheduledThemesNewItemNumber + ' .datePicker').datepicker({ dateFormat: 'yy-mm-dd' });
	jQuery('#newItem' + scheduledThemesNewItemNumber + ' .deleteLink').click(removeScheduledItemFromDOM);
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
	jQuery('#scheduledItems .datePicker').datepicker({ dateFormat: 'yy-mm-dd' });
	jQuery('#ui-datepicker-div').css('clip', 'auto');
	jQuery('.addScheduledItem').click(cloneBlankItemTemplate);
	jQuery('#scheduledItems .deleteLink').click(markScheduledItemForDeletion);
	jQuery('#scheduledItemsForm').validate();
}

jQuery(document).ready(initializeScheduledThemes);