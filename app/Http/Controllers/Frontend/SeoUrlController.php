<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\UrlAlias;

class SeoUrlController extends Controller
{
    /**
     * Dispatch $alias and forward to next
     *
     * @param Request $request
     * @param string $alias
     * @return void
     */
    public function __invoke(Request $request, $alias)
    {
        $alias = UrlAlias::where('alias', $alias)->first();

        if ($alias) {
            $next = $request->duplicate();
            $next->server->set('REQUEST_URI', $alias->uri);

            $app = App::getFacadeRoot();
            $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
            $response = $kernel->handle($next);

            return $response;
        }

        abort(404);
    }
}
