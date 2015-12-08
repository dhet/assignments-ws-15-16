var express = require('express');
var path = require('path');

var spotify = express.static(path.join(__dirname, "../spotifysearch"));

module.exports = spotify;