<?php

namespace App\Http\Controllers;

use App\ContactMe;
use Illuminate\Http\Request;

class ContactMeController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'first-name' => 'max:100',
            'last-name'  => 'max:100',
            'email'      => 'max:100',
            'comments'   => 'required|max:3000'
        ]);

        $contactMe = new ContactMe();
        $contactMe->first_name = $request->input('first-name');
        $contactMe->last_name = $request->input('last-name');
        $contactMe->email = $request->input('email');
        $contactMe->comments = $request->input('comments');

        $contactMe->save();

        return $this->respondCreated('contact', $contactMe);
    }
}