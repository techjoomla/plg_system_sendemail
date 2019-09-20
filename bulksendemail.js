/*
 * @version    SVN:<SVN_ID>
 * @package    TJsendemail
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */


var tjutilitysendemail = {
    initialize: function(tableContainer) {
		var isSendEmail = jQuery('body').find('.td-sendemail').length;
		var isCheckboxes = jQuery('body').find('input[name="cid[]"]').length;

		if (isSendEmail)
		{
			// If on list view already checkbox are present then avoid the add column function
			if (!isCheckboxes)
			{
				this.addColumn(tableContainer);
			}

			this.btnSendEmail();
		}
    },
    addColumn: function(tblId)
	{
		var tr = document.getElementById(tblId).tHead.children[0];
		tr.insertCell(0).outerHTML = '<th><input type="checkbox" name="checkall-toggle" value="" class="hasTooltip" title="Check All Items" onclick="Joomla.checkAll(this)"></th>'

		var tblBodyObj = document.getElementById(tblId).tBodies[0];
		for (var i=0; i<tblBodyObj.rows.length; i++) {
			var newCell = tblBodyObj.rows[i].insertCell(0);
			newCell.innerHTML = '<input type="checkbox" id="cb0" name="cid[]" value="' + i + '" onclick="Joomla.isChecked(this.checked);">'
		}
	},
	btnSendEmail: function () {
		try {
			var alertMessage = 'alert("Please select recods");';
			let btnHtml = '<div class="btn-wrapper" id="tj-sendemail" style="float: right;">';
					btnHtml += '<button type="button" class="btn btn-primary" id="email-queue-column" data-toggle="modal" data-target="#builkEmailModal" onclick="tjutilitysendemail.openEmailPopup();">';
						btnHtml += Joomla.JText._('PLG_SYSTEM_SENDEMAIL_BTN') ;
					btnHtml += '</button>';
				btnHtml += '</div>';

			jQuery('#toolbar').append(btnHtml);

		}
		catch (err) {
			/*console.log(err.message);*/
		}
	},
	openEmailPopup: function () {
		try {

			if (document.adminForm.boxchecked.value==0)
			{
				alert(Joomla.JText._('PLG_SYSTEM_SENDEMAIL_SELECT_CHECKBOX'));
				return false;
			}

			// Remove below line removeclass it is temp added
			jQuery("div").find('.tjlms-wrapper').removeClass("tjBs3");

			let modelEmail = '<div id="builkEmailModal" class="emailModal modal fade" role="dialog">';
					modelEmail += '<div class="modal-dialog">';
						modelEmail += '<div class="modal-content">';
						modelEmail += '<div id="preload"><img src="http://i.imgur.com/KUJoe.gif"></div>';
								modelEmail += '<div class="modal-header">';
									modelEmail += '<div class="row-fluid">';
										modelEmail += '<div class="span10">';
											modelEmail += '<h5 class="modal-title" id="exampleModalLabel">' + Joomla.JText._('PLG_SYSTEM_SENDEMAIL_POPUP_HEADING') + '</h5>';
										modelEmail += '</div>';
										modelEmail += '<div class="span2">';
											modelEmail += '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
										modelEmail += '</div>';
									modelEmail += '</div>';
									modelEmail += '<span id="errorMessage"></span>';
								modelEmail += '</div>';

								modelEmail += '<div class="modal-body">';
									modelEmail += '<div class="container-fluid">';
										modelEmail += '<form action="#" method="post" enctype="multipart/form-data" name="emailTemplateForm" id="emailTemplateForm" class="form-validate">';
											modelEmail += '<div class="row-fluid">';
												modelEmail += '<div class="span12">';
													modelEmail += '<div class="control-group">';
														modelEmail += '<div class="control-label">';
															modelEmail += '<label id="email-subject-label" for="email-subject" class="hasPopover required" title="" >';
																modelEmail += Joomla.JText._('PLG_SYSTEM_SENDEMAIL_POPUP_EMAIL_SUBJECT') + ' <span class="star">&nbsp;*</span>';
															modelEmail += '</label>';
														modelEmail += '</div>';

														modelEmail += '<div class="controls">';
															modelEmail += '<input type="text" name="template[subject]" id="email-subject" value="" class="required" required="required" aria-required="true">';
														modelEmail += '</div>';
													modelEmail += '</div>';
													modelEmail += '<div class="control-group">';
														modelEmail += '<div class="control-label">';
															modelEmail += '<label id="email-message-label" for="email-message" class="hasPopover required" title="" >';
																modelEmail += Joomla.JText._('PLG_SYSTEM_SENDEMAIL_POPUP_EMAIL_BODY_MESSAGE') + ' <span class="star">&nbsp;*</span>';
															modelEmail += '</label>';
														modelEmail += '</div>';
														modelEmail += '<div class="controls">';
															modelEmail += '<textarea name="template[message]" rows="7" style="width: 30%;" id="email-message" value="" class="required" required="required" aria-required="true"></textarea>';
														modelEmail += '</div>';
													modelEmail += '</div>';
												modelEmail += '</div>';
												modelEmail += '<div id="emailsDiv"></div>';
											modelEmail += '</div>';
										modelEmail += '</div>';
									modelEmail += '</form>';
								modelEmail += '</div>';
								modelEmail += '<div class="modal-footer">';
									modelEmail += '<button type="button" class="btn btn-primary validate" id="send-email" onclick="tjutilitysendemail.sendEmailToUser();">' + Joomla.JText._('PLG_SYSTEM_SENDEMAIL_POPUP_SEND_BTN') + '</button>';
								modelEmail += '</div>';
						modelEmail += '</div>';
					modelEmail += '</div>';
				modelEmail += '</div>';

			// Confirm this class to append popup
			jQuery('#j-main-container').append(modelEmail);

			jQuery('div').find("#preload").hide();
			jQuery('div').find("#send-email").attr("disabled", false);
			jQuery("#email-subject").val('');
			jQuery("#email-message").val('');

			var values = new Array();
			jQuery("#emailsDiv").empty();

			jQuery.each(jQuery("input[name='cid[]']:checked").closest("td").siblings("td.td-sendemail"), function () {
				values.push(jQuery(this).text());

				var hiddenEle = "<input readonly type='hidden' name='emails[]' value='" + jQuery(this).text() + "'/>";
				jQuery("#builkEmailModal").find("#emailsDiv").append(hiddenEle);
			});

			// alert(values.join (", "));
		}
		catch (err) {
			/*console.log(err.message);*/
		}
	},
	sendEmailToUser: function () {
		var emailSubjectValue = jQuery("#email-subject").val();
		var emailMessageValue = jQuery("#email-message").val();
		var invalidCount = 0;

		if (!emailSubjectValue)
		{
			invalidCount = 1;

			jQuery('#email-subject').addClass("invalid");
			jQuery('#email-subject-label').addClass("invalid");
		}

		if (!emailMessageValue)
		{
			invalidCount = 1;

			jQuery('#email-message').addClass("invalid");
			jQuery('#email-message-label').addClass("invalid");
		}

		if (invalidCount)
		{
			return false;
		}

		jQuery('#preload').show();
		jQuery('#send-email').attr("disabled", true);
		var chunk_size = 5;

		var postData = jQuery("#emailTemplateForm").serializeArray();

		var emailContent = postData.splice(0,2);

		// Remove duplicate values from email array
		postData = postData.map(JSON.stringify).reverse() // convert to JSON string the array content, then reverse it (to check from end to begining)
		.filter(function(item, index, postData){ return postData.indexOf(item, index + 1) === -1; }) // check if there is any occurence of the item in whole array
		.reverse().map(JSON.parse) // revert it to original state

		// Chuck of 10 emails
		var emails = postData.map( function(e,i){
			return i%chunk_size===0 ? postData.slice(i,i+chunk_size) : null;
		}).filter(function(e){ return e; });

		var batchCount = emails.length;

		jQuery.each(emails, function (i, batch) {
			var emailData = batch.concat(emailContent);

			// Call the send email function
			tjutilitysendemail.tjSendEmail(emailData, i, batchCount);
		});
	},
	tjSendEmail: function (emailData, index, batchCount) {
		index ++;

		var url = "index.php?option=com_ajax&plugin=plg_System_Sendemail&format=json";

		var promise = jQuery.ajax({url: url, type: 'POST', async:true, data:emailData,dataType: 'json'});

		promise.fail(
			function(response) {
				console.log('Something went wrong.');

				var errormsg = '<div class="alert alert-error alert-danger"><button type="button" data-dismiss="alert" class="close">×</button><h4 class="alert-heading"></h4><div>' + response.message + '</div></div>';
				jQuery("#builkEmailModal").find("#errorMessage").append(errormsg);
				jQuery('#preload').hide();
			}
		).done(
			function(response) {
				if (index == batchCount)
				{
					jQuery('#preload').hide();
					jQuery('#builkEmailModal').modal('hide');

					 // Remove below line removeclass it is temp added
					 jQuery("div").find('.tjlms-wrapper').addClass("tjBs3");

					 Joomla.renderMessages({"success":[response.message]});
				}
			}
		).always(
			function(response) {
				// console.log('response always.' , response);
			}
		);
	}
};
