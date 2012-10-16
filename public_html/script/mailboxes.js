
$(document).ready(function()
{
	$('#Mailboxes a.mailboxOpener').click(function(event)
	{
		event.preventDefault();
		
		$('#MailboxMessages table tbody').empty();
		$('#MailboxMessages').addClass("loading");
		
		$.get(this.href, {}, onMessagesGot)
			.error(function(){ alert("opening mailbox failed"); })
			.complete(function() { $('#MailboxMessages').removeClass("loading"); });
	});
	
});

function onMessagesGot(data, textStatus, jqXHR)
{
	var tbody = $('#MailboxMessages table tbody');
	
	for(var i in data.headers)
	{
		var messageHref = "/newsletters/add-message-from-mailbox/mailbox/"
			+ encodeURIComponent(data.mailbox) +"/message/" + encodeURIComponent(data.headers[i].msgno);
		
		var messageTr = document.createElement('tr');
		
		var from = data.headers[i].fromName;
		if(!from)
		{
			from = data.headers[i].fromEmail;
		}
		
		var fromLink = document.createElement('a');
		fromLink.href = messageHref;
		fromLink.appendChild(document.createTextNode(from));
		
		var fromTd = document.createElement('td');
		fromTd.appendChild(fromLink);
		messageTr.appendChild(fromTd);
		
		var subjectLink = document.createElement('a');
		subjectLink.href = messageHref;
		subjectLink.appendChild(document.createTextNode(data.headers[i].subject));
		
		var subjectTd = document.createElement('td');
		subjectTd.appendChild(subjectLink);
		messageTr.appendChild(subjectTd);
		
		tbody.append(messageTr);
	}
}