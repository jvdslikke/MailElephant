
$(document).ready(function()
{
	$('iframe.heightToContent').load(function()
	{
		this.style.height = this.contentDocument.body.scrollHeight+"px";
	});
});