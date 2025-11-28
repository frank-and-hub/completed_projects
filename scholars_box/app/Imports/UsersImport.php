<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Hash;

class UsersImport implements ToModel, WithHeadingRow
{
 
    public function model(array $row)
    {
        $existingUser = User::where('email', $row['email'])->first();

        if ($existingUser) {
            // Throw a custom exception with a user-friendly message
            throw new \Exception("User with email '{$row['email']}' already exists.");
        }

        // Create a new user if the email is not a duplicate
        return new User([
            'first_name' => $row['name'],
            'email' => $row['email'],
            'role_id' => $row['role'],
        ]);
    }
}