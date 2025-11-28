<?php

namespace App\Exceptions;

use App\Helpers\YResponse;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;

class EmailNotVerifiedException extends Exception
{
    //
    public function __construct(public User $user)
    {
    }

    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {

            $token = $this->user->createToken(abilities: ['verify-email']);
            $this->user->sendEmailVerificationNotification();
            $data = [
                "token" => $token->plainTextToken,
                "user" => new UserResource($this->user),
                "roles" => $this->user->getRoleNames()
            ];
            return YResponse::json(message: "Your email is not verified, please verify your email.", data: $data, error: "EMAIL_NOT_VERIFIED", status: 406);
        }
    }
}
