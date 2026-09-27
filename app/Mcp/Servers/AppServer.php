<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ClearRoutesAndTestTool;
use Laravel\Mcp\Server;

class AppServer extends Server
{
 
    protected array $tools = [
        ClearRoutesAndTestTool::class,
    ];
}
