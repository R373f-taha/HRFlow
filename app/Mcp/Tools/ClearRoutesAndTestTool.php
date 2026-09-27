<?php

// namespace App\Mcp\Tools;

// use Laravel\Mcp\Server\Tool;
// use Illuminate\Support\Facades\Artisan;

// class ClearRoutesAndTestTool extends Tool
// {
//     /**
//      * The tool's name exposed to the MCP client.
//      */
//     protected string $name = 'clear-routes-and-test';

//     /**
//      * Tool description for AI agents.
//      */
//     protected string $description = 'Clears Laravel route cache and runs the Pest test suite.';

//     /**
//      * Handle the tool execution.
//      */
//     public function handle(): string
//     {
//         Artisan::call('route:clear');

//         $output = shell_exec('vendor/bin/pest --compact');

//         return "Route cache cleared successfully!\n\nPest Test Results:\n" . ($output ?? 'No output received.');
//     }
// }

namespace App\Mcp\Tools;

use Illuminate\Support\Facades\Artisan;
use Laravel\Mcp\Server\Tool;

class ClearRoutesAndTestTool extends Tool
{
    protected string $name = 'clear-routes-and-test';


    protected string $description = 'Clears Laravel route cache and executes the Pest test suite.';


    public function handle(): string
    {

        Artisan::call('route:clear');

        // Run pest in compact mode with reduced overhead
        $output = shell_exec('vendor/bin/pest --compact --bail');

        return "Route cache cleared successfully!\n\nPest Test Results:\n".($output ?? 'No output received.');

    }
}
