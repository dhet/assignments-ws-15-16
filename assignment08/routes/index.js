var express = require('express');
var router = express.Router();
var shoot = require("./shoot");
var path = require("path");

router.use("/shoot/screenshots", express.static(path.join(__dirname, '../screenshots')));

router.use("/shoot", function(req, res, next){
	shoot.takeScreenshot(req.query.url, function(result){
		return res.json(result);
	});
});

module.exports = router;
