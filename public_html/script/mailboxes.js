
$(document).ready(function()
{

	$('#Mailboxes a').click(function(event)
	{
		event.preventDefault();
		
		$('#MailboxMessages').addClass("loading");
		
		$.get(this.href, {}, onMessagesGot);
	});
	
});

function onMessagesGot(data, textStatus, jqXHR)
{
	$('#MailboxMessages').removeClass("loading");
	var ul = $('#MailboxMessages ul');
	
	for(var i in data.headers)
	{
		var messageLi = document.createElement('li');
		
		messageLink = document.createElement('a');
		messageLink.href = "/newsletters/add-message-from-mailbox/mailbox/"
				+ encodeURIComponent(data.mailbox) +"/message/" + encodeURIComponent(data.headers[i].msgno);
		messageLink.appendChild(document.createTextNode(data.headers[i].subject));
		
		messageLi.appendChild(messageLink);		
		ul.append(messageLi);
	}
}