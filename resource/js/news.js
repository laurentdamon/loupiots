$(document).ready(function() {
	var siteUrl = $("#siteUrl").text();
	var getNewsURL = siteUrl + "/news/viewLastNews";
	$.ajax({
		url : getNewsURL,
		type : "GET",
		dataType : "html",
		success : function(result) {
		$("#newsContent").html(result);
		}
	});
});