<?php
// Copyright (c) Microsoft Corporation.
// Licensed under the MIT License.

// Access environment through the config helper
// This will avoid issues when using Laravel's config caching
// https://laravel.com/docs/8.x/configuration#configuration-caching
return [
  'appId'             => '',
  'appSecret'         => '',
  'redirectUri'       => '',
  'scopes'            => 'openid profile offline_access user.read.all mailboxsettings.readwrite mail.send calendars.readwrite calendars.readwrite.shared tasks.readwrite files.readwrite.all sites.readwrite.all',
  'authority'         => env('OAUTH_AUTHORITY', 'https://login.microsoftonline.com/'),
  'authorizeEndpoint' => env('OAUTH_AUTHORIZE_ENDPOINT', '/oauth2/v2.0/authorize'),
  'tokenEndpoint'     => env('OAUTH_TOKEN_ENDPOINT', '/oauth2/v2.0/token'),
];