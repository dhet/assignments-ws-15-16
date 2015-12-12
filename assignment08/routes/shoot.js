var express = require("express");
// var router = express.Router();
var webshot = require("webshot");
var path = require("path");
var urlHelper = require("url");
var fs = require("fs");

const screenshotDir = "/shoot/screenshots/";
const errorImage = screenshotDir + "caution.png";

// router.use(screenshotDir, express.static(path.join(__dirname, '../screenshots')));

function createFilename(inputUrl){
	var url = urlHelper.parse(inputUrl);
	var filename = url.host + url.path + (url.hash || "");
	filename = filename.replace(/^www\d*?\./g, ""); // remove preceding www.
	filename = filename.replace(/[^a-z0-9]/gi, '_').toLowerCase(); // make filename file system safe
	filename += ".png";
	return filename;
};

function createErrorResponse(message){
	return {
		status: "error",
		path: errorImage,
		message: message
	};
};

function createSuccessResponse(filename, message){
	return {
		status: "ok",
		path: screenshotDir + filename,
		message: message
	};
};

function urlIsValid(url){
	return urlHelper.parse(url).host;
};

this.takeScreenshot = function(inputUrl, callback){
	if(inputUrl && !inputUrl.startsWith("http://")) inputUrl = "http://" + inputUrl;

	if(!urlIsValid(inputUrl)){ // invalid URL
		callback(createErrorResponse("Invalid URL"));
	} else{
		var filename = createFilename(inputUrl);
		var fsPath = path.join(__dirname, "../screenshots/" + filename);
		if(fs.existsSync(fsPath)){
			callback(createSuccessResponse(filename, "Re-used screenshot"));
		} else{
			webshot(inputUrl, fsPath, function(err){
				var result = err ? 
					createErrorResponse("Error taking screenshot") :
					createSuccessResponse(filename, "Created new screenshot");
				callback(result);
			});
		}
	}
};

module.exports = this;
