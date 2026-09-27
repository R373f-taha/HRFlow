<?php

use App\Mcp\Servers\AppServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('laravel-boost', AppServer::class);

Mcp::web('/mcp', AppServer::class);
