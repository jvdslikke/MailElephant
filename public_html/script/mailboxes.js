
$(document).ready(function()
{

	$('#Mailboxes a').click(function(event)
	{
		event.preventDefault();
		
		$.get(this.href, {}, onMessagesGot);
	});
	
});

function onMessagesGot(data, textStatus, jqXHR)
{
	var ul = $('#MailboxMessages');
	
	for(var i in data)
	{
		var messageLi = document.createElement('li');
		messageLi.appendChild(document.createTextNode(data[i].subject));
		ul.append(messageLi);
	}
}