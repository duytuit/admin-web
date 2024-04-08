/*
 Copyright (c) 2007-2019, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or https://ckeditor.com/sales/license/ckfinder
 */

var config = {};

// Set your configuration options below.

// Examples:
// config.language = 'pl';
// config.skin = 'jquery-mobile';
config.pasteFilter = 'p; a[!href]';
config.extraPlugins = 'uploadimage';
config.uploadUrl = '/uploader/upload.php';
CKFinder.define( config );
