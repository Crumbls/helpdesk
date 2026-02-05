<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomFieldController extends ApiController
{
	public function getModel(): string
	{
		return Models::customField();
	}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
	    abort(500);
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
	    abort(500);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
	    abort(500);
        //
    }
}
