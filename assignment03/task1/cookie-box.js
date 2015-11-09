"use strict";

if (typeof jQuery == "undefined") {
	console.log("asdf");
	document.write(
		"<script type='text/javascript' src='http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.3.min.js'></" +
		"script>");
}

$(document).ready(function() {
	$("head").append('<link rel="stylesheet" href="cookie-box.css" type="text/css" ></link>');
	$("head").append('<script src="js.cookie.js"></script>');


	var cookieBox = $("<div id='cookieBox'>").appendTo("body");
	cookieBox.html("This site uses cookies.");
	var closeButton = $("<a href='#' id='close'>").appendTo(cookieBox);
	closeButton.html("X");

	function setCookie() {
		Cookies.set("visited", true);
		console.log("cookie set");
	}

	function removeCookie() {
		Cookies.remove("visited");
		console.log("cookie removed");
	}

	function hideBox() {
		$("#cookieBox").hide();
		setCookie();
	}

	function onScroll() {
		var body = document.body,
			html = document.documentElement;
		var scrollPos = document.body.scrollTop + window.innerHeight;
		var docHeight = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight,
			html.offsetHeight);
		if (scrollPos >= docHeight) {
			hideBox();
		}
	}

	if (!Cookies.get("visited")) {
		$("#cookieBox").show();
	}

	setTimeout(setCookie, 10000);

	var clicks = 0;
	$(document).click(function() {
		clicks++;
		if (clicks == 3) {
			hideBox();
		}
	});

	$(window).scroll(onScroll);
	$("#remove").click(removeCookie);
	closeButton.click(hideBox);
});