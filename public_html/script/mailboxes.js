
$(document).ready(function()
{

	$('#Mailboxes a.mailboxOpener').click(function(event)
	{
		event.preventDefault();
		
		$('#MailboxMessages').addClass("loading");
		
		$.get(this.href, {}, onMessagesGot)
			.error(function(){ alert("opening mailbox failed"); })
			.complete(function() { $('#MailboxMessages').removeClass("loading"); });
	});
	
});

function onMessagesGot(data, textStatus, jqXHR)
{
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